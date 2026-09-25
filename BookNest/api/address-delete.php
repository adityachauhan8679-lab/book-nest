<?php
/**
 * BookNest - Delete Address API Endpoint
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please sign in to delete saved addresses.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

$addressId = (int)($jsonData['address_id'] ?? $_POST['address_id'] ?? 0);
$user = getCurrentUser();

if ($addressId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Address ID.']);
    exit;
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $user['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Address removed.'
    ]);
} catch (Exception $e) {
    error_log("Address delete error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not remove address.']);
}
