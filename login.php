<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in | BuildStock</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700&display=swap">
<link rel="stylesheet" href="styles/login.css">
</head>
<body>
<div class="login-page">

  <!-- ===== Brand panel ===== -->
  <aside class="brand-panel">
    <div class="brand">
      <div class="brand-mark">B</div>
      <div>
        <div class="brand-name">BuildStock</div>
        <div class="brand-sub">Inventory &amp; Sales</div>
      </div>
    </div>
    <div class="brand-copy">
      <p class="tagline">Know what's on the shelf before the customer asks.</p>
      <p>Track stock, suppliers, purchase orders and sales for your hardware and construction supply store.</p>
    </div>
    <footer class="brand-foot">CCS110 Finals Project</footer>
  </aside>

  <!-- ===== Login form ===== -->
  <main class="form-panel">
    <section class="login-card" aria-labelledby="login-title">
      <header>
        <h1 id="login-title">Sign in</h1>
        <p class="lead">Choose your role, then enter your details.</p>
      </header>

      <form action="#" method="post">
        <!-- Role radios must come first so sibling selectors can react to them -->
        <input class="role-radio" type="radio" name="role" id="r-admin" value="admin" checked>
        <input class="role-radio" type="radio" name="role" id="r-staff" value="staff">

        <fieldset class="role-switch">
          <legend class="visually-hidden">Sign in as</legend>
          <label for="r-admin">Admin</label>
          <label for="r-staff">Staff</label>
        </fieldset>

        <p class="role-hint hint-admin">Admins manage products, suppliers, purchase orders, user accounts and reports.</p>
        <p class="role-hint hint-staff">Staff record sales and stock movements and look up product availability.</p>

        <div class="field">
          <label for="username">
            <span class="when-admin">Admin username or email</span>
            <span class="when-staff">Employee ID</span>
          </label>
          <input id="username" name="username" type="text" placeholder="Enter your username" autocomplete="username" required>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
        </div>

        <p class="form-error" id="login-error" role="alert"></p>

        <div class="row-between">
          <label class="remember"><input type="checkbox" name="remember"> Keep me signed in</label>
          <a href="#">Forgot password?</a>
        </div>

        <div class="form-actions">
          <button class="btn btn-orange" type="submit">
            <span class="when-admin">Sign in as admin</span>
            <span class="when-staff">Sign in as staff</span>
          </button>
        </div>
      </form>

      <p class="help">Need an account? Ask your store administrator.<br>Demo accounts: admin / admin123 and staff / staff123</p>
    </section>
  </main>

</div>
<script src="scripts/login.js"></script>
</body>
</html>
