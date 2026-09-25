<?php
/**
 * BookNest - Admin Order Status Transition API
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$currentUser = getCurrentUser();
if (!$currentUser || $currentUser['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Administrator privileges required.']);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$orderId = (int)($input['order_id'] ?? $input['id'] ?? 0);
$status = trim($input['status'] ?? '');

$allowedStatuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
if ($orderId <= 0 || !in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID or unsupported status transition.']);
    exit;
}

$pdo = Database::getConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $orderId]);

    echo json_encode([
        'success' => true,
        'message' => "Order #BN-" . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT) . " status updated to \"{$status}\".",
        'order_id' => $orderId,
        'new_status' => $status
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
