<?php
/**
 * BookNest - User Registration Page
 * College Project: BookNest Online Bookstore
 */

$pageTitle = "Create an Account - BookNest";
$activeNav = "register";

require_once __DIR__ . '/includes/header.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}
?>

<div class="container" style="padding: 60px 20px 80px 20px; max-width: 480px;">
  <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:36px; box-shadow:var(--shadow-md);">
    
    <div style="text-align:center; margin-bottom:24px;">
      <div style="width:48px; height:48px; background:var(--color-primary-light); color:var(--color-primary); border-radius:var(--radius-lg); display:inline-flex; align-items:center; justify-content:center; font-size:1.5rem; margin-bottom:12px;">
        ✨
      </div>
      <h1 style="font-family:var(--font-serif); font-size:1.85rem; font-weight:700; margin-bottom:6px;">Create your account</h1>
      <p style="color:var(--color-text-muted); font-size:0.9rem;">Join BookNest to explore books, review reads, and track shipments</p>
    </div>

    <!-- Registration Form -->
    <form action="api/auth/register.php" method="POST" id="registerForm">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

      <div style="margin-bottom:16px;">
        <label for="name" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Full Name *</label>
        <input 
          type="text" 
          id="name" 
          name="name" 
          required 
          class="nav-search-input" 
          style="padding:11px 14px; border-radius:var(--radius-md);" 
          placeholder="e.g. Aditya Chauhan"
          autocomplete="name"
        >
      </div>

      <div style="margin-bottom:16px;">
        <label for="email" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Email Address *</label>
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

      <div style="margin-bottom:16px;">
        <label for="phone" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Mobile Number (Optional)</label>
        <input 
          type="tel" 
          id="phone" 
          name="phone" 
          class="nav-search-input" 
          style="padding:11px 14px; border-radius:var(--radius-md);" 
          placeholder="+91 98765 43210"
          autocomplete="tel"
        >
      </div>

      <div style="margin-bottom:16px;">
        <label for="password" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Password *</label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          required 
          minlength="6" 
          class="nav-search-input" 
          style="padding:11px 14px; border-radius:var(--radius-md);" 
          placeholder="Minimum 6 characters"
          autocomplete="new-password"
        >
      </div>

      <div style="margin-bottom:24px;">
        <label for="confirm_password" style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Confirm Password *</label>
        <input 
          type="password" 
          id="confirm_password" 
          name="confirm_password" 
          required 
          minlength="6" 
          class="nav-search-input" 
          style="padding:11px 14px; border-radius:var(--radius-md);" 
          placeholder="Repeat your password"
          autocomplete="new-password"
        >
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; margin-bottom:16px;">
        Create BookNest Account
      </button>
    </form>

    <div style="text-align:center; font-size:0.88rem; color:var(--color-text-muted); border-top:1px solid var(--color-card-border); padding-top:16px;">
      Already have an account? <a href="login.php" style="color:var(--color-primary); font-weight:600;">Sign in here</a>
    </div>

  </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
  var p1 = document.getElementById('password').value;
  var p2 = document.getElementById('confirm_password').value;
  if (p1 !== p2) {
    e.preventDefault();
    showToast('Passwords do not match. Please verify.', 'error');
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
