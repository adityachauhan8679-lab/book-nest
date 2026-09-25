<?php
/**
 * BookNest - Books Catalog with Multi-Faceted Filtering
 * Phase 3: Catalog, Search, and Facets
 */

$pageTitle = "Explore Books - BookNest";
$activeNav = "books";

require_once __DIR__ . '/includes/header.php';

// 1. Extract Filter Parameters
$q           = trim($_GET['q'] ?? '');
$category    = trim($_GET['category'] ?? '');
$authorId    = isset($_GET['author']) && is_numeric($_GET['author']) ? (int)$_GET['author'] : null;
$minPrice    = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice    = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$minRating   = isset($_GET['rating']) && is_numeric($_GET['rating']) ? (float)$_GET['rating'] : null;
$inStockOnly = !empty($_GET['in_stock']);
$sort        = trim($_GET['sort'] ?? 'relevance');
$page        = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 12;
$offset      = ($page - 1) * $perPage;

$books = [];
$totalBooks = 0;
$categories = [];
$authors = [];
$priceRange = ['min' => 0, 'max' => 1500];

$pdo = Database::getConnection();

if ($pdo) {
    try {
        // Fetch Categories with count for sidebar
        $catStmt = $pdo->query("
            SELECT c.id, c.name, c.slug, COUNT(b.id) AS book_count 
            FROM categories c 
            LEFT JOIN books b ON b.category_id = c.id 
            WHERE c.status = 'active'
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        $categories = $catStmt->fetchAll();

        // Fetch Authors with count for sidebar
        $authStmt = $pdo->query("
            SELECT a.id, a.name, COUNT(b.id) AS book_count
            FROM authors a
            JOIN books b ON b.author_id = a.id
            GROUP BY a.id
            ORDER BY book_count DESC, a.name ASC
            LIMIT 15
        ");
        $authors = $authStmt->fetchAll();

        // Build dynamic WHERE clause
        $where = ["1=1"];
        $params = [];

        if (!empty($q)) {
            $where[] = "(b.title LIKE ? OR a.name LIKE ? OR b.isbn LIKE ?)";
            $searchTerm = "%{$q}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($category)) {
            $where[] = "c.slug = ?";
            $params[] = $category;
        }

        if ($authorId) {
            $where[] = "b.author_id = ?";
            $params[] = $authorId;
        }

        if ($minPrice !== null && $minPrice > 0) {
            $where[] = "(b.price * (1 - (b.discount / 100))) >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice !== null && $maxPrice > 0) {
            $where[] = "(b.price * (1 - (b.discount / 100))) <= ?";
            $params[] = $maxPrice;
        }

        if ($minRating !== null && $minRating > 0) {
            $where[] = "b.rating >= ?";
            $params[] = $minRating;
        }

        if ($inStockOnly) {
            $where[] = "b.stock > 0";
        }

        $whereClause = implode(" AND ", $where);

        // Count total matching books for pagination
        $countSql = "
            SELECT COUNT(*) 
            FROM books b 
            JOIN authors a ON b.author_id = a.id 
            JOIN categories c ON b.category_id = c.id 
            WHERE {$whereClause}
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalBooks = (int)$countStmt->fetchColumn();

        // Sorting
        $orderBy = "b.views DESC, b.rating DESC";
        if ($sort === 'newest') {
            $orderBy = "b.created_at DESC";
        } elseif ($sort === 'price-low') {
            $orderBy = "(b.price * (1 - (b.discount / 100))) ASC";
        } elseif ($sort === 'price-high') {
            $orderBy = "(b.price * (1 - (b.discount / 100))) DESC";
        } elseif ($sort === 'rating') {
            $orderBy = "b.rating DESC";
        } elseif ($sort === 'discount') {
            $orderBy = "b.discount DESC";
        }

        // Fetch Books page
        $sql = "
            SELECT b.*, a.name AS author_name, c.name AS category_name, c.slug AS category_slug
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll();

    } catch (Exception $e) {
        error_log("Catalog query error: " . $e->getMessage());
    }
}

// Fallback seed catalog if database is not yet imported
if (empty($books) && empty($q) && empty($category) && !$authorId && $minPrice === null && $maxPrice === null) {
    $books = [
        ['id' => 1, 'title' => 'Atomic Habits', 'author_name' => 'James Clear', 'category_name' => 'Self Help', 'category_slug' => 'self-help', 'price' => 499.00, 'discount' => 15.00, 'rating' => 4.9, 'stock' => 45, 'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80'],
        ['id' => 2, 'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship', 'author_name' => 'Robert C. Martin', 'category_name' => 'Programming', 'category_slug' => 'programming', 'price' => 799.00, 'discount' => 10.00, 'rating' => 4.85, 'stock' => 28, 'cover_image' => 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=600&auto=format&fit=crop&q=80'],
        ['id' => 3, 'title' => 'The Great Gatsby', 'author_name' => 'F. Scott Fitzgerald', 'category_name' => 'Fiction', 'category_slug' => 'fiction', 'price' => 299.00, 'discount' => 0.00, 'rating' => 4.70, 'stock' => 60, 'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=600&auto=format&fit=crop&q=80'],
        ['id' => 4, 'title' => 'Dune', 'author_name' => 'Frank Herbert', 'category_name' => 'Science Fiction', 'category_slug' => 'science-fiction', 'price' => 599.00, 'discount' => 20.00, 'rating' => 4.88, 'stock' => 35, 'cover_image' => 'https://images.unsplash.com/photo-1506466010722-395aa2bef877?w=600&auto=format&fit=crop&q=80'],
        ['id' => 5, 'title' => '1984', 'author_name' => 'George Orwell', 'category_name' => 'Fiction', 'category_slug' => 'fiction', 'price' => 349.00, 'discount' => 12.00, 'rating' => 4.82, 'stock' => 50, 'cover_image' => 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?w=600&auto=format&fit=crop&q=80'],
        ['id' => 6, 'title' => 'The Psychology of Money', 'author_name' => 'Morgan Housel', 'category_name' => 'Business', 'category_slug' => 'business', 'price' => 449.00, 'discount' => 10.00, 'rating' => 4.80, 'stock' => 75, 'cover_image' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=600&auto=format&fit=crop&q=80'],
        ['id' => 8, 'title' => 'The Art of Invisibility', 'author_name' => 'Kevin D. Mitnick', 'category_name' => 'Cybersecurity', 'category_slug' => 'cybersecurity', 'price' => 649.00, 'discount' => 5.00, 'rating' => 4.65, 'stock' => 22, 'cover_image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=600&auto=format&fit=crop&q=80'],
        ['id' => 12, 'title' => 'The Pragmatic Programmer', 'author_name' => 'Robert C. Martin', 'category_name' => 'Programming', 'category_slug' => 'programming', 'price' => 899.00, 'discount' => 15.00, 'rating' => 4.92, 'stock' => 18, 'cover_image' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=600&auto=format&fit=crop&q=80']
    ];
    $totalBooks = count($books);
}

$totalPages = ceil($totalBooks / $perPage);

// Helper function to build clean filter query URLs
function buildCatalogUrl(array $overrides = []): string {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    return 'books.php?' . http_build_query($params);
}
?>

<div class="container" style="padding: 40px 20px 80px 20px;">
  
  <!-- Breadcrumb -->
  <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 20px;">
    <a href="index.php" style="color:inherit; text-decoration:none;">Home</a> › 
    <span style="color: var(--color-text-main); font-weight: 600;">Books Catalog</span>
    <?php if (!empty($category)): ?>
      › <span style="color: var(--color-primary); font-weight: 600;"><?= e(ucwords(str_replace('-', ' ', $category))) ?></span>
    <?php endif; ?>
  </div>

  <!-- Active Filter Chips -->
  <?php 
  $hasFilters = !empty($q) || !empty($category) || $authorId || $minPrice || $maxPrice || $minRating || $inStockOnly;
  if ($hasFilters): 
  ?>
    <div class="active-filters-bar">
      <span style="font-size: 0.85rem; font-weight: 700; color: var(--color-text-main);">Active Filters:</span>

      <?php if (!empty($q)): ?>
        <a href="<?= buildCatalogUrl(['q' => null]) ?>" class="filter-chip">
          Search: "<?= e($q) ?>" <span class="chip-remove">✕</span>
        </a>
      <?php endif; ?>

      <?php if (!empty($category)): ?>
        <a href="<?= buildCatalogUrl(['category' => null]) ?>" class="filter-chip">
          Genre: <?= e(ucwords(str_replace('-', ' ', $category))) ?> <span class="chip-remove">✕</span>
        </a>
      <?php endif; ?>

      <?php if ($authorId): ?>
        <a href="<?= buildCatalogUrl(['author' => null]) ?>" class="filter-chip">
          Author Filter <span class="chip-remove">✕</span>
        </a>
      <?php endif; ?>

      <?php if ($minPrice || $maxPrice): ?>
        <a href="<?= buildCatalogUrl(['min_price' => null, 'max_price' => null]) ?>" class="filter-chip">
          Price: ₹<?= (int)$minPrice ?> - ₹<?= (int)($maxPrice ?? 1500) ?> <span class="chip-remove">✕</span>
        </a>
      <?php endif; ?>

      <?php if ($minRating): ?>
        <a href="<?= buildCatalogUrl(['rating' => null]) ?>" class="filter-chip">
          Rating: <?= $minRating ?>★+ <span class="chip-remove">✕</span>
        </a>
      <?php endif; ?>

      <?php if ($inStockOnly): ?>
        <a href="<?= buildCatalogUrl(['in_stock' => null]) ?>" class="filter-chip">
          In Stock Only <span class="chip-remove">✕</span>
        </a>
      <?php endif; ?>

      <a href="books.php" style="font-size: 0.8rem; color: var(--color-danger); font-weight: 600; text-decoration: none; margin-left: 8px;">
        Clear All Filters
      </a>
    </div>
  <?php endif; ?>

  <!-- Main Catalog Grid Layout -->
  <div class="catalog-layout">
    
    <!-- 1. Left Sidebar: Faceted Filters -->
    <aside class="filters-sidebar">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <h3 style="font-size: 1.1rem; font-weight: 700;">Filters</h3>
        <?php if ($hasFilters): ?>
          <a href="books.php" style="font-size: 0.78rem; color: var(--color-text-muted); text-decoration: none;">Reset</a>
        <?php endif; ?>
      </div>

      <form action="books.php" method="GET" id="catalogFilterForm">
        <?php if (!empty($q)): ?>
          <input type="hidden" name="q" value="<?= e($q) ?>">
        <?php endif; ?>
        <?php if (!empty($sort)): ?>
          <input type="hidden" name="sort" value="<?= e($sort) ?>">
        <?php endif; ?>

        <!-- Categories Filter -->
        <div class="filter-group">
          <div class="filter-heading">
            <span>Categories</span>
            <span style="font-size:0.75rem; color:var(--color-text-light);"><?= count($categories) ?></span>
          </div>
          <ul class="filter-options-list">
            <li>
              <label class="filter-label">
                <span>
                  <input type="radio" name="category" value="" <?= empty($category) ? 'checked' : '' ?> onchange="this.form.submit()">
                  All Genres
                </span>
                <span class="filter-count"><?= $totalBooks ?></span>
              </label>
            </li>
            <?php foreach ($categories as $cat): ?>
              <li>
                <label class="filter-label">
                  <span>
                    <input 
                      type="radio" 
                      name="category" 
                      value="<?= e($cat['slug']) ?>" 
                      <?= $category === $cat['slug'] ? 'checked' : '' ?>
                      onchange="this.form.submit()"
                    >
                    <?= e($cat['name']) ?>
                  </span>
                  <span class="filter-count"><?= (int)$cat['book_count'] ?></span>
                </label>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-group">
          <div class="filter-heading">Price Range (₹)</div>
          <div class="price-inputs">
            <input 
              type="number" 
              name="min_price" 
              placeholder="Min" 
              class="price-field"
              value="<?= e($minPrice ?? '') ?>"
              min="0"
              step="50"
            >
            <span style="color:var(--color-text-light);">-</span>
            <input 
              type="number" 
              name="max_price" 
              placeholder="Max" 
              class="price-field"
              value="<?= e($maxPrice ?? '') ?>"
              min="0"
              step="50"
            >
          </div>
          <button type="submit" class="btn btn-secondary btn-sm" style="width:100%; margin-top:10px; font-size:0.8rem;">
            Apply Price
          </button>
        </div>

        <!-- Rating Filter -->
        <div class="filter-group">
          <div class="filter-heading">Minimum Rating</div>
          <ul class="filter-options-list">
            <?php foreach ([4.5 => '4.5★ & above', 4.0 => '4.0★ & above', 3.5 => '3.5★ & above'] as $val => $label): ?>
              <li>
                <label class="filter-label">
                  <span>
                    <input 
                      type="radio" 
                      name="rating" 
                      value="<?= $val ?>" 
                      <?= $minRating == $val ? 'checked' : '' ?>
                      onchange="this.form.submit()"
                    >
                    <span style="color:#F59E0B;">★</span> <?= $label ?>
                  </span>
                </label>
              </li>
            <?php endforeach; ?>
            <?php if ($minRating): ?>
              <li>
                <a href="<?= buildCatalogUrl(['rating' => null]) ?>" style="font-size:0.75rem; color:var(--color-primary); text-decoration:none;">Clear rating filter</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- In-Stock Only -->
        <div class="filter-group">
          <label class="filter-label" style="font-weight:600; color:var(--color-text-main);">
            <span>
              <input 
                type="checkbox" 
                name="in_stock" 
                value="1" 
                <?= $inStockOnly ? 'checked' : '' ?>
                onchange="this.form.submit()"
              >
              In Stock Only
            </span>
            <span style="font-size:0.75rem; color:var(--color-success);">Ready to ship</span>
          </label>
        </div>

        <!-- Authors Filter (if available) -->
        <?php if (!empty($authors)): ?>
          <div class="filter-group">
            <div class="filter-heading">Featured Authors</div>
            <ul class="filter-options-list">
              <?php foreach ($authors as $auth): ?>
                <li>
                  <label class="filter-label">
                    <span>
                      <input 
                        type="radio" 
                        name="author" 
                        value="<?= (int)$auth['id'] ?>" 
                        <?= $authorId == $auth['id'] ? 'checked' : '' ?>
                        onchange="this.form.submit()"
                      >
                      <?= e($auth['name']) ?>
                    </span>
                    <span class="filter-count"><?= (int)$auth['book_count'] ?></span>
                  </label>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

      </form>
    </aside>

    <!-- 2. Right Main Area: Controls Bar & Books Grid -->
    <main>
      <!-- Controls Bar -->
      <div class="catalog-controls">
        <div style="font-size: 0.9rem; color: var(--color-text-muted);">
          Showing <strong style="color:var(--color-text-main);"><?= count($books) ?></strong> of <strong style="color:var(--color-text-main);"><?= $totalBooks ?></strong> books
          <?= !empty($q) ? ' for "' . e($q) . '"' : '' ?>
        </div>

        <!-- Sort Select -->
        <div style="display:flex; align-items:center; gap:8px;">
          <label for="sortSelect" style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-muted);">Sort by:</label>
          <select 
            id="sortSelect" 
            class="catalog-sort-select"
            onchange="window.location.href = '<?= buildCatalogUrl(['sort' => 'PLACEHOLDER']) ?>'.replace('PLACEHOLDER', this.value)"
          >
            <option value="relevance" <?= $sort === 'relevance' ? 'selected' : '' ?>>Relevance & Popularity</option>
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
            <option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Customer Rating</option>
            <option value="discount" <?= $sort === 'discount' ? 'selected' : '' ?>>Highest Discount</option>
          </select>
        </div>
      </div>

      <!-- Books Grid -->
      <?php if (empty($books)): ?>
        <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:60px 20px; text-align:center;">
          <div style="font-size:3rem; margin-bottom:12px;">🔍</div>
          <h3 style="font-size:1.3rem; font-weight:700; margin-bottom:6px;">No books match your criteria</h3>
          <p style="color:var(--color-text-muted); font-size:0.92rem; max-width:440px; margin:0 auto 20px auto;">
            Try loosening your price range or clearing selected categories to see more titles.
          </p>
          <a href="books.php" class="btn btn-primary">Reset All Filters</a>
        </div>
      <?php else: ?>
        <div class="books-grid">
          <?php foreach ($books as $book): ?>
            <?php include __DIR__ . '/components/book-card.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <?php 
          $baseUrl = buildCatalogUrl(['page' => '']) . '';
          $currentPage = $page;
          include __DIR__ . '/components/pagination.php'; 
          ?>
        <?php endif; ?>
      <?php endif; ?>
    </main>

  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
