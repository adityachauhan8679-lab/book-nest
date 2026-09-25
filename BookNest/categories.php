<?php
/**
 * BookNest - Categories Overview
 */
$pageTitle = "Explore Categories - BookNest";
$activeNav = "categories";

require_once __DIR__ . '/includes/header.php';

$pdo = Database::getConnection();
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT c.*, COUNT(b.id) AS book_count FROM categories c LEFT JOIN books b ON b.category_id = c.id WHERE c.status = 'active' GROUP BY c.id ORDER BY c.name ASC");
        $categories = $stmt->fetchAll();
    } catch(Exception $e) {}
}

if (empty($categories)) {
    $categories = [
        ['name' => 'Fiction', 'slug' => 'fiction', 'book_count' => 12],
        ['name' => 'Mystery & Thriller', 'slug' => 'mystery-thriller', 'book_count' => 8],
        ['name' => 'Romance', 'slug' => 'romance', 'book_count' => 6],
        ['name' => 'Fantasy', 'slug' => 'fantasy', 'book_count' => 15],
        ['name' => 'Science Fiction', 'slug' => 'science-fiction', 'book_count' => 9],
        ['name' => 'Self Help', 'slug' => 'self-help', 'book_count' => 14],
        ['name' => 'Business', 'slug' => 'business', 'book_count' => 11],
        ['name' => 'Technology', 'slug' => 'technology', 'book_count' => 8],
        ['name' => 'Programming', 'slug' => 'programming', 'book_count' => 16],
        ['name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'book_count' => 7],
        ['name' => 'History', 'slug' => 'history', 'book_count' => 5],
        ['name' => 'Biography', 'slug' => 'biography', 'book_count' => 6],
        ['name' => 'Academic', 'slug' => 'academic', 'book_count' => 10],
        ['name' => 'Children', 'slug' => 'children', 'book_count' => 4],
        ['name' => 'Comics', 'slug' => 'comics', 'book_count' => 6]
    ];
}
?>
<div class="container" style="padding: 50px 20px 80px 20px;">
  <div class="section-header">
    <div>
      <h1 class="section-title">All Book Genres</h1>
      <p class="section-desc">Find books tailored to your passion, coursework, or curiosity.</p>
    </div>
  </div>

  <div class="categories-grid">
    <?php foreach ($categories as $category): ?>
      <?php include __DIR__ . '/components/category-card.php'; ?>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
