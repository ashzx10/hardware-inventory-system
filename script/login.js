/* BuildStock login logic. Accounts are verified by api.php against MySQL. */
(function () {
  'use strict';

  // Already signed in? Go straight to the dashboard.
  if (localStorage.getItem('bs_user') || sessionStorage.getItem('bs_user')) {
    location.replace('index.php');
    return;
  }

  var form = document.querySelector('form');
  var error = document.getElementById('login-error');

  function clearError() { error.textContent = ''; }
  form.addEventListener('input', clearError);
  form.addEventListener('change', clearError);

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

    // "Keep me signed in" uses localStorage; otherwise the session ends when the tab closes.
    var store = form.elements.remember.checked ? localStorage : sessionStorage;
    store.setItem('bs_user', JSON.stringify(result.user));
    location.href = 'index.php';
  });

  document.querySelector('.row-between a').addEventListener('click', function (e) {
    e.preventDefault();
    alert('Password resets are handled by your store administrator.');
  });
})();
