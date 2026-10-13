<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BuildStock | Hardware Inventory &amp; Sales System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700&display=swap">
<link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
<div class="app">

  <!-- Tab controls (checked radio decides which view is visible) -->
  <input class="view-toggle" type="radio" name="view" id="v-dash" checked>
  <input class="view-toggle" type="radio" name="view" id="v-products">
  <input class="view-toggle" type="radio" name="view" id="v-suppliers">
  <input class="view-toggle" type="radio" name="view" id="v-movements">
  <input class="view-toggle" type="radio" name="view" id="v-sales">

  <!-- ================= SIDEBAR ================= -->
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">B</div>
      <div>
        <div class="brand-name">BuildStock</div>
        <div class="brand-sub">Inventory &amp; Sales</div>
      </div>
    </div>
    <nav class="nav" aria-label="Main navigation">
      <label for="v-dash"><span class="nav-icon">▦</span>Dashboard</label>
      <label for="v-products"><span class="nav-icon">▤</span>Products &amp; Categories</label>
      <label for="v-suppliers"><span class="nav-icon">⚒</span>Suppliers &amp; Orders</label>
      <label for="v-movements"><span class="nav-icon">⇅</span>Stock Movements</label>
      <label for="v-sales"><span class="nav-icon">₱</span>Sales</label>
    </nav>
    <footer class="sidebar-foot">CCS110 Finals Project</footer>
  </aside>

  <!-- ================= MAIN ================= -->
  <div class="main">

    <header class="topbar">
      <search class="search">
        <span class="search-glyph" aria-hidden="true">⌕</span>
        <input type="search" placeholder="Search products, SKUs, suppliers…" aria-label="Search">
      </search>
      <details class="profile">
        <summary>
          <span class="avatar">MR</span>
          <span class="profile-name">System Administrator</span>
        </summary>
        <nav class="profile-menu" aria-label="Account">
          <a href="#">My profile</a>
          <a href="#">Store settings</a>
          <hr>
          <a href="login.php">Sign out</a>
        </nav>
      </details>
    </header>

    <main class="content">

      <!-- ===== 1. DASHBOARD ===== -->
      <section class="view" id="dash">
        <header class="view-head">
          <div>
            <h1>Dashboard</h1>
            <p>Stock levels and sales at a glance.</p>
          </div>
          <a class="btn btn-primary" href="#">New sale</a>
        </header>

        <section class="stats" aria-label="Key figures">
          <dl class="stat">
            <dt class="stat-label">Total products</dt>
            <dd class="stat-value">1,248</dd>
            <dd class="stat-note">Across 14 categories</dd>
          </dl>
          <dl class="stat warn">
            <dt class="stat-label">Low-stock alerts</dt>
            <dd class="stat-value">23</dd>
            <dd class="stat-note">Below reorder level</dd>
          </dl>
          <dl class="stat">
            <dt class="stat-label">Active suppliers</dt>
            <dd class="stat-value">36</dd>
            <dd class="stat-note">5 orders in transit</dd>
          </dl>
          <dl class="stat">
            <dt class="stat-label">Total sales (this month)</dt>
            <dd class="stat-value">₱482,350</dd>
            <dd class="stat-note">212 transactions</dd>
          </dl>
        </section>

        <div class="split">
          <section class="panel">
            <div class="panel-head"><h2>Recent sales</h2><a href="#">View all</a></div>
            <div class="table-wrap">
              <table>
                <caption class="visually-hidden">Recent sales</caption>
                <thead><tr><th scope="col">Receipt</th><th scope="col">Customer</th><th scope="col">Date</th><th scope="col" class="num">Total</th><th scope="col">Status</th></tr></thead>
                <tbody>
                  <tr><td>#S-10482</td><td>Dela Cruz Builders</td><td>Oct 10, 2026</td><td class="num">₱18,450.00</td><td><span class="badge badge-done">Paid</span></td></tr>
                  <tr><td>#S-10481</td><td>Walk-in customer</td><td>Oct 10, 2026</td><td class="num">₱1,260.00</td><td><span class="badge badge-done">Paid</span></td></tr>
                  <tr><td>#S-10480</td><td>Santos Hardware</td><td>Oct 9, 2026</td><td class="num">₱32,900.00</td><td><span class="badge badge-low">Partial</span></td></tr>
                  <tr><td>#S-10479</td><td>Walk-in customer</td><td>Oct 9, 2026</td><td class="num">₱745.50</td><td><span class="badge badge-done">Paid</span></td></tr>
                </tbody>
              </table>
            </div>
          </section>

          <section class="panel">
            <div class="panel-head"><h2>Needs restocking</h2></div>
            <div class="panel-body">
              <ul class="alert-list">
                <li><span>Portland Cement 40kg</span><span>12 left</span></li>
                <li><span>Deformed Bar 10mm x 6m</span><span>18 left</span></li>
                <li><span>Common Wire Nails 3"</span><span>4 kg left</span></li>
                <li><span>Marine Plywood 1/2"</span><span>0 left</span></li>
                <li><span>PVC Pipe 2" x 3m</span><span>9 left</span></li>
              </ul>
            </div>
          </section>
        </div>
      </section>

      <!-- ===== 2. PRODUCTS & CATEGORIES ===== -->
      <section class="view" id="products">
        <header class="view-head">
          <div>
            <h1>Products &amp; Categories</h1>
            <p>Add, update and remove items in your catalog.</p>
          </div>
        </header>

        <section class="panel">
          <div class="panel-head"><h2>Add new product</h2></div>
          <div class="panel-body">
            <form action="#" method="post">
              <fieldset class="form-grid">
                <legend class="visually-hidden">Product details</legend>
                <div class="field">
                  <label for="p-name">Product name</label>
                  <input id="p-name" name="name" type="text" placeholder="e.g. Portland Cement 40kg" required>
                </div>
                <div class="field">
                  <label for="p-sku">SKU</label>
                  <input id="p-sku" name="sku" type="text" placeholder="e.g. CEM-040" required>
                </div>
                <div class="field">
                  <label for="p-cat">Category</label>
                  <select id="p-cat" name="category">
                    <option>Cement &amp; Aggregates</option>
                    <option>Steel &amp; Rebar</option>
                    <option>Lumber &amp; Plywood</option>
                    <option>Plumbing</option>
                    <option>Electrical</option>
                    <option>Paint &amp; Finishes</option>
                    <option>Fasteners &amp; Hardware</option>
                  </select>
                </div>
                <div class="field">
                  <label for="p-supplier">Supplier</label>
                  <select id="p-supplier" name="supplier">
                    <option>Pacific Cement Corp.</option>
                    <option>Luzon Steel Works</option>
                    <option>Calabarzon Lumber Co.</option>
                  </select>
                </div>
                <div class="field">
                  <label for="p-price">Unit price (₱)</label>
                  <input id="p-price" name="price" type="number" min="0" step="0.01" placeholder="0.00" required>
                </div>
                <div class="field">
                  <label for="p-stock">Opening stock</label>
                  <input id="p-stock" name="stock" type="number" min="0" step="1" placeholder="0" required>
                </div>
                <div class="field">
                  <label for="p-reorder">Reorder level</label>
                  <input id="p-reorder" name="reorder" type="number" min="0" step="1" placeholder="20">
                  <small>You'll get a low-stock alert at this quantity.</small>
                </div>
              </fieldset>
              <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save product</button>
                <button class="btn btn-ghost" type="reset">Clear form</button>
              </div>
            </form>
          </div>
        </section>

        <section class="panel">
          <header class="panel-head">
            <h2>Product list</h2>
            <div class="toolbar">
              <select aria-label="Filter by category"><option>All categories</option><option>Cement &amp; Aggregates</option><option>Steel &amp; Rebar</option><option>Plumbing</option></select>
              <select aria-label="Filter by status"><option>All statuses</option><option>In Stock</option><option>Low Stock</option><option>Out of Stock</option></select>
            </div>
          </header>
          <div class="table-wrap">
            <table>
                <caption class="visually-hidden">Product list</caption>
              <thead>
                <tr><th scope="col">SKU</th><th scope="col">Product</th><th scope="col">Category</th><th scope="col" class="num">Unit price</th><th scope="col" class="num">Stock</th><th scope="col">Status</th><th scope="col" class="num">Actions</th></tr>
              </thead>
              <tbody>
                <tr><td class="sku">CEM-040</td><td>Portland Cement 40kg</td><td>Cement &amp; Aggregates</td><td class="num">₱265.00</td><td class="num">12</td><td><span class="badge badge-low">Low Stock</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                <tr><td class="sku">STL-D10</td><td>Deformed Bar 10mm x 6m</td><td>Steel &amp; Rebar</td><td class="num">₱198.00</td><td class="num">340</td><td><span class="badge badge-in">In Stock</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                <tr><td class="sku">LMB-P12</td><td>Marine Plywood 1/2"</td><td>Lumber &amp; Plywood</td><td class="num">₱1,150.00</td><td class="num">0</td><td><span class="badge badge-out">Out of Stock</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                <tr><td class="sku">PLB-P20</td><td>PVC Pipe 2" x 3m</td><td>Plumbing</td><td class="num">₱142.00</td><td class="num">9</td><td><span class="badge badge-low">Low Stock</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                <tr><td class="sku">FST-N03</td><td>Common Wire Nails 3"</td><td>Fasteners &amp; Hardware</td><td class="num">₱78.00</td><td class="num">96</td><td><span class="badge badge-in">In Stock</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                <tr><td class="sku">PNT-E04</td><td>Latex Paint, Flat White 4L</td><td>Paint &amp; Finishes</td><td class="num">₱640.00</td><td class="num">58</td><td><span class="badge badge-in">In Stock</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <!-- ===== 3. SUPPLIERS & PURCHASE ORDERS ===== -->
      <section class="view" id="suppliers">
        <header class="view-head">
          <div>
            <h1>Suppliers &amp; Purchase Orders</h1>
            <p>Manage who you buy from and track what's on the way.</p>
          </div>
        </header>

        <div class="split">
          <section class="panel">
            <div class="panel-head"><h2>Suppliers</h2><button class="btn btn-sm btn-blue" type="button">Add supplier</button></div>
            <div class="table-wrap">
              <table>
                <caption class="visually-hidden">Suppliers</caption>
                <thead><tr><th scope="col">Supplier</th><th scope="col">Contact</th><th scope="col">Phone</th><th scope="col">Status</th><th scope="col" class="num">Actions</th></tr></thead>
                <tbody>
                  <tr><td>Pacific Cement Corp.</td><td>Ramon Villanueva</td><td>0917 555 0142</td><td><span class="badge badge-in">Active</span></td>
                    <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                  <tr><td>Luzon Steel Works</td><td>Angela Tan</td><td>0928 555 0187</td><td><span class="badge badge-in">Active</span></td>
                    <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                  <tr><td>Calabarzon Lumber Co.</td><td>Joel Mendoza</td><td>0905 555 0119</td><td><span class="badge badge-low">On hold</span></td>
                    <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Edit</button><button class="btn btn-sm btn-delete" type="button">Delete</button></div></td></tr>
                </tbody>
              </table>
            </div>
          </section>

          <section class="panel">
            <div class="panel-head"><h2>Create purchase order</h2></div>
            <div class="panel-body">
              <form action="#" method="post">
                <fieldset class="form-grid">
                  <legend class="visually-hidden">Purchase order details</legend>
                  <div class="field wide">
                    <label for="po-supplier">Supplier</label>
                    <select id="po-supplier"><option>Pacific Cement Corp.</option><option>Luzon Steel Works</option><option>Calabarzon Lumber Co.</option></select>
                  </div>
                  <div class="field wide">
                    <label for="po-product">Product</label>
                    <select id="po-product"><option>Portland Cement 40kg</option><option>Deformed Bar 10mm x 6m</option><option>Marine Plywood 1/2"</option></select>
                  </div>
                  <div class="field">
                    <label for="po-qty">Quantity</label>
                    <input id="po-qty" type="number" min="1" placeholder="100">
                  </div>
                  <div class="field">
                    <label for="po-date">Expected delivery</label>
                    <input id="po-date" type="date">
                  </div>
                </fieldset>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Place order</button></div>
              </form>
            </div>
          </section>
        </div>

        <section class="panel">
          <div class="panel-head"><h2>Purchase orders</h2></div>
          <div class="table-wrap">
            <table>
                <caption class="visually-hidden">Purchase orders</caption>
              <thead><tr><th scope="col">PO number</th><th scope="col">Supplier</th><th scope="col">Ordered</th><th scope="col">Expected</th><th scope="col" class="num">Amount</th><th scope="col">Status</th><th scope="col" class="num">Actions</th></tr></thead>
              <tbody>
                <tr><td>PO-2041</td><td>Pacific Cement Corp.</td><td>Oct 6, 2026</td><td>Oct 12, 2026</td><td class="num">₱53,000.00</td><td><span class="badge badge-info">In transit</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Update</button><button class="btn btn-sm btn-delete" type="button">Cancel</button></div></td></tr>
                <tr><td>PO-2040</td><td>Luzon Steel Works</td><td>Oct 3, 2026</td><td>Oct 9, 2026</td><td class="num">₱99,000.00</td><td><span class="badge badge-done">Received</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">View</button></div></td></tr>
                <tr><td>PO-2039</td><td>Calabarzon Lumber Co.</td><td>Sep 28, 2026</td><td>Oct 14, 2026</td><td class="num">₱46,000.00</td><td><span class="badge badge-low">Pending</span></td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">Update</button><button class="btn btn-sm btn-delete" type="button">Cancel</button></div></td></tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <!-- ===== 4. STOCK IN / OUT MOVEMENTS ===== -->
      <section class="view" id="movements">
        <header class="view-head">
          <div>
            <h1>Stock Movements</h1>
            <p>Every stock-in, stock-out and adjustment, in one log.</p>
          </div>
        </header>

        <section class="panel">
          <div class="panel-head"><h2>Record a movement</h2></div>
          <div class="panel-body">
            <form action="#" method="post">
              <fieldset class="form-grid">
                <legend class="visually-hidden">Movement details</legend>
                <div class="field">
                  <label for="m-product">Product</label>
                  <select id="m-product"><option>Portland Cement 40kg</option><option>Deformed Bar 10mm x 6m</option><option>PVC Pipe 2" x 3m</option></select>
                </div>
                <div class="field">
                  <label for="m-type">Movement type</label>
                  <select id="m-type"><option>Stock in</option><option>Stock out</option><option>Adjustment</option></select>
                </div>
                <div class="field">
                  <label for="m-qty">Quantity</label>
                  <input id="m-qty" type="number" min="1" placeholder="0">
                </div>
                <div class="field">
                  <label for="m-ref">Reference</label>
                  <input id="m-ref" type="text" placeholder="e.g. PO-2041">
                </div>
                <div class="field wide">
                  <label for="m-note">Note</label>
                  <input id="m-note" type="text" placeholder="Reason for the change (required for adjustments)">
                </div>
              </fieldset>
              <div class="form-actions">
                <button class="btn btn-primary" type="submit">Record movement</button>
                <button class="btn btn-ghost" type="reset">Clear form</button>
              </div>
            </form>
          </div>
        </section>

        <section class="panel">
          <header class="panel-head">
            <h2>Movement log</h2>
            <div class="toolbar">
              <select aria-label="Filter by type"><option>All types</option><option>Stock in</option><option>Stock out</option><option>Adjustment</option></select>
            </div>
          </header>
          <div class="table-wrap">
            <table>
                <caption class="visually-hidden">Movement log</caption>
              <thead><tr><th scope="col">Date</th><th scope="col">Product</th><th scope="col">Type</th><th scope="col" class="num">Quantity</th><th scope="col">Reference</th><th scope="col">Recorded by</th></tr></thead>
              <tbody>
                <tr><td>Oct 10, 2026 9:14 AM</td><td>Deformed Bar 10mm x 6m</td><td><span class="badge badge-in">Stock in</span></td><td class="num qty-in">+200</td><td>PO-2040</td><td>System Administrator</td></tr>
                <tr><td>Oct 10, 2026 8:40 AM</td><td>Portland Cement 40kg</td><td><span class="badge badge-out">Stock out</span></td><td class="num qty-out">−30</td><td>#S-10482</td><td>System Administrator</td></tr>
                <tr><td>Oct 9, 2026 4:02 PM</td><td>Common Wire Nails 3"</td><td><span class="badge badge-info">Adjustment</span></td><td class="num qty-out">−2</td><td>Count correction</td><td>Staff Member</td></tr>
                <tr><td>Oct 9, 2026 1:25 PM</td><td>PVC Pipe 2" x 3m</td><td><span class="badge badge-out">Stock out</span></td><td class="num qty-out">−15</td><td>#S-10480</td><td>Staff Member</td></tr>
                <tr><td>Oct 8, 2026 10:05 AM</td><td>Latex Paint, Flat White 4L</td><td><span class="badge badge-in">Stock in</span></td><td class="num qty-in">+48</td><td>PO-2038</td><td>System Administrator</td></tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

      <!-- ===== 5. SALES & SALES ITEMS ===== -->
      <section class="view" id="sales">
        <header class="view-head">
          <div>
            <h1>Sales</h1>
            <p>Ring up an order and review past transactions.</p>
          </div>
        </header>

        <div class="split">
          <section class="panel">
            <div class="panel-head"><h2>New sale</h2></div>
            <div class="panel-body">
              <form action="#" method="post">
                <fieldset class="form-grid">
                  <legend class="visually-hidden">Sale details</legend>
                  <div class="field">
                    <label for="s-customer">Customer</label>
                    <input id="s-customer" type="text" placeholder="Walk-in customer">
                  </div>
                  <div class="field">
                    <label for="s-pay">Payment method</label>
                    <select id="s-pay"><option>Cash</option><option>GCash</option><option>Bank transfer</option><option>Credit (charge)</option></select>
                  </div>
                  <div class="field">
                    <label for="s-product">Product</label>
                    <select id="s-product"><option>Portland Cement 40kg</option><option>Deformed Bar 10mm x 6m</option><option>Common Wire Nails 3"</option></select>
                  </div>
                  <div class="field">
                    <label for="s-qty">Quantity</label>
                    <input id="s-qty" type="number" min="1" value="1">
                  </div>
                </fieldset>
                <div class="form-actions">
                  <button class="btn btn-blue" type="button">Add item</button>
                  <button class="btn btn-primary" type="submit">Complete sale</button>
                </div>
              </form>
            </div>
          </section>

          <section class="panel">
            <div class="panel-head"><h2>Current order</h2></div>
            <div class="panel-body">
              <ul class="alert-list">
                <li><span>Portland Cement 40kg × 10</span><span>₱2,650.00</span></li>
                <li><span>Common Wire Nails 3" × 5</span><span>₱390.00</span></li>
                <li><span><strong>Total</strong></span><span><strong>₱3,040.00</strong></span></li>
              </ul>
            </div>
          </section>
        </div>

        <section class="panel">
          <div class="panel-head"><h2>Order history</h2><button class="btn btn-sm btn-ghost" type="button">Export</button></div>
          <div class="table-wrap">
            <table>
                <caption class="visually-hidden">Order history</caption>
              <thead><tr><th scope="col">Receipt</th><th scope="col">Date</th><th scope="col">Customer</th><th scope="col">Items sold</th><th scope="col">Payment</th><th scope="col" class="num">Total</th><th scope="col" class="num">Actions</th></tr></thead>
              <tbody>
                <tr><td>#S-10482</td><td>Oct 10, 2026</td><td>Dela Cruz Builders</td>
                  <td><span class="item-lines">Cement 40kg × 30<br>Deformed Bar 10mm × 40</span></td><td>Bank transfer</td><td class="num">₱15,870.00</td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">View</button><button class="btn btn-sm btn-delete" type="button">Void</button></div></td></tr>
                <tr><td>#S-10481</td><td>Oct 10, 2026</td><td>Walk-in customer</td>
                  <td><span class="item-lines">Wire Nails 3" × 10<br>Latex Paint 4L × 1</span></td><td>Cash</td><td class="num">₱1,420.00</td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">View</button><button class="btn btn-sm btn-delete" type="button">Void</button></div></td></tr>
                <tr><td>#S-10480</td><td>Oct 9, 2026</td><td>Santos Hardware</td>
                  <td><span class="item-lines">PVC Pipe 2" × 15<br>Deformed Bar 10mm × 100</span></td><td>Credit (charge)</td><td class="num">₱21,930.00</td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">View</button><button class="btn btn-sm btn-delete" type="button">Void</button></div></td></tr>
                <tr><td>#S-10479</td><td>Oct 9, 2026</td><td>Walk-in customer</td>
                  <td><span class="item-lines">Wire Nails 3" × 5</span></td><td>GCash</td><td class="num">₱390.00</td>
                  <td><div class="row-actions"><button class="btn btn-sm btn-edit" type="button">View</button><button class="btn btn-sm btn-delete" type="button">Void</button></div></td></tr>
              </tbody>
            </table>
          </div>
        </section>
      </section>

    </main>
  </div>
</div>
<script src="scripts/dashboard.js"></script>
</body>
</html>
