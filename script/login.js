/* PrimeBuild login logic. Accounts are verified by api.php against MySQL. */
(function () {
  'use strict';

  // If already signed in, go straight to the dashboard.
  if (localStorage.getItem('bs_user') || sessionStorage.getItem('bs_user')) {
    location.replace('index.php');
    return;
  }

  var form = document.querySelector('form');
  var error = document.getElementById('login-error');

  function clearError() { error.textContent = ''; }
  form.addEventListener('input', clearError);
  form.addEventListener('change', clearError);

  // Show / hide password
  var toggle = document.querySelector('.toggle-pass');
  toggle.addEventListener('click', function () {
    var pw = form.elements.password;
    var show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    toggle.classList.toggle('showing', show);
    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  });

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    var role = form.elements.role.value;
    var id = form.elements.username.value.trim().toLowerCase();
    var pass = form.elements.password.value;

    var response, result;
    try {
      response = await fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'login', role: role, username: id, password: pass }) });
      result = await response.json();
    } catch (err) {
      error.textContent = 'Cannot reach the PHP server. Start Apache and MySQL, then import database.sql.';
      return;
    }
    if (!response.ok || !result.user) {
      error.textContent = result.error || 'Unable to sign in.';
      form.elements.password.value = '';
      form.elements.password.focus();
      return;
    }

    // The session ends when the tab closes.
    var store = sessionStorage;
    store.setItem('bs_user', JSON.stringify(result.user));
    location.href = 'index.php';
  });

  document.querySelector('.row-between a').addEventListener('click', function (e) {
    e.preventDefault();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('forgotModal')).show();   // a modal box, not a browser popup
  });
})();