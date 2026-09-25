<?php
/**
 * BookNest - Reusable Book Card Component
 * Expects $book array
 */
if (!isset($book) || !is_array($book)) return;

$finalPrice = calculateDiscountPrice((float)$book['price'], (float)($book['discount'] ?? 0));
$coverImg = !empty($book['cover_image']) ? $book['cover_image'] : 'assets/images/book-placeholder.jpg';
$author = $book['author_name'] ?? $book['author'] ?? 'Unknown Author';
$rating = (float)($book['rating'] ?? 4.5);
$discount = (float)($book['discount'] ?? 0);
?>
<div class="book-card">
  <div class="book-cover-wrap">
    <a href="book-details.php?id=<?= (int)$book['id'] ?>">
      <img 
        src="<?= e($coverImg) ?>" 
        alt="<?= e($book['title']) ?>" 
        class="book-cover-img"
        loading="lazy"
        onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=600&auto=format&fit=crop&q=80'"
      >
    </a>

    <?php if ($discount > 0): ?>
      <span class="book-discount-badge"><?= (int)$discount ?>% OFF</span>
    <?php endif; ?>

    <button 
      class="book-wishlist-btn" 
      data-book-id="<?= (int)$book['id'] ?>" 
      title="Add to Wishlist"
      aria-label="Save to Wishlist"
    >
      ♥
    </button>
  </div>

  <div class="book-info">
    <div class="book-author"><?= e($author) ?></div>
    <h3 class="book-title">
      <a href="book-details.php?id=<?= (int)$book['id'] ?>"><?= e($book['title']) ?></a>
    </h3>

    <?= renderRatingStars($rating) ?>

    <div class="book-bottom">
      <div class="price-wrap">
        <span class="book-price"><?= formatPrice($finalPrice) ?></span>
        <?php if ($discount > 0): ?>
          <span class="book-original-price"><?= formatPrice((float)$book['price']) ?></span>
        <?php endif; ?>
      </div>

      <button 
        class="add-cart-btn" 
        data-book-id="<?= (int)$book['id'] ?>"
        data-book-title="<?= e($book['title']) ?>"
      >
        <span>+</span> Add
      </button>
    </div>
  </div>
</div>
