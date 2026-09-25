<?php
/**
 * BookNest - Admin Real-time Inventory Stock Update API
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

$bookId = (int)($input['book_id'] ?? $input['id'] ?? 0);
$stock = isset($input['stock']) ? (int)$input['stock'] : -1;
$delta = isset($input['delta']) ? (int)$input['delta'] : 0;

if ($bookId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid book reference ID.']);
    exit;
}

$pdo = Database::getConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable.']);
    exit;
}

try {
    if ($delta !== 0) {
        $stmt = $pdo->prepare("UPDATE books SET stock = GREATEST(0, stock + ?) WHERE id = ?");
        $stmt->execute([$delta, $bookId]);
    } else if ($stock >= 0) {
        $stmt = $pdo->prepare("UPDATE books SET stock = ? WHERE id = ?");
        $stmt->execute([$stock, $bookId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Specify either stock or delta.']);
        exit;
    }

    $curr = $pdo->prepare("SELECT id, title, stock FROM books WHERE id = ?");
    $curr->execute([$bookId]);
    $updated = $curr->fetch();

    echo json_encode([
        'success' => true,
        'message' => "Stock updated successfully.",
        'new_stock' => (int)($updated['stock'] ?? 0),
        'book_title' => $updated['title'] ?? ''
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
