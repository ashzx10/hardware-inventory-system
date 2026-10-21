<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <!-- Set page metadata and load the Bootstrap and login-page styles. -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in | PrimeBuild</title>
  <link rel="icon" type="image/png" href="assets/primebuild-icon.png">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles/login.css">
</head>

<body>
  <!-- Login form: lets an administrator or staff member choose a role and enter credentials. -->
  <div class="login-page position-relative overflow-hidden min-vh-100">

    <!-- Decorative login background; hidden from assistive technology. -->
    <img class="bg-photo position-absolute top-0 start-0 w-100 h-100 object-fit-cover" src="assets/login-bg.jpg"
      alt="Background photo of a login page" aria-hidden="true">

    <!-- Main sign-in panel with branding, role selection, and credential fields. -->
    <main class="form-panel d-flex align-items-center min-vh-100">
      <section class="login-card w-100" aria-labelledby="login-title">

        <div class="brand d-flex align-items-center gap-3">
          <img class="brand-logo" src="assets/primebuild-icon.png" alt="PrimeBuild logo">
          <div>
            <div class="brand-name">Prime<span>Build</span></div>
          </div>
        </div>
        <div class="brand-rule mt-3 mb-4"></div>

        <header>
          <h1 id="login-title" class="display-6 fw-bold mb-0">Login</h1>
          <p class="lead-text mb-3">Select your role to continue</p>
        </header>

        <form action="#" method="post">
          <!-- These role controls determine which account type is submitted and which labels are shown. -->
          <input class="role-radio" type="radio" name="role" id="r-admin" value="admin" checked>
          <input class="role-radio" type="radio" name="role" id="r-staff" value="staff">

          <fieldset class="role-switch row g-2 mb-3">
            <legend class="visually-hidden">Sign in as</legend>
            <div class="col-6">
              <label class="btn w-100" for="r-admin">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <circle cx="12" cy="8" r="4.5" />
                  <path d="M3.5 21c0-4.7 3.8-7.5 8.5-7.5s8.5 2.8 8.5 7.5z" />
                </svg>
                Admin
              </label>
            </div>
            <div class="col-6">
              <label class="btn w-100" for="r-staff">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                  stroke-linejoin="round" aria-hidden="true">
                  <circle cx="12" cy="8" r="4.2" />
                  <path d="M4 21c0-4.4 3.5-7 8-7s8 2.6 8 7" />
                </svg>
                Staff
              </label>
            </div>
          </fieldset>

          <!-- Credentials are sent by script/login.js to the login API action. -->
          <div class="field mb-3">
            <label for="username" class="visually-hidden">
              <span class="when-admin">Admin username or email</span>
              <span class="when-staff">Employee ID</span>
            </label>
            <div class="input-group input-group-lg">
              <span class="input-group-text">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                  stroke-linejoin="round" aria-hidden="true">
                  <circle cx="12" cy="8" r="4.2" />
                  <path d="M4 21c0-4.4 3.5-7 8-7s8 2.6 8 7" />
                </svg>
              </span>
              <input id="username" name="username" type="text" class="form-control" placeholder="Username"
                autocomplete="username" required>
            </div>
          </div>

          <div class="field mb-3">
            <label for="password" class="visually-hidden">Password</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                  stroke-linejoin="round" aria-hidden="true">
                  <rect x="4.5" y="10.5" width="15" height="10.5" rx="2.5" />
                  <path d="M8 10.5V7.5a4 4 0 0 1 8 0v3" />
                  <circle cx="12" cy="15.7" r="1.3" fill="currentColor" />
                </svg>
              </span>
              <input id="password" name="password" type="password" class="form-control" placeholder="Password"
                autocomplete="current-password" required>
              <button class="toggle-pass btn border" type="button" aria-label="Show password">
                <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                  stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" />
                  <circle cx="12" cy="12" r="3" />
                  <path d="M3 3l18 18" />
                </svg>
                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                  stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
              </button>
            </div>
          </div>

          <div class="form-error alert alert-danger py-2" id="login-error" role="alert"></div>

          <div class="row-between d-flex justify-content-end align-items-center mb-3 small">
            <a href="#">Forgot password?</a>
          </div>

          <div class="form-actions">
            <button class="btn btn-login w-100 d-flex align-items-center justify-content-center gap-2" type="submit">
              <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <circle cx="16" cy="16" r="12.5" />
                <path d="M8.5 16h13M17.5 11.5l4.5 4.5-4.5 4.5" />
              </svg>
              <span class="when-admin">Login as Admin</span>
              <span class="when-staff">Login as Staff</span>
            </button>
          </div>
        </form>
      </section>
    </main>

  </div>

  <!-- Informational password-reset dialog; resets are handled by the store administrator. -->
  <div class="modal fade" id="forgotModal" tabindex="-1" aria-labelledby="forgotTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h2 class="modal-title fs-4" id="forgotTitle">Forgot your password?</h2><button type="button"
            class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">Password resets are handled by your store administrator. Please ask them to set a new
          password for you.</div>
        <div class="modal-footer"><button type="button" class="btn btn-login py-2 px-4 fs-6"
            data-bs-dismiss="modal">Okay</button></div>
      </div>
    </div>
  </div>
  <!-- Bootstrap provides the modal behavior; login.js handles form submission and password visibility. -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script/login.js"></script>
</body>

</html>
