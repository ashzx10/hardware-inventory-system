<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PrimeBuild | Hardware & Construction Supply</title>
    <link rel="icon" type="image/png" href="assets/primebuild-icon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        <input class="view-toggle" type="radio" name="view" id="v-history">
        <input class="view-toggle" type="radio" name="view" id="v-archive">

        <!-- ================= SIDEBAR ================= -->
        <aside class="sidebar offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-label="Menu">
            <div class="brand">
                <img class="brand-logo" src="assets/primebuild-logo.png"
                    alt="PrimeBuild Hardware & Construction Supply">
            </div>
            <nav class="nav" aria-label="Main navigation">
                <label for="v-dash"><i class="nav-icon bi bi-speedometer2"></i>Dashboard</label>
                <label for="v-products"><i class="nav-icon bi bi-box-seam"></i>Products & Categories</label>
                <label for="v-suppliers"><i class="nav-icon bi bi-truck"></i>Suppliers & Orders</label>
                <label for="v-movements"><i class="nav-icon bi bi-arrow-left-right"></i>Stock Movements</label>
                <label for="v-sales"><i class="nav-icon bi bi-cart-check"></i>Sales</label>
                <label for="v-archive" data-admin><i class="nav-icon bi bi-archive"></i>Archive Items</label>
            </nav>
            <footer class="sidebar-foot">PrimeBuild Inventory System</footer>
        </aside>

        <!-- ================= MAIN ================= -->
        <div class="main">

            <header class="topbar">
                <div class="topbar-left">
                    <button class="burger" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar"
                        aria-controls="sidebar" aria-label="Open menu"><i class="bi bi-list"></i></button>
                    <span class="topbar-title">PrimeBuild</span>
                </div>
                <details class="profile">
                    <summary>
                        <span class="avatar">PB</span>
                        <span class="profile-info"><span class="profile-name">Loading…</span><span
                                class="role-badge"></span></span>
                        <i class="profile-caret bi bi-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <nav class="profile-menu" aria-label="Account">
                        <div class="menu-user"></div>
                        <hr>
                        <a href="login.php"><i class="bi bi-box-arrow-right"></i>Sign out</a>
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
                    </header>

                    <section class="stats" aria-label="Key figures">
                        <div class="stat warn">
                            <div class="stat-top"><span class="stat-label">Low-Stock Alerts</span><i
                                    class="bi bi-exclamation-triangle stat-icon"></i></div>
                            <div class="stat-value" id="s-low">0</div>
                            <div class="stat-note" id="n-low">At or below reorder level</div>
                        </div>
                        <div class="stat warn">
                            <div class="stat-top"><span class="stat-label">Out-of-Stock Items</span><i
                                    class="bi bi-x-octagon stat-icon"></i></div>
                            <div class="stat-value" id="s-out">0</div>
                            <div class="stat-note" id="n-out">Need an urgent order</div>
                        </div>
                        <div class="stat">
                            <div class="stat-top"><span class="stat-label">Sales Today</span><i
                                    class="bi bi-cash-coin stat-icon"></i></div>
                            <div class="stat-value" id="s-today">0</div>
                            <div class="stat-note" id="n-today">transactions today</div>
                        </div>
                        <div class="stat">
                            <div class="stat-top"><span class="stat-label">Sales This Month</span><i
                                    class="bi bi-calendar-check stat-icon"></i></div>
                            <div class="stat-value" id="s-month">0</div>
                            <div class="stat-note" id="n-month">transactions this month</div>
                        </div>
                        <div class="stat">
                            <div class="stat-top"><span class="stat-label">Inventory Value</span><i
                                    class="bi bi-bank stat-icon"></i></div>
                            <div class="stat-value" id="s-value">0</div>
                            <div class="stat-note" id="n-value">Stock on hand at selling price</div>
                        </div>
                    </section>

                    <div class="dash-two">
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-graph-up-arrow"></i> Sales, last 7 days</h2>
                            </div>
                            <div class="panel-body chart-box"><canvas id="chartSales"
                                    aria-label="Sales for the last 7 days"></canvas></div>
                        </section>
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-pie-chart"></i> Stock by category</h2>
                            </div>
                            <div class="panel-body chart-box"><canvas id="chartStock"
                                    aria-label="Units in stock by category"></canvas></div>
                        </section>
                    </div>

                    <div class="dash-three">
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-trophy"></i> Top selling products</h2>
                            </div>
                            <div class="panel-body" id="topList"></div>
                        </section>
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-exclamation-triangle"></i> Needs restocking</h2>
                            </div>
                            <div class="panel-body">
                                <ul class="alert-list" id="restockList"></ul>
                            </div>
                        </section>
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-lightning-charge"></i> Quick actions</h2>
                            </div>
                            <div class="panel-body quick-grid">
                                <button class="btn btn-primary" type="button" data-goto="v-sales"><i
                                        class="bi bi-cart-plus"></i> New Sale</button>
                                <button class="btn btn-ghost" type="button" data-goto="v-movements"><i
                                        class="bi bi-arrow-left-right"></i> Record Stock Movement</button>
                                <button class="btn btn-ghost" type="button" data-goto="v-products" data-admin><i
                                        class="bi bi-plus-circle"></i> Add Product</button>
                                <button class="btn btn-ghost" type="button" data-goto="v-suppliers" data-admin><i
                                        class="bi bi-clipboard-plus"></i> Create Purchase Order</button>
                            </div>
                        </section>
                    </div>

                    <div class="dash-two wide-left">
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-receipt"></i> Recent sales</h2><button class="btn btn-sm btn-edit"
                                    type="button" data-goto="v-sales">View all</button>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <caption class="visually-hidden">Recent sales</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Receipt</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Payment</th>
                                            <th scope="col" class="num">Total</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </section>
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-arrow-left-right"></i> Recent stock movements</h2><button
                                    class="btn btn-sm btn-edit" type="button" data-goto="v-history">View all</button>
                            </div>
                            <div class="table-wrap">
                                <table>
                                    <caption class="visually-hidden">Recent stock movements</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Product</th>
                                            <th scope="col">Type</th>
                                            <th scope="col" class="num">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentMoves"></tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </section>

                <!-- ===== 2. PRODUCTS & CATEGORIES ===== -->
                <section class="view" id="products">
                    <header class="view-head">
                        <div>
                            <h1>Products & Categories</h1>
                            <p>Browse the catalog and keep prices and stock levels up to date.</p>
                        </div>
                        <div class="toolbar" data-admin>
                            <button class="btn btn-ghost" type="button" id="btnCategories"><i class="bi bi-tags"></i>
                                Manage Categories</button>
                            <button class="btn btn-primary" type="button" id="btnAddProduct"><i
                                    class="bi bi-plus-circle"></i> Add Product</button>
                        </div>
                    </header>

                    <section class="panel">
                        <header class="panel-head">
                            <h2><i class="bi bi-box-seam"></i> Product list</h2>
                            <div class="toolbar">
                                <input class="table-search" id="prodSearch" type="search"
                                    placeholder="Search by name, Stock Keeping Unit or category"
                                    aria-label="Search products">
                                <select id="fCat" aria-label="Filter by category">
                                    <option>All categories</option>
                                </select>
                                <select id="fStatus" aria-label="Filter by status">
                                    <option>All statuses</option>
                                    <option>In Stock</option>
                                    <option>Low Stock</option>
                                    <option>Out of Stock</option>
                                </select>
                            </div>
                        </header>
                        <div class="table-wrap">
                            <table>
                                <caption class="visually-hidden">Product list</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Stock Keeping Unit</th>
                                        <th scope="col">Product</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Supplier</th>
                                        <th scope="col" class="num">Unit price</th>
                                        <th scope="col" class="num">Stock</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="num" data-admin>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </section>
                </section>

                <!-- ===== 3. SUPPLIERS & PURCHASE ORDERS (administrator only) ===== -->
                <section class="view" id="suppliers">
                    <header class="view-head">
                        <div>
                            <h1>Suppliers & Purchase Orders</h1>
                            <p>Manage who you buy from and track what is on the way.</p>
                        </div>
                    </header>

                    <div class="split">
                        <section class="panel">
                            <header class="panel-head">
                                <h2><i class="bi bi-truck"></i> Suppliers</h2>
                                <button class="btn btn-sm btn-primary" type="button" id="btnAddSupplier"><i
                                        class="bi bi-plus-circle"></i> Add Supplier</button>
                            </header>
                            <div class="panel-body panel-search"><input class="table-search w-100" id="supSearch"
                                    type="search" placeholder="Search suppliers by name, contact or phone"
                                    aria-label="Search suppliers"></div>
                            <div class="table-wrap">
                                <table>
                                    <caption class="visually-hidden">Suppliers</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Supplier</th>
                                            <th scope="col">Contact person</th>
                                            <th scope="col">Phone number</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" class="num">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="supRows"></tbody>
                                </table>
                            </div>
                        </section>

                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-clipboard-plus"></i> Create purchase order</h2>
                            </div>
                            <div class="panel-body">
                                <div class="form-grid">
                                    <div class="field wide"><label for="po-supplier">Supplier</label><select
                                            id="po-supplier"></select></div>
                                    <div class="field wide"><label for="po-product">Product</label><select
                                            id="po-product"></select><small>Only products from this supplier are
                                            listed.</small></div>
                                    <div class="field"><label for="po-qty">Quantity</label><input id="po-qty"
                                            type="number" min="1" step="1" placeholder="100">
                                        <div class="field-error"></div>
                                    </div>
                                    <div class="field"><label for="po-date">Expected delivery</label><input id="po-date"
                                            type="date">
                                        <div class="field-error"></div>
                                    </div>
                                    <div class="field wide"><label for="po-amount">Order amount (calculated
                                            automatically)</label><input id="po-amount" type="text" readonly
                                            placeholder="Enter a quantity"><small>The purchase order number is created
                                            for you when you place the order.</small></div>
                                </div>
                                <div class="form-actions"><button class="btn btn-primary" type="button" id="po-save"><i
                                            class="bi bi-send-check"></i> Place order</button></div>
                            </div>
                        </section>
                    </div>

                    <section class="panel">
                        <header class="panel-head">
                            <h2><i class="bi bi-clipboard-check"></i> Purchase orders</h2>
                            <div class="toolbar">
                                <input class="table-search" id="poSearch" type="search"
                                    placeholder="Search by number, supplier or product"
                                    aria-label="Search purchase orders">
                                <select id="poStatus" aria-label="Filter by status">
                                    <option value="">All statuses</option>
                                    <option>Pending</option>
                                    <option>In transit</option>
                                    <option>Received</option>
                                    <option>Cancelled</option>
                                </select>
                            </div>
                        </header>
                        <div class="table-wrap">
                            <table>
                                <caption class="visually-hidden">Purchase orders</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Purchase order number</th>
                                        <th scope="col">Supplier</th>
                                        <th scope="col">Product</th>
                                        <th scope="col" class="num">Quantity</th>
                                        <th scope="col">Expected delivery</th>
                                        <th scope="col" class="num">Amount</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="num">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="poRows"></tbody>
                            </table>
                        </div>
                    </section>
                </section>

                <!-- ===== 4. STOCK MOVEMENTS ===== -->
                <section class="view" id="movements">
                    <header class="view-head">
                        <div>
                            <h1>Stock Movements</h1>
                            <p>Record stock coming in, going out and corrections.</p>
                        </div>
                    </header>

                    <section class="panel">
                        <div class="panel-head">
                            <h2><i class="bi bi-pencil-square"></i> Record a movement</h2>
                        </div>
                        <div class="panel-body">
                            <div class="form-grid">
                                <div class="field span2"><label for="m-product">Product</label><select
                                        id="m-product"></select></div>
                                <div class="field"><label for="m-type">Movement type</label><select
                                        id="m-type"></select></div>
                                <div class="field"><label for="m-qty">Quantity</label><input id="m-qty" type="number"
                                        step="1" placeholder="0"><small id="m-qtyHelp">How many units moved.</small>
                                </div>
                                <div class="field"><label for="m-sku">Stock Keeping Unit</label><input id="m-sku"
                                        type="text" readonly></div>
                                <div class="field"><label for="m-stock">Current stock</label><input id="m-stock"
                                        type="text" readonly></div>
                                <div class="field span2"><label for="m-note">Note (optional)</label><textarea
                                        id="m-note" rows="2"
                                        placeholder="Add a reason if it helps your team, for example a count correction."></textarea><small>The
                                        reference number is created automatically.</small></div>
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-primary" type="button" id="m-save"><i
                                        class="bi bi-check2-circle"></i> Record movement</button>
                                <button class="btn btn-ghost" type="button" id="m-clear"><i class="bi bi-eraser"></i>
                                    Clear form</button>
                            </div>
                        </div>
                    </section>

                    <section class="panel">
                        <header class="panel-head">
                            <h2><i class="bi bi-clock-history"></i> Latest movements</h2>
                            <button class="btn btn-sm btn-edit" type="button" data-goto="v-history">View all</button>
                        </header>
                        <div class="table-wrap">
                            <table>
                                <caption class="visually-hidden">Latest five movements</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Product</th>
                                        <th scope="col">Type</th>
                                        <th scope="col" class="num">Quantity</th>
                                        <th scope="col">Reference</th>
                                        <th scope="col">Recorded by</th>
                                    </tr>
                                </thead>
                                <tbody id="recentMovesFull"></tbody>
                            </table>
                        </div>
                    </section>
                </section>

                <!-- ===== 4b. STOCK MOVEMENT HISTORY ===== -->
                <section class="view" id="history">
                    <header class="view-head">
                        <div>
                            <h1>Stock Movement History</h1>
                            <p>The full log of every stock-in, stock-out and adjustment.</p>
                        </div>
                        <button class="btn btn-ghost" type="button" data-goto="v-movements"><i
                                class="bi bi-arrow-left"></i> Back to Stock Movements</button>
                    </header>
                    <section class="panel">
                        <header class="panel-head">
                            <h2><i class="bi bi-journal-text"></i> Movement log</h2>
                            <button class="btn btn-sm btn-edit" type="button" id="hExport"><i
                                    class="bi bi-download"></i> Download Movement Report</button>
                        </header>
                        <div class="panel-body filter-bar">
                            <div class="field"><label for="hSearch">Search</label><input id="hSearch" type="search"
                                    placeholder="Product, reference, note or person"></div>
                            <div class="field"><label for="hType">Movement type</label><select id="hType">
                                    <option value="">All types</option>
                                    <option value="in">Stock in</option>
                                    <option value="out">Stock out</option>
                                    <option value="adj">Adjustment</option>
                                </select></div>
                            <div class="field"><label for="hProduct">Product</label><select id="hProduct"></select>
                            </div>
                            <div class="field"><label for="hYear">Year</label><select id="hYear"></select></div>
                            <div class="field"><label for="hFrom">From date</label><input id="hFrom" type="date"></div>
                            <div class="field"><label for="hTo">To date</label><input id="hTo" type="date"></div>
                            <div class="field filter-clear"><button class="btn btn-ghost" type="button"
                                    id="hClear">Clear filters</button></div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <caption class="visually-hidden">Stock movement history</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Movement number</th>
                                        <th scope="col">Product</th>
                                        <th scope="col">Type</th>
                                        <th scope="col" class="num">Quantity</th>
                                        <th scope="col">Reference</th>
                                        <th scope="col">Note</th>
                                        <th scope="col">Recorded by</th>
                                    </tr>
                                </thead>
                                <tbody id="historyRows"></tbody>
                            </table>
                        </div>
                        <div class="pager"><span id="hInfo" class="muted"></span>
                            <div><button class="btn btn-sm btn-ghost" type="button" id="hPrev"><i
                                        class="bi bi-chevron-left"></i> Previous</button> <button
                                    class="btn btn-sm btn-ghost" type="button" id="hNext">Next <i
                                        class="bi bi-chevron-right"></i></button></div>
                        </div>
                    </section>
                </section>

                <!-- ===== 5. SALES ===== -->
                <section class="view" id="sales">
                    <header class="view-head">
                        <div>
                            <h1>Sales</h1>
                            <p>Ring up an order, collect payment and review past transactions.</p>
                        </div>
                    </header>

                    <div class="split">
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-cart-plus"></i> New sale</h2>
                            </div>
                            <div class="panel-body">
                                <div class="form-grid">
                                    <div class="field"><label for="s-pay">Payment method</label><select
                                            id="s-pay"></select></div>
                                    <div class="field"><label for="s-product">Product</label><select
                                            id="s-product"></select></div>
                                    <div class="field"><label for="s-qty">Quantity</label><input id="s-qty"
                                            type="number" min="1" step="1" value="1"></div>
                                    <div class="field wide">
                                        <div class="sale-info" id="s-info" aria-live="polite"></div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="btn btn-ghost" type="button" id="s-add"><i class="bi bi-plus-lg"></i>
                                        Add Item</button>
                                    <button class="btn btn-primary" type="button" id="s-complete" disabled><i
                                            class="bi bi-credit-card"></i> Complete Sale</button>
                                </div>
                            </div>
                        </section>
                        <section class="panel">
                            <div class="panel-head">
                                <h2><i class="bi bi-basket"></i> Current order</h2>
                            </div>
                            <div class="panel-body">
                                <ul class="cart-list" id="cartList"></ul>
                            </div>
                        </section>
                    </div>

                    <section class="panel">
                        <header class="panel-head">
                            <h2><i class="bi bi-receipt"></i> Order history</h2>
                            <button class="btn btn-sm btn-edit" type="button" id="oExport"><i
                                    class="bi bi-download"></i> Download Sales Report</button>
                        </header>
                        <div class="panel-body filter-bar">
                            <div class="field"><label for="oSearch">Search</label><input id="oSearch" type="search"
                                    placeholder="Receipt number, cashier or item"></div>
                            <div class="field"><label for="oYear">Year</label><select id="oYear"></select></div>
                            <div class="field"><label for="oPeriod">Period</label><select id="oPeriod">
                                    <option value="">All dates</option>
                                    <option value="today">Today</option>
                                    <option value="week">Last 7 days</option>
                                    <option value="month">This month</option>
                                    <option value="year">This year</option>
                                    <option value="custom">Choose dates...</option>
                                </select></div>
                            <div class="field" id="oFromBox" hidden><label for="oFrom">From date</label><input
                                    id="oFrom" type="date"></div>
                            <div class="field" id="oToBox" hidden><label for="oTo">To date</label><input id="oTo"
                                    type="date"></div>
                            <div class="field"><label for="oProduct">Items sold</label><select id="oProduct"></select>
                            </div>
                            <div class="field"><label for="oPay">Payment method</label><select id="oPay">
                                    <option value="">All methods</option>
                                    <option>Cash</option>
                                    <option>Bank Transfer</option>
                                    <option>E-Wallet</option>
                                </select></div>
                            <div class="field filter-clear"><button class="btn btn-ghost" type="button"
                                    id="oClear">Clear filters</button></div>
                        </div>
                        <div class="totals-strip" id="oTotals"></div>
                        <div class="table-wrap">
                            <table>
                                <caption class="visually-hidden">Order history</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Receipt</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Items sold</th>
                                        <th scope="col">Payment</th>
                                        <th scope="col" class="num">Total</th>
                                        <th scope="col" class="num">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="orderRows"></tbody>
                            </table>
                        </div>
                        <div class="pager"><span id="oInfo" class="muted"></span>
                            <div><button class="btn btn-sm btn-ghost" type="button" id="oPrev"><i
                                        class="bi bi-chevron-left"></i> Previous</button> <button
                                    class="btn btn-sm btn-ghost" type="button" id="oNext">Next <i
                                        class="bi bi-chevron-right"></i></button></div>
                        </div>
                    </section>
                </section>

                <!-- ===== 6. ARCHIVE AND DELETED ITEMS (administrator only) ===== -->
                <section class="view" id="archive">
                    <header class="view-head">
                        <div>
                            <h1>Archive and Deleted Items</h1>
                            <p>Everything that was archived stays safe here. You can look at it or restore it.</p>
                        </div>
                    </header>
                    <div class="arch-tabs" role="tablist" aria-label="Archived item types">
                        <button type="button" class="active" data-tab="products" role="tab"><i
                                class="bi bi-box-seam"></i> Products <span class="tab-count">0</span></button>
                        <button type="button" data-tab="suppliers" role="tab"><i class="bi bi-truck"></i> Suppliers
                            <span class="tab-count">0</span></button>
                        <button type="button" data-tab="sales" role="tab"><i class="bi bi-receipt"></i> Sales <span
                                class="tab-count">0</span></button>
                        <button type="button" data-tab="pos" role="tab"><i class="bi bi-clipboard-check"></i> Purchase
                            Orders <span class="tab-count">0</span></button>
                    </div>
                    <section class="panel">
                        <header class="panel-head">
                            <h2><i class="bi bi-archive"></i> <span id="archTitle">Archived products</span></h2>
                            <input class="table-search" id="archSearch" type="search"
                                placeholder="Search archived items" aria-label="Search archived items">
                        </header>
                        <div class="table-wrap">
                            <table>
                                <caption class="visually-hidden">Archived items</caption>
                                <thead id="archHead"></thead>
                                <tbody id="archRows"></tbody>
                            </table>
                        </div>
                    </section>
                </section>

            </main>
        </div>
    </div>
    <!-- Product modal (add and edit) -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="productModalTitle">Add product</h2><button type="button"
                        class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="field wide"><label for="p-name">Product name</label><input id="p-name" type="text"
                                placeholder="e.g. Portland Cement 40kg" autocomplete="off">
                            <div class="field-error"></div>
                        </div>
                        <div class="field"><label for="p-cat">Category</label><select id="p-cat"></select>
                            <div class="field-error"></div>
                        </div>
                        <div class="field"><label for="p-supplier">Supplier</label><select id="p-supplier"></select>
                            <div class="field-error"></div>
                        </div>
                        <div class="field"><label for="p-sku">Stock Keeping Unit (generated automatically)</label><input
                                id="p-sku" type="text" readonly><small>Created for you when you save. You never type
                                it.</small></div>
                        <div class="field"><label for="p-price">Unit price (&#8369;)</label><input id="p-price"
                                type="number" min="0" step="0.01" placeholder="0.00">
                            <div class="field-error"></div>
                        </div>
                        <div class="field" id="p-stockField"><label for="p-stock">Opening stock</label><input
                                id="p-stock" type="number" min="0" step="1" placeholder="0"><small>How many are on the
                                shelf today.</small></div>
                        <div class="field"><label for="p-reorder">Reorder level</label><input id="p-reorder"
                                type="number" min="0" step="1" placeholder="20"><small>You will get a low-stock alert at
                                this quantity.</small></div>
                    </div>
                    <p class="muted mt-3 mb-0" id="p-editNote" hidden>To change how many are in stock, record a movement
                        on the Stock Movements page.</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost"
                        data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary"
                        id="p-save">Save product</button></div>
            </div>
        </div>
    </div>

    <!-- Category modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="categoryModalTitle">Manage categories</h2><button type="button"
                        class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="cat-add"><input id="catNew" class="table-search" type="text"
                            placeholder="New category name" aria-label="New category name"><button
                            class="btn btn-primary" type="button" id="catAdd"><i class="bi bi-plus-lg"></i> Add</button>
                    </div>
                    <p class="muted small">The first three letters become the code for new Stock Keeping Units.</p>
                    <div id="catList"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost"
                        data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>

    <!-- Payment modal (point of sale) -->
    <div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="payModalTitle">Collect payment</h2><button type="button"
                        class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="alert-list" id="payLines"></ul>
                    <div class="pay-due"><span>Total amount due</span><strong id="payTotal">&#8369;0.00</strong></div>
                    <p class="muted">Payment method: <strong id="payMethod"></strong></p>
                    <div class="field" id="cashBox">
                        <label for="payReceived">Amount received from customer (&#8369;)</label>
                        <div class="d-flex gap-2"><input id="payReceived" type="number" min="0" step="0.01"
                                placeholder="0.00"><button class="btn btn-ghost" type="button" id="payExact">Exact
                                amount</button></div>
                        <div class="field-error"></div>
                        <div class="pay-change"><span>Change</span><strong id="payChange">&#8369;0.00</strong></div>
                    </div>
                    <div class="field" id="refBox" hidden><label for="payRef" id="refLabel">Reference
                            number</label><input id="payRef" type="text" placeholder="Reference number from the payment"
                            autocomplete="off">
                        <div class="field-error"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost"
                        data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary"
                        id="payConfirm"><i class="bi bi-check2-circle"></i> Confirm payment</button></div>
            </div>
        </div>
    </div>

    <!-- Receipt modal (also used by View) -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="receiptModalTitle">Receipt</h2><button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="receiptBody"></div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost"
                        data-bs-dismiss="modal">Close</button><button type="button" class="btn btn-primary"
                        id="receiptPrint"><i class="bi bi-printer"></i> Print receipt</button></div>
            </div>
        </div>
    </div>

    <!-- Supplier modal (add and edit) -->
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="supplierModalTitle">Add supplier</h2><button type="button"
                        class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="field wide"><label for="sp-name">Supplier name</label><input id="sp-name"
                                type="text" placeholder="e.g. Pacific Cement Corp." autocomplete="off">
                            <div class="field-error"></div>
                        </div>
                        <div class="field"><label for="sp-contact">Contact person</label><input id="sp-contact"
                                type="text" placeholder="Full name" autocomplete="off"></div>
                        <div class="field"><label for="sp-phone">Phone number</label><input id="sp-phone" type="tel"
                                placeholder="0917 555 0100" autocomplete="off"></div>
                        <div class="field wide"><label for="sp-status">Status</label><select id="sp-status">
                                <option>Active</option>
                                <option>On hold</option>
                            </select><small>Choose On hold to pause ordering without deleting the supplier.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost"
                        data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary"
                        id="sp-save">Save supplier</button></div>
            </div>
        </div>
    </div>

    <!-- Purchase order details modal -->
    <div class="modal fade" id="poModal" tabindex="-1" aria-labelledby="poModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="poModalTitle">Purchase order</h2><button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="poBody"></div>
                <div class="modal-footer"><button type="button" class="btn btn-ghost"
                        data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>

    <div class="toast-stack" id="toastStack" aria-live="polite"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="script/dashboard.js"></script>
</body>

</html>
