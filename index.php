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
<script>
/* BuildStock dashboard logic (CCS110 Finals Project)
   Front-end demo: data lives in the browser's localStorage under "bs_data".
   Swap load()/save() for fetch() calls to your PHP/Node API when the database is ready. */
(function () {
  'use strict';

  /* ---------- Session & role ---------- */
  var user = JSON.parse(localStorage.getItem('bs_user') || sessionStorage.getItem('bs_user') || 'null');
  if (!user) { location.href = 'login.php'; return; }
  var isAdmin = user.role === 'admin';

  /* ---------- Helpers ---------- */
  var $ = function (s) { return document.querySelector(s); };
  var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };
  var money = function (n) { return '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
  var esc = function (s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); };
  var fmt = function (iso, time) { return new Date(iso).toLocaleString('en-US', time ? { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' } : { month: 'short', day: 'numeric', year: 'numeric' }); };
  var badge = function (c, t) { return '<span class="badge badge-' + c + '">' + t + '</span>'; };
  var td = function (v, c) { return '<td' + (c ? ' class="' + c + '"' : '') + '>' + v + '</td>'; };
  var btn = function (s, a, id, cls, label) { return '<button class="btn btn-sm btn-' + cls + '" type="button" data-s="' + s + '" data-a="' + a + '" data-id="' + esc(id) + '">' + label + '</button>'; };
  var actionCell = function (html) { return td('<div class="row-actions">' + html + '</div>', 'num'); };

  var toastEl, toastTimer;
  function toast(msg) {
    if (!toastEl) { toastEl = document.createElement('div'); toastEl.className = 'toast'; toastEl.setAttribute('role', 'status'); document.body.appendChild(toastEl); }
    toastEl.textContent = msg; toastEl.style.display = 'block';
    clearTimeout(toastTimer); toastTimer = setTimeout(function () { toastEl.style.display = 'none'; }, 3000);
  }

  function fill(sel, items) {   // items: 'text' or [value, text]
    var v = sel.value;
    sel.innerHTML = items.map(function (i) { var a = [].concat(i); return '<option value="' + esc(a[0]) + '">' + esc(a[a.length - 1]) + '</option>'; }).join('');
    if ([].some.call(sel.options, function (o) { return o.value === v; })) sel.value = v;
  }
  function fillRows(tbody, items, cols, fn, msg) {
    tbody.innerHTML = items.length ? items.map(fn).join('') : '<tr class="empty-row"><td colspan="' + cols + '">' + msg + '</td></tr>';
  }
  function ask(msg, def) {
    var v = prompt(msg, def); if (v === null) return null;
    v = parseFloat(v); if (isNaN(v) || v < 0) { toast('Enter a valid number.'); return null; }
    return v;
  }

  /* ---------- Data ---------- */
  function seed() {
    return {
      products: [
        { sku: 'CEM-040', name: 'Portland Cement 40kg', cat: 'Cement & Aggregates', supplier: 'Pacific Cement Corp.', price: 265, stock: 12, reorder: 20 },
        { sku: 'STL-D10', name: 'Deformed Bar 10mm x 6m', cat: 'Steel & Rebar', supplier: 'Luzon Steel Works', price: 198, stock: 340, reorder: 50 },
        { sku: 'LMB-P12', name: 'Marine Plywood 1/2"', cat: 'Lumber & Plywood', supplier: 'Calabarzon Lumber Co.', price: 1150, stock: 0, reorder: 15 },
        { sku: 'PLB-P20', name: 'PVC Pipe 2" x 3m', cat: 'Plumbing', supplier: 'Pacific Cement Corp.', price: 142, stock: 9, reorder: 20 },
        { sku: 'FST-N03', name: 'Common Wire Nails 3"', cat: 'Fasteners & Hardware', supplier: 'Luzon Steel Works', price: 78, stock: 96, reorder: 20 },
        { sku: 'PNT-E04', name: 'Latex Paint, Flat White 4L', cat: 'Paint & Finishes', supplier: 'Calabarzon Lumber Co.', price: 640, stock: 58, reorder: 15 }
      ],
      suppliers: [
        { name: 'Pacific Cement Corp.', contact: 'Ramon Villanueva', phone: '0917 555 0142', status: 'Active' },
        { name: 'Luzon Steel Works', contact: 'Angela Tan', phone: '0928 555 0187', status: 'Active' },
        { name: 'Calabarzon Lumber Co.', contact: 'Joel Mendoza', phone: '0905 555 0119', status: 'On hold' }
      ],
      pos: [
        { no: 'PO-2041', supplier: 'Pacific Cement Corp.', product: 'CEM-040', qty: 200, ordered: '2026-10-06T00:00', expected: '2026-10-12T00:00', amount: 53000, status: 'In transit' },
        { no: 'PO-2040', supplier: 'Luzon Steel Works', product: 'STL-D10', qty: 500, ordered: '2026-10-03T00:00', expected: '2026-10-09T00:00', amount: 99000, status: 'Received' },
        { no: 'PO-2039', supplier: 'Calabarzon Lumber Co.', product: 'LMB-P12', qty: 40, ordered: '2026-09-28T00:00', expected: '2026-10-14T00:00', amount: 46000, status: 'Pending' }
      ],
      moves: [
        { date: '2026-10-10T09:14', product: 'Deformed Bar 10mm x 6m', type: 'in', qty: 200, ref: 'PO-2040', by: 'System Administrator' },
        { date: '2026-10-10T08:40', product: 'Portland Cement 40kg', type: 'out', qty: -30, ref: '#S-10482', by: 'System Administrator' },
        { date: '2026-10-09T16:02', product: 'Common Wire Nails 3"', type: 'adj', qty: -2, ref: 'Count correction', by: 'Staff Member' },
        { date: '2026-10-09T13:25', product: 'PVC Pipe 2" x 3m', type: 'out', qty: -15, ref: '#S-10480', by: 'Staff Member' },
        { date: '2026-10-08T10:05', product: 'Latex Paint, Flat White 4L', type: 'in', qty: 48, ref: 'PO-2038', by: 'System Administrator' }
      ],
      sales: [
        { no: '#S-10482', date: '2026-10-10T08:40', customer: 'Dela Cruz Builders', pay: 'Bank transfer', status: 'Paid', total: 15870, items: [{ sku: 'CEM-040', name: 'Portland Cement 40kg', qty: 30, price: 265 }, { sku: 'STL-D10', name: 'Deformed Bar 10mm x 6m', qty: 40, price: 198 }] },
        { no: '#S-10481', date: '2026-10-10T08:10', customer: 'Walk-in customer', pay: 'Cash', status: 'Paid', total: 1420, items: [{ sku: 'FST-N03', name: 'Common Wire Nails 3"', qty: 10, price: 78 }, { sku: 'PNT-E04', name: 'Latex Paint, Flat White 4L', qty: 1, price: 640 }] },
        { no: '#S-10480', date: '2026-10-09T13:25', customer: 'Santos Hardware', pay: 'Credit (charge)', status: 'Credit', total: 21930, items: [{ sku: 'PLB-P20', name: 'PVC Pipe 2" x 3m', qty: 15, price: 142 }, { sku: 'STL-D10', name: 'Deformed Bar 10mm x 6m', qty: 100, price: 198 }] },
        { no: '#S-10479', date: '2026-10-09T09:30', customer: 'Walk-in customer', pay: 'GCash', status: 'Paid', total: 390, items: [{ sku: 'FST-N03', name: 'Common Wire Nails 3"', qty: 5, price: 78 }] }
      ]
    };
  }
  function apiSync(action, data) {
    var request = new XMLHttpRequest();
    request.open('POST', 'api.php', false);
    request.setRequestHeader('Content-Type', 'application/json');
    try { request.send(JSON.stringify({ action: action, data: data })); }
    catch (e) { alert('Cannot reach the PHP server. Start Apache and MySQL, then import database.sql.'); return null; }
    var result;
    try { result = JSON.parse(request.responseText); } catch (e) { result = {}; }
    if (request.status === 401) { localStorage.removeItem('bs_user'); sessionStorage.removeItem('bs_user'); location.href = 'login.php'; return null; }
    if (!result.data && !result.user && !result.ok) { alert(result.error || 'Database request failed.'); return null; }
    return result;
  }
  var currentUser = apiSync('current_user');
  if (!currentUser) return;
  user = currentUser.user;
  var userStore = localStorage.getItem('bs_user') ? localStorage : sessionStorage;
  userStore.setItem('bs_user', JSON.stringify(user));
  isAdmin = user.role === 'admin';
  var loaded = apiSync('load');
  if (!loaded) return;
  var db = loaded.data;
  var cart = [];
  function save() { return apiSync('save', db); }
  function find(sku) { return db.products.filter(function (p) { return p.sku === sku; })[0]; }
  function status(p) { return p.stock <= 0 ? ['out', 'Out of Stock'] : p.stock <= p.reorder ? ['low', 'Low Stock'] : ['in', 'In Stock']; }
  function nextNo(list, prefixLen) { return Math.max.apply(null, list.map(function (o) { return +o.no.slice(prefixLen); }).concat(0)) + 1; }
  function log(product, type, qty, ref) { db.moves.unshift({ date: new Date().toISOString(), product: product, type: type, qty: qty, ref: ref, by: user.name }); }
  function commit(msg) {
    if (save()) {
      renderAll();
      toast(msg);
      return true;
    }

    // The browser state was already changed when the action was started.
    // If saving fails, restore the last state confirmed by the server so a
    // retry does not incorrectly report that an unsaved record already exists.
    var restored = apiSync('load');
    if (restored && restored.data) {
      db = restored.data;
      cart = [];
      renderAll();
    }
    return false;
  }

  /* ---------- Rendering ---------- */
  function renderProducts() {
    var sel = $$('#products .toolbar select');
    var cats = []; db.products.forEach(function (p) { if (cats.indexOf(p.cat) < 0) cats.push(p.cat); });
    fill(sel[0], ['All categories'].concat(cats));
    var list = db.products.filter(function (p) {
      return (sel[0].selectedIndex < 1 || p.cat === sel[0].value) && (sel[1].selectedIndex < 1 || status(p)[1] === sel[1].value);
    });
    fillRows($('#products tbody'), list, 7, function (p) {
      var s = status(p);
      return '<tr>' + td(esc(p.sku), 'sku') + td(esc(p.name)) + td(esc(p.cat)) + td(money(p.price), 'num') + td(p.stock, 'num') + td(badge(s[0], s[1])) +
        (isAdmin ? actionCell(btn('prod', 'edit', p.sku, 'edit', 'Edit') + btn('prod', 'del', p.sku, 'delete', 'Delete')) : td('-', 'num')) + '</tr>';
    }, 'No products match these filters.');
  }

  function renderSuppliers() {
    var t = $$('#suppliers tbody');
    fillRows(t[0], db.suppliers, 5, function (s) {
      return '<tr>' + td(esc(s.name)) + td(esc(s.contact)) + td(esc(s.phone)) + td(badge(s.status === 'Active' ? 'in' : 'low', esc(s.status))) +
        actionCell(btn('sup', 'edit', s.name, 'edit', 'Edit') + btn('sup', 'del', s.name, 'delete', 'Delete')) + '</tr>';
    }, 'No suppliers yet. Select Add supplier to create one.');
    var cls = { 'Pending': 'low', 'In transit': 'info', 'Received': 'done', 'Cancelled': 'out' };
    fillRows(t[1], db.pos, 7, function (o) {
      var open = o.status === 'Pending' || o.status === 'In transit';
      return '<tr>' + td(o.no) + td(esc(o.supplier)) + td(fmt(o.ordered)) + td(fmt(o.expected)) + td(money(o.amount), 'num') + td(badge(cls[o.status], o.status)) +
        actionCell(open ? btn('po', 'up', o.no, 'edit', o.status === 'Pending' ? 'Mark shipped' : 'Mark received') + btn('po', 'cancel', o.no, 'delete', 'Cancel') : btn('po', 'view', o.no, 'edit', 'View')) + '</tr>';
    }, 'No purchase orders yet.');
  }

  function renderMoves() {
    var f = $('#movements .toolbar select').value;
    var map = { 'Stock in': 'in', 'Stock out': 'out', 'Adjustment': 'adj' };
    var label = { in: ['in', 'Stock in'], out: ['out', 'Stock out'], adj: ['info', 'Adjustment'] };
    var list = db.moves.filter(function (m) { return f === 'All types' || map[f] === m.type; });
    fillRows($('#movements tbody'), list, 6, function (m) {
      var l = label[m.type];
      return '<tr>' + td(fmt(m.date, true)) + td(esc(m.product)) + td(badge(l[0], l[1])) +
        td((m.qty > 0 ? '+' : '−') + Math.abs(m.qty), 'num ' + (m.qty > 0 ? 'qty-in' : 'qty-out')) + td(esc(m.ref)) + td(esc(m.by)) + '</tr>';
    }, 'No movements of this type.');
  }

  function cartTotal() { return cart.reduce(function (t, c) { return t + c.qty * c.price; }, 0); }
  function renderCart() {
    var rows = cart.map(function (c) {
      return '<li><span>' + esc(c.name) + ' × ' + c.qty + '</span><span>' + money(c.qty * c.price) + ' ' + btn('cart', 'rm', c.sku, 'delete', 'Remove') + '</span></li>';
    }).join('');
    $('#sales .alert-list').innerHTML = rows
      ? rows + '<li><span><strong>Total</strong></span><span><strong>' + money(cartTotal()) + '</strong></span></li>'
      : '<li><span>No items yet. Add a product to start.</span><span></span></li>';
  }

  function renderSales() {
    fillRows($('#sales tbody'), db.sales, 7, function (s) {
      var lines = s.items.map(function (i) { return esc(i.name) + ' × ' + i.qty; }).join('<br>');
      return '<tr>' + td(s.no) + td(fmt(s.date)) + td(esc(s.customer)) + td('<span class="item-lines">' + lines + '</span>') + td(esc(s.pay)) + td(money(s.total), 'num') +
        actionCell(btn('sale', 'view', s.no, 'edit', 'View') + (isAdmin ? btn('sale', 'void', s.no, 'delete', 'Void') : '')) + '</tr>';
    }, 'No sales recorded yet.');
    renderCart();
  }

  function renderDash() {
    var cats = {}; db.products.forEach(function (p) { cats[p.cat] = 1; });
    var low = db.products.filter(function (p) { return p.stock <= p.reorder; }).sort(function (a, b) { return a.stock - b.stock; });
    var sum = db.sales.reduce(function (t, s) { return t + s.total; }, 0);
    var transit = db.pos.filter(function (o) { return o.status === 'In transit'; }).length;
    var vals = [
      [db.products.length, Object.keys(cats).length + ' categories'],
      [low.length, 'At or below reorder level'],
      [db.suppliers.filter(function (s) { return s.status === 'Active'; }).length, transit + ' orders in transit'],
      [money(sum), db.sales.length + ' transactions']
    ];
    $$('#dash .stat').forEach(function (el, i) {
      el.querySelector('.stat-value').textContent = vals[i][0];
      el.querySelector('.stat-note').textContent = vals[i][1];
    });
    $$('#dash .stat-label')[3].textContent = 'Total sales';
    $('#dash .alert-list').innerHTML = low.slice(0, 5).map(function (p) {
      return '<li><span>' + esc(p.name) + '</span><span>' + (p.stock ? p.stock + ' left' : 'Out of stock') + '</span></li>';
    }).join('') || '<li><span>All stock levels are healthy.</span><span></span></li>';
    fillRows($('#dash tbody'), db.sales.slice(0, 4), 5, function (s) {
      return '<tr>' + td(s.no) + td(esc(s.customer)) + td(fmt(s.date)) + td(money(s.total), 'num') + td(badge(s.status === 'Paid' ? 'done' : 'low', s.status)) + '</tr>';
    }, 'No sales yet.');
  }

  function fillSelects() {
    var names = db.suppliers.map(function (s) { return s.name; });
    var prods = db.products.map(function (p) { return [p.sku, p.name]; });
    fill($('#p-supplier'), names); fill($('#po-supplier'), names);
    fill($('#po-product'), prods); fill($('#m-product'), prods); fill($('#s-product'), prods);
  }

  var search = $('.search input');
  function applySearch() {   // header search filters the table rows
    var q = search.value.trim().toLowerCase();
    $$('.view tbody tr').forEach(function (tr) { tr.style.display = !q || tr.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none'; });
  }
  function renderAll() { fillSelects(); renderProducts(); renderSuppliers(); renderMoves(); renderSales(); renderDash(); applySearch(); }

  /* ---------- Row actions (Edit / Delete / status changes) ---------- */
  var actions = {
    'prod-edit': function (sku) {
      var p = find(sku), price = ask('New unit price (₱) for ' + p.name, p.price); if (price === null) return;
      var stock = ask('Stock on hand', p.stock); if (stock === null) return;
      if (stock !== p.stock) log(p.name, 'adj', stock - p.stock, 'Manual edit');
      p.price = price; p.stock = stock; commit('Product updated.');
    },
    'prod-del': function (sku) {
      if (!confirm('Delete ' + find(sku).name + '? This cannot be undone.')) return;
      db.products = db.products.filter(function (p) { return p.sku !== sku; }); commit('Product deleted.');
    },
    'sup-edit': function (name) {
      var s = db.suppliers.filter(function (x) { return x.name === name; })[0];
      var contact = prompt('Contact person:', s.contact); if (contact === null) return;
      var phone = prompt('Phone number:', s.phone); if (phone === null) return;
      var st = prompt('Status (Active or On hold):', s.status); if (st === null) return;
      s.contact = contact.trim() || s.contact; s.phone = phone.trim() || s.phone; s.status = /hold/i.test(st) ? 'On hold' : 'Active';
      commit('Supplier updated.');
    },
    'sup-del': function (name) {
      if (!confirm('Delete supplier ' + name + '?')) return;
      db.suppliers = db.suppliers.filter(function (x) { return x.name !== name; }); commit('Supplier deleted.');
    },
    'po-up': function (no) {
      var o = db.pos.filter(function (x) { return x.no === no; })[0];
      if (o.status === 'Pending') { o.status = 'In transit'; return commit(no + ' marked as shipped.'); }
      var p = find(o.product); o.status = 'Received';
      if (p) { p.stock += o.qty; log(p.name, 'in', o.qty, no); }
      commit(no + ' received. Stock updated.');
    },
    'po-cancel': function (no) {
      if (!confirm('Cancel ' + no + '?')) return;
      db.pos.filter(function (x) { return x.no === no; })[0].status = 'Cancelled'; commit(no + ' cancelled.');
    },
    'po-view': function (no) {
      var o = db.pos.filter(function (x) { return x.no === no; })[0], p = find(o.product);
      alert(o.no + '\nSupplier: ' + o.supplier + '\nItem: ' + (p ? p.name : o.product) + ' × ' + o.qty + '\nAmount: ' + money(o.amount) + '\nStatus: ' + o.status);
    },
    'sale-view': function (no) {
      var s = db.sales.filter(function (x) { return x.no === no; })[0];
      alert(s.no + ' - ' + s.customer + '\n' + s.items.map(function (i) { return i.name + ' × ' + i.qty + ' @ ' + money(i.price); }).join('\n') + '\nTotal: ' + money(s.total) + ' (' + s.pay + ')');
    },
    'sale-void': function (no) {
      if (!confirm('Void ' + no + '? Items will be returned to stock.')) return;
      var s = db.sales.filter(function (x) { return x.no === no; })[0];
      s.items.forEach(function (i) { var p = find(i.sku); if (p) { p.stock += i.qty; log(p.name, 'in', i.qty, 'Void ' + no); } });
      db.sales = db.sales.filter(function (x) { return x.no !== no; }); commit(no + ' voided.');
    },
    'cart-rm': function (sku) { cart = cart.filter(function (c) { return c.sku !== sku; }); renderCart(); }
  };

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.profile')) $('.profile').open = false;   // close profile menu on outside click
    var b = e.target.closest('[data-a]'); if (!b) return;
    var fn = actions[b.dataset.s + '-' + b.dataset.a]; if (fn) fn(b.dataset.id);
  });

  /* ---------- Forms ---------- */
  $('#products form').addEventListener('submit', function (e) {   // Create product
    e.preventDefault(); if (!isAdmin) return;
    var sku = $('#p-sku').value.trim().toUpperCase();
    if (find(sku)) return toast('SKU ' + sku + ' already exists.');
    db.products.push({ sku: sku, name: $('#p-name').value.trim(), cat: $('#p-cat').value, supplier: $('#p-supplier').value, price: +$('#p-price').value, stock: +$('#p-stock').value, reorder: +$('#p-reorder').value || 0 });
    if (commit('Product saved.')) e.target.reset();
  });

  $('#suppliers .panel-head .btn-blue').addEventListener('click', function () {   // Create supplier
    var name = prompt('Supplier name:'); if (!name || !name.trim()) return;
    var contact = prompt('Contact person:', '') || '-', phone = prompt('Phone number:', '') || '-';
    db.suppliers.push({ name: name.trim(), contact: contact.trim(), phone: phone.trim(), status: 'Active' }); commit('Supplier added.');
  });

  $('#suppliers form').addEventListener('submit', function (e) {   // Create purchase order
    e.preventDefault();
    var p = find($('#po-product').value), qty = +$('#po-qty').value;
    if (!p || qty < 1) return toast('Enter a quantity of 1 or more.');
    var exp = $('#po-date').value || new Date(Date.now() + 6048e5).toISOString().slice(0, 10);
    db.pos.unshift({ no: 'PO-' + nextNo(db.pos, 3), supplier: $('#po-supplier').value, product: p.sku, qty: qty, ordered: new Date().toISOString(), expected: exp + 'T00:00', amount: qty * p.price, status: 'Pending' });
    commit('Purchase order placed.'); e.target.reset();
  });

  $('#m-type').addEventListener('change', function () {   // adjustments may be negative
    var adj = this.value === 'Adjustment';
    $('#m-qty').min = adj ? '' : '1'; $('#m-qty').placeholder = adj ? '+ or - change' : '0';
  });
  $('#movements form').addEventListener('submit', function (e) {   // Record stock movement
    e.preventDefault();
    var t = $('#m-type').value, q = +$('#m-qty').value, p = find($('#m-product').value), note = $('#m-note').value.trim();
    if (!p || !q || (t !== 'Adjustment' && q < 0)) return toast('Enter a valid quantity.');
    if (t === 'Adjustment' && !note) return toast('Add a note explaining the adjustment.');
    var d = t === 'Stock out' ? -q : q;
    if (p.stock + d < 0) return toast('Only ' + p.stock + ' of ' + p.name + ' on hand.');
    p.stock += d;
    log(p.name, t === 'Stock in' ? 'in' : t === 'Stock out' ? 'out' : 'adj', d, $('#m-ref').value.trim() || note || '-');
    commit('Movement recorded.'); e.target.reset();
  });

  $('#sales .btn-blue').addEventListener('click', function () {   // Add item to current order
    var p = find($('#s-product').value), q = +$('#s-qty').value;
    if (!p || q < 1) return toast('Enter a quantity of 1 or more.');
    var line = cart.filter(function (c) { return c.sku === p.sku; })[0], want = (line ? line.qty : 0) + q;
    if (want > p.stock) return toast('Only ' + p.stock + ' of ' + p.name + ' in stock.');
    if (line) line.qty = want; else cart.push({ sku: p.sku, name: p.name, qty: q, price: p.price });
    $('#s-qty').value = 1; renderCart();
  });
  $('#sales form').addEventListener('submit', function (e) {   // Complete sale
    e.preventDefault();
    if (!cart.length) return toast('Add at least one item first.');
    var no = '#S-' + nextNo(db.sales, 3), pay = $('#s-pay').value;
    cart.forEach(function (c) { var p = find(c.sku); p.stock -= c.qty; log(p.name, 'out', -c.qty, no); });
    db.sales.unshift({ no: no, date: new Date().toISOString(), customer: $('#s-customer').value.trim() || 'Walk-in customer', pay: pay, status: pay === 'Credit (charge)' ? 'Credit' : 'Paid', total: cartTotal(), items: cart.map(function (c) { return { sku: c.sku, name: c.name, qty: c.qty, price: c.price }; }) });
    cart = []; e.target.reset(); commit('Sale ' + no + ' completed.');
  });

  /* ---------- Header, filters, role setup ---------- */
  search.addEventListener('input', applySearch);
  $$('#products .toolbar select, #movements .toolbar select').forEach(function (s) { s.addEventListener('change', renderAll); });

  $('.profile-name').textContent = user.name + ' (' + (isAdmin ? 'Admin' : 'Staff') + ')';
  $('.avatar').textContent = user.name.split(' ').map(function (w) { return w[0]; }).join('').slice(0, 2).toUpperCase();

  var menu = $$('.profile-menu a');   // My profile, Store settings, Sign out
  menu[0].addEventListener('click', function (e) { e.preventDefault(); toast('Signed in as ' + user.name + ' (' + user.role + ').'); });
  menu[1].textContent = 'Reset demo data';
  menu[1].addEventListener('click', function (e) {
    e.preventDefault();
    if (isAdmin && confirm('Reset all products, orders and sales to the sample data?')) { db = seed(); cart = []; commit('Demo data restored.'); }
    else if (!isAdmin) toast('Only admins can reset data.');
  });
  menu[2].addEventListener('click', function () { apiSync('logout'); localStorage.removeItem('bs_user'); sessionStorage.removeItem('bs_user'); });

  $('#dash .view-head a').addEventListener('click', function (e) { e.preventDefault(); $('#v-sales').checked = true; });

  if (!isAdmin) {   // Staff: no supplier module, no product add form
    $('.nav label[for="v-suppliers"]').style.display = 'none';
    $('#products .panel').style.display = 'none';
  }

  renderAll();
})();


</script>
</body>
</html>
