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
