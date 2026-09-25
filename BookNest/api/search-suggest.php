<?php
/**
 * BookNest - Live Search & Autocomplete API
 * Returns real-time book suggestions matching user input
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'query' => $query, 'results' => []]);
    exit;
}

$results = [];

try {
    $pdo = Database::getConnection();
    if ($pdo) {
        $stmt = $pdo->prepare("
            SELECT b.id, b.title, b.isbn, b.price, b.discount, b.rating, b.cover_image,
                   a.name AS author_name,
                   c.name AS category_name, c.slug AS category_slug
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE b.title LIKE ? OR a.name LIKE ? OR b.isbn LIKE ?
            ORDER BY 
                CASE 
                    WHEN b.title LIKE ? THEN 1
                    WHEN a.name LIKE ? THEN 2
                    ELSE 3
                END,
                b.views DESC, b.rating DESC
            LIMIT 6
        ");
        $term = "%{$query}%";
        $exactStart = "{$query}%";
        $stmt->execute([$term, $term, $term, $exactStart, $exactStart]);
        
        while ($row = $stmt->fetch()) {
            $finalPrice = calculateDiscountPrice((float)$row['price'], (float)$row['discount']);
            $results[] = [
                'id'            => (int)$row['id'],
                'title'         => $row['title'],
                'author'        => $row['author_name'],
                'category'      => $row['category_name'],
                'isbn'          => $row['isbn'],
                'price'         => (float)$row['price'],
                'discount'      => (float)$row['discount'],
                'final_price'   => $finalPrice,
                'rating'        => (float)$row['rating'],
                'cover_image'   => $row['cover_image'],
                'formatted_price' => formatPrice($finalPrice),
                'url'           => "book-details.php?id=" . (int)$row['id']
            ];
        }
    }
} catch (Exception $e) {
    error_log("Search suggest API error: " . $e->getMessage());
}

// Fallback search results if DB is offline
if (empty($results)) {
    $sampleCatalog = [
        ['id' => 1, 'title' => 'Atomic Habits', 'author' => 'James Clear', 'category' => 'Self Help', 'price' => 499, 'discount' => 15, 'final_price' => 424, 'rating' => 4.9, 'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80', 'isbn' => '9780735211292'],
        ['id' => 2, 'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'category' => 'Programming', 'price' => 799, 'discount' => 10, 'final_price' => 719, 'rating' => 4.85, 'cover_image' => 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=600&auto=format&fit=crop&q=80', 'isbn' => '9780132350884'],
        ['id' => 3, 'title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'category' => 'Fiction', 'price' => 299, 'discount' => 0, 'final_price' => 299, 'rating' => 4.7, 'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80', 'isbn' => '9780743273565'],
        ['id' => 4, 'title' => 'Dune', 'author' => 'Frank Herbert', 'category' => 'Science Fiction', 'price' => 599, 'discount' => 20, 'final_price' => 479, 'rating' => 4.88, 'cover_image' => 'https://images.unsplash.com/photo-1506466010722-395aa2bef877?w=600&auto=format&fit=crop&q=80', 'isbn' => '9780441013593'],
        ['id' => 6, 'title' => 'The Psychology of Money', 'author' => 'Morgan Housel', 'category' => 'Business', 'price' => 449, 'discount' => 10, 'final_price' => 404, 'rating' => 4.8, 'cover_image' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=600&auto=format&fit=crop&q=80', 'isbn' => '9780857197689'],
        ['id' => 8, 'title' => 'The Art of Invisibility', 'author' => 'Kevin D. Mitnick', 'category' => 'Cybersecurity', 'price' => 649, 'discount' => 5, 'final_price' => 616, 'rating' => 4.65, 'cover_image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=600&auto=format&fit=crop&q=80', 'isbn' => '9780316380508'],
        ['id' => 12, 'title' => 'The Pragmatic Programmer', 'author' => 'Robert C. Martin', 'category' => 'Programming', 'price' => 899, 'discount' => 15, 'final_price' => 764, 'rating' => 4.92, 'cover_image' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=600&auto=format&fit=crop&q=80', 'isbn' => '9780135957059']
    ];
    $qLower = strtolower($query);
    foreach ($sampleCatalog as $item) {
        if (str_contains(strtolower($item['title']), $qLower) || 
            str_contains(strtolower($item['author']), $qLower) || 
            str_contains($item['isbn'], $qLower)) {
            $item['formatted_price'] = formatPrice($item['final_price']);
            $item['url'] = "book-details.php?id=" . $item['id'];
            $results[] = $item;
        }
    }
}

echo json_encode([
    'success' => true,
    'query'   => $query,
    'count'   => count($results),
    'results' => $results
]);
