<?php
/**
 * BookNest - Homepage
 * College Project: BookNest Full-Stack Bookstore
 */

$pageTitle = "BookNest - Find your next great read";
$activeNav = "home";

require_once __DIR__ . '/includes/header.php';

// Fetch Categories and Books from Database (with graceful fallback if MySQL is not yet seeded)
$categories = [];
$featuredBooks = [];
$trendingBooks = [];
$newArrivals = [];
$dbStatus = false;

try {
    $pdo = Database::getConnection();
    if ($pdo) {
        $dbStatus = true;

        // Fetch categories with book counts
        $catStmt = $pdo->query("
            SELECT c.*, COUNT(b.id) AS book_count 
            FROM categories c 
            LEFT JOIN books b ON b.category_id = c.id 
            WHERE c.status = 'active'
            GROUP BY c.id 
            ORDER BY c.id ASC 
            LIMIT 10
        ");
        $categories = $catStmt->fetchAll();

        // Fetch Featured Books (Top rated)
        $featStmt = $pdo->query("
            SELECT b.*, a.name AS author_name, c.name AS category_name
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE b.stock > 0
            ORDER BY b.rating DESC, b.views DESC
            LIMIT 8
        ");
        $featuredBooks = $featStmt->fetchAll();

        // Fetch Trending Books (High views)
        $trendStmt = $pdo->query("
            SELECT b.*, a.name AS author_name, c.name AS category_name
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            ORDER BY b.views DESC
            LIMIT 4
        ");
        $trendingBooks = $trendStmt->fetchAll();

        // Fetch New Arrivals
        $newStmt = $pdo->query("
            SELECT b.*, a.name AS author_name, c.name AS category_name
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            ORDER BY b.created_at DESC
            LIMIT 4
        ");
        $newArrivals = $newStmt->fetchAll();
    }
} catch (Exception $e) {
    error_log("Homepage query error: " . $e->getMessage());
}

// Fallback seed data if DB is not yet imported in XAMPP
if (empty($categories)) {
    $categories = [
        ['id' => 1, 'name' => 'Fiction', 'slug' => 'fiction', 'book_count' => 12],
        ['id' => 2, 'name' => 'Mystery & Thriller', 'slug' => 'mystery-thriller', 'book_count' => 8],
        ['id' => 4, 'name' => 'Fantasy', 'slug' => 'fantasy', 'book_count' => 15],
        ['id' => 5, 'name' => 'Science Fiction', 'slug' => 'science-fiction', 'book_count' => 9],
        ['id' => 6, 'name' => 'Self Help', 'slug' => 'self-help', 'book_count' => 14],
        ['id' => 7, 'name' => 'Business', 'slug' => 'business', 'book_count' => 11],
        ['id' => 9, 'name' => 'Programming', 'slug' => 'programming', 'book_count' => 16],
        ['id' => 10, 'name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'book_count' => 7],
    ];
}

if (empty($featuredBooks)) {
    $featuredBooks = [
        [
            'id' => 1,
            'title' => 'Atomic Habits',
            'author_name' => 'James Clear',
            'price' => 499.00,
            'discount' => 15.00,
            'rating' => 4.9,
            'stock' => 45,
            'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 2,
            'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'author_name' => 'Robert C. Martin',
            'price' => 799.00,
            'discount' => 10.00,
            'rating' => 4.85,
            'stock' => 28,
            'cover_image' => 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=600&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 4,
            'title' => 'Dune',
            'author_name' => 'Frank Herbert',
            'price' => 599.00,
            'discount' => 20.00,
            'rating' => 4.88,
            'stock' => 35,
            'cover_image' => 'https://images.unsplash.com/photo-1506466010722-395aa2bef877?w=600&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 6,
            'title' => 'The Psychology of Money',
            'author_name' => 'Morgan Housel',
            'price' => 449.00,
            'discount' => 10.00,
            'rating' => 4.80,
            'stock' => 75,
            'cover_image' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=600&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 8,
            'title' => 'The Art of Invisibility',
            'author_name' => 'Kevin D. Mitnick',
            'price' => 649.00,
            'discount' => 5.00,
            'rating' => 4.65,
            'stock' => 22,
            'cover_image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=600&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 12,
            'title' => 'The Pragmatic Programmer',
            'author_name' => 'Robert C. Martin',
            'price' => 899.00,
            'discount' => 15.00,
            'rating' => 4.92,
            'stock' => 18,
            'cover_image' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=600&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 13,
            'title' => 'Harry Potter and the Sorcerer\'s Stone',
            'author_name' => 'J.K. Rowling',
            'price' => 450.00,
            'discount' => 10.00,
            'rating' => 4.95,
            'stock' => 80,
            'cover_image' => 'https://images.unsplash.com/photo-1618666012174-83b441c0bc76?w=600&auto=format&fit=crop&q=80'
        ],
        [
            'id' => 20,
            'title' => 'Designing Data-Intensive Applications',
            'author_name' => 'Martin Kleppmann',
            'price' => 1199.00,
            'discount' => 10.00,
            'rating' => 4.94,
            'stock' => 15,
            'cover_image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&auto=format&fit=crop&q=80'
        ]
    ];
}

if (empty($trendingBooks)) {
    $trendingBooks = array_slice($featuredBooks, 0, 4);
}
if (empty($newArrivals)) {
    $newArrivals = array_slice($featuredBooks, 4, 4);
}
?>

<!-- 1. Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="hero-grid">
      <div class="hero-content">
        <div class="hero-tagline">
          <span>✨</span>
          <span>Online Bookstore & Physical Book Scanner</span>
        </div>
        <h1 class="hero-title">
          Your next great read is <span>waiting.</span>
        </h1>
        <p class="hero-subtitle">
          Discover books you'll love, explore new worlds, and find your next favorite story. Have a physical book with you? Use our smart scanner to look it up in seconds.
        </p>

        <div class="hero-cta-group">
          <a href="books.php" class="btn btn-primary">
            <span>📚</span> Explore Books
          </a>
          <a href="scanner.php" class="btn btn-secondary" style="border-color: var(--color-accent); color: var(--color-accent);">
            <span>📷</span> Scan a Book
          </a>
        </div>

        <div class="hero-features-list">
          <div class="hero-feature-item">
            <span class="icon">✓</span> Free campus shipping
          </div>
          <div class="hero-feature-item">
            <span class="icon">✓</span> 30+ curated categories
          </div>
          <div class="hero-feature-item">
            <span class="icon">✓</span> Instant ISBN & camera search
          </div>
        </div>
      </div>

      <!-- Hero Visual -->
      <div class="hero-visual-card">
        <img 
          src="https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800&auto=format&fit=crop&q=80" 
          alt="BookNest Bookstore Shelf" 
          style="width: 100%; border-radius: var(--radius-lg); height: 380px; object-fit: cover;"
        >
        <div class="hero-scanner-preview">
          <div>
            <div style="font-weight: 700; font-size: 0.95rem; color: var(--color-text-main);">Camera Book Scanner</div>
            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Point camera at any cover or barcode</div>
          </div>
          <span class="pill">Try Feature ›</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 2. Value Propositions Bar -->
<section style="background: #fff; border-bottom: 1px solid var(--color-card-border); padding: 24px 0;">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; text-align: left;">
      <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size: 2rem;">🚚</span>
        <div>
          <div style="font-weight:700; font-size:0.92rem;">Speedy Delivery</div>
          <div style="font-size:0.8rem; color:var(--color-text-muted);">Standard 2-4 day doorstep delivery</div>
        </div>
      </div>
      <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size: 2rem;">🛡️</span>
        <div>
          <div style="font-weight:700; font-size:0.92rem;">Genuine Copies</div>
          <div style="font-size:0.8rem; color:var(--color-text-muted);">100% authentic publisher editions</div>
        </div>
      </div>
      <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size: 2rem;">📷</span>
        <div>
          <div style="font-weight:700; font-size:0.92rem;">BookNest Scanner</div>
          <div style="font-size:0.8rem; color:var(--color-text-muted);">Barcode & cover recognition</div>
        </div>
      </div>
      <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size: 2rem;">⭐</span>
        <div>
          <div style="font-weight:700; font-size:0.92rem;">Student Verified</div>
          <div style="font-size:0.8rem; color:var(--color-text-muted);">Real reviews from avid readers</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 3. Explore Categories Section -->
<section class="categories-section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Explore by Category</h2>
        <p class="section-desc">Hand-picked genres covering academic depth to cozy weekend fiction.</p>
      </div>
      <a href="categories.php" class="view-all-link">View all 15 genres →</a>
    </div>

    <div class="categories-grid">
      <?php foreach ($categories as $category): ?>
        <?php include __DIR__ . '/components/category-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 4. Featured Books Section -->
<section style="padding: 20px 0 60px 0;">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Featured Books</h2>
        <p class="section-desc">Top-rated titles recommended by the BookNest editorial team.</p>
      </div>
      <a href="books.php?filter=featured" class="view-all-link">Browse all books →</a>
    </div>

    <div class="books-grid">
      <?php foreach ($featuredBooks as $book): ?>
        <?php include __DIR__ . '/components/book-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 5. BookNest Scanner Hero Banner -->
<section class="container">
  <div class="scanner-banner">
    <div class="scanner-banner-grid">
      <div>
        <div class="scanner-badge">📷 Major Feature</div>
        <h2 class="scanner-title">Have a physical book in your hands? Let BookNest find it.</h2>
        <p class="scanner-desc">
          Scan the barcode, ISBN number, or point your phone camera at the front cover. We'll search our catalog instantly and find pricing, availability, and reader reviews.
        </p>
        <a href="scanner.php" class="btn btn-accent">
          <span>📷</span> Open Book Scanner
        </a>
      </div>
      <div style="text-align: center;">
        <div style="display:inline-block; background: rgba(255,255,255,0.06); padding: 24px; border-radius: var(--radius-xl); border: 1px dashed rgba(255,255,255,0.25);">
          <div style="font-size: 3.5rem; margin-bottom: 8px;">📖 ⚡ 📱</div>
          <div style="font-weight:600; font-size: 0.95rem; color: #F1F5F9;">Instant ISBN & Title Recognition</div>
          <div style="font-size: 0.8rem; color: #94A3B8;">Webcam or photo upload support</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 6. Trending This Week -->
<section style="padding: 20px 0 60px 0;">
  <div class="container">
    <div class="section-header">
      <div>
        <h2 class="section-title">Trending This Week</h2>
        <p class="section-desc">The most viewed and saved books across all disciplines.</p>
      </div>
      <a href="books.php?sort=trending" class="view-all-link">See trending list →</a>
    </div>

    <div class="books-grid">
      <?php foreach ($trendingBooks as $book): ?>
        <?php include __DIR__ . '/components/book-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 7. New Arrivals -->
<section style="padding: 20px 0 70px 0; background-color: #F6F4EE; border-top: 1px solid var(--color-card-border);">
  <div class="container" style="padding-top: 50px;">
    <div class="section-header">
      <div>
        <h2 class="section-title">New Arrivals</h2>
        <p class="section-desc">Freshly stocked titles hot off the press.</p>
      </div>
      <a href="books.php?sort=newest" class="view-all-link">View all new releases →</a>
    </div>

    <div class="books-grid">
      <?php foreach ($newArrivals as $book): ?>
        <?php include __DIR__ . '/components/book-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
