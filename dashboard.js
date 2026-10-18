/* PrimeBuild dashboard logic
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

  /* Toasts appear at the top center. type: success | warning | error | info */
  var toastIcons = { success: 'bi-check-circle-fill', warning: 'bi-exclamation-triangle-fill', error: 'bi-x-octagon-fill', info: 'bi-info-circle-fill' };
  function showToast(type, message) {
    var stack = document.getElementById('toastStack');
    var el = document.createElement('div');
    el.className = 'toast toast-' + type + ' show'; el.setAttribute('role', 'alert');
    el.innerHTML = '<i class="bi ' + (toastIcons[type] || toastIcons.info) + '"></i><div class="toast-text"></div><button type="button" class="btn-close" aria-label="Close"></button>';
    el.querySelector('.toast-text').textContent = message;
    var close = function () { el.remove(); };
    el.querySelector('.btn-close').addEventListener('click', close);
    stack.appendChild(el); setTimeout(close, 5000);
  }
  /* Older code calls toast(message); pick a type from the wording */
  function toast(msg) { showToast(/only|enter|already|add at least|valid|add a note/i.test(msg) ? 'warning' : 'success', msg); }

  /* Centered confirmation modal: showConfirm({ title, message, confirmText, onConfirm }) */
  function showConfirm(o) {
    var wrap = document.createElement('div');
    wrap.className = 'modal fade'; wrap.tabIndex = -1;
    wrap.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title"></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" data-ok></button></div></div></div>';
    wrap.querySelector('.modal-title').textContent = o.title || 'Please confirm';
    wrap.querySelector('.modal-body').textContent = o.message || '';
    wrap.querySelector('[data-ok]').textContent = o.confirmText || 'Confirm';
    document.body.appendChild(wrap);
    var modal = new bootstrap.Modal(wrap);
    wrap.querySelector('[data-ok]').addEventListener('click', function () { modal.hide(); if (o.onConfirm) o.onConfirm(); });
    wrap.addEventListener('hidden.bs.modal', function () { modal.dispose(); wrap.remove(); });
    modal.show();
  }
  /* Archive modal: asks for an optional reason, then calls onConfirm(reason) */
  function showArchive(o) {
    var wrap = document.createElement('div');
    wrap.className = 'modal fade'; wrap.tabIndex = -1;
    wrap.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title"></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p class="msg"></p><div class="field"><label>Reason (optional)</label><textarea rows="2" class="reason" placeholder="Why is this being archived?"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-delete" data-ok></button></div></div></div>';
    wrap.querySelector('.modal-title').textContent = o.title; wrap.querySelector('.msg').textContent = o.message;
    wrap.querySelector('[data-ok]').textContent = o.confirmText || 'Archive';
    document.body.appendChild(wrap);
    var modal = new bootstrap.Modal(wrap);
    wrap.querySelector('[data-ok]').addEventListener('click', function () { var r = wrap.querySelector('.reason').value.trim(); modal.hide(); o.onConfirm(r); });
    wrap.addEventListener('hidden.bs.modal', function () { modal.dispose(); wrap.remove(); });
    modal.show();
  }
  window.showToast = showToast; window.showConfirm = showConfirm; window.showArchive = showArchive;

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
  /* The old save-everything action was removed in Step 2. Each page is rewired to its own api.php action in Steps 3 to 8. */
  function save() { showToast('warning', 'Saving for this page is being rebuilt and will work again in the next steps.'); return false; }
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

  /* Talk to api.php. Errors become toasts; the promise gives null on failure. */
  function api(action, data) {
    return fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.assign({ action: action }, data || {})) })
      .then(function (r) {
        return r.json().then(function (res) {
          if (r.status === 401) { location.href = 'login.php'; }
          if (!r.ok || res.error) throw new Error(res.error || 'The request failed.');
          return res;
        });
      })
      .catch(function (e) { showToast('error', e.message || 'Cannot reach the server.'); return null; });
  }
  function refresh() { return api('load').then(function (res) { if (res) { db = res.data; renderAll(); } return res; }); }
  function fieldError(input, msg) {   // inline validation under the field
    var box = input.closest('.field').querySelector('.field-error');
    if (box) { box.textContent = msg || ''; input.classList.toggle('is-bad', !!msg); }
    return !msg;
  }

  /* ---------- Rendering ---------- */
  function renderProducts() {
    fill($('#fCat'), ['All categories'].concat(db.categories.map(function (c) { return c.name; })));
    var q = $('#prodSearch').value.trim().toLowerCase(), fc = $('#fCat'), fs = $('#fStatus');
    var list = db.products.filter(function (p) {
      var hay = (p.sku + ' ' + p.name + ' ' + p.cat + ' ' + p.supplier).toLowerCase();
      return (!q || hay.indexOf(q) > -1) && (fc.selectedIndex < 1 || p.cat === fc.value) && (fs.selectedIndex < 1 || status(p)[1] === fs.value);
    });
    fillRows($('#products tbody'), list, isAdmin ? 8 : 7, function (p) {
      var s = status(p);
      return '<tr>' + td(esc(p.sku), 'sku') + td(esc(p.name)) + td(esc(p.cat)) + td(esc(p.supplier)) + td(money(p.price), 'num') + td(p.stock, 'num') + td(badge(s[0], s[1])) +
        (isAdmin ? actionCell(btn('prod', 'edit', p.sku, 'edit', '<i class="bi bi-pencil"></i> Edit') + btn('prod', 'del', p.sku, 'delete', '<i class="bi bi-archive"></i> Archive')) : '') + '</tr>';
    }, 'No products match these filters.');
  }
  function renderCategoryList() {
    $('#catList').innerHTML = db.categories.map(function (c) {
      return '<div class="cat-row" data-name="' + esc(c.name) + '"><span class="cat-prefix">' + esc(c.prefix) + '</span><input class="cat-input" value="' + esc(c.name) + '" aria-label="Category name"><button class="btn btn-sm btn-edit" data-cat="rename" type="button">Save name</button><button class="btn btn-sm btn-delete" data-cat="archive" type="button">Archive</button></div>';
    }).join('') || '<p class="muted">No categories yet.</p>';
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

  var moveLabel = { in: ['in', 'Stock in'], out: ['move-out', 'Stock out'], adj: ['info', 'Adjustment'] };
  var moveQtyClass = function (m) { return m.type === 'out' ? 'qty-moveout' : m.qty > 0 ? 'qty-in' : 'qty-out'; };
  var moveQty = function (m) { return td((m.qty > 0 ? '+' : '\u2212') + Math.abs(m.qty), 'num ' + moveQtyClass(m)); };
  var hPage = 1, HSIZE = 10;

  function histList() {
    var q = $('#hSearch').value.trim().toLowerCase(), t = $('#hType').value, p = $('#hProduct').value, y = $('#hYear').value, f = $('#hFrom').value, to = $('#hTo').value;
    return db.moves.filter(function (m) {
      var day = m.date.slice(0, 10);
      return (!q || (m.no + ' ' + m.product + ' ' + m.ref + ' ' + (m.note || '') + ' ' + m.by).toLowerCase().indexOf(q) > -1) &&
        (!t || m.type === t) && (!p || m.sku === p) && (!y || day.slice(0, 4) === y) && (!f || day >= f) && (!to || day <= to);
    });
  }
  function renderHistory() {
    var list = histList(), pages = Math.max(1, Math.ceil(list.length / HSIZE));
    if (hPage > pages) hPage = pages;
    var from = (hPage - 1) * HSIZE;
    fillRows($('#historyRows'), list.slice(from, from + HSIZE), 8, function (m) {
      var l = moveLabel[m.type];
      return '<tr>' + td(fmt(m.date, true)) + td(esc(m.no)) + td(esc(m.product)) + td(badge(l[0], l[1])) + moveQty(m) + td(esc(m.ref)) + td(esc(m.note || '-')) + td(esc(m.by)) + '</tr>';
    }, 'No movements match these filters.');
    $('#hInfo').textContent = list.length ? 'Showing ' + (from + 1) + ' to ' + Math.min(from + HSIZE, list.length) + ' of ' + list.length + ' movements' : '0 movements';
    $('#hPrev').disabled = hPage <= 1; $('#hNext').disabled = hPage >= pages;
  }
  function showPicked() {   // shows the Stock Keeping Unit and stock of the chosen product
    var p = find($('#m-product').value), adj = $('#m-type').value === 'adj';
    $('#m-sku').value = p ? p.sku : ''; $('#m-stock').value = p ? p.stock + ' in stock' : '';
    $('#m-qtyHelp').textContent = adj ? 'Use a minus sign to remove stock, for example -2.' : 'How many units moved.';
  }
  function renderMoves() {
    fillRows($('#recentMovesFull'), db.moves.slice(0, 5), 6, function (m) {
      var l = moveLabel[m.type];
      return '<tr>' + td(fmt(m.date, true)) + td(esc(m.product)) + td(badge(l[0], l[1])) + moveQty(m) + td(esc(m.ref)) + td(esc(m.by)) + '</tr>';
    }, 'No movements yet.');
    fill($('#hProduct'), [['', 'All products']].concat(db.products.map(function (p) { return [p.sku, p.name]; })));
    var years = []; db.moves.forEach(function (m) { var y = m.date.slice(0, 4); if (years.indexOf(y) < 0) years.push(y); });
    fill($('#hYear'), [['', 'All years']].concat(years.sort().reverse().map(function (y) { return [y, y]; })));
    renderHistory(); showPicked();
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

  var charts = {};
  function drawChart(id, cfg) {
    if (!window.Chart) return;
    if (charts[id]) charts[id].destroy();
    charts[id] = new Chart(document.getElementById(id), cfg);
  }
  function dayKey(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }

  function renderDash() {
    var now = new Date(), today = dayKey(now), month = today.slice(0, 7);
    var cats = {}, units = {}, value = 0;
    db.products.forEach(function (p) { cats[p.cat] = 1; units[p.cat] = (units[p.cat] || 0) + p.stock; value += p.stock * p.price; });
    var low = db.products.filter(function (p) { return p.stock <= p.reorder; }).sort(function (a, b) { return a.stock - b.stock; });
    var out = db.products.filter(function (p) { return p.stock <= 0; });
    var salesToday = db.sales.filter(function (s) { return s.date.slice(0, 10) === today; });
    var salesMonth = db.sales.filter(function (s) { return s.date.slice(0, 7) === month; });
    var sum = function (l) { return l.reduce(function (t, s) { return t + s.total; }, 0); };
    var openPos = db.pos.filter(function (o) { return o.status === 'Pending' || o.status === 'In transit'; }).length;
    var set = function (k, v, n) { $('#s-' + k).textContent = v; $('#n-' + k).textContent = n; };
    set('products', db.products.length, 'across ' + Object.keys(cats).length + ' categories');
    set('low', low.length, 'At or below reorder level');
    set('out', out.length, out.length ? 'Need an urgent order' : 'Everything is available');
    set('today', money(sum(salesToday)), salesToday.length + ' transactions today');
    set('month', money(sum(salesMonth)), salesMonth.length + ' transactions this month');
    set('value', money(value), 'Stock on hand at selling price');
    set('suppliers', db.suppliers.filter(function (s) { return s.status === 'Active'; }).length, db.pos.filter(function (o) { return o.status === 'In transit'; }).length + ' orders in transit');
    set('pos', openPos, 'Pending or in transit');

    /* Needs restocking */
    $('#restockList').innerHTML = low.slice(0, 6).map(function (p) {
      return '<li><span>' + esc(p.name) + '</span><span>' + (p.stock ? p.stock + ' left' : 'Out of stock') + '</span></li>';
    }).join('') || '<li><span>All stock levels are healthy.</span><span></span></li>';

    /* Top selling products */
    var tally = {};
    db.sales.forEach(function (s) { s.items.forEach(function (i) { tally[i.name] = (tally[i.name] || 0) + i.qty; }); });
    var top = Object.keys(tally).map(function (n) { return [n, tally[n]]; }).sort(function (a, b) { return b[1] - a[1]; }).slice(0, 5);
    var max = top.length ? top[0][1] : 1;
    $('#topList').innerHTML = top.map(function (t) {
      return '<div class="top-row"><div class="top-label"><span>' + esc(t[0]) + '</span><strong>' + t[1] + ' sold</strong></div><div class="top-bar"><span style="width:' + Math.round(t[1] / max * 100) + '%"></span></div></div>';
    }).join('') || '<p class="muted">No sales yet.</p>';

    /* Recent sales and movements */
    fillRows($('#dash .table-wrap tbody'), db.sales.slice(0, 5), 5, function (s) {
      return '<tr>' + td(s.no) + td(fmt(s.date)) + td(esc(s.pay)) + td(money(s.total), 'num') + td(badge('done', 'Paid')) + '</tr>';
    }, 'No sales yet.');
    var label = { in: ['in', 'Stock in'], out: ['move-out', 'Stock out'], adj: ['info', 'Adjustment'] };
    fillRows($('#recentMoves'), db.moves.slice(0, 5), 3, function (m) {
      var l = label[m.type];
      return '<tr>' + td(esc(m.product)) + td(badge(l[0], l[1])) + td((m.qty > 0 ? '+' : '\u2212') + Math.abs(m.qty), 'num ' + (m.qty > 0 ? 'qty-in' : 'qty-out')) + '</tr>';
    }, 'No movements yet.');

    /* Charts */
    if (window.Chart) { Chart.defaults.color = '#b5b5b5'; Chart.defaults.borderColor = '#2d2d2d'; Chart.defaults.font.family = 'Barlow, sans-serif'; }
    var days = [], totals = [];
    for (var d = 6; d >= 0; d--) {
      var day = new Date(now.getFullYear(), now.getMonth(), now.getDate() - d), k = dayKey(day);
      days.push(day.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
      totals.push(sum(db.sales.filter(function (s) { return s.date.slice(0, 10) === k; })));
    }
    drawChart('chartSales', { type: 'bar', data: { labels: days, datasets: [{ label: 'Sales (\u20B1)', data: totals, backgroundColor: '#FFB51B', borderRadius: 4 }] },
      options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } } });
    var names = Object.keys(units);
    drawChart('chartStock', { type: 'doughnut', data: { labels: names, datasets: [{ data: names.map(function (n) { return units[n]; }), borderColor: '#202020',
      backgroundColor: ['#FFB51B', '#F2F2F0', '#a78bfa', '#4f9cf9', '#22c55e', '#fb923c', '#9a9a9a'] }] },
      options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } } });
  }

  function fillSelects() {
    var names = db.suppliers.map(function (s) { return s.name; });
    var prods = db.products.map(function (p) { return [p.sku, p.name]; });
    fill($('#p-supplier'), names); fill($('#po-supplier'), names); fill($('#p-cat'), db.categories.map(function (c) { return c.name; }));
    fill($('#po-product'), prods); fill($('#m-product'), db.products.map(function (p) { return [p.sku, p.name + ' (' + p.stock + ' in stock)']; })); fill($('#s-product'), prods);
  }

  function applySearch() {}   // header search was removed; each page gets its own search in later steps
  function renderAll() { fillSelects(); renderCategoryList(); renderProducts(); renderSuppliers(); renderMoves(); renderSales(); renderDash(); applySearch(); }

  /* ---------- Row actions (Edit / Delete / status changes) ---------- */
  var actions = {
    'prod-edit': function (sku) { openProduct(find(sku)); },
    'prod-del': function (sku) {
      var p = find(sku);
      showArchive({ title: 'Archive product', message: 'Archive "' + p.name + '"? It moves to Archive and Deleted Items, where it can be restored later.',
        onConfirm: function (reason) { api('product_archive', { id: sku, reason: reason }).then(function (r) { if (r) { refresh(); showToast('success', p.name + ' was archived.'); } }); } });
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
  /* ----- Products: add / edit modal ----- */
  var productModal = bootstrap.Modal.getOrCreateInstance($('#productModal')), editingSku = '';
  function previewSku() {
    if (editingSku) return;
    api('next_sku', { category: $('#p-cat').value }).then(function (r) { if (r) $('#p-sku').value = r.sku; });
  }
  function openProduct(p) {
    editingSku = p ? p.sku : '';
    $$('#productModal .field-error').forEach(function (b) { b.textContent = ''; });
    $('#productModalTitle').textContent = p ? 'Edit product' : 'Add product';
    $('#p-stockField').hidden = !!p; $('#p-editNote').hidden = !p;
    $('#p-name').value = p ? p.name : ''; $('#p-price').value = p ? p.price : ''; $('#p-reorder').value = p ? p.reorder : 20; $('#p-stock').value = p ? '' : 0;
    if (p) { $('#p-cat').value = p.cat; $('#p-supplier').value = p.supplier; $('#p-sku').value = p.sku; } else { $('#p-sku').value = ''; previewSku(); }
    productModal.show();
  }
  $('#btnAddProduct').addEventListener('click', function () { openProduct(null); });
  $('#p-cat').addEventListener('change', previewSku);
  $('#p-save').addEventListener('click', function () {
    var ok = true;
    ok = fieldError($('#p-name'), $('#p-name').value.trim() ? '' : 'Enter the product name.') && ok;
    ok = fieldError($('#p-cat'), $('#p-cat').value ? '' : 'Choose a category.') && ok;
    ok = fieldError($('#p-supplier'), $('#p-supplier').value ? '' : 'Choose a supplier.') && ok;
    ok = fieldError($('#p-price'), +$('#p-price').value > 0 ? '' : 'Enter a price above zero.') && ok;
    if (!ok) return;
    api('product_save', { sku: editingSku, name: $('#p-name').value.trim(), category: $('#p-cat').value, supplier: $('#p-supplier').value, price: +$('#p-price').value, stock: +$('#p-stock').value || 0, reorder: +$('#p-reorder').value || 0 })
      .then(function (r) { if (r) { productModal.hide(); refresh(); showToast('success', editingSku ? 'Product updated.' : 'Product saved. Stock Keeping Unit: ' + r.sku); } });
  });

  /* ----- Categories modal ----- */
  var categoryModal = bootstrap.Modal.getOrCreateInstance($('#categoryModal'));
  $('#btnCategories').addEventListener('click', function () { categoryModal.show(); });
  $('#catAdd').addEventListener('click', function () {
    var name = $('#catNew').value.trim(); if (!name) return showToast('warning', 'Enter a category name.');
    api('category_save', { name: name }).then(function (r) { if (r) { $('#catNew').value = ''; refresh(); showToast('success', 'Category added.'); } });
  });
  $('#catList').addEventListener('click', function (e) {
    var b = e.target.closest('[data-cat]'); if (!b) return;
    var row = b.closest('.cat-row'), old = row.dataset.name;
    if (b.dataset.cat === 'rename') {
      var name = row.querySelector('.cat-input').value.trim(); if (!name) return showToast('warning', 'Enter a category name.');
      api('category_save', { name: name, old_name: old }).then(function (r) { if (r) { refresh(); showToast('success', 'Category renamed.'); } });
    } else {
      showConfirm({ title: 'Archive category', message: 'Archive "' + old + '"? You can only do this when it has no active products.', confirmText: 'Archive',
        onConfirm: function () { api('category_archive', { name: old }).then(function (r) { if (r) { refresh(); showToast('success', 'Category archived.'); } }); } });
    }
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

  /* ----- Stock movements form ----- */
  fill($('#m-type'), isAdmin ? [['in', 'Stock in'], ['out', 'Stock out'], ['adj', 'Adjustment']] : [['in', 'Stock in'], ['out', 'Stock out']]);   // only the administrator sees Adjustment
  $('#m-product').addEventListener('change', showPicked); $('#m-type').addEventListener('change', showPicked);
  function clearMoveForm() { $('#m-qty').value = ''; $('#m-note').value = ''; showPicked(); }
  $('#m-clear').addEventListener('click', clearMoveForm);
  $('#m-save').addEventListener('click', function () {
    var p = find($('#m-product').value), type = $('#m-type').value, q = +$('#m-qty').value;
    if (!p) return showToast('warning', 'Choose a product.');
    if (!q || (type !== 'adj' && q < 0)) return showToast('warning', 'Enter a valid quantity.');
    if (type === 'out' && q > p.stock) return showToast('warning', 'Only ' + p.stock + ' of ' + p.name + ' on hand.');
    api('movement_record', { sku: p.sku, type: type, qty: q, note: $('#m-note').value.trim() }).then(function (r) {
      if (r) { clearMoveForm(); refresh(); showToast('success', 'Movement recorded: ' + r.no + '.'); }
    });
  });

  /* ----- Stock Movement History: filters, pagination, download ----- */
  ['hSearch', 'hType', 'hProduct', 'hYear', 'hFrom', 'hTo'].forEach(function (id) {
    $('#' + id).addEventListener(id === 'hSearch' ? 'input' : 'change', function () { hPage = 1; renderHistory(); });
  });
  $('#hClear').addEventListener('click', function () {
    ['hSearch', 'hType', 'hProduct', 'hYear', 'hFrom', 'hTo'].forEach(function (id) { $('#' + id).value = ''; }); hPage = 1; renderHistory();
  });
  $('#hPrev').addEventListener('click', function () { hPage--; renderHistory(); });
  $('#hNext').addEventListener('click', function () { hPage++; renderHistory(); });
  $('#hExport').addEventListener('click', function () {
    var list = histList(); if (!list.length) return showToast('warning', 'There are no movements to download.');
    var names = { in: 'Stock in', out: 'Stock out', adj: 'Adjustment' };
    var rows = [['Date', 'Movement number', 'Stock Keeping Unit', 'Product', 'Type', 'Quantity', 'Reference', 'Note', 'Recorded by']].concat(list.map(function (m) {
      return [fmt(m.date, true), m.no, m.sku, m.product, names[m.type], m.qty, m.ref, m.note || '', m.by];
    }));
    var csv = rows.map(function (r) { return r.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(','); }).join('\r\n');
    var a = document.createElement('a'); a.href = URL.createObjectURL(new Blob(['\ufeff' + csv], { type: 'text/csv' })); a.download = 'primebuild-stock-movements.csv'; a.click();
    showToast('success', 'Movement report downloaded.');
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
  $$('#fCat, #fStatus').forEach(function (s) { s.addEventListener('change', renderAll); });
  $('#prodSearch').addEventListener('input', renderProducts);

  $('.profile-name').textContent = user.name;
  $('.role-badge').textContent = isAdmin ? 'Administrator' : 'Staff';
  $('.menu-user').textContent = 'Signed in as ' + user.name;
  $('.avatar').textContent = user.name.split(' ').map(function (w) { return w[0]; }).join('').slice(0, 2).toUpperCase();

  var logoutLink = $('.profile-menu a');   // only Sign out remains (demo reset removed)
  logoutLink.addEventListener('click', function (e) {
    e.preventDefault();
    apiSync('logout');
    localStorage.removeItem('bs_user');
    sessionStorage.removeItem('bs_user');
    location.replace('login.php');
  });

  document.addEventListener('click', function (e) {   // buttons that jump to another page
    var g = e.target.closest('[data-goto]'); if (g) { $('#' + g.dataset.goto).checked = true; window.scrollTo(0, 0); }
  });

  if (!isAdmin) {   // Staff: no supplier module, no product add form
    $$('[data-admin]').forEach(function (el) { el.style.display = 'none'; });
    $('.nav label[for="v-suppliers"]').style.display = 'none';
  }

  renderAll();
})();

/* Close the mobile menu after choosing a page */
document.querySelectorAll('.nav label').forEach(function (l) {
  l.addEventListener('click', function () {
    var el = document.getElementById('sidebar'), oc = bootstrap.Offcanvas.getInstance(el);
    if (oc) oc.hide();
  });
});
