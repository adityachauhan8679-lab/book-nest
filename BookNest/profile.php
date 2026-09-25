<?php
/**
 * BookNest - User Profile & Account Settings
 * Protected by includes/auth.php
 */

$pageTitle = "My Profile - BookNest";
$activeNav = "account";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/auth.php';

$userId = (int)$_SESSION['user']['id'];
$pdo = Database::getConnection();

$userData = null;
$userAddresses = [];
$orderCount = 0;

if ($pdo) {
    try {
        // Fetch fresh user data
        $stmt = $pdo->prepare("SELECT id, name, email, phone, role, status, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch();

        // Fetch user addresses
        $addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $addrStmt->execute([$userId]);
        $userAddresses = $addrStmt->fetchAll();

        // Count user orders
        $orderStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $orderStmt->execute([$userId]);
        $orderCount = (int)$orderStmt->fetchColumn();

    } catch (Exception $e) {
        error_log("Profile load error: " . $e->getMessage());
    }
}

// Fallback if DB not ready
if (!$userData) {
    $userData = [
        'id' => $userId,
        'name' => $_SESSION['user']['name'] ?? 'BookNest Reader',
        'email' => $_SESSION['user']['email'] ?? 'reader@booknest.com',
        'phone' => $_SESSION['user']['phone'] ?? '+91 98765 43210',
        'role' => $_SESSION['user']['role'] ?? 'user',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Handle Profile Information Update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: profile.php');
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || strlen($name) < 2) {
        setFlash('error', 'Please enter a valid name.');
        header('Location: profile.php');
        exit;
    }

    if ($pdo) {
        try {
            $upStmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?");
            $upStmt->execute([$name, $phone, $userId]);
            
            // Update session
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;

            setFlash('success', 'Profile information updated successfully.');
            header('Location: profile.php');
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Failed to update profile: ' . $e->getMessage());
            header('Location: profile.php');
            exit;
        }
    }
}

// Handle Password Change (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        header('Location: profile.php');
        exit;
    }

    $currentPass = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (strlen($newPass) < 6) {
        setFlash('error', 'New password must be at least 6 characters.');
        header('Location: profile.php');
        exit;
    }

    if ($newPass !== $confirmPass) {
        setFlash('error', 'New passwords do not match.');
        header('Location: profile.php');
        exit;
    }

    if ($pdo) {
        try {
            $pwdStmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            $pwdStmt->execute([$userId]);
            $currentHashed = $pwdStmt->fetchColumn();

            if (!password_verify($currentPass, $currentHashed)) {
                setFlash('error', 'Your current password was entered incorrectly.');
                header('Location: profile.php');
                exit;
            }

            $newHashed = password_hash($newPass, PASSWORD_BCRYPT);
            $upPwdStmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $upPwdStmt->execute([$newHashed, $userId]);

            setFlash('success', 'Your password has been changed successfully.');
            header('Location: profile.php');
            exit;
        } catch (Exception $e) {
            setFlash('error', 'Error updating password.');
            header('Location: profile.php');
            exit;
        }
    }
}
?>

