<?php
/**
 * BookNest - Order Placement API Handler
 * Performs atomic transactional order creation, inventory stock deduction, and cart flushing
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

$addressId = isset($jsonData['address_id']) ? (int)$jsonData['address_id'] : (int)($_POST['address_id'] ?? 0);
$paymentMethod = trim($jsonData['payment_method'] ?? $_POST['payment_method'] ?? 'Cash on Delivery');
$couponCode = trim($jsonData['coupon'] ?? $_SESSION['applied_coupon'] ?? '');

// Map payment method enum safely
$allowedPaymentMethods = ['UPI', 'Card', 'Cash on Delivery'];
if (!in_array($paymentMethod, $allowedPaymentMethods)) {
    $paymentMethod = 'Cash on Delivery';
}

$cartItems = getCartItems();
if (empty($cartItems)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Your cart is empty. Add items before checking out.']);
    exit;
}

$pdo = Database::getConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection unavailable.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Determine User ID
    $userId = 0;
    if (isLoggedIn()) {
        $user = getCurrentUser();
        $userId = (int)$user['id'];
    } else {
        // Fallback for guest checkout: find or create guest user account
        $guestEmail = trim($jsonData['guest_email'] ?? 'guest@booknest.com');
        $guestName  = trim($jsonData['guest_name'] ?? 'Guest Customer');
        
        $uStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $uStmt->execute([$guestEmail]);
        $userId = (int)$uStmt->fetchColumn();

        if (!$userId) {
            $hashedPass = password_hash('Guest@123', PASSWORD_DEFAULT);
            $createU = $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())");
            $createU->execute([$guestName, $guestEmail, $hashedPass]);
            $userId = (int)$pdo->lastInsertId();
        }
    }

    // 2. Address Handling
    $finalAddressId = $addressId;
    if ($finalAddressId <= 0 || !empty($jsonData['new_address'])) {
        $addrData = $jsonData['new_address'] ?? $_POST;
        $fullName = trim($addrData['full_name'] ?? '');
        $phone    = trim($addrData['phone'] ?? '');
        $address  = trim($addrData['address'] ?? '');
        $city     = trim($addrData['city'] ?? '');
        $state    = trim($addrData['state'] ?? 'Delhi');
        $pincode  = trim($addrData['pincode'] ?? '');

        if (!empty($fullName) && !empty($address) && !empty($phone)) {
            $insAddr = $pdo->prepare("
                INSERT INTO addresses (user_id, full_name, phone, address, city, state, pincode, is_default, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            $insAddr->execute([$userId, $fullName, $phone, $address, $city, $state, $pincode]);
            $finalAddressId = (int)$pdo->lastInsertId();
        }
    }

    // 3. Verify stock availability and lock rows for update
    foreach ($cartItems as $item) {
        $stockCheck = $pdo->prepare("SELECT id, title, stock, price, discount FROM books WHERE id = ? FOR UPDATE");
        $stockCheck->execute([$item['id']]);
        $liveBook = $stockCheck->fetch();

        if (!$liveBook || (int)$liveBook['stock'] < $item['quantity']) {
            $pdo->rollBack();
            $available = $liveBook ? (int)$liveBook['stock'] : 0;
            echo json_encode([
                'success' => false,
                'error' => "Insufficient stock for '{$item['title']}'. Only {$available} copies left in inventory."
            ]);
            exit;
        }
    }

    // 4. Calculate Final Totals
    $summary = calculateCartSummary($cartItems, $couponCode);
    $totalAmount = $summary['grand_total'];

    // Payment status
    $paymentStatus = ($paymentMethod === 'Cash on Delivery') ? 'Pending' : 'Paid';
    $orderStatus   = 'Confirmed';

    // 5. Insert into `orders`
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, address_id, total_amount, payment_method, payment_status, order_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $orderStmt->execute([
        $userId,
        $finalAddressId > 0 ? $finalAddressId : null,
        $totalAmount,
        $paymentMethod,
        $paymentStatus,
        $orderStatus
    ]);
    $newOrderId = (int)$pdo->lastInsertId();

    // 6. Insert `order_items` & deduct inventory stock
    $orderItemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, book_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");

    $deductStockStmt = $pdo->prepare("
        UPDATE books SET stock = stock - ? WHERE id = ?
    ");

    foreach ($cartItems as $item) {
        $orderItemStmt->execute([
            $newOrderId,
            $item['id'],
            $item['quantity'],
            $item['unit_price']
        ]);

        $deductStockStmt->execute([
            $item['quantity'],
            $item['id']
        ]);
    }

    // 7. Clear user cart in database & session
    if (isLoggedIn()) {
        $clearDbCart = $pdo->prepare("
            DELETE ci FROM cart_items ci
            JOIN cart c ON ci.cart_id = c.id
            WHERE c.user_id = ?
        ");
        $clearDbCart->execute([$userId]);
    }

    $_SESSION['cart'] = [];
    unset($_SESSION['applied_coupon']);

    $pdo->commit();

    setFlash('success', "Order #BN-{$newOrderId} placed successfully!");

    echo json_encode([
        'success' => true,
        'order_id' => $newOrderId,
        'message' => 'Order placed successfully!',
        'redirect' => 'order-success.php?id=' . $newOrderId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Order placement transaction error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Order could not be processed due to a server error. Please try again.'
    ]);
}
