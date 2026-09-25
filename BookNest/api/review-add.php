<?php
/**
 * BookNest - Review Submission API Endpoint
 * Handles verified reviews, rating calculation and updates
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'You must be logged in to leave a review.',
        'redirect' => 'login.php'
    ]);
    exit;
}

$userId = (int)$currentUser['id'];

// Read JSON body or POST form data
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $input = $_POST;
}

$bookId = (int)($input['book_id'] ?? 0);
$rating = (int)($input['rating'] ?? 5);
$title = trim($input['title'] ?? '');
$comment = trim($input['comment'] ?? '');

if ($bookId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid book reference ID.']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be an integer between 1 and 5 stars.']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Review comment cannot be empty.']);
    exit;
}

// Submit review through our core function
$result = submitBookReview($userId, $bookId, $rating, $title, $comment);

if ($result['success']) {
    $stats = getBookRatingStats($bookId);
    $result['stats'] = $stats;
    $result['user_name'] = $currentUser['name'];
    $result['created_at'] = date('M d, Y');
}

echo json_encode($result);
exit;
