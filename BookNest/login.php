<?php
/**
 * BookNest - User Login Page
 * College Project: BookNest Online Bookstore
 */

$pageTitle = "Sign In - BookNest";
$activeNav = "login";

require_once __DIR__ . '/includes/header.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/index.php' : 'profile.php'));
    exit;
}

$redirect = $_GET['redirect'] ?? '';
?>

<div class="container" style="padding: 60px 20px 80px 20px; max-width: 460px;">
  <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:36px; box-shadow:var(--shadow-md);">
    
    <div style="text-align:center; margin-bottom:24px;">
      <div style="width:48px; height:48px; background:var(--color-primary-light); color:var(--color-primary); border-radius:var(--radius-lg); display:inline-flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:12px;">
        📖
      </div>
      <h1 style="font-family:var(--font-serif); font-size:1.85rem; font-weight:700; margin-bottom:6px;">Welcome back</h1>
      <p style="color:var(--color-text-muted); font-size:0.9rem;">Sign in to access your BookNest account</p>
    </div>

    <!-- Quick Demo Accounts (1-click fill for examiners/testers) -->
    <div style="background:var(--color-bg); border:1px dashed #CBD5E1; border-radius:var(--radius-md); padding:14px; font-size:0.82rem; margin-bottom:24px;">
      <div style="font-weight:700; color:var(--color-text-main); margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
        <span>⚡ Quick Demo Credentials</span>
        <span style="font-size:0.75rem; color:var(--color-text-light);">Click to fill</span>
      </div>
      
      <div style="display:flex; gap:8px;">
        <button 
          type="button" 
          onclick="fillCredentials('admin@booknest.com', 'Admin@123')"
          class="btn btn-secondary btn-sm" 
          style="flex:1; font-size:0.75rem; padding:6px 8px;"
        >
          👑 Admin (admin@...)
        </button>
        <button 
          type="button" 
          onclick="fillCredentials('user@booknest.com', 'User@123')"
          class="btn btn-secondary btn-sm" 
          style="flex:1; font-size:0.75rem; padding:6px 8px;"
        >
          👤 User (user@...)
        </button>
      </div>
    </div>

    <!-- Login Form -->
    <form action="api/auth/login.php" method="POST" id="loginForm">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <?php if (!empty($redirect)): ?>
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
      <?php endif; ?>

      <div style="margin-bottom:18px;">
        <label for="email" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Email Address</label>
        <input 
          type="email" 
          id="email"
          name="email" 
          required 
          class="nav-search-input" 
          style="padding:11px 14px; border-radius:var(--radius-md);" 
          placeholder="e.g. aditya@example.com"
          autocomplete="email"
        >
      </div>

      <div style="margin-bottom:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
          <label for="password" style="font-size:0.85rem; font-weight:600;">Password</label>
        </div>
        <input 
          type="password" 
          id="password"
          name="password" 
          required 
          class="nav-search-input" 
          style="padding:11px 14px; border-radius:var(--radius-md);" 
          placeholder="••••••••"
          autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; margin-bottom:16px;">
        Sign In to BookNest
      </button>
    </form>

    <div style="text-align:center; font-size:0.88rem; color:var(--color-text-muted); border-top:1px solid var(--color-card-border); padding-top:16px;">
      Don't have an account yet? <a href="register.php" style="color:var(--color-primary); font-weight:600;">Create an account</a>
    </div>

  </div>
</div>

<script>
function fillCredentials(email, pass) {
  document.getElementById('email').value = email;
  document.getElementById('password').value = pass;
  showToast('Filled credentials for ' + email, 'info');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
