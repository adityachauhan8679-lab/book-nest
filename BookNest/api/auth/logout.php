<?php
/**
 * BookNest - User Logout API Handler
 * Cleans up session and redirects to login with flash notice
 */

require_once __DIR__ . '/../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $_SESSION['user']['name'] ?? 'User';

// Unset user session
unset($_SESSION['user']);
unset($_SESSION['cart']);
unset($_SESSION['wishlist']);

// Reset session data
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Start fresh session for flash message
session_start();
setFlash('info', 'You have been logged out successfully.');
header('Location: ../../login.php');
exit;
