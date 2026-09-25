<?php
/**
 * BookNest - Wishlist Toggle API Endpoint
 * Toggles a book in/out of the user's wishlist
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

$bookId = isset($jsonData['book_id']) ? (int)$jsonData['book_id'] : (int)($_POST['book_id'] ?? 0);

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid Book ID provided.']);
    exit;
}

try {
    $pdo = Database::getConnection();

    // Verify book exists
    $bookTitle = "Book";
    if ($pdo) {
        $bStmt = $pdo->prepare("SELECT title FROM books WHERE id = ? LIMIT 1");
        $bStmt->execute([$bookId]);
        $book = $bStmt->fetch();
        if ($book) {
            $bookTitle = $book['title'];
        }
    }

    if (!isset($_SESSION['wishlist']) || !is_array($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }

    $inWishlist = in_array($bookId, $_SESSION['wishlist']);

    if ($inWishlist) {
        // Remove from wishlist
        $_SESSION['wishlist'] = array_values(array_diff($_SESSION['wishlist'], [$bookId]));
        $added = false;
        $message = "Removed \"{$bookTitle}\" from your wishlist.";

        if (isLoggedIn() && $pdo) {
            $user = getCurrentUser();
            $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
            $del->execute([$user['id'], $bookId]);
        }
    } else {
        // Add to wishlist
        $_SESSION['wishlist'][] = $bookId;
        $added = true;
        $message = "Added \"{$bookTitle}\" to your wishlist!";

        if (isLoggedIn() && $pdo) {
            $user = getCurrentUser();
            $add = $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, book_id, created_at) VALUES (?, ?, NOW())");
            $add->execute([$user['id'], $bookId]);
        }
    }

    echo json_encode([
        'success' => true,
        'bookId' => $bookId,
        'inWishlist' => $added,
        'message' => $message,
        'wishlistCount' => getWishlistCount()
    ]);

} catch (Exception $e) {
    error_log("Wishlist toggle error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update wishlist.']);
}
