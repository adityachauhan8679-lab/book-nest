<?php
/**
 * BookNest - Coupon Validation & Application API Endpoint
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

$couponCode = isset($jsonData['coupon']) ? trim($jsonData['coupon']) : trim($_POST['coupon'] ?? '');
$action = isset($jsonData['action']) ? trim($jsonData['action']) : trim($_POST['action'] ?? 'apply');

if ($action === 'remove') {
    unset($_SESSION['applied_coupon']);
    $cartItems = getCartItems();
    $summary = calculateCartSummary($cartItems, null);

    echo json_encode([
        'success' => true,
        'message' => 'Coupon removed.',
        'summary' => $summary
    ]);
    exit;
}

if (empty($couponCode)) {
    echo json_encode([
        'success' => false,
        'error' => 'Please enter a coupon code.'
    ]);
    exit;
}

$cartItems = getCartItems();
if (empty($cartItems)) {
    echo json_encode([
        'success' => false,
        'error' => 'Your cart is empty. Add books before applying a coupon.'
    ]);
    exit;
}

$summary = calculateCartSummary($cartItems, $couponCode);

if ($summary['coupon_error']) {
    echo json_encode([
        'success' => false,
        'error' => $summary['coupon_error']
    ]);
    exit;
}

// Store applied coupon in session
$_SESSION['applied_coupon'] = $summary['applied_coupon']['code'];

echo json_encode([
    'success' => true,
    'message' => "Coupon '{$summary['applied_coupon']['code']}' applied successfully!",
    'coupon' => $summary['applied_coupon'],
    'summary' => $summary
]);
