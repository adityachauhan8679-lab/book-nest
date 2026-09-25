<?php
/**
 * BookNest - Global Navbar Include
 */
$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$currentUser = getCurrentUser();
?>
<header class="site-header">
  <div class="container">
    <nav class="navbar">
      <!-- Brand Logo -->
      <a href="index.php" class="brand-logo">
        <span class="logo-icon">📖</span>
        <span>BookNest</span>
      </a>

      <!-- Desktop Nav Menu -->
      <ul class="nav-menu">
        <li>
          <a href="index.php" class="nav-link <?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">Home</a>
        </li>
        <li>
          <a href="books.php" class="nav-link <?= ($activeNav ?? '') === 'books' ? 'active' : '' ?>">Books</a>
        </li>
        <li>
          <a href="categories.php" class="nav-link <?= ($activeNav ?? '') === 'categories' ? 'active' : '' ?>">Categories</a>
        </li>
        <li>
          <a href="scanner.php" class="nav-link scanner-badge-link <?= ($activeNav ?? '') === 'scanner' ? 'active' : '' ?>">
            <span>📷</span>
            <span>Scanner</span>
          </a>
        </li>
      </ul>

      <!-- Search Bar -->
      <form action="books.php" method="GET" class="nav-search-form">
        <div class="search-input-wrapper">
          <span class="search-icon">🔍</span>
          <input 
            type="search" 
            name="q" 
            class="nav-search-input" 
            placeholder="Search by title, author, ISBN..." 
            autocomplete="off"
            value="<?= e($_GET['q'] ?? '') ?>"
          >
        </div>
      </form>

      <!-- Nav Actions (Wishlist, Cart, Account) -->
      <div class="nav-actions">
        <!-- Wishlist -->
        <a href="wishlist.php" class="action-btn-icon" title="Wishlist">
          <span>♡</span>
          <span class="badge-count" id="wishlistBadge"><?= $wishlistCount ?></span>
        </a>

        <!-- Cart -->
        <a href="cart.php" class="action-btn-icon" title="Cart">
          <span>🛒</span>
          <span class="badge-count" id="cartBadge"><?= $cartCount ?></span>
        </a>

        <!-- User Account / Auth -->
        <?php if ($currentUser): ?>
          <div class="user-account-dropdown" style="position:relative; display:inline-block;">
            <a href="profile.php" class="btn btn-outline btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
              <span>👤</span>
              <span><?= e(explode(' ', $currentUser['name'])[0]) ?></span>
              <span style="font-size:0.7rem;">▼</span>
            </a>
            <div class="dropdown-menu" style="position:absolute; right:0; top:calc(100% + 8px); background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-md); box-shadow:var(--shadow-lg); width:180px; padding:6px 0; z-index:200; display:none;">
              <a href="profile.php" style="display:block; padding:8px 16px; font-size:0.88rem; color:var(--color-text-main); text-decoration:none;">👤 My Profile</a>
              <a href="orders.php" style="display:block; padding:8px 16px; font-size:0.88rem; color:var(--color-text-main); text-decoration:none;">📦 My Orders</a>
              <a href="wishlist.php" style="display:block; padding:8px 16px; font-size:0.88rem; color:var(--color-text-main); text-decoration:none;">♡ Wishlist</a>
              <?php if (isAdmin()): ?>
                <div style="border-top:1px solid var(--color-card-border); margin:4px 0;"></div>
                <a href="admin/index.php" style="display:block; padding:8px 16px; font-size:0.88rem; color:var(--color-primary); font-weight:700; text-decoration:none;">⚙️ Admin Panel</a>
              <?php endif; ?>
              <div style="border-top:1px solid var(--color-card-border); margin:4px 0;"></div>
              <a href="logout.php" style="display:block; padding:8px 16px; font-size:0.88rem; color:var(--color-danger); text-decoration:none;">🚪 Logout</a>
            </div>
          </div>
          <?php if (isAdmin()): ?>
            <a href="admin/index.php" class="btn btn-primary btn-sm" style="padding:6px 12px; font-size:0.8rem;">Admin</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="login.php" class="btn btn-secondary btn-sm">Login</a>
          <a href="register.php" class="btn btn-primary btn-sm">Register</a>
        <?php endif; ?>

        <!-- Mobile Menu Trigger -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation">
          ☰
        </button>
      </div>
    </nav>
  </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="mobile-menu-overlay" id="mobileOverlay"></div>
<div class="mobile-drawer" id="mobileDrawer">
  <div class="mobile-drawer-header">
    <div class="brand-logo" style="font-size: 1.4rem;">
      <span class="logo-icon" style="width: 32px; height: 32px; font-size: 1rem;">📖</span>
      <span>BookNest</span>
    </div>
    <button class="mobile-drawer-close" id="mobileDrawerClose">✕</button>
  </div>

  <form action="books.php" method="GET" style="margin-bottom: 20px;">
    <input 
      type="search" 
      name="q" 
      class="nav-search-input" 
      placeholder="Search books..." 
      style="padding: 10px 14px;"
    >
  </form>

  <ul class="mobile-nav-links">
    <li><a href="index.php" class="mobile-nav-link">Home</a></li>
    <li><a href="books.php" class="mobile-nav-link">All Books</a></li>
    <li><a href="categories.php" class="mobile-nav-link">Categories</a></li>
    <li><a href="scanner.php" class="mobile-nav-link" style="color: var(--color-accent);">📷 Book Scanner</a></li>
    <li><a href="wishlist.php" class="mobile-nav-link">Wishlist (<?= $wishlistCount ?>)</a></li>
    <li><a href="cart.php" class="mobile-nav-link">Cart (<?= $cartCount ?>)</a></li>
    <?php if ($currentUser): ?>
      <li><a href="orders.php" class="mobile-nav-link">My Orders</a></li>
      <li><a href="profile.php" class="mobile-nav-link">Profile</a></li>
      <?php if (isAdmin()): ?>
        <li><a href="admin/index.php" class="mobile-nav-link" style="color: var(--color-primary); font-weight:700;">Admin Panel</a></li>
      <?php endif; ?>
      <li><a href="logout.php" class="mobile-nav-link" style="color: var(--color-danger);">Logout</a></li>
    <?php else: ?>
      <li style="margin-top: 10px; display: flex; gap: 8px;">
        <a href="login.php" class="btn btn-secondary" style="flex:1;">Login</a>
        <a href="register.php" class="btn btn-primary" style="flex:1;">Register</a>
      </li>
    <?php endif; ?>
  </ul>
</div>
