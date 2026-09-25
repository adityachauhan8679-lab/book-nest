<?php
/**
 * BookNest - Add Address API Endpoint
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

$fullName = trim($jsonData['full_name'] ?? $_POST['full_name'] ?? '');
$phone    = trim($jsonData['phone'] ?? $_POST['phone'] ?? '');
$address  = trim($jsonData['address'] ?? $_POST['address'] ?? '');
$city     = trim($jsonData['city'] ?? $_POST['city'] ?? '');
$state    = trim($jsonData['state'] ?? $_POST['state'] ?? 'Delhi');
$pincode  = trim($jsonData['pincode'] ?? $_POST['pincode'] ?? '');
$isDefault = !empty($jsonData['is_default'] ?? $_POST['is_default']) ? 1 : 0;

if (empty($fullName) || empty($phone) || empty($address) || empty($city) || empty($pincode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'All delivery address fields are required.']);
    exit;
}

try {
    $pdo = Database::getConnection();
    if (!$pdo) {
        throw new Exception("Database unavailable.");
    }

    $userId = 0;
    if (isLoggedIn()) {
        $user = getCurrentUser();
        $userId = (int)$user['id'];
    }

    if ($userId > 0) {
        if ($isDefault) {
            $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO addresses (user_id, full_name, phone, address, city, state, pincode, is_default, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $fullName, $phone, $address, $city, $state, $pincode, $isDefault]);
        $addressId = (int)$pdo->lastInsertId();
    } else {
        // Store guest address in session
        $addressId = 1000 + rand(1, 999);
        $_SESSION['guest_address'] = [
            'id' => $addressId,
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'is_default' => 1
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Delivery address saved successfully.',
        'address' => [
            'id' => $addressId,
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'is_default' => $isDefault
        ]
    ]);

} catch (Exception $e) {
    error_log("Address add error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save address.']);
}
