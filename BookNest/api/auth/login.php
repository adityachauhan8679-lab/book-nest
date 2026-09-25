<?php
/**
 * BookNest - User Login API Handler
 * College Project: BookNest Online Bookstore
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    setFlash('error', 'Method not allowed.');
    header('Location: ../../login.php');
    exit;
}

// 1. Verify CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($token)) {
    setFlash('error', 'Invalid security token. Please try submitting the form again.');
    header('Location: ../../login.php');
    exit;
}

// 2. Extract and Sanitize Inputs
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$redirect = trim($_POST['redirect'] ?? '');

if (!$email || empty($password)) {
    setFlash('error', 'Please enter a valid email address and password.');
    header('Location: ../../login.php');
    exit;
}

// 3. Authenticate User against Database
try {
    $pdo = Database::getConnection();
    if (!$pdo) {
        throw new Exception("Database connection unavailable.");
    }

    $stmt = $pdo->prepare("SELECT id, name, email, password, role, phone, status FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        setFlash('error', 'Invalid email address or password.');
        header('Location: ../../login.php' . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : ''));
        exit;
    }

    // Check account status
    if ($user['status'] === 'banned') {
        setFlash('error', 'Your account has been suspended. Please contact support.');
        header('Location: ../../login.php');
        exit;
    }

    if ($user['status'] === 'inactive') {
        setFlash('error', 'Your account is currently inactive.');
        header('Location: ../../login.php');
        exit;
    }

    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);

    // Save user session
    $_SESSION['user'] = [
        'id'    => (int)$user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
        'phone' => $user['phone']
    ];

    // Sync any guest cart items with the database cart
    syncGuestCartToUser((int)$user['id'], $pdo);

    // Load wishlist and merge with any guest session wishlist
    $wishStmt = $pdo->prepare("SELECT book_id FROM wishlist WHERE user_id = ?");
    $wishStmt->execute([$user['id']]);
    $dbWishlist = $wishStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // Sync session wishlist into DB
    if (!empty($_SESSION['wishlist']) && is_array($_SESSION['wishlist'])) {
        $insertWish = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, book_id, created_at) VALUES (?, ?, NOW())");
        foreach ($_SESSION['wishlist'] as $wBookId) {
            $insertWish->execute([$user['id'], (int)$wBookId]);
            if (!in_array((int)$wBookId, $dbWishlist)) {
                $dbWishlist[] = (int)$wBookId;
            }
        }
    }
    $_SESSION['wishlist'] = $dbWishlist;

    setFlash('success', "Welcome back, " . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . "!");

    // Role-based or redirect dispatch
    if (!empty($redirect) && strpos($redirect, 'http') === false) {
        header("Location: ../../" . ltrim($redirect, '/'));
        exit;
    }

    if ($user['role'] === 'admin') {
        header('Location: ../../admin/index.php');
    } else {
        header('Location: ../../index.php');
    }
    exit;

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    setFlash('error', 'Authentication failed due to a server error. Please try again.');
    header('Location: ../../login.php');
    exit;
}
