<?php
/**
 * BookNest - Admin Book Delete API
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

$bookId = (int)($input['id'] ?? $input['book_id'] ?? 0);
if ($bookId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid book reference.']);
    exit;
}

$pdo = Database::getConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable.']);
    exit;
}

try {
    // Check if book exists
    $check = $pdo->prepare("SELECT title FROM books WHERE id = ?");
    $check->execute([$bookId]);
    $title = $check->fetchColumn();

    if (!$title) {
        echo json_encode(['success' => false, 'message' => 'Book not found in database.']);
        exit;
    }

    // Safely delete book (cascades to cart_items and wishlist as per foreign key)
    $del = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $del->execute([$bookId]);

    echo json_encode([
        'success' => true,
        'message' => "Book \"{$title}\" was successfully deleted from catalog."
    ]);
} catch (Exception $e) {
    // If foreign key constraint on historical orders prevents deletion, soft delete
    $upd = $pdo->prepare("UPDATE books SET status = 'archived', stock = 0 WHERE id = ?");
    $upd->execute([$bookId]);
    echo json_encode([
        'success' => true,
        'message' => "Book has past order history; safely archived from storefront."
    ]);
}
