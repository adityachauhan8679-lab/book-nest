<?php
/**
 * BookNest - Cart Clear API Endpoint
 * Empties all items from the current cart
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

try {
    $_SESSION['cart'] = [];
    unset($_SESSION['applied_coupon']);

    if (isLoggedIn()) {
        $pdo = Database::getConnection();
        if ($pdo) {
            $user = getCurrentUser();
            $stmt = $pdo->prepare("
                DELETE ci FROM cart_items ci
                JOIN cart c ON ci.cart_id = c.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$user['id']]);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared.',
        'cartCount' => 0
    ]);

} catch (Exception $e) {
    error_log("Cart clear error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to clear cart.']);
}
