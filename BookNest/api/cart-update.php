<?php
/**
 * BookNest - Cart Update API Endpoint
 * Handles quantity modifications, enforces inventory stock limits, and returns updated summary
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
$action = isset($jsonData['action']) ? trim($jsonData['action']) : trim($_POST['action'] ?? 'set');
$quantity = isset($jsonData['quantity']) ? (int)$jsonData['quantity'] : (int)($_POST['quantity'] ?? 0);
$coupon = isset($jsonData['coupon']) ? trim($jsonData['coupon']) : trim($_POST['coupon'] ?? ($_SESSION['applied_coupon'] ?? ''));

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Book ID provided.']);
    exit;
}

try {
    $pdo = Database::getConnection();
    if (!$pdo) {
        throw new Exception("Database connection unavailable.");
    }

    // Verify book and stock
    $stmt = $pdo->prepare("SELECT id, title, price, discount, stock FROM books WHERE id = ? LIMIT 1");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if (!$book) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Book not found.']);
        exit;
    }

    $availableStock = (int)$book['stock'];
    $currentCartQty = (int)($_SESSION['cart'][$bookId] ?? 0);
    $newQty = $quantity;

    if ($action === 'increase') {
        $newQty = $currentCartQty + 1;
    } elseif ($action === 'decrease') {
        $newQty = $currentCartQty - 1;
    }

    $itemRemoved = false;

    if ($newQty <= 0) {
        // Remove item from cart
        unset($_SESSION['cart'][$bookId]);
        $itemRemoved = true;

        if (isLoggedIn()) {
            $user = getCurrentUser();
            $delStmt = $pdo->prepare("
                DELETE ci FROM cart_items ci
                JOIN cart c ON ci.cart_id = c.id
                WHERE c.user_id = ? AND ci.book_id = ?
            ");
            $delStmt->execute([$user['id'], $bookId]);
        }
    } else {
        // Enforce inventory stock limit
        if ($newQty > $availableStock) {
            $newQty = $availableStock;
            $stockCapWarning = "Stock limit reached. Maximum {$availableStock} copies available.";
        }

        $_SESSION['cart'][$bookId] = $newQty;

        if (isLoggedIn()) {
            $user = getCurrentUser();
            $userId = (int)$user['id'];

            $cartStmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? LIMIT 1");
            $cartStmt->execute([$userId]);
            $cartId = $cartStmt->fetchColumn();

            if (!$cartId) {
                $createStmt = $pdo->prepare("INSERT INTO cart (user_id, created_at) VALUES (?, NOW())");
                $createStmt->execute([$userId]);
                $cartId = $pdo->lastInsertId();
            }

            $upsert = $pdo->prepare("
                INSERT INTO cart_items (cart_id, book_id, quantity, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE quantity = ?
            ");
            $upsert->execute([$cartId, $bookId, $newQty, $newQty]);
        }
    }

    // Recalculate full cart summary
    $cartItems = getCartItems();
    $summary = calculateCartSummary($cartItems, $coupon);

    $unitPrice = calculateDiscountPrice((float)$book['price'], (float)$book['discount']);
    $itemSubtotal = round($unitPrice * $newQty, 2);

    echo json_encode([
        'success' => true,
        'bookId' => $bookId,
        'quantity' => $newQty,
        'itemRemoved' => $itemRemoved,
        'unitPrice' => $unitPrice,
        'itemSubtotal' => $itemSubtotal,
        'cartCount' => getCartCount(),
        'warning' => $stockCapWarning ?? null,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    error_log("Cart update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update item quantity.']);
}
