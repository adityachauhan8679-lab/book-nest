<?php
/**
 * BookNest - Cart Add API Endpoint
 * Handles adding books to cart for both logged-in users and guests
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// Support JSON input or form-encoded POST
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

$bookId = isset($jsonData['book_id']) ? (int)$jsonData['book_id'] : (int)($_POST['book_id'] ?? 0);
$quantity = isset($jsonData['quantity']) ? (int)$jsonData['quantity'] : (int)($_POST['quantity'] ?? 1);

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Book ID provided.']);
    exit;
}

if ($quantity <= 0) {
    $quantity = 1;
}

try {
    $pdo = Database::getConnection();
    if (!$pdo) {
        throw new Exception("Database connection unavailable.");
    }

    // 1. Verify book existence and stock
    $stmt = $pdo->prepare("SELECT id, title, price, discount, stock, cover_image FROM books WHERE id = ? LIMIT 1");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if (!$book) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Book not found.']);
        exit;
    }

    $availableStock = (int)$book['stock'];
    if ($availableStock <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'error' => 'Sorry, "' . $book['title'] . '" is currently out of stock.'
        ]);
        exit;
    }

    // Initialize session cart if needed
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $currentCartQty = (int)($_SESSION['cart'][$bookId] ?? 0);
    $newTotalQty = $currentCartQty + $quantity;

    if ($newTotalQty > $availableStock) {
        $allowedAdd = max(0, $availableStock - $currentCartQty);
        if ($allowedAdd === 0) {
            echo json_encode([
                'success' => false,
                'error' => "You already have all {$availableStock} available copies in your cart.",
                'cartCount' => getCartCount()
            ]);
            exit;
        }
        $newTotalQty = $availableStock;
        $quantity = $allowedAdd;
    }

    // 2. Update session cart
    $_SESSION['cart'][$bookId] = $newTotalQty;

    // 3. If user is logged in, sync directly to database
    if (isLoggedIn()) {
        $user = getCurrentUser();
        $userId = (int)$user['id'];

        // Get or create cart row
        $cartStmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? LIMIT 1");
        $cartStmt->execute([$userId]);
        $cartId = $cartStmt->fetchColumn();

        if (!$cartId) {
            $createStmt = $pdo->prepare("INSERT INTO cart (user_id, created_at) VALUES (?, NOW())");
            $createStmt->execute([$userId]);
            $cartId = $pdo->lastInsertId();
        }

        $itemStmt = $pdo->prepare("
            INSERT INTO cart_items (cart_id, book_id, quantity, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE quantity = ?
        ");
        $itemStmt->execute([$cartId, $bookId, $newTotalQty, $newTotalQty]);
    }

    $discountedPrice = calculateDiscountPrice((float)$book['price'], (float)$book['discount']);
    $cartCount = getCartCount();

    echo json_encode([
        'success' => true,
        'message' => "Added " . ($quantity > 1 ? "{$quantity}x " : "") . "\"{$book['title']}\" to your cart.",
        'book' => [
            'id' => $bookId,
            'title' => $book['title'],
            'price' => (float)$book['price'],
            'unit_price' => $discountedPrice,
            'quantity' => $newTotalQty,
            'stock' => $availableStock
        ],
        'cartCount' => $cartCount
    ]);

} catch (Exception $e) {
    error_log("Cart add error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while updating your cart.'
    ]);
}
