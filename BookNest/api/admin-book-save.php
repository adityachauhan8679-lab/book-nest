<?php
/**
 * BookNest - Admin Book Create / Update API
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

$bookId = (int)($input['id'] ?? 0);
$title = trim($input['title'] ?? '');
$authorName = trim($input['author'] ?? $input['author_name'] ?? '');
$categoryId = (int)($input['category_id'] ?? 1);
$isbn = trim($input['isbn'] ?? '');
$price = (float)($input['price'] ?? 0);
$discount = (float)($input['discount'] ?? 0);
$stock = (int)($input['stock'] ?? 10);
$publisher = trim($input['publisher'] ?? 'BookNest Publications');
$publicationYear = (int)($input['year'] ?? $input['publication_year'] ?? date('Y'));
$pages = (int)($input['pages'] ?? 300);
$language = trim($input['language'] ?? 'English');
$description = trim($input['description'] ?? '');
$coverImage = trim($input['cover_image'] ?? $input['coverImage'] ?? '');

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Book title is required.']);
    exit;
}
if ($price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Price must be greater than zero.']);
    exit;
}
if (empty($coverImage)) {
    $coverImage = 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80';
}

$pdo = Database::getConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable.']);
    exit;
}

try {
    // 1. Resolve or create author
    $authorId = 1;
    if (!empty($authorName)) {
        $aStmt = $pdo->prepare("SELECT id FROM authors WHERE name = ? LIMIT 1");
        $aStmt->execute([$authorName]);
        $existingAuthor = $aStmt->fetchColumn();
        if ($existingAuthor) {
            $authorId = (int)$existingAuthor;
        } else {
            $insA = $pdo->prepare("INSERT INTO authors (name, bio) VALUES (?, ?)");
            $insA->execute([$authorName, "Author profile on BookNest."]);
            $authorId = (int)$pdo->lastInsertId();
        }
    }

    if ($bookId > 0) {
        // Update existing book
        $stmt = $pdo->prepare("
            UPDATE books 
            SET title = ?, author_id = ?, category_id = ?, isbn = ?, price = ?, 
                discount = ?, stock = ?, publisher = ?, publication_year = ?, 
                pages = ?, language = ?, description = ?, cover_image = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $title, $authorId, $categoryId, $isbn, $price,
            $discount, $stock, $publisher, $publicationYear,
            $pages, $language, $description, $coverImage,
            $bookId
        ]);
        $savedId = $bookId;
        $message = "Book \"{$title}\" updated successfully!";
    } else {
        // Create new book
        $stmt = $pdo->prepare("
            INSERT INTO books 
            (title, author_id, category_id, isbn, price, discount, stock, publisher, publication_year, pages, language, description, cover_image, rating, views, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 5.0, 0, 'active')
        ");
        $stmt->execute([
            $title, $authorId, $categoryId, $isbn, $price, $discount, $stock, $publisher, $publicationYear, $pages, $language, $description, $coverImage
        ]);
        $savedId = (int)$pdo->lastInsertId();
        $message = "New book \"{$title}\" added to catalog successfully!";
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'book_id' => $savedId
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
