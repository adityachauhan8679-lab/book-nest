<?php
/**
 * BookNest - Optical & Barcode Scanner Lookup API
 * Handles ISBN validation, barcode resolution, and multi-field fuzzy cover match
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Accept JSON or form-encoded POST/GET
$method = $_SERVER['REQUEST_METHOD'];
$input = [];
if ($method === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $input = $_POST;
    }
} else {
    $input = $_GET;
}

$rawQuery = trim($input['query'] ?? $input['isbn'] ?? $input['code'] ?? $input['text'] ?? '');
if (empty($rawQuery)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No ISBN barcode or title text provided for scanner lookup.'
    ]);
    exit;
}

// Clean candidate ISBN by stripping hyphens, spaces, and prefix words
$cleanIsbn = preg_replace('/[^0-9X]/i', '', $rawQuery);
$pdo = Database::getConnection();

// Fallback seed books array in case DB isn't seeded yet
$fallbackCatalog = [
    [
        'id' => 1,
        'title' => 'Atomic Habits',
        'author_name' => 'James Clear',
        'category_name' => 'Self Help',
        'isbn' => '9780735211292',
        'price' => 499.00,
        'discount' => 15.00,
        'stock' => 45,
        'rating' => 4.90,
        'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80',
        'description' => 'No matter your goals, Atomic Habits offers a proven framework for improving every day.'
    ],
    [
        'id' => 2,
        'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
        'author_name' => 'Robert C. Martin',
        'category_name' => 'Programming',
        'isbn' => '9780132350884',
        'price' => 799.00,
        'discount' => 10.00,
        'stock' => 28,
        'rating' => 4.85,
        'cover_image' => 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=600&auto=format&fit=crop&q=80',
        'description' => 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees.'
    ],
    [
        'id' => 3,
        'title' => 'The Great Gatsby',
        'author_name' => 'F. Scott Fitzgerald',
        'category_name' => 'Fiction',
        'isbn' => '9780743273565',
        'price' => 299.00,
        'discount' => 0.00,
        'stock' => 60,
        'rating' => 4.70,
        'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80',
        'description' => 'The quintessential Jazz Age novel capturing Jay Gatsby\'s tragic pursuit of Daisy Buchanan.'
    ],
    [
        'id' => 4,
        'title' => 'Dune',
        'author_name' => 'Frank Herbert',
        'category_name' => 'Science Fiction',
        'isbn' => '9780441013593',
        'price' => 599.00,
        'discount' => 20.00,
        'stock' => 35,
        'rating' => 4.88,
        'cover_image' => 'https://images.unsplash.com/photo-1506466010722-395aa2bef877?w=600&auto=format&fit=crop&q=80',
        'description' => 'Set on the desert planet Arrakis, Dune is the story of the boy Paul Atreides.'
    ],
    [
        'id' => 6,
        'title' => 'The Psychology of Money',
        'author_name' => 'Morgan Housel',
        'category_name' => 'Business',
        'isbn' => '9780857197689',
        'price' => 449.00,
        'discount' => 10.00,
        'stock' => 75,
        'rating' => 4.80,
        'cover_image' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=600&auto=format&fit=crop&q=80',
        'description' => 'Timeless lessons on wealth, greed, and happiness.'
    ],
    [
        'id' => 15,
        'title' => 'Steve Jobs',
        'author_name' => 'Walter Isaacson',
        'category_name' => 'Biography',
        'isbn' => '9781451648539',
        'price' => 699.00,
        'discount' => 20.00,
        'stock' => 25,
        'rating' => 4.79,
        'cover_image' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600&auto=format&fit=crop&q=80',
        'description' => 'Based on more than forty interviews with Steve Jobs conducted over two years.'
    ]
];

$matchedBook = null;
$matchType = '';
$confidence = 0;

if ($pdo) {
    // 1. Exact ISBN Match (Barcode scan direct hit)
    if (strlen($cleanIsbn) >= 9) {
        $stmt = $pdo->prepare("
            SELECT b.*, a.name AS author_name, c.name AS category_name, c.slug AS category_slug
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE REPLACE(REPLACE(b.isbn, '-', ''), ' ', '') = ?
            LIMIT 1
        ");
        $stmt->execute([$cleanIsbn]);
        $row = $stmt->fetch();
        if ($row) {
            $matchedBook = $row;
            $matchType = 'exact_isbn';
            $confidence = 100;
        }
    }

    // 2. Fuzzy Title / Author Match if no exact ISBN found
    if (!$matchedBook) {
        $tokens = preg_split('/\s+/', $rawQuery, -1, PREG_SPLIT_NO_EMPTY);
        $searchTerms = array_slice($tokens, 0, 4); // First few words
        
        $sql = "
            SELECT b.*, a.name AS author_name, c.name AS category_name, c.slug AS category_slug,
                   (CASE 
                      WHEN b.title LIKE ? THEN 50
                      WHEN a.name LIKE ? THEN 40
                      ELSE 20
                    END) AS relevance
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE b.title LIKE ? OR a.name LIKE ? OR b.description LIKE ?
            ORDER BY relevance DESC, b.rating DESC
            LIMIT 1
        ";
        $wildcard = "%{$rawQuery}%";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$wildcard, $wildcard, $wildcard, $wildcard, $wildcard]);
        $row = $stmt->fetch();
        if ($row) {
            $matchedBook = $row;
            $matchType = 'fuzzy_title';
            $confidence = 92;
        }
    }
}

// 3. Fallback search in static catalog if no DB
if (!$matchedBook) {
    foreach ($fallbackCatalog as $b) {
        if (!empty($cleanIsbn) && strpos($b['isbn'], $cleanIsbn) !== false) {
            $matchedBook = $b;
            $matchType = 'exact_isbn';
            $confidence = 100;
            break;
        }
        if (stripos($b['title'], $rawQuery) !== false || stripos($b['author_name'], $rawQuery) !== false) {
            $matchedBook = $b;
            $matchType = 'fuzzy_title';
            $confidence = 88;
            break;
        }
    }
}

if (!$matchedBook) {
    echo json_encode([
        'success' => false,
        'message' => 'No title in the BookNest catalog matched "' . htmlspecialchars($rawQuery) . '". Try scanning the rear ISBN barcode in good lighting.',
        'scanned_term' => $rawQuery
    ]);
    exit;
}

// Format prices & output
$origPrice = (float)$matchedBook['price'];
$discount = (float)($matchedBook['discount'] ?? 0);
$finalPrice = calculateDiscountPrice($origPrice, $discount);

$bookData = [
    'id' => (int)$matchedBook['id'],
    'title' => $matchedBook['title'],
    'author_name' => $matchedBook['author_name'] ?? 'Author',
    'category_name' => $matchedBook['category_name'] ?? 'General',
    'isbn' => $matchedBook['isbn'],
    'price' => $origPrice,
    'discount' => $discount,
    'final_price' => $finalPrice,
    'formatted_price' => '₹' . number_format($finalPrice, 2),
    'stock' => (int)$matchedBook['stock'],
    'in_stock' => ((int)$matchedBook['stock']) > 0,
    'rating' => (float)$matchedBook['rating'],
    'cover_image' => $matchedBook['cover_image'],
    'description' => $matchedBook['description'] ?? '',
    'url' => 'book-details.php?id=' . (int)$matchedBook['id']
];

echo json_encode([
    'success' => true,
    'match_type' => $matchType,
    'confidence' => $confidence,
    'book' => $bookData
]);
exit;
