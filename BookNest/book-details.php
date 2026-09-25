<?php
/**
 * BookNest - Single Book Details Page
 * Phase 3: Book Details, Specifications & Related Recommendations
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$bookId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = Database::getConnection();

$book = null;
$relatedBooks = [];
$reviews = [];

if ($pdo && $bookId > 0) {
    try {
        // Increment view count for trending algorithm
        $pdo->prepare("UPDATE books SET views = views + 1 WHERE id = ?")->execute([$bookId]);

        // Record book view in analytics table if table exists
        $userId = $_SESSION['user']['id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $pdo->prepare("INSERT INTO book_views (book_id, user_id, ip_address, viewed_at) VALUES (?, ?, ?, NOW())")->execute([$bookId, $userId, $ip]);

        // Fetch Book Details
        $stmt = $pdo->prepare("
            SELECT b.*, 
                   a.name AS author_name, a.bio AS author_bio,
                   c.name AS category_name, c.slug AS category_slug
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE b.id = ?
            LIMIT 1
        ");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch();

        if ($book) {
            // Fetch related books from same category
            $relStmt = $pdo->prepare("
                SELECT b.*, a.name AS author_name, c.name AS category_name
                FROM books b
                JOIN authors a ON b.author_id = a.id
                JOIN categories c ON b.category_id = c.id
                WHERE b.category_id = ? AND b.id != ?
                ORDER BY b.rating DESC, b.views DESC
                LIMIT 4
            ");
            $relStmt->execute([$book['category_id'], $bookId]);
            $relatedBooks = $relStmt->fetchAll();

            // Fetch reviews & rating statistics
            $reviews = getBookReviews($bookId, 10);
            $ratingStats = getBookRatingStats($bookId);
            $frequentlyBought = getFrequentlyBoughtTogether($bookId);
            $smartRecommendations = getRecommendedBooks($bookId, $currentUser ? (int)$currentUser['id'] : null, 4);
        }
    } catch (Exception $e) {
        error_log("Book details query error: " . $e->getMessage());
    }
}

// Fallback seed book if database is not yet seeded
if (!$book) {
    $fallbackBooks = [
        1 => [
            'id' => 1,
            'title' => 'Atomic Habits: An Easy & Proven Way to Build Good Habits & Break Bad Ones',
            'author_name' => 'James Clear',
            'author_bio' => 'James Clear is a writer and speaker focused on habits, decision making, and continuous improvement.',
            'category_name' => 'Self Help',
            'category_slug' => 'self-help',
            'price' => 499.00,
            'discount' => 15.00,
            'rating' => 4.90,
            'stock' => 45,
            'isbn' => '9780735211292',
            'publisher' => 'Avery / Penguin Random House',
            'publication_date' => '2018-10-16',
            'pages' => 320,
            'language' => 'English',
            'description' => "No matter your goals, Atomic Habits offers a proven framework for improving--every day. James Clear, one of the world's leading experts on habit formation, reveals practical strategies that will teach you exactly how to form good habits, break bad ones, and master the tiny behaviors that lead to remarkable results.\n\nIf you're having trouble changing your habits, the problem isn't you. The problem is your system. Bad habits repeat themselves again and again not because you don't want to change, but because you have the wrong system for change.",
            'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=800&auto=format&fit=crop&q=80'
        ],
        2 => [
            'id' => 2,
            'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'author_name' => 'Robert C. Martin',
            'author_bio' => 'Robert C. Martin (Uncle Bob) has been a software professional since 1970 and is a co-author of the Agile Manifesto.',
            'category_name' => 'Programming',
            'category_slug' => 'programming',
            'price' => 799.00,
            'discount' => 10.00,
            'rating' => 4.85,
            'stock' => 28,
            'isbn' => '9780132350884',
            'publisher' => 'Prentice Hall',
            'publication_date' => '2008-08-01',
            'pages' => 464,
            'language' => 'English',
            'description' => "Even bad code can function. But if code isn't clean, it can bring a development organization to its knees. Every year, countless hours and significant resources are lost because of poorly written code. But it doesn't have to be that way.\n\nNoted software expert Robert C. Martin presents a revolutionary paradigm with Clean Code: A Handbook of Agile Software Craftsmanship. Martin has teamed up with his colleagues from Object Mentor to distill their best agile practice of cleaning code 'on the fly' into a book that will cultivate inside you the values of a software craftsman.",
            'cover_image' => 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=800&auto=format&fit=crop&q=80'
        ]
    ];

    $book = $fallbackBooks[$bookId] ?? $fallbackBooks[1];
}

$pageTitle = $book['title'] . " - BookNest";
$activeNav = "books";

require_once __DIR__ . '/includes/header.php';

$finalPrice = calculateDiscountPrice((float)$book['price'], (float)($book['discount'] ?? 0));
$discount = (float)($book['discount'] ?? 0);
$savings = (float)$book['price'] - $finalPrice;
$inStock = ((int)$book['stock']) > 0;
?>

<div class="container" style="padding: 40px 20px 80px 20px;">
  
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 24px;">
    <a href="index.php" style="color:inherit; text-decoration:none;">Home</a> › 
    <a href="books.php" style="color:inherit; text-decoration:none;">Books</a> › 
    <a href="books.php?category=<?= urlencode($book['category_slug'] ?? '') ?>" style="color:inherit; text-decoration:none;">
      <?= e($book['category_name']) ?>
    </a> › 
    <span style="color: var(--color-text-main); font-weight: 600;"><?= e($book['title']) ?></span>
  </nav>

  <!-- Book Main Grid (Cover Gallery & Details) -->
  <div class="book-details-grid">
    
    <!-- Left Column: Sticky Cover Gallery -->
    <div class="book-details-gallery">
      <div class="details-cover-wrap">
        <img 
          src="<?= e($book['cover_image']) ?>" 
          alt="<?= e($book['title']) ?>" 
          class="details-cover-img"
          onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=800&auto=format&fit=crop&q=80'"
        >
      </div>

      <!-- Quick Trust Indicators -->
      <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-lg); padding:16px; font-size:0.82rem; color:var(--color-text-muted); display:flex; flex-direction:column; gap:10px;">
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="color:var(--color-success); font-size:1.1rem;">✓</span>
          <span>100% Genuine Publisher Edition</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="color:var(--color-primary); font-size:1.1rem;">🚚</span>
          <span>Free Campus Delivery on orders above ₹499</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="color:var(--color-accent); font-size:1.1rem;">🔄</span>
          <span>7-Day Replacement Guarantee</span>
        </div>
      </div>
    </div>

    <!-- Right Column: Info, Pricing, Actions & Description -->
    <div>
      
      <!-- Badges -->
      <div class="details-badges-row">
        <span class="details-genre-badge"><?= e($book['category_name']) ?></span>
        <?php if ($discount > 0): ?>
          <span class="details-discount-pill"><?= (int)$discount ?>% OFF</span>
        <?php endif; ?>
        <span style="font-size:0.8rem; color:var(--color-text-light); font-family:var(--font-mono);">
          ISBN: <?= e($book['isbn']) ?>
        </span>
      </div>

      <!-- Title & Author -->
      <h1 class="details-title"><?= e($book['title']) ?></h1>
      <div class="details-author">
        By <a href="books.php?q=<?= urlencode($book['author_name']) ?>"><?= e($book['author_name']) ?></a> (Author)
      </div>

      <!-- Ratings Summary -->
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <?= renderRatingStars((float)$book['rating']) ?>
        <span style="font-weight:700; font-size:0.95rem; color:var(--color-text-main);">
          <?= number_format((float)$book['rating'], 2) ?>
        </span>
        <span style="font-size:0.85rem; color:var(--color-text-muted);">
          • <?= count($reviews) > 0 ? count($reviews) . ' customer reviews' : 'Verified Reader Ratings' ?>
        </span>
        <span style="font-size:0.85rem; color:var(--color-text-muted);">
          • <?= (int)($book['views'] ?? 140) ?> views
        </span>
      </div>

      <!-- Price Box -->
      <div class="details-price-box">
        <div>
          <div>
            <span class="details-final-price"><?= formatPrice($finalPrice) ?></span>
            <?php if ($discount > 0): ?>
              <span class="details-orig-price"><?= formatPrice((float)$book['price']) ?></span>
            <?php endif; ?>
          </div>
          <?php if ($discount > 0): ?>
            <div class="details-savings">
              You save <?= formatPrice($savings) ?> (<?= (int)$discount ?>%) inclusive of all taxes
            </div>
          <?php endif; ?>
        </div>

        <!-- Stock Status Badge -->
        <div>
          <?php if ($inStock): ?>
            <div style="display:inline-flex; align-items:center; gap:6px; background:#DCFCE7; color:#166534; padding:6px 14px; border-radius:var(--radius-full); font-size:0.85rem; font-weight:700;">
              <span>●</span> In Stock (<?= (int)$book['stock'] ?> available)
            </div>
          <?php else: ?>
            <div style="display:inline-flex; align-items:center; gap:6px; background:#FEE2E2; color:#991B1B; padding:6px 14px; border-radius:var(--radius-full); font-size:0.85rem; font-weight:700;">
              <span>●</span> Out of Stock
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Purchase & Action Buttons -->
      <div style="display:flex; align-items:center; gap:16px; margin-bottom:32px; flex-wrap:wrap;">
        
        <!-- Quantity Picker -->
        <?php if ($inStock): ?>
          <div class="quantity-picker">
            <button type="button" class="quantity-btn" onclick="decreaseQty()">-</button>
            <input type="number" id="bookQty" value="1" min="1" max="<?= (int)$book['stock'] ?>" class="quantity-input" readonly>
            <button type="button" class="quantity-btn" onclick="increaseQty()">+</button>
          </div>
        <?php endif; ?>

        <!-- Add to Cart -->
        <button 
          class="btn btn-primary add-cart-btn" 
          data-book-id="<?= (int)$book['id'] ?>"
          data-book-title="<?= e($book['title']) ?>"
          style="padding:14px 28px; font-size:1rem;"
          <?= !$inStock ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '' ?>
        >
          <span>🛒</span> Add to Cart
        </button>

        <!-- Wishlist -->
        <button 
          class="btn btn-secondary book-wishlist-btn" 
          data-book-id="<?= (int)$book['id'] ?>"
          style="padding:14px 20px; font-size:1rem;"
        >
          <span>♥</span> Save to Wishlist
        </button>
      </div>

      <!-- Description Section -->
      <div style="margin-bottom: 36px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 12px; border-bottom: 1px solid var(--color-card-border); padding-bottom: 8px;">
          Book Synopsis & Overview
        </h2>
        <div style="font-size: 0.95rem; line-height: 1.8; color: var(--color-text-main); white-space: pre-line;">
          <?= e($book['description'] ?? 'No synopsis available for this title.') ?>
        </div>
      </div>

      <!-- Specifications Table -->
      <div style="margin-bottom: 40px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 12px; border-bottom: 1px solid var(--color-card-border); padding-bottom: 8px;">
          Product Specifications
        </h2>
        <table class="details-specs-table">
          <tbody>
            <tr>
              <td>ISBN-13</td>
              <td><code><?= e($book['isbn']) ?></code></td>
            </tr>
            <tr>
              <td>Author</td>
              <td><?= e($book['author_name']) ?></td>
            </tr>
            <tr>
              <td>Category / Genre</td>
              <td><?= e($book['category_name']) ?></td>
            </tr>
            <tr>
              <td>Publisher</td>
              <td><?= e($book['publisher'] ?? 'BookNest Publications') ?></td>
            </tr>
            <tr>
              <td>Publication Date</td>
              <td><?= !empty($book['publication_date']) ? date('F d, Y', strtotime($book['publication_date'])) : '2024' ?></td>
            </tr>
            <tr>
              <td>Page Count</td>
              <td><?= !empty($book['pages']) ? (int)$book['pages'] . ' pages' : '320 pages' ?></td>
            </tr>
            <tr>
              <td>Language</td>
              <td><?= e($book['language'] ?? 'English') ?></td>
            </tr>
            <tr>
              <td>Format</td>
              <td>Paperback / High Quality Print</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

  </div>

  <!-- Frequently Bought Together Bundle -->
  <?php if (!empty($frequentlyBought)): 
    $currB = $frequentlyBought['current_book'];
    $compB = $frequentlyBought['companion_book'];
  ?>
    <section style="margin-top: 50px; background: #fff; border: 1px solid var(--color-card-border); border-radius: var(--radius-xl); padding: 28px; box-shadow: var(--shadow-sm);">
      <div style="display:flex; align-items:center; gap:8px; margin-bottom: 20px;">
        <span style="background: #FEF3C7; color: #92400E; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full); text-transform: uppercase; letter-spacing: 0.5px;">
          Smart Combo
        </span>
        <h2 style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700; color: var(--color-text-main); margin: 0;">
          Frequently Bought Together
        </h2>
      </div>

      <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 24px;">
        
        <!-- Books preview duo -->
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
          <!-- Current book -->
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="width: 72px; height: 100px; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid #E2E8F0; flex-shrink: 0;">
              <img src="<?= e($currB['cover_image']) ?>" alt="<?= e($currB['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div>
              <div style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600;">This Item:</div>
              <div style="font-weight: 700; font-size: 0.9rem; color: var(--color-text-main); max-width: 220px; line-height: 1.3;" class="truncate-2">
                <?= e($currB['title']) ?>
              </div>
              <div style="font-weight: 700; color: var(--color-primary); font-size: 0.95rem; margin-top: 2px;">
                ₹<?= calculateDiscountPrice((float)$currB['price'], (float)$currB['discount']) ?>
              </div>
            </div>
          </div>

          <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-text-light);">+</div>

          <!-- Companion book -->
          <div style="display:flex; align-items:center; gap:12px;">
            <div style="width: 72px; height: 100px; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid #E2E8F0; flex-shrink: 0;">
              <img src="<?= e($compB['cover_image']) ?>" alt="<?= e($compB['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div>
              <div style="font-size: 0.75rem; color: #D97706; font-weight: 700;">Recommended Companion:</div>
              <a href="book-details.php?id=<?= $compB['id'] ?>" style="text-decoration:none;">
                <div style="font-weight: 700; font-size: 0.9rem; color: var(--color-text-main); max-width: 220px; line-height: 1.3;" class="truncate-2">
                  <?= e($compB['title']) ?>
                </div>
              </a>
              <div style="font-weight: 700; color: var(--color-primary); font-size: 0.95rem; margin-top: 2px;">
                ₹<?= calculateDiscountPrice((float)$compB['price'], (float)$compB['discount']) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Pricing & 1-Click Action -->
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-lg); padding: 18px 24px; min-width: 260px;">
          <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 4px;">
            Bundle Price (2 Books):
          </div>
          <div style="display:flex; align-items:baseline; gap: 8px;">
            <span style="font-size: 1.5rem; font-weight: 800; color: var(--color-text-main);">
              ₹<?= number_format($frequentlyBought['bundle_price'], 2) ?>
            </span>
            <span style="font-size: 0.9rem; color: var(--color-text-light); text-decoration: line-through;">
              ₹<?= number_format($frequentlyBought['individual_total'], 2) ?>
            </span>
          </div>
          <div style="font-size: 0.8rem; color: #059669; font-weight: 700; margin-top: 2px; margin-bottom: 14px;">
            ✓ Extra 10% Bundle Discount Saved (₹<?= number_format($frequentlyBought['bundle_savings'], 2) ?>)
          </div>

          <button 
            type="button" 
            onclick="addBundleToCart(<?= $currB['id'] ?>, <?= $compB['id'] ?>)" 
            class="btn btn-primary"
            style="width: 100%; justify-content: center; font-size: 0.9rem; padding: 10px;"
          >
            <span>⚡ Add Both to Cart</span>
          </button>
        </div>

      </div>
    </section>
  <?php endif; ?>

  <!-- Smart Recommendations: Readers Also Explored -->
  <?php if (!empty($smartRecommendations)): ?>
    <section style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--color-card-border);">
      <div class="section-header">
        <div>
          <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
            Smart Recommendation Engine
          </div>
          <h2 class="section-title" style="font-size: 1.8rem;">Readers Who Explored This Also Liked</h2>
          <p class="section-desc">Ranked by shared authorship, category relevance, and high reader ratings.</p>
        </div>
        <a href="books.php?category=<?= urlencode($book['category_slug'] ?? '') ?>" class="view-all-link">
          Explore Genre →
        </a>
      </div>

      <div class="books-grid">
        <?php foreach ($smartRecommendations as $relBook): ?>
          <?php $book = $relBook; include __DIR__ . '/components/book-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- Customer Reviews & Rating Breakdown -->
  <section style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--color-card-border);" id="reviews-section">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
      <div>
        <h2 class="section-title" style="font-size: 1.8rem; margin-bottom: 4px;">Customer Reviews & Ratings</h2>
        <p class="section-desc">Honest, verified opinions from university students and verified purchasers.</p>
      </div>
      <button 
        type="button" 
        onclick="toggleReviewForm()" 
        class="btn btn-secondary btn-sm"
        id="writeReviewToggleBtn"
      >
        <span>✍️</span>
        <span>Write a Review</span>
      </button>
    </div>

    <!-- Rating Summary Dashboard (Histogram & Overall Score) -->
    <?php $stats = $ratingStats ?? getBookRatingStats($bookId); ?>
    <div style="display: grid; grid-template-columns: 240px 1fr; gap: 32px; background: #fff; border: 1px solid var(--color-card-border); border-radius: var(--radius-xl); padding: 28px; box-shadow: var(--shadow-sm); margin-bottom: 32px;" class="cart-layout-grid">
      
      <!-- Overall Score Box -->
      <div style="text-align: center; display: flex; flex-direction: column; justify-content: center; border-right: 1px solid #F1F5F9; padding-right: 20px;">
        <div style="font-size: 3.5rem; font-weight: 800; color: var(--color-text-main); line-height: 1;">
          <?= number_format($stats['average'], 1) ?>
        </div>
        <div style="font-size: 1.2rem; margin: 8px 0; color: #F59E0B;">
          <?= renderRatingStars((float)$stats['average']) ?>
        </div>
        <div style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 600;">
          Based on <?= $stats['total'] ?> verified ratings
        </div>
        <div style="margin-top: 8px; font-size: 0.75rem; color: #059669; font-weight: 700;">
          ✓ 100% Verified Academic Community
        </div>
      </div>

      <!-- Star Distribution Histogram -->
      <div style="display: flex; flex-direction: column; justify-content: center; gap: 8px;">
        <?php for ($s = 5; $s >= 1; $s--): 
          $pct = $stats['stars'][$s]['percentage'] ?? 0;
          $cnt = $stats['stars'][$s]['count'] ?? 0;
        ?>
          <div style="display: flex; align-items: center; gap: 12px; font-size: 0.85rem;">
            <div style="width: 50px; font-weight: 600; color: var(--color-text-main); display: flex; align-items: center; gap: 4px;">
              <span><?= $s ?></span>
              <span style="color: #F59E0B;">★</span>
            </div>
            <div style="flex: 1; height: 10px; background: #F1F5F9; border-radius: 999px; overflow: hidden;">
              <div style="width: <?= $pct ?>%; height: 100%; background: #F59E0B; border-radius: 999px; transition: width 0.5s ease;"></div>
            </div>
            <div style="width: 45px; text-align: right; color: var(--color-text-muted); font-size: 0.8rem; font-weight: 600;">
              <?= $pct ?>%
            </div>
            <div style="width: 35px; text-align: right; color: var(--color-text-light); font-size: 0.75rem;">
              (<?= $cnt ?>)
            </div>
          </div>
        <?php endfor; ?>
      </div>

    </div>

    <!-- Interactive Review Form (Collapsible) -->
    <div id="reviewFormCard" style="display: none; background: #fff; border: 2px solid var(--color-primary); border-radius: var(--radius-xl); padding: 28px; box-shadow: var(--shadow-md); margin-bottom: 32px; animation: fadeIn 0.3s ease;">
      <h3 style="font-family: var(--font-serif); font-size: 1.3rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 6px;">
        Share Your Reading Experience
      </h3>
      <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 20px;">
        Your review helps fellow students choose their study materials and recreational reads.
      </p>

      <?php if (!isLoggedIn()): ?>
        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-lg); padding: 20px; text-align: center;">
          <p style="font-size: 0.9rem; color: var(--color-text-main); margin-bottom: 12px;">
            Please log in with your BookNest account to submit a verified rating.
          </p>
          <a href="login.php?redirect=<?= urlencode("book-details.php?id={$bookId}#reviews-section") ?>" class="btn btn-primary btn-sm">
            Sign In to Review →
          </a>
        </div>
      <?php else: ?>
        <form id="ajaxReviewForm" onsubmit="handleReviewSubmit(event)">
          <input type="hidden" name="book_id" value="<?= $bookId ?>">

          <!-- Star Rating Picker -->
          <div style="margin-bottom: 18px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 6px;">
              Your Overall Rating *
            </label>
            <div style="display: flex; align-items: center; gap: 8px;">
              <div id="starPickerContainer" style="display: flex; gap: 6px; font-size: 1.8rem; cursor: pointer;">
                <span class="star-pick" data-val="1" onclick="setPickRating(1)">★</span>
                <span class="star-pick" data-val="2" onclick="setPickRating(2)">★</span>
                <span class="star-pick" data-val="3" onclick="setPickRating(3)">★</span>
                <span class="star-pick" data-val="4" onclick="setPickRating(4)">★</span>
                <span class="star-pick" data-val="5" onclick="setPickRating(5)">★</span>
              </div>
              <input type="hidden" id="selectedRatingInput" name="rating" value="5">
              <span id="starRatingLabel" style="font-size: 0.85rem; font-weight: 700; color: #D97706; margin-left: 8px;">
                5.0 Stars (Masterpiece / Essential Read)
              </span>
            </div>
          </div>

          <!-- Headline / Title -->
          <div style="margin-bottom: 16px;">
            <label for="reviewTitleInput" style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 6px;">
              Review Headline (Optional)
            </label>
            <input 
              type="text" 
              id="reviewTitleInput" 
              name="title" 
              class="form-control" 
              placeholder="e.g. Life-changing perspective on habits and discipline"
              style="width: 100%; border: 1px solid #CBD5E1; border-radius: var(--radius-md); padding: 10px 14px; font-size: 0.9rem;"
            >
          </div>

          <!-- Detailed Comment -->
          <div style="margin-bottom: 20px;">
            <label for="reviewCommentInput" style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 6px;">
              Detailed Review *
            </label>
            <textarea 
              id="reviewCommentInput" 
              name="comment" 
              rows="4" 
              required
              class="form-control" 
              placeholder="What did you like or dislike? How does this book compare to others in the subject?"
              style="width: 100%; border: 1px solid #CBD5E1; border-radius: var(--radius-md); padding: 12px 14px; font-size: 0.9rem; line-height: 1.5;"
            ></textarea>
          </div>

          <div style="display: flex; gap: 12px; align-items: center;">
            <button 
              type="submit" 
              id="submitReviewBtn" 
              class="btn btn-primary"
              style="padding: 10px 24px;"
            >
              <span>Submit Verified Review</span>
            </button>
            <button 
              type="button" 
              onclick="toggleReviewForm()" 
              class="btn btn-outline btn-sm"
            >
              Cancel
            </button>
            <span id="reviewFormStatus" style="font-size: 0.85rem; font-weight: 600;"></span>
          </div>
        </form>
      <?php endif; ?>
    </div>

    <!-- Reviews List -->
    <div id="reviewsListContainer" style="display: flex; flex-direction: column; gap: 16px;">
      <?php if (empty($reviews)): ?>
        <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:40px 20px; text-align:center;">
          <div style="font-size:2.2rem; margin-bottom:8px;">✍️</div>
          <h3 style="font-weight:700; margin-bottom:4px;">No reviews yet for this edition</h3>
          <p style="color:var(--color-text-muted); font-size:0.88rem; margin-bottom:16px;">Be the first campus reader to share your thoughts!</p>
          <button type="button" onclick="toggleReviewForm()" class="btn btn-primary btn-sm">Leave a Rating</button>
        </div>
      <?php else: ?>
        <?php foreach ($reviews as $rev): ?>
          <div class="review-card" style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:24px; box-shadow:var(--shadow-sm);">
            
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
              <div>
                <div style="display:flex; align-items:center; gap:8px;">
                  <strong style="font-size:0.95rem; color:var(--color-text-main);"><?= e($rev['user_name']) ?></strong>
                  <?php if (!empty($rev['is_verified'])): ?>
                    <span style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:var(--radius-full); display:inline-flex; align-items:center; gap:3px;">
                      ✓ Verified Purchaser
                    </span>
                  <?php endif; ?>
                </div>
                <div style="font-size:0.75rem; color:var(--color-text-muted); margin-top:2px;">
                  Reviewed on <?= date('F d, Y', strtotime($rev['created_at'])) ?>
                </div>
              </div>

              <div style="color: #F59E0B; font-size: 1rem;">
                <?= renderRatingStars((float)$rev['rating']) ?>
              </div>
            </div>

            <?php if (!empty($rev['title'])): ?>
              <h4 style="font-size:1rem; font-weight:700; color:var(--color-text-main); margin:0 0 6px 0;">
                <?= e($rev['title']) ?>
              </h4>
            <?php endif; ?>

            <p style="color:var(--color-text-main); font-size:0.9rem; line-height:1.6; margin:0;">
              <?= nl2br(e($rev['comment'])) ?>
            </p>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </section>

</div>

<script>
function decreaseQty() {
  var input = document.getElementById('bookQty');
  if (input && parseInt(input.value) > 1) {
    input.value = parseInt(input.value) - 1;
  }
}

function increaseQty() {
  var input = document.getElementById('bookQty');
  if (input) {
    var max = parseInt(input.getAttribute('max')) || 99;
    if (parseInt(input.value) < max) {
      input.value = parseInt(input.value) + 1;
    }
  }
}

// Toggle Review Submission Form
function toggleReviewForm() {
  var card = document.getElementById('reviewFormCard');
  if (card) {
    if (card.style.display === 'none' || card.style.display === '') {
      card.style.display = 'block';
      card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
      card.style.display = 'none';
    }
  }
}

// 5-Star Interactive Picker
var currentRating = 5;
var ratingLabels = {
  1: '1.0 Star (Poor / Did not finish)',
  2: '2.0 Stars (Fair / Below expectations)',
  3: '3.0 Stars (Average / Good reference)',
  4: '4.0 Stars (Very Good / Highly recommended)',
  5: '5.0 Stars (Masterpiece / Essential Read)'
};

function setPickRating(val) {
  currentRating = val;
  var input = document.getElementById('selectedRatingInput');
  if (input) input.value = val;

  var label = document.getElementById('starRatingLabel');
  if (label) label.textContent = ratingLabels[val] || (val + ' Stars');

  var stars = document.querySelectorAll('.star-pick');
  stars.forEach(function(s) {
    var v = parseInt(s.getAttribute('data-val'));
    if (v <= val) {
      s.style.color = '#F59E0B';
    } else {
      s.style.color = '#CBD5E1';
    }
  });
}

// Initialize picker
document.addEventListener('DOMContentLoaded', function() {
  setPickRating(5);
});

// Submit review via AJAX
function handleReviewSubmit(e) {
  e.preventDefault();
  var form = e.target;
  var btn = document.getElementById('submitReviewBtn');
  var status = document.getElementById('reviewFormStatus');
  
  if (btn) btn.disabled = true;
  if (status) {
    status.style.color = '#64748B';
    status.textContent = 'Submitting review...';
  }

  var formData = new FormData(form);

  fetch('api/review-add.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    if (data.success) {
      if (status) {
        status.style.color = '#059669';
        status.textContent = '✓ Review published successfully!';
      }
      setTimeout(function() {
        window.location.reload();
      }, 1000);
    } else {
      if (status) {
        status.style.color = '#DC2626';
        status.textContent = 'Error: ' + (data.message || 'Submission failed');
      }
      if (btn) btn.disabled = false;
    }
  })
  .catch(function(err) {
    if (status) {
      status.style.color = '#DC2626';
      status.textContent = 'Network error. Please try again.';
    }
    if (btn) btn.disabled = false;
  });
}

// Add Frequently Bought Together Bundle to Cart
function addBundleToCart(book1Id, book2Id) {
  Promise.all([
    fetch('api/cart-add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ book_id: book1Id, quantity: 1 })
    }),
    fetch('api/cart-add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ book_id: book2Id, quantity: 1 })
    })
  ])
  .then(function() {
    window.location.href = 'cart.php';
  })
  .catch(function() {
    window.location.href = 'cart.php';
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
