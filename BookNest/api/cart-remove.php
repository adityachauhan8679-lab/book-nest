<?php
/**
 * BookNest - Cart Remove API Endpoint
 * Handles removing an item completely from the cart
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

$bookId = isset($jsonData['book_id']) ? (int)$jsonData['book_id'] : (int)($_POST['book_id'] ?? 0);
$coupon = isset($jsonData['coupon']) ? trim($jsonData['coupon']) : trim($_POST['coupon'] ?? ($_SESSION['applied_coupon'] ?? ''));

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Book ID provided.']);
    exit;
}

try {
    $pdo = Database::getConnection();

    // 1. Remove from session
    if (isset($_SESSION['cart'][$bookId])) {
        unset($_SESSION['cart'][$bookId]);
    }

    // 2. Remove from DB if user is logged in
    if (isLoggedIn() && $pdo) {
        $user = getCurrentUser();
        $delStmt = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            WHERE c.user_id = ? AND ci.book_id = ?
        ");
        $delStmt->execute([$user['id'], $bookId]);
    }

    // 3. Recalculate summary
    $cartItems = getCartItems();
    $summary = calculateCartSummary($cartItems, $coupon);

    echo json_encode([
        'success' => true,
        'message' => 'Item removed from your cart.',
        'bookId' => $bookId,
        'cartCount' => getCartCount(),
        'summary' => $summary,
        'isEmpty' => empty($cartItems)
    ]);

} catch (Exception $e) {
    error_log("Cart remove error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not remove item.']);
}
