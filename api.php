<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

/* ---------- Helpers ---------- */
function reply(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}
class ApiError extends Exception
{
}
/* fail() throws, so any open database transaction is rolled back before the error is sent */
function fail(string $message, int $code = 422): never
{
    throw new ApiError($message, $code);
}
function require_login(): array
{
    if (empty($_SESSION['user']))
        reply(['error' => 'Please sign in first.'], 401);
    return $_SESSION['user'];
}
function need_admin(): array
{
    $u = require_login();
    if ($u['role'] !== 'admin')
        fail('Only an administrator can do this.', 403);
    return $u;
}
function body(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : $_POST;
}
function rows(PDO $pdo, string $sql, array $args = []): array
{
    $q = $pdo->prepare($sql);
    $q->execute($args);
    return $q->fetchAll();
}
function text(array $in, string $key): string
{
    return trim((string) ($in[$key] ?? ''));
}
function now(): string
{
    return date('Y-m-d H:i:s');
}

/* Next running number, e.g. seq('purchase_orders','po_number','PO-') returns 2046 */
function seq(PDO $pdo, string $table, string $col, string $prefix): int
{
    $q = $pdo->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING($col, " . (strlen($prefix) + 1) . ") AS UNSIGNED)), 0) FROM $table WHERE $col LIKE ?");
    $q->execute([$prefix . '%']);
    return (int) $q->fetchColumn() + 1;
}

