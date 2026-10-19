/* PrimeBuild dashboard logic
   All data comes from api.php (MySQL). The browser only keeps the signed-in user's name and role. */
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
    wrap.querySelector('.modal-title').textContent = o.title; wrap.querySelector('.msg').textContent = o.message; if (o.required) wrap.querySelector('label').textContent = 'Reason (required)';
    wrap.querySelector('[data-ok]').textContent = o.confirmText || 'Archive';
    document.body.appendChild(wrap);
    var modal = new bootstrap.Modal(wrap);
    wrap.querySelector('[data-ok]').addEventListener('click', function () { var r = wrap.querySelector('.reason').value.trim(); if (o.required && !r) return showToast('warning', 'Enter the reason first.'); modal.hide(); o.onConfirm(r); });
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
  /* ---------- Data ---------- */
  function apiSync(action, data) {
    var request = new XMLHttpRequest();
    request.open('POST', 'api.php', false);
    request.setRequestHeader('Content-Type', 'application/json');
    try { request.send(JSON.stringify({ action: action, data: data })); }
    catch (e) { showToast('error', 'Cannot reach the PHP server. Start Apache and MySQL, then import database.sql.'); return null; }
    var result;
    try { result = JSON.parse(request.responseText); } catch (e) { result = {}; }
    if (request.status === 401) { localStorage.removeItem('bs_user'); sessionStorage.removeItem('bs_user'); location.href = 'login.php'; return null; }
    if (!result.data && !result.user && !result.ok) { showToast('error', result.error || 'Database request failed.'); return null; }
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
  function find(sku) { return db.products.filter(function (p) { return p.sku === sku; })[0]; }
  function status(p) { return p.stock <= 0 ? ['out', 'Out of Stock'] : p.stock <= p.reorder ? ['low', 'Low Stock'] : ['in', 'In Stock']; }
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

  /* ---------- Suppliers and purchase orders ---------- */
  function poProduct(o) { var p = find(o.product); return p ? p.name : o.product; }
  function fillPoProducts() {   // only the chosen supplier's products
    var sup = $('#po-supplier').value, sel = $('#po-product'), v = sel.value;
    var list = db.products.filter(function (p) { return p.supplier === sup; });
    sel.innerHTML = list.length ? list.map(function (p) { return '<option value="' + esc(p.sku) + '">' + esc(p.name) + ' (' + esc(p.sku) + ') - ' + money(p.price) + '</option>'; }).join('') : '<option value="">No products from this supplier</option>';
    if (list.some(function (p) { return p.sku === v; })) sel.value = v;
    showPoAmount();
  }
  function showPoAmount() {
    var p = find($('#po-product').value), q = +$('#po-qty').value || 0;
    $('#po-amount').value = p && q > 0 ? money(p.price * q) : '';
  }
  function renderSuppliers() {
    var sq = $('#supSearch').value.trim().toLowerCase();
    fillRows($('#supRows'), db.suppliers.filter(function (s) { return !sq || (s.name + ' ' + s.contact + ' ' + s.phone).toLowerCase().indexOf(sq) > -1; }), 5, function (s) {
      return '<tr>' + td(esc(s.name)) + td(esc(s.contact || '-')) + td(esc(s.phone || '-')) + td(badge(s.status === 'Active' ? 'in' : 'low', esc(s.status))) +
        actionCell(btn('sup', 'edit', s.name, 'edit', '<i class="bi bi-pencil"></i> Edit') + btn('sup', 'del', s.name, 'delete', '<i class="bi bi-archive"></i> Archive')) + '</tr>';
    }, 'No suppliers found. Select Add Supplier to create one.');
    var cls = { 'Pending': 'low', 'In transit': 'info', 'Received': 'done', 'Cancelled': 'out' };
    var pq = $('#poSearch').value.trim().toLowerCase(), ps = $('#poStatus').value;
    var list = db.pos.filter(function (o) { return (!pq || (o.no + ' ' + o.supplier + ' ' + poProduct(o)).toLowerCase().indexOf(pq) > -1) && (!ps || o.status === ps); });
    fillRows($('#poRows'), list, 8, function (o) {
      var open = o.status === 'Pending' || o.status === 'In transit';
      var acts = btn('po', 'view', o.no, 'edit', '<i class="bi bi-eye"></i> View') +
        (open ? btn('po', o.status === 'Pending' ? 'ship' : 'receive', o.no, 'edit', o.status === 'Pending' ? '<i class="bi bi-truck"></i> Mark shipped' : '<i class="bi bi-box-arrow-in-down"></i> Mark received') + btn('po', 'cancel', o.no, 'delete', '<i class="bi bi-x-circle"></i> Cancel')
          : btn('po', 'archive', o.no, 'delete', '<i class="bi bi-archive"></i> Archive'));
      return '<tr>' + td(esc(o.no)) + td(esc(o.supplier)) + td(esc(poProduct(o))) + td(o.qty, 'num') + td(fmt(o.expected)) + td(money(o.amount), 'num') + td(badge(cls[o.status] || 'info', esc(o.status))) + actionCell(acts) + '</tr>';
    }, 'No purchase orders found.');
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

  /* ---------- Sales: current order ---------- */
  function cartTotal() { return cart.reduce(function (t, c) { return t + c.qty * c.price; }, 0); }
  function inCart(sku) { var l = cart.filter(function (c) { return c.sku === sku; })[0]; return l ? l.qty : 0; }
  function showSaleInfo() {
    var p = find($('#s-product').value), box = $('#s-info');
    if (!p) { box.className = 'sale-info low'; box.innerHTML = '<i class="bi bi-info-circle"></i> No product is available to sell.'; return; }
    var left = p.stock - inCart(p.sku);
    box.className = 'sale-info' + (left <= p.reorder ? ' low' : '');
    box.innerHTML = '<i class="bi bi-box-seam"></i> <strong>' + left + ' in stock</strong> &middot; Stock Keeping Unit ' + esc(p.sku) + ' &middot; ' + money(p.price) + ' each';
    $('#s-qty').max = Math.max(left, 1);
  }
  function fillSaleProducts() {
    var sel = $('#s-product'), v = sel.value;
    sel.innerHTML = db.products.map(function (p) {
      return '<option value="' + esc(p.sku) + '"' + (p.stock <= 0 ? ' disabled' : '') + '>' + esc(p.name) + (p.stock <= 0 ? ' (out of stock)' : '') + '</option>';
    }).join('');
    var ok = db.products.filter(function (p) { return p.stock > 0; });
    if (v && ok.some(function (p) { return p.sku === v; })) sel.value = v; else if (ok.length) sel.value = ok[0].sku;
    showSaleInfo();
  }
  function renderCart() {
    $('#cartList').innerHTML = cart.length ? cart.map(function (c) {
      var p = find(c.sku), max = p ? p.stock : c.qty;
      return '<li class="cart-line"><div class="cart-name">' + esc(c.name) + '<small>' + money(c.price) + ' each</small></div><input class="cart-qty" type="number" min="1" max="' + max + '" value="' + c.qty + '" data-sku="' + esc(c.sku) + '" aria-label="Quantity of ' + esc(c.name) + '"><span class="cart-amt">' + money(c.qty * c.price) + '</span>' + btn('cart', 'rm', c.sku, 'delete', '<i class="bi bi-trash"></i>') + '</li>';
    }).join('') + '<li class="cart-total"><span>Total</span><strong>' + money(cartTotal()) + '</strong></li>'
      : '<li class="muted">No items yet. Choose a product and press Add Item.</li>';
    $('#s-complete').disabled = !cart.length;
    showSaleInfo();
  }

  /* ---------- Sales: receipt ---------- */
  function receiptHtml(s) {
    var named = s.customer && s.customer !== 'Walk-in customer';
    var lines = s.items.map(function (i) { return '<tr><td>' + esc(i.name) + '</td><td class="num">' + i.qty + '</td><td class="num">' + money(i.price) + '</td><td class="num">' + money(i.qty * i.price) + '</td></tr>'; }).join('');
    return '<div class="receipt"><div class="receipt-band"><img src="assets/primebuild-logo.png" alt="PrimeBuild Hardware &amp; Construction Supply"></div>' +
      '<div class="receipt-meta"><div><span>Receipt number</span><strong>' + esc(s.no) + '</strong></div><div><span>Date and time</span><strong>' + fmt(s.date, true) + '</strong></div>' +
      '<div><span>Cashier</span><strong>' + esc(s.cashier || '-') + '</strong></div><div><span>Payment method</span><strong>' + esc(s.pay) + '</strong></div>' +
      (named ? '<div><span>Customer</span><strong>' + esc(s.customer) + '</strong></div>' : '') +
      (s.ref ? '<div><span>Reference number</span><strong>' + esc(s.ref) + '</strong></div>' : '') + '</div>' +
      '<table class="receipt-table"><thead><tr><th>Item</th><th class="num">Quantity</th><th class="num">Unit price</th><th class="num">Amount</th></tr></thead><tbody>' + lines + '</tbody></table>' +
      '<div class="receipt-sum"><div><span>Total</span><strong>' + money(s.total) + '</strong></div><div><span>Amount received</span><span>' + money(s.received || s.total) + '</span></div><div><span>Change</span><span>' + money(s.change || 0) + '</span></div></div>' +
      '<p class="receipt-thanks">Thank you for shopping at PrimeBuild.</p></div>';
  }
  function showReceipt(no, obj) {
    var s = obj || db.sales.filter(function (x) { return x.no === no; })[0];
    if (!s) return showToast('error', 'That receipt could not be found.');
    $('#receiptBody').innerHTML = receiptHtml(s) + (s.archived_by ? '<p class="archive-note"><i class="bi bi-archive"></i> Archived by ' + esc(s.archived_by) + ' on ' + archWhen(s.archived_at) + '. Reason: ' + esc(s.archive_reason || 'No reason given') + '</p>' : '');
    bootstrap.Modal.getOrCreateInstance($('#receiptModal')).show();
  }

  /* ---------- Sales: order history ---------- */
  var oPage = 1, OSIZE = 10;
  function ordersList() {
    var q = $('#oSearch').value.trim().toLowerCase(), y = $('#oYear').value, per = $('#oPeriod').value, p = $('#oProduct').value, pay = $('#oPay').value;
    var f = $('#oFrom').value, t = $('#oTo').value, now = new Date(), today = dayKey(now);
    if (per && per !== 'custom') {   // quick periods replace the manual dates
      t = today;
      f = per === 'today' ? today : per === 'week' ? dayKey(new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6)) : per === 'month' ? today.slice(0, 8) + '01' : today.slice(0, 5) + '01-01';
    } else if (!per) { f = ''; t = ''; }
    return db.sales.filter(function (s) {
      var day = s.date.slice(0, 10);
      return (!q || (s.no + ' ' + s.customer + ' ' + (s.cashier || '') + ' ' + s.items.map(function (i) { return i.name; }).join(' ')).toLowerCase().indexOf(q) > -1) &&
        (!y || day.slice(0, 4) === y) && (!f || day >= f) && (!t || day <= t) &&
        (!p || s.items.some(function (i) { return i.sku === p; })) && (!pay || s.pay === pay);
    });
  }
  function renderOrders() {
    var list = ordersList(), pages = Math.max(1, Math.ceil(list.length / OSIZE));
    if (oPage > pages) oPage = pages;
    var from = (oPage - 1) * OSIZE, units = 0, sum = 0;
    list.forEach(function (s) { sum += s.total; s.items.forEach(function (i) { units += i.qty; }); });
    $('#oTotals').innerHTML = '<div><span>Sales</span><strong>' + list.length + '</strong></div><div><span>Units sold</span><strong>' + units + '</strong></div><div><span>Total amount</span><strong>' + money(sum) + '</strong></div>';
    fillRows($('#orderRows'), list.slice(from, from + OSIZE), 7, function (s) {
      var lines = s.items.map(function (i) { return esc(i.name) + ' × ' + i.qty; }).join('<br>');
      return '<tr>' + td(esc(s.no)) + td(fmt(s.date)) + td(s.customer === 'Walk-in customer' ? '<span class="muted">Walk-in</span>' : esc(s.customer)) + td('<span class="item-lines">' + lines + '</span>') + td(esc(s.pay)) + td(money(s.total), 'num') +
        actionCell(btn('sale', 'view', s.no, 'edit', '<i class="bi bi-eye"></i> View') + (isAdmin ? btn('sale', 'archive', s.no, 'delete', '<i class="bi bi-archive"></i> Archive') : '')) + '</tr>';
    }, 'No sales match these filters.');
    $('#oInfo').textContent = list.length ? 'Showing ' + (from + 1) + ' to ' + Math.min(from + OSIZE, list.length) + ' of ' + list.length + ' sales' : '0 sales';
    $('#oPrev').disabled = oPage <= 1; $('#oNext').disabled = oPage >= pages;
  }
  function renderSales() {
    fill($('#oProduct'), [['', 'All products']].concat(db.products.map(function (p) { return [p.sku, p.name]; })));
    var years = []; db.sales.forEach(function (s) { var y = s.date.slice(0, 4); if (years.indexOf(y) < 0) years.push(y); });
    fill($('#oYear'), [['', 'All years']].concat(years.sort().reverse().map(function (y) { return [y, y]; })));
    renderOrders(); renderCart();
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
    set('low', low.length, 'At or below reorder level');
    set('out', out.length, out.length ? 'Need an urgent order' : 'Everything is available');
    set('today', money(sum(salesToday)), salesToday.length + ' transactions today');
    set('month', money(sum(salesMonth)), salesMonth.length + ' transactions this month');
    set('value', money(value), 'Stock on hand at selling price');

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
    drawChart('chartSales', {
      type: 'bar', data: { labels: days, datasets: [{ label: 'Sales (\u20B1)', data: totals, backgroundColor: '#FFB51B', borderRadius: 4 }] },
      options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    var names = Object.keys(units);
    drawChart('chartStock', {
      type: 'doughnut', data: {
        labels: names, datasets: [{
          data: names.map(function (n) { return units[n]; }), borderColor: '#202020',
          backgroundColor: ['#FFB51B', '#F2F2F0', '#a78bfa', '#4f9cf9', '#22c55e', '#fb923c', '#9a9a9a']
        }]
      },
      options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });
  }

  function fillSelects() {
    var names = db.suppliers.map(function (s) { return s.name; });
    var prods = db.products.map(function (p) { return [p.sku, p.name]; });
    fill($('#p-supplier'), names); fill($('#po-supplier'), names); fill($('#p-cat'), db.categories.map(function (c) { return c.name; }));
    fillPoProducts(); fill($('#m-product'), db.products.map(function (p) { return [p.sku, p.name]; })); fillSaleProducts();
  }

  function renderAll() { fillSelects(); renderCategoryList(); renderProducts(); renderSuppliers(); renderMoves(); renderSales(); renderDash(); }

  /* ---------- Row actions (Edit / Delete / status changes) ---------- */
  var actions = {
    'prod-edit': function (sku) { openProduct(find(sku)); },
    'prod-del': function (sku) {
      var p = find(sku);
      showArchive({
        title: 'Archive product', message: 'Archive "' + p.name + '"? It moves to Archive and Deleted Items, where it can be restored later.',
        onConfirm: function (reason) { api('product_archive', { id: sku, reason: reason }).then(function (r) { if (r) { refresh(); showToast('success', p.name + ' was archived.'); } }); }
      });
    },
    'sup-edit': function (name) { openSupplier(db.suppliers.filter(function (x) { return x.name === name; })[0]); },
    'sup-del': function (name) {
      showArchive({
        title: 'Archive supplier', message: 'Archive "' + name + '"? It moves to Archive and Deleted Items and can be restored later. A supplier that still has active products cannot be archived.',
        onConfirm: function (reason) { api('supplier_archive', { id: name, reason: reason }).then(function (r) { if (r) { refresh(); showToast('success', name + ' was archived.'); } }); }
      });
    },
    'po-ship': function (no) { api('po_status', { id: no, status: 'In transit' }).then(function (r) { if (r) { refresh(); showToast('success', no + ' is now in transit.'); } }); },
    'po-receive': function (no) {
      var o = db.pos.filter(function (x) { return x.no === no; })[0];
      showConfirm({
        title: 'Mark as received', message: 'Mark ' + no + ' as received? This adds ' + o.qty + ' x ' + poProduct(o) + ' to your stock and records a Stock In movement.', confirmText: 'Mark received',
        onConfirm: function () { api('po_status', { id: no, status: 'Received' }).then(function (r) { if (r) { refresh(); showToast('success', no + ' received. Stock was updated.'); } }); }
      });
    },
    'po-cancel': function (no) {
      showConfirm({
        title: 'Cancel purchase order', message: 'Cancel ' + no + '? Nothing will be added to your stock.', confirmText: 'Cancel order',
        onConfirm: function () { api('po_status', { id: no, status: 'Cancelled' }).then(function (r) { if (r) { refresh(); showToast('success', no + ' was cancelled.'); } }); }
      });
    },
    'po-archive': function (no) {
      showArchive({
        title: 'Archive purchase order', message: 'Archive ' + no + '? It moves to Archive and Deleted Items and can be restored later.',
        onConfirm: function (reason) { api('po_archive', { id: no, reason: reason }).then(function (r) { if (r) { refresh(); showToast('success', no + ' was archived.'); } }); }
      });
    },
    'po-view': function (no) {
      var o = db.pos.filter(function (x) { return x.no === no; })[0], p = find(o.product);
      var rows = [['Purchase order number', o.no], ['Supplier', o.supplier], ['Product', poProduct(o) + (p ? ' (' + p.sku + ')' : '')], ['Quantity', o.qty], ['Order amount', money(o.amount)], ['Ordered on', fmt(o.ordered)], ['Expected delivery', fmt(o.expected)], ['Status', o.status]];
      $('#poBody').innerHTML = '<dl class="detail-list">' + rows.map(function (r) { return '<div><dt>' + r[0] + '</dt><dd>' + esc(r[1]) + '</dd></div>'; }).join('') + '</dl>';
      $('#poModalTitle').textContent = o.no; bootstrap.Modal.getOrCreateInstance($('#poModal')).show();
    },
    'sale-view': function (no) { showReceipt(no); },
    'sale-archive': function (no) {
      showArchive({
        title: 'Archive sale', message: 'Archive sale ' + no + '? Its items go back into stock and the sale moves to Archive and Deleted Items.', confirmText: 'Archive sale', required: true,
        onConfirm: function (reason) { api('sale_archive', { id: no, reason: reason }).then(function (r) { if (r) { refresh(); showToast('success', no + ' was archived and its stock was returned.'); } }); }
      });
    },
    'arch-view': function (id) {
      var r = arch[archTab].filter(function (x) { return x.id === id; })[0]; if (!r) return;
      if (archTab === 'sales') return showReceipt(r.no, r);
      var rows = archTab === 'products' ? [['Stock Keeping Unit', r.id], ['Product', r.name], ['Category', r.category], ['Unit price', money(r.price)], ['Stock when archived', r.stock]]
        : archTab === 'suppliers' ? [['Supplier', r.name], ['Contact person', r.contact || '-'], ['Phone number', r.phone || '-']]
          : [['Purchase order number', r.id], ['Supplier', r.supplier], ['Quantity', r.qty], ['Order amount', money(r.amount)], ['Status', r.status]];
      rows = rows.concat([['Archived by', r.archived_by || '-'], ['Archived on', archWhen(r.archived_at)], ['Reason', r.archive_reason || 'No reason given']]);
      $('#poBody').innerHTML = '<dl class="detail-list">' + rows.map(function (x) { return '<div><dt>' + x[0] + '</dt><dd>' + esc(x[1]) + '</dd></div>'; }).join('') + '</dl>';
      $('#poModalTitle').textContent = r.name || r.id; bootstrap.Modal.getOrCreateInstance($('#poModal')).show();
    },
    'arch-restore': function (id) {
      var r = arch[archTab].filter(function (x) { return x.id === id; })[0]; if (!r) return;
      var label = r.name || r.no || r.id, type = archTab;
      var extra = type === 'sales' ? ' Its items are taken out of stock again. This is refused if there is not enough stock.' : '';
      showConfirm({
        title: 'Restore item', message: 'Restore ' + label + '? It will appear in the normal lists again.' + extra, confirmText: 'Restore',
        onConfirm: function () { api('restore', { type: type, id: id }).then(function (res) { if (res) { loadArchive(); refresh(); showToast('success', label + ' was restored.'); } }); }
      });
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
      showConfirm({
        title: 'Archive category', message: 'Archive "' + old + '"? You can only do this when it has no active products.', confirmText: 'Archive',
        onConfirm: function () { api('category_archive', { name: old }).then(function (r) { if (r) { refresh(); showToast('success', 'Category archived.'); } }); }
      });
    }
  });

  /* ----- Suppliers: add / edit modal ----- */
  var supplierModal = bootstrap.Modal.getOrCreateInstance($('#supplierModal')), editingSupplier = '';
  function openSupplier(s) {
    editingSupplier = s ? s.name : '';
    $$('#supplierModal .field-error').forEach(function (b) { b.textContent = ''; });
    $('#supplierModalTitle').textContent = s ? 'Edit supplier' : 'Add supplier';
    $('#sp-name').value = s ? s.name : ''; $('#sp-name').readOnly = !!s;
    $('#sp-contact').value = s ? s.contact : ''; $('#sp-phone').value = s ? s.phone : ''; $('#sp-status').value = s ? s.status : 'Active';
    supplierModal.show();
  }
  $('#btnAddSupplier').addEventListener('click', function () { openSupplier(null); });
  $('#sp-save').addEventListener('click', function () {
    if (!fieldError($('#sp-name'), $('#sp-name').value.trim() ? '' : 'Enter the supplier name.')) return;
    api('supplier_save', { name: $('#sp-name').value.trim(), old_name: editingSupplier, contact: $('#sp-contact').value.trim(), phone: $('#sp-phone').value.trim(), status: $('#sp-status').value })
      .then(function (r) { if (r) { supplierModal.hide(); refresh(); showToast('success', editingSupplier ? 'Supplier updated.' : 'Supplier added.'); } });
  });
  $('#supSearch').addEventListener('input', renderSuppliers); $('#poSearch').addEventListener('input', renderSuppliers); $('#poStatus').addEventListener('change', renderSuppliers);

  /* ----- Purchase orders: create ----- */
  function defaultPoDate() { var d = new Date(); d.setDate(d.getDate() + 7); $('#po-date').value = dayKey(d); $('#po-date').min = dayKey(new Date()); }
  defaultPoDate();
  $('#po-supplier').addEventListener('change', fillPoProducts); $('#po-product').addEventListener('change', showPoAmount); $('#po-qty').addEventListener('input', showPoAmount);
  $('#po-save').addEventListener('click', function () {
    var sku = $('#po-product').value, qty = +$('#po-qty').value, ok = true;
    if (!$('#po-supplier').value || !sku) return showToast('warning', 'Choose a supplier that has at least one product.');
    ok = fieldError($('#po-qty'), qty >= 1 && qty % 1 === 0 ? '' : 'Enter a quantity of 1 or more.') && ok;
    ok = fieldError($('#po-date'), $('#po-date').value && $('#po-date').value >= dayKey(new Date()) ? '' : 'Choose a delivery date from today onward.') && ok;
    if (!ok) return;
    api('po_create', { supplier: $('#po-supplier').value, sku: sku, qty: qty, expected: $('#po-date').value }).then(function (r) {
      if (r) { $('#po-qty').value = ''; defaultPoDate(); refresh(); showToast('success', 'Purchase order ' + r.no + ' placed.'); }
    });
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

  /* ----- Sales: new sale, payment, receipt ----- */
  fill($('#s-pay'), ['Cash', 'Bank Transfer', 'E-Wallet']);
  $('#s-product').addEventListener('change', showSaleInfo);
  $('#s-add').addEventListener('click', function () {
    var p = find($('#s-product').value), q = +$('#s-qty').value;
    if (!p) return showToast('warning', 'Choose a product.');
    if (!q || q < 1 || q % 1) return showToast('warning', 'Enter a quantity of 1 or more.');
    var want = inCart(p.sku) + q;
    if (want > p.stock) return showToast('warning', 'Only ' + p.stock + ' of ' + p.name + ' in stock.');
    var line = cart.filter(function (c) { return c.sku === p.sku; })[0];
    if (line) line.qty = want; else cart.push({ sku: p.sku, name: p.name, qty: q, price: p.price });
    $('#s-qty').value = 1; renderCart();
  });
  $('#cartList').addEventListener('change', function (e) {
    if (!e.target.classList.contains('cart-qty')) return;
    var line = cart.filter(function (c) { return c.sku === e.target.dataset.sku; })[0], p = find(e.target.dataset.sku), v = Math.floor(+e.target.value) || 1;
    if (p && v > p.stock) { v = p.stock; showToast('warning', 'Only ' + p.stock + ' of ' + p.name + ' in stock.'); }
    line.qty = Math.max(1, v); renderCart();
  });
  var payModal = bootstrap.Modal.getOrCreateInstance($('#payModal'));
  $('#s-complete').addEventListener('click', function () {
    if (!cart.length) return showToast('warning', 'Add at least one item first.');
    var m = $('#s-pay').value;
    $('#payLines').innerHTML = cart.map(function (c) { return '<li><span>' + esc(c.name) + ' × ' + c.qty + '</span><span>' + money(c.qty * c.price) + '</span></li>'; }).join('');
    $('#payTotal').textContent = money(cartTotal()); $('#payMethod').textContent = m;
    $('#cashBox').hidden = m !== 'Cash'; $('#refBox').hidden = m === 'Cash';
    $('#refLabel').textContent = (m === 'Bank Transfer' ? 'Bank transfer' : 'E-Wallet') + ' reference number';
    $('#payReceived').value = ''; $('#payRef').value = ''; $('#payChange').textContent = money(0);
    $$('#payModal .field-error').forEach(function (b) { b.textContent = ''; });
    payModal.show();
  });
  $('#payReceived').addEventListener('input', function () { $('#payChange').textContent = money(Math.max((+this.value || 0) - cartTotal(), 0)); });
  $('#payExact').addEventListener('click', function () { $('#payReceived').value = cartTotal().toFixed(2); $('#payReceived').dispatchEvent(new Event('input')); });
  $('#payConfirm').addEventListener('click', function () {
    var m = $('#s-pay').value, total = cartTotal(), rec = +$('#payReceived').value, ref = $('#payRef').value.trim();
    if (m === 'Cash' && !fieldError($('#payReceived'), rec + 0.001 >= total ? '' : 'The amount received is less than the total due.')) return;
    if (m !== 'Cash' && !fieldError($('#payRef'), ref ? '' : 'Enter the reference number.')) return;
    var btnConfirm = this; btnConfirm.disabled = true;
    api('sale_complete', { items: cart.map(function (c) { return { sku: c.sku, qty: c.qty }; }), payment_method: m, amount_received: rec, payment_reference: ref }).then(function (r) {
      btnConfirm.disabled = false;
      if (r) { payModal.hide(); cart = []; refresh().then(function () { showToast('success', 'Sale ' + r.receipt + ' completed.'); showReceipt(r.receipt); }); }
    });
  });
  $('#receiptPrint').addEventListener('click', function () { window.print(); });

  /* ----- Sales: order history filters, pagination, download ----- */
  function showCustomDates() { var c = $('#oPeriod').value === 'custom'; $('#oFromBox').hidden = !c; $('#oToBox').hidden = !c; }
  ['oSearch', 'oYear', 'oPeriod', 'oFrom', 'oTo', 'oProduct', 'oPay'].forEach(function (id) {
    $('#' + id).addEventListener(id === 'oSearch' ? 'input' : 'change', function () { if (id === 'oPeriod') showCustomDates(); oPage = 1; renderOrders(); });
  });
  $('#oClear').addEventListener('click', function () {
    ['oSearch', 'oYear', 'oPeriod', 'oFrom', 'oTo', 'oProduct', 'oPay'].forEach(function (id) { $('#' + id).value = ''; }); showCustomDates(); oPage = 1; renderOrders();
  });
  $('#oPrev').addEventListener('click', function () { oPage--; renderOrders(); });
  $('#oNext').addEventListener('click', function () { oPage++; renderOrders(); });
  $('#oExport').addEventListener('click', function () {
    var list = ordersList(); if (!list.length) return showToast('warning', 'There are no sales to download.');
    var rows = [['Receipt', 'Date', 'Customer', 'Items sold', 'Payment method', 'Reference number', 'Total', 'Amount received', 'Change', 'Cashier']].concat(list.map(function (s) {
      return [s.no, fmt(s.date, true), s.customer, s.items.map(function (i) { return i.name + ' x ' + i.qty; }).join('; '), s.pay, s.ref || '', s.total.toFixed(2), (s.received || s.total).toFixed(2), (s.change || 0).toFixed(2), s.cashier || ''];
    }));
    var csv = rows.map(function (r) { return r.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(','); }).join('\r\n');
    var a = document.createElement('a'); a.href = URL.createObjectURL(new Blob(['\ufeff' + csv], { type: 'text/csv' })); a.download = 'primebuild-sales-report.csv'; a.click();
    showToast('success', 'Sales report downloaded.');
  });

  /* ---------- Archive and Deleted Items (administrator) ---------- */
  var arch = { products: [], suppliers: [], sales: [], pos: [] }, archTab = 'products';
  function archWhen(s) { return s ? fmt(String(s).replace(' ', 'T'), true) : '-'; }
  var archDef = {
    products: { title: 'Archived products', head: ['Stock Keeping Unit', 'Product', 'Category'], cells: function (r) { return td(esc(r.id), 'sku') + td(esc(r.name)) + td(esc(r.category)); } },
    suppliers: { title: 'Archived suppliers', head: ['Supplier', 'Contact person', 'Phone number'], cells: function (r) { return td(esc(r.name)) + td(esc(r.contact || '-')) + td(esc(r.phone || '-')); } },
    sales: { title: 'Archived sales', head: ['Receipt', 'Sale date', 'Total'], cells: function (r) { return td(esc(r.no)) + td(fmt(r.date, true)) + td(money(r.total)); } },
    pos: { title: 'Archived purchase orders', head: ['Purchase order number', 'Supplier', 'Amount'], cells: function (r) { return td(esc(r.id)) + td(esc(r.supplier)) + td(money(r.amount)); } }
  };
  function renderArchive() {
    var def = archDef[archTab], q = $('#archSearch').value.trim().toLowerCase();
    Object.keys(archDef).forEach(function (k) {
      var tab = $('[data-tab="' + k + '"]'); tab.classList.toggle('active', k === archTab); tab.querySelector('.tab-count').textContent = arch[k].length;
    });
    $('#archTitle').textContent = def.title;
    $('#archHead').innerHTML = '<tr>' + def.head.concat(['Archived by', 'Archived on', 'Reason']).map(function (t) { return '<th scope="col">' + t + '</th>'; }).join('') + '<th scope="col" class="num">Actions</th></tr>';
    var list = arch[archTab].filter(function (r) { return !q || [r.id, r.name, r.supplier, r.category, r.contact, r.archived_by, r.archive_reason].join(' ').toLowerCase().indexOf(q) > -1; });
    fillRows($('#archRows'), list, 7, function (r) {
      return '<tr>' + def.cells(r) + td(esc(r.archived_by || '-')) + td(archWhen(r.archived_at)) + td(esc(r.archive_reason || 'No reason given')) +
        actionCell(btn('arch', 'view', r.id, 'edit', '<i class="bi bi-eye"></i> View details') + btn('arch', 'restore', r.id, 'edit', '<i class="bi bi-arrow-counterclockwise"></i> Restore')) + '</tr>';
    }, 'Nothing is archived here yet.');
  }
  function loadArchive() {
    if (!isAdmin) return;
    return api('archive_list').then(function (res) {
      if (res) { arch = res.data; arch.sales.forEach(function (s) { s.id = s.no; }); renderArchive(); }
    });
  }
  $('#v-archive').addEventListener('change', loadArchive);   // refreshed every time the page is opened
  $('#archSearch').addEventListener('input', renderArchive);
  $('.arch-tabs').addEventListener('click', function (e) {
    var t = e.target.closest('[data-tab]'); if (!t) return;
    archTab = t.dataset.tab; $('#archSearch').value = ''; renderArchive();
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