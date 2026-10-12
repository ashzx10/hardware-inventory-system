<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

function reply(array $data, int $code = 200): never { http_response_code($code); echo json_encode($data); exit; }
function require_login(): array { if (empty($_SESSION['user'])) reply(['error' => 'Please sign in first.'], 401); return $_SESSION['user']; }
function body(): array { $data = json_decode(file_get_contents('php://input'), true); return is_array($data) ? $data : $_POST; }
function rows(PDO $pdo, string $sql): array { return $pdo->query($sql)->fetchAll(); }
function state(PDO $pdo): array {
    $products = rows($pdo, 'SELECT sku, name, category AS cat, supplier_name AS supplier, price, stock, reorder_level AS reorder FROM products ORDER BY id');
    foreach ($products as &$p) { $p['price'] = (float)$p['price']; $p['stock'] = (int)$p['stock']; $p['reorder'] = (int)$p['reorder']; }
    $suppliers = rows($pdo, 'SELECT name, contact, phone, status FROM suppliers ORDER BY id');
    $pos = rows($pdo, "SELECT po_number AS no, supplier_name AS supplier, product_sku AS product, quantity AS qty, DATE_FORMAT(ordered_at, '%Y-%m-%dT%H:%i') AS ordered, DATE_FORMAT(expected_at, '%Y-%m-%dT%H:%i') AS expected, amount, status FROM purchase_orders ORDER BY ordered_at DESC, id DESC");
    foreach ($pos as &$p) { $p['qty'] = (int)$p['qty']; $p['amount'] = (float)$p['amount']; }
    $moves = rows($pdo, "SELECT DATE_FORMAT(moved_at, '%Y-%m-%dT%H:%i:%s') AS date, product_name AS product, type, quantity AS qty, reference AS ref, recorded_by AS `by` FROM stock_movements ORDER BY moved_at DESC, id DESC");
    foreach ($moves as &$m) $m['qty'] = (int)$m['qty'];
    $sales = rows($pdo, "SELECT receipt_no AS no, DATE_FORMAT(sold_at, '%Y-%m-%dT%H:%i:%s') AS date, customer, payment_method AS pay, status, total FROM sales ORDER BY sold_at DESC, id DESC");
    foreach ($sales as &$sale) {
        $sale['total'] = (float)$sale['total'];
        $q = $pdo->prepare('SELECT product_sku AS sku, product_name AS name, quantity AS qty, unit_price AS price FROM sale_items WHERE sale_receipt=?');
        $q->execute([$sale['no']]); $sale['items'] = $q->fetchAll();
        foreach ($sale['items'] as &$item) { $item['qty'] = (int)$item['qty']; $item['price'] = (float)$item['price']; }
    }
    return compact('products', 'suppliers', 'pos', 'moves', 'sales');
}
function put_state(PDO $pdo, array $d): void {
    foreach (['products','suppliers','pos','moves','sales'] as $key) if (!isset($d[$key]) || !is_array($d[$key])) throw new InvalidArgumentException('Invalid dashboard data.');
    $pdo->beginTransaction();
    try {
        $pdo->exec('DELETE FROM stock_movements'); $pdo->exec('DELETE FROM sale_items'); $pdo->exec('DELETE FROM sales'); $pdo->exec('DELETE FROM purchase_orders'); $pdo->exec('DELETE FROM products'); $pdo->exec('DELETE FROM suppliers');
        $qSup = $pdo->prepare('INSERT INTO suppliers (name,contact,phone,status) VALUES (?,?,?,?)');
        foreach ($d['suppliers'] as $s) $qSup->execute([trim((string)$s['name']), trim((string)$s['contact']), trim((string)$s['phone']), $s['status'] === 'On hold' ? 'On hold' : 'Active']);
        $qProd = $pdo->prepare('INSERT INTO products (sku,name,category,supplier_name,price,stock,reorder_level) VALUES (?,?,?,?,?,?,?)');
        foreach ($d['products'] as $p) $qProd->execute([strtoupper(trim((string)$p['sku'])), trim((string)$p['name']), (string)$p['cat'], (string)$p['supplier'], (float)$p['price'], (int)$p['stock'], (int)$p['reorder']]);
        $qPo = $pdo->prepare('INSERT INTO purchase_orders (po_number,supplier_name,product_sku,quantity,ordered_at,expected_at,amount,status) VALUES (?,?,?,?,?,?,?,?)');
        foreach ($d['pos'] as $o) $qPo->execute([(string)$o['no'],(string)$o['supplier'],(string)$o['product'],(int)$o['qty'],str_replace('T',' ',(string)$o['ordered']),str_replace('T',' ',(string)$o['expected']),(float)$o['amount'],(string)$o['status']]);
        $qSale = $pdo->prepare('INSERT INTO sales (receipt_no,sold_at,customer,payment_method,status,total) VALUES (?,?,?,?,?,?)');
        $qItem = $pdo->prepare('INSERT INTO sale_items (sale_receipt,product_sku,product_name,quantity,unit_price) VALUES (?,?,?,?,?)');
        foreach ($d['sales'] as $s) { $qSale->execute([(string)$s['no'],str_replace('T',' ',(string)$s['date']), (string)$s['customer'], (string)$s['pay'], (string)$s['status'], (float)$s['total']]); foreach ($s['items'] as $i) $qItem->execute([(string)$s['no'],(string)$i['sku'],(string)$i['name'],(int)$i['qty'],(float)$i['price']]); }
        $qMove = $pdo->prepare('INSERT INTO stock_movements (moved_at,product_sku,product_name,type,quantity,reference,recorded_by) VALUES (?,?,?,?,?,?,?)');
        $productsByName = []; foreach ($d['products'] as $p) $productsByName[$p['name']] = $p['sku'];
        foreach ($d['moves'] as $m) $qMove->execute([str_replace('T',' ',(string)$m['date']),$productsByName[$m['product']] ?? '',(string)$m['product'],(string)$m['type'],(int)$m['qty'],(string)$m['ref'],(string)$m['by']]);
        $pdo->commit();
    } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
}
try {
    $input = body(); $action = $input['action'] ?? $_GET['action'] ?? '';
    if ($action === 'login') {
        $q = db()->prepare('SELECT id, username, email, password_hash, full_name, role FROM users WHERE role=? AND (LOWER(username)=? OR LOWER(COALESCE(email,\'\'))=?) LIMIT 1');
        $role = $input['role'] ?? ''; $identity = strtolower(trim((string)($input['username'] ?? ''))); $q->execute([$role, $identity, $identity]); $u = $q->fetch();
        if (!$u || !hash_equals($u['password_hash'], hash('sha256', (string)($input['password'] ?? '')))) reply(['error' => "Those details don't match a $role account. Check the role, ID and password."], 422);
        $_SESSION['user'] = ['id' => $u['username'], 'name' => $u['full_name'], 'role' => $u['role']]; reply(['user' => $_SESSION['user']]);
    }
    if ($action === 'logout') { $_SESSION = []; session_destroy(); reply(['ok' => true]); }
    require_login();
    if ($action === 'current_user') {
        $q = db()->prepare('SELECT username, full_name, role FROM users WHERE username=? AND role=? LIMIT 1');
        $q->execute([$_SESSION['user']['id'], $_SESSION['user']['role']]);
        $u = $q->fetch();
        if (!$u) { unset($_SESSION['user']); reply(['error' => 'Your account could not be found. Please sign in again.'], 401); }
        $_SESSION['user'] = ['id' => $u['username'], 'name' => $u['full_name'], 'role' => $u['role']];
        reply(['user' => $_SESSION['user']]);
    }
    if ($action === 'load') reply(['data' => state(db())]);
    if ($action === 'save') { put_state(db(), $input['data'] ?? []); reply(['ok' => true]); }
    reply(['error' => 'Unknown request.'], 400);
} catch (Throwable $e) { reply(['error' => 'Database request failed: ' . $e->getMessage()], 500); }