/* Every stock change goes through here so the log always matches the stock. The reference is never typed by a person. */
function add_movement(PDO $pdo, array $product, string $type, int $qty, string $ref, string $note, string $by): string
{
    $no = 'MOV-' . str_pad((string) seq($pdo, 'stock_movements', 'movement_no', 'MOV-'), 5, '0', STR_PAD_LEFT);
    $pdo->prepare('INSERT INTO stock_movements (movement_no,moved_at,product_sku,product_name,type,quantity,reference,note,recorded_by) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([$no, now(), $product['sku'], $product['name'], $type, $qty, $ref !== '' ? $ref : $no, $note, $by]);
    $pdo->prepare('UPDATE products SET stock = stock + ? WHERE sku = ?')->execute([$qty, $product['sku']]);
    return $no;
}
function lock_product(PDO $pdo, string $sku): array
{
    $p = rows($pdo, 'SELECT * FROM products WHERE sku = ? AND is_archived = 0 FOR UPDATE', [$sku])[0] ?? null;
    if (!$p)
        fail('That product could not be found.');
    return $p;
}
function archive_row(PDO $pdo, string $table, string $col, string $key, array $user, string $reason): void
{
    $pdo->prepare("UPDATE $table SET is_archived=1, archived_by=?, archived_at=?, archive_reason=? WHERE $col=?")->execute([$user['name'], now(), $reason, $key]);
}
function restore_row(PDO $pdo, string $table, string $col, string $key): void
{
    $pdo->prepare("UPDATE $table SET is_archived=0, archived_by=NULL, archived_at=NULL, archive_reason=NULL WHERE $col=?")->execute([$key]);
}
function in_tx(PDO $pdo, callable $fn): mixed
{
    $pdo->beginTransaction();
    try {
        $r = $fn();
        $pdo->commit();
        return $r;
    } catch (Throwable $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        throw $e;
    }
}

/* ---------- Read data ---------- */
function sale_items(PDO $pdo, string $receipt): array
{
    $items = rows($pdo, 'SELECT product_sku AS sku, product_name AS name, quantity AS qty, unit_price AS price FROM sale_items WHERE sale_receipt = ?', [$receipt]);
    foreach ($items as &$i) {
        $i['qty'] = (int) $i['qty'];
        $i['price'] = (float) $i['price'];
    }
    return $items;
}
function sale_rows(PDO $pdo, int $archived): array
{
    $sales = rows($pdo, "SELECT receipt_no AS no, DATE_FORMAT(sold_at, '%Y-%m-%dT%H:%i:%s') AS date, COALESCE(NULLIF(customer,''),'Walk-in customer') AS customer, payment_method AS pay, status, total, amount_received AS received, change_amount AS `change`, payment_reference AS ref, cashier, archived_by, archived_at, archive_reason FROM sales WHERE is_archived = ? ORDER BY sold_at DESC, id DESC", [$archived]);
    foreach ($sales as &$s) {
        foreach (['total', 'received', 'change'] as $k)
            $s[$k] = (float) $s[$k];
        $s['items'] = sale_items($pdo, $s['no']);
    }
    return $sales;
}
function state(PDO $pdo, array $user): array
{
    $admin = $user['role'] === 'admin';
    $products = rows($pdo, 'SELECT sku, name, category AS cat, supplier_name AS supplier, price, stock, reorder_level AS reorder FROM products WHERE is_archived = 0 ORDER BY id');
    foreach ($products as &$p) {
        $p['price'] = (float) $p['price'];
        $p['stock'] = (int) $p['stock'];
        $p['reorder'] = (int) $p['reorder'];
    }
    $moves = rows($pdo, "SELECT movement_no AS no, DATE_FORMAT(moved_at, '%Y-%m-%dT%H:%i:%s') AS date, product_sku AS sku, product_name AS product, type, quantity AS qty, reference AS ref, note, recorded_by AS `by` FROM stock_movements ORDER BY moved_at DESC, id DESC");
    foreach ($moves as &$m)
        $m['qty'] = (int) $m['qty'];
    $suppliers = $pos = [];
    if ($admin) {   // staff never receive supplier or purchase order data
        $suppliers = rows($pdo, 'SELECT name, contact, phone, status FROM suppliers WHERE is_archived = 0 ORDER BY id');
        $pos = rows($pdo, "SELECT po_number AS no, supplier_name AS supplier, product_sku AS product, quantity AS qty, DATE_FORMAT(ordered_at, '%Y-%m-%dT%H:%i') AS ordered, DATE_FORMAT(expected_at, '%Y-%m-%dT%H:%i') AS expected, amount, status FROM purchase_orders WHERE is_archived = 0 ORDER BY ordered_at DESC, id DESC");
        foreach ($pos as &$o) {
            $o['qty'] = (int) $o['qty'];
            $o['amount'] = (float) $o['amount'];
        }
    }
    $categories = rows($pdo, 'SELECT name, prefix FROM categories WHERE is_archived = 0 ORDER BY name');
    return ['products' => $products, 'suppliers' => $suppliers, 'pos' => $pos, 'moves' => $moves, 'sales' => sale_rows($pdo, 0), 'categories' => $categories];
}
function archive_state(PDO $pdo): array
{
    $cols = 'archived_by, DATE_FORMAT(archived_at, \'%Y-%m-%d %H:%i\') AS archived_at, archive_reason';
    return [
        'products' => rows($pdo, "SELECT sku AS id, name, category, price, stock, $cols FROM products WHERE is_archived = 1 ORDER BY archived_at DESC"),
        'suppliers' => rows($pdo, "SELECT name AS id, name, contact, phone, $cols FROM suppliers WHERE is_archived = 1 ORDER BY archived_at DESC"),
        'sales' => sale_rows($pdo, 1),
        'pos' => rows($pdo, "SELECT po_number AS id, po_number AS name, supplier_name AS supplier, quantity AS qty, amount, status, $cols FROM purchase_orders WHERE is_archived = 1 ORDER BY archived_at DESC"),
    ];
}

/* ---------- Actions ---------- */
try {
    $input = body();
    $action = $input['action'] ?? $_GET['action'] ?? '';
    $pdo = null;

    /* Login code is unchanged: another teammate owns it. */
    if ($action === 'login') {
        $q = db()->prepare('SELECT id, username, email, password_hash, full_name, role FROM users WHERE role=? AND (LOWER(username)=? OR LOWER(COALESCE(email,\'\'))=?) LIMIT 1');
        $role = $input['role'] ?? '';
        $identity = strtolower(trim((string) ($input['username'] ?? '')));
        $q->execute([$role, $identity, $identity]);
        $u = $q->fetch();
        if (!$u || !hash_equals($u['password_hash'], hash('sha256', (string) ($input['password'] ?? ''))))
            reply(['error' => "Those details don't match a $role account. Check the role, ID and password."], 422);
        $_SESSION['user'] = ['id' => $u['username'], 'name' => $u['full_name'], 'role' => $u['role']];
        reply(['user' => $_SESSION['user']]);
    }
    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        reply(['ok' => true]);
    }
    $user = require_login();
    $pdo = db();

    if ($action === 'current_user') {
        $q = $pdo->prepare('SELECT username, full_name, role FROM users WHERE username=? AND role=? LIMIT 1');
        $q->execute([$_SESSION['user']['id'], $_SESSION['user']['role']]);
        $u = $q->fetch();
        if (!$u) {
            unset($_SESSION['user']);
            reply(['error' => 'Your account could not be found. Please sign in again.'], 401);
        }
        $_SESSION['user'] = ['id' => $u['username'], 'name' => $u['full_name'], 'role' => $u['role']];
        reply(['user' => $_SESSION['user']]);
    }
    if ($action === 'load')
        reply(['data' => state($pdo, $user)]);
    if ($action === 'archive_list') {
        need_admin();
        reply(['data' => archive_state($pdo)]);
    }

    /* ----- Categories (administrator) ----- */
    if ($action === 'category_save') {
        need_admin();
        $name = text($input, 'name');
        $old = text($input, 'old_name');
        if ($name === '')
            fail('Enter a category name.');
        if ($old === '') {
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
            if (strlen($prefix) < 3)
                fail('The category name needs at least three letters.');
            if (rows($pdo, 'SELECT 1 FROM categories WHERE name = ? OR prefix = ?', [$name, $prefix]))
                fail('That category already exists or its code is taken.');
            $pdo->prepare('INSERT INTO categories (name, prefix) VALUES (?,?)')->execute([$name, $prefix]);
        } else {
            in_tx($pdo, function () use ($pdo, $name, $old) {
                $pdo->prepare('UPDATE categories SET name=? WHERE name=?')->execute([$name, $old]);
                $pdo->prepare('UPDATE products SET category=? WHERE category=?')->execute([$name, $old]);
            });
        }
        reply(['ok' => true]);
    }
    if ($action === 'category_archive') {
        need_admin();
        $name = text($input, 'name');
        if (rows($pdo, 'SELECT 1 FROM products WHERE category=? AND is_archived=0 LIMIT 1', [$name]))
            fail('Move or archive the products in this category first.');
        $pdo->prepare('UPDATE categories SET is_archived=1 WHERE name=?')->execute([$name]);
        reply(['ok' => true]);
    }

    /* ----- Products (administrator) ----- */
    if ($action === 'next_sku') {   // preview of the automatic Stock Keeping Unit shown in the Add Product form
        need_admin();
        $prefix = rows($pdo, 'SELECT prefix FROM categories WHERE name=? AND is_archived=0', [text($input, 'category')])[0]['prefix'] ?? null;
        if (!$prefix)
            fail('Choose a category from the list.');
        reply(['sku' => $prefix . '-' . str_pad((string) seq($pdo, 'products', 'sku', $prefix . '-'), 4, '0', STR_PAD_LEFT)]);
    }
    if ($action === 'product_save') {
        need_admin();
        $name = text($input, 'name');
        $cat = text($input, 'category');
        $sup = text($input, 'supplier');
        $price = (float) ($input['price'] ?? 0);
        $reorder = (int) ($input['reorder'] ?? 0);
        $sku = strtoupper(text($input, 'sku'));
        if ($name === '' || $cat === '' || $sup === '')
            fail('Fill in the product name, category and supplier.');
        if ($price <= 0)
            fail('Enter a unit price above zero.');
        if (!rows($pdo, 'SELECT 1 FROM suppliers WHERE name=? AND is_archived=0', [$sup]))
            fail('Choose a supplier from the list.');
        $result = in_tx($pdo, function () use ($pdo, $user, $input, $name, $cat, $sup, $price, $reorder, $sku) {
            if ($sku !== '') {   // edit: stock only changes through stock movements
                $pdo->prepare('UPDATE products SET name=?, category=?, supplier_name=?, price=?, reorder_level=? WHERE sku=? AND is_archived=0')->execute([$name, $cat, $sup, $price, $reorder, $sku]);
                return $sku;
            }
            $prefix = rows($pdo, 'SELECT prefix FROM categories WHERE name=? AND is_archived=0', [$cat])[0]['prefix'] ?? null;
            if (!$prefix)
                fail('Choose a category from the list.');
            $sku = $prefix . '-' . str_pad((string) seq($pdo, 'products', 'sku', $prefix . '-'), 4, '0', STR_PAD_LEFT);   // automatic Stock Keeping Unit
            $opening = max(0, (int) ($input['stock'] ?? 0));
            $pdo->prepare('INSERT INTO products (sku,name,category,supplier_name,price,stock,reorder_level) VALUES (?,?,?,?,?,0,?)')->execute([$sku, $name, $cat, $sup, $price, $reorder]);
            if ($opening > 0)
                add_movement($pdo, ['sku' => $sku, 'name' => $name], 'in', $opening, '', 'Opening stock', $user['name']);
            return $sku;
        });
        reply(['ok' => true, 'sku' => $result]);
    }
    if ($action === 'product_archive') {
        $u = need_admin();
        archive_row($pdo, 'products', 'sku', text($input, 'id'), $u, text($input, 'reason'));
        reply(['ok' => true]);
    }

    /* ----- Suppliers (administrator) ----- */
    if ($action === 'supplier_save') {
        need_admin();
        $name = text($input, 'name');
        $old = text($input, 'old_name');
        $status = text($input, 'status') === 'On hold' ? 'On hold' : 'Active';
        if ($name === '')
            fail('Enter the supplier name.');
        if ($old === '') {
            if (rows($pdo, 'SELECT 1 FROM suppliers WHERE name=?', [$name]))
                fail('That supplier already exists.');
            $pdo->prepare('INSERT INTO suppliers (name,contact,phone,status) VALUES (?,?,?,?)')->execute([$name, text($input, 'contact'), text($input, 'phone'), $status]);
        } else {
            $pdo->prepare('UPDATE suppliers SET contact=?, phone=?, status=? WHERE name=?')->execute([text($input, 'contact'), text($input, 'phone'), $status, $old]);
        }
        reply(['ok' => true]);
    }
    if ($action === 'supplier_archive') {
        $u = need_admin();
        $name = text($input, 'id');
        if (rows($pdo, 'SELECT 1 FROM products WHERE supplier_name=? AND is_archived=0 LIMIT 1', [$name]))
            fail('This supplier still has active products. Archive or reassign them first.');
        archive_row($pdo, 'suppliers', 'name', $name, $u, text($input, 'reason'));
        reply(['ok' => true]);
    }

    /* ----- Purchase orders (administrator) ----- */
    if ($action === 'po_create') {
        need_admin();
        $sup = text($input, 'supplier');
        $qty = (int) ($input['qty'] ?? 0);
        $p = rows($pdo, 'SELECT * FROM products WHERE sku=? AND supplier_name=? AND is_archived=0', [text($input, 'sku'), $sup])[0] ?? null;
        if (!$p)
            fail('Choose a product that belongs to the selected supplier.');
        if (rows($pdo, 'SELECT 1 FROM suppliers WHERE name=? AND status=?', [$sup, 'On hold']))
            fail('This supplier is on hold. Set it to Active before placing an order.');
        if ($qty < 1)
            fail('Enter a quantity of 1 or more.');
        $expected = text($input, 'expected') ?: date('Y-m-d', strtotime('+7 days'));
        $no = in_tx($pdo, function () use ($pdo, $sup, $p, $qty, $expected) {
            $no = 'PO-' . seq($pdo, 'purchase_orders', 'po_number', 'PO-');
            $pdo->prepare('INSERT INTO purchase_orders (po_number,supplier_name,product_sku,quantity,ordered_at,expected_at,amount,status) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$no, $sup, $p['sku'], $qty, now(), $expected, $qty * (float) $p['price'], 'Pending']);
            return $no;
        });
        reply(['ok' => true, 'no' => $no]);
    }
    if ($action === 'po_status') {
        $u = need_admin();
        $no = text($input, 'id');
        $to = text($input, 'status');
        in_tx($pdo, function () use ($pdo, $u, $no, $to) {
            $o = rows($pdo, 'SELECT * FROM purchase_orders WHERE po_number=? AND is_archived=0 FOR UPDATE', [$no])[0] ?? null;
            if (!$o)
                fail('That purchase order could not be found.');
            $allowed = ['Pending' => ['In transit', 'Cancelled'], 'In transit' => ['Received', 'Cancelled']];
            if (!in_array($to, $allowed[$o['status']] ?? [], true))
                fail('This purchase order cannot be changed to ' . $to . '.');
            if ($to === 'Received')
                add_movement($pdo, lock_product($pdo, $o['product_sku']), 'in', (int) $o['quantity'], $no, '', $u['name']);   // creates Stock In automatically
            $pdo->prepare('UPDATE purchase_orders SET status=? WHERE po_number=?')->execute([$to, $no]);
        });
        reply(['ok' => true]);
    }
    if ($action === 'po_archive') {
        $u = need_admin();
        archive_row($pdo, 'purchase_orders', 'po_number', text($input, 'id'), $u, text($input, 'reason'));
        reply(['ok' => true]);
    }

    /* ----- Stock movements (staff: in/out only; administrator: also adjustment) ----- */
    if ($action === 'movement_record') {
        $type = text($input, 'type');
        $qty = (int) ($input['qty'] ?? 0);
        $note = text($input, 'note');
        if (!in_array($type, ['in', 'out', 'adj'], true))
            fail('Choose a movement type.');
        if ($type === 'adj' && $user['role'] !== 'admin')
            fail('Only an administrator can record an adjustment.', 403);
        if ($qty === 0 || ($type !== 'adj' && $qty < 0))
            fail('Enter a valid quantity.');
        $no = in_tx($pdo, function () use ($pdo, $user, $input, $type, $qty, $note) {
            $p = lock_product($pdo, text($input, 'sku'));
            $delta = $type === 'out' ? -$qty : $qty;
            if ((int) $p['stock'] + $delta < 0)
                fail('Only ' . $p['stock'] . ' of ' . $p['name'] . ' on hand.');
            return add_movement($pdo, $p, $type, $delta, '', $note, $user['name']);
        });
        reply(['ok' => true, 'no' => $no]);
    }

    /* ----- Sales ----- */
    if ($action === 'sale_complete') {
        $items = $input['items'] ?? [];
        $method = text($input, 'payment_method');
        $ref = text($input, 'payment_reference');
        if (!is_array($items) || !$items)
            fail('Add at least one item first.');
        if (!in_array($method, ['Cash', 'Bank Transfer', 'E-Wallet'], true))
            fail('Choose a payment method.');
        if ($method !== 'Cash' && $ref === '')
            fail('Enter the payment reference number.');
        $sale = in_tx($pdo, function () use ($pdo, $user, $items, $method, $ref, $input) {
            $lines = [];
            $total = 0.0;
            foreach ($items as $it) {   // price and stock are always taken from the database, never from the browser
                $p = lock_product($pdo, strtoupper((string) ($it['sku'] ?? '')));
                $q = (int) ($it['qty'] ?? 0);
                if ($q < 1)
                    fail('Enter a quantity of 1 or more.');
                if ($q > (int) $p['stock'])
                    fail('Only ' . $p['stock'] . ' of ' . $p['name'] . ' in stock.');
                $lines[] = [$p, $q];
                $total += $q * (float) $p['price'];
            }
            $received = $method === 'Cash' ? (float) ($input['amount_received'] ?? 0) : $total;
            if ($received + 0.001 < $total)
                fail('The amount received is less than the total due.');
            $no = '#S-' . seq($pdo, 'sales', 'receipt_no', '#S-');
            $pdo->prepare('INSERT INTO sales (receipt_no,sold_at,customer,payment_method,status,total,amount_received,change_amount,payment_reference,cashier) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$no, now(), '', $method, 'Paid', $total, $received, $received - $total, $ref, $user['name']]);
            foreach ($lines as [$p, $q]) {
                $pdo->prepare('INSERT INTO sale_items (sale_receipt,product_sku,product_name,quantity,unit_price) VALUES (?,?,?,?,?)')->execute([$no, $p['sku'], $p['name'], $q, $p['price']]);
                add_movement($pdo, $p, 'out', -$q, $no, '', $user['name']);   // Stock Out uses the receipt number
            }
            return $no;
        });
        reply(['ok' => true, 'receipt' => $sale]);
    }
    if ($action === 'sale_archive') {
        $u = need_admin();
        $no = text($input, 'id');
        $reason = text($input, 'reason');
        if ($reason === '')
            fail('Enter the reason for archiving this sale.');
        in_tx($pdo, function () use ($pdo, $u, $no, $reason) {
            $s = rows($pdo, 'SELECT * FROM sales WHERE receipt_no=? AND is_archived=0 FOR UPDATE', [$no])[0] ?? null;
            if (!$s)
                fail('That sale could not be found.');
            foreach (sale_items($pdo, $no) as $i) {
                $p = rows($pdo, 'SELECT * FROM products WHERE sku=? FOR UPDATE', [$i['sku']])[0] ?? null;
                if ($p)
                    add_movement($pdo, $p, 'in', $i['qty'], $no, 'Sale archived: ' . $reason, $u['name']);   // stock returns to the shelf
            }
            archive_row($pdo, 'sales', 'receipt_no', $no, $u, $reason);
        });
        reply(['ok' => true]);
    }

    /* ----- Restore from Archive and Deleted Items (administrator) ----- */
    if ($action === 'restore') {
        $u = need_admin();
        $type = text($input, 'type');
        $id = text($input, 'id');
        in_tx($pdo, function () use ($pdo, $u, $type, $id) {
            if ($type === 'products')
                restore_row($pdo, 'products', 'sku', $id);
            elseif ($type === 'suppliers')
                restore_row($pdo, 'suppliers', 'name', $id);
            elseif ($type === 'pos')
                restore_row($pdo, 'purchase_orders', 'po_number', $id);
            elseif ($type === 'sales') {   // re-deduct stock only if enough is available
                foreach (sale_items($pdo, $id) as $i) {
                    $p = rows($pdo, 'SELECT * FROM products WHERE sku=? FOR UPDATE', [$i['sku']])[0] ?? null;
                    if (!$p || $p['is_archived'] || (int) $p['stock'] < $i['qty'])
                        fail('Not enough stock of ' . $i['name'] . ' to restore this sale.');
                }
                foreach (sale_items($pdo, $id) as $i)
                    add_movement($pdo, rows($pdo, 'SELECT * FROM products WHERE sku=?', [$i['sku']])[0], 'out', -$i['qty'], $id, 'Sale restored', $u['name']);
                restore_row($pdo, 'sales', 'receipt_no', $id);
            } else
                fail('Unknown item type.');
        });
        reply(['ok' => true]);
    }

    reply(['error' => 'Unknown request.'], 400);
} catch (ApiError $e) {
    reply(['error' => $e->getMessage()], $e->getCode() ?: 422);
} catch (Throwable $e) {
    reply(['error' => 'Database request failed: ' . $e->getMessage()], 500);
}