<div class="container" style="padding: 50px 20px 80px 20px; max-width: 1000px;">
  
  <!-- Profile Page Header -->
  <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom: 32px;">
    <div>
      <div style="display:inline-flex; align-items:center; gap:6px; background:var(--color-primary-light); color:var(--color-primary); padding:3px 10px; border-radius:var(--radius-full); font-size:0.8rem; font-weight:700; margin-bottom:8px;">
        <span>👤</span> <?= strtoupper(e($userData['role'])) ?> ACCOUNT
      </div>
      <h1 class="section-title"><?= e($userData['name']) ?></h1>
      <p class="section-desc">Member since <?= date('F Y', strtotime($userData['created_at'])) ?> • <?= e($userData['email']) ?></p>
    </div>

    <div style="display:flex; gap:10px;">
      <?php if (isAdmin()): ?>
        <a href="admin/index.php" class="btn btn-secondary">
          <span>⚙️</span> Admin Dashboard
        </a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-outline" style="color:var(--color-danger); border-color:#FCA5A5;">
        <span>🚪</span> Logout
      </a>
    </div>
  </div>

  <!-- Account Quick Cards -->
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:36px;">
    <a href="orders.php" style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-lg); padding:20px; text-decoration:none; color:inherit; display:flex; align-items:center; gap:14px; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
      <div style="width:44px; height:44px; border-radius:var(--radius-md); background:var(--color-primary-light); display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
        📦
      </div>
      <div>
        <div style="font-size:1.4rem; font-weight:800;"><?= $orderCount ?></div>
        <div style="font-size:0.82rem; color:var(--color-text-muted);">Orders Placed</div>
      </div>
    </a>

    <a href="wishlist.php" style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-lg); padding:20px; text-decoration:none; color:inherit; display:flex; align-items:center; gap:14px; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
      <div style="width:44px; height:44px; border-radius:var(--radius-md); background:#FFE4E6; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
        ♡
      </div>
      <div>
        <div style="font-size:1.4rem; font-weight:800;"><?= getWishlistCount() ?></div>
        <div style="font-size:0.82rem; color:var(--color-text-muted);">Saved in Wishlist</div>
      </div>
    </a>

    <a href="cart.php" style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-lg); padding:20px; text-decoration:none; color:inherit; display:flex; align-items:center; gap:14px; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
      <div style="width:44px; height:44px; border-radius:var(--radius-md); background:#FEF3C7; display:flex; align-items:center; justify-content:center; font-size:1.3rem;">
        🛒
      </div>
      <div>
        <div style="font-size:1.4rem; font-weight:800;"><?= getCartCount() ?></div>
        <div style="font-size:0.82rem; color:var(--color-text-muted);">Items in Cart</div>
      </div>
    </a>
  </div>

  <!-- Settings Grid -->
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:32px;">
    
    <!-- Profile Info Form -->
    <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:32px; box-shadow:var(--shadow-sm);">
      <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:8px;">Personal Information</h3>
      <p style="font-size:0.85rem; color:var(--color-text-muted); margin-bottom:20px;">Update your name and contact phone number.</p>

      <form action="profile.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <input type="hidden" name="action" value="update_profile">

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Full Name</label>
          <input type="text" name="name" value="<?= e($userData['name']) ?>" required class="nav-search-input" style="padding:10px 14px; border-radius:var(--radius-md);">
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Email Address</label>
          <input type="email" value="<?= e($userData['email']) ?>" disabled class="nav-search-input" style="padding:10px 14px; border-radius:var(--radius-md); background:#F3F4F6; cursor:not-allowed;" title="Email cannot be changed">
          <small style="color:var(--color-text-light); font-size:0.75rem;">Email is linked to your account orders and cannot be changed.</small>
        </div>

        <div style="margin-bottom:24px;">
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Phone Number</label>
          <input type="tel" name="phone" value="<?= e($userData['phone'] ?? '') ?>" class="nav-search-input" style="padding:10px 14px; border-radius:var(--radius-md);" placeholder="+91 98765 43210">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">
          Save Profile Changes
        </button>
      </form>
    </div>

    <!-- Change Password Form -->
    <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:32px; box-shadow:var(--shadow-sm);">
      <h3 style="font-size:1.2rem; font-weight:700; margin-bottom:8px;">Change Password</h3>
      <p style="font-size:0.85rem; color:var(--color-text-muted); margin-bottom:20px;">Use a secure password with at least 6 characters.</p>

      <form action="profile.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <input type="hidden" name="action" value="change_password">

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Current Password</label>
          <input type="password" name="current_password" required class="nav-search-input" style="padding:10px 14px; border-radius:var(--radius-md);" placeholder="Enter current password">
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">New Password</label>
          <input type="password" name="new_password" required minlength="6" class="nav-search-input" style="padding:10px 14px; border-radius:var(--radius-md);" placeholder="Min 6 characters">
        </div>

        <div style="margin-bottom:24px;">
          <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:6px;">Confirm New Password</label>
          <input type="password" name="confirm_password" required minlength="6" class="nav-search-input" style="padding:10px 14px; border-radius:var(--radius-md);" placeholder="Repeat new password">
        </div>

        <button type="submit" class="btn btn-secondary" style="width:100%; border-color:var(--color-primary); color:var(--color-primary);">
          Update Password
        </button>
      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
