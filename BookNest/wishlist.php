<?php
/**
 * BookNest - User Wishlist Page
 * College Project: BookNest Online Bookstore
 */

$pageTitle = "My Wishlist - BookNest";
$activeNav = "wishlist";

require_once __DIR__ . '/includes/header.php';

$wishlistItems = getWishlistItems();
$wishlistCount = count($wishlistItems);
?>

<div class="container" style="padding: 40px 20px 80px 20px;">

  <!-- Breadcrumbs -->
  <nav class="breadcrumb" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--color-text-muted); margin-bottom:24px;">
    <a href="index.php" style="color:var(--color-text-muted); text-decoration:none;">Home</a>
    <span>›</span>
    <span style="color:var(--color-text-main); font-weight:600;">My Wishlist</span>
  </nav>

  <!-- Page Header -->
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
    <div>
      <h1 style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--color-text-main); margin:0 0 6px 0;">
        My Wishlist <span style="font-size:1.1rem; font-family:var(--font-sans); color:var(--color-text-muted); font-weight:500;">(<?= $wishlistCount ?> <?= $wishlistCount === 1 ? 'item' : 'items' ?>)</span>
      </h1>
      <p style="color:var(--color-text-muted); font-size:0.9rem; margin:0;">
        Saved titles you love. Move them into your cart whenever you're ready to order.
      </p>
    </div>

    <?php if ($wishlistCount > 0): ?>
      <div style="display:flex; gap:10px;">
        <button 
          type="button" 
          id="addAllToCartBtn" 
          class="btn btn-secondary btn-sm"
          onclick="addAllWishlistToCart()"
        >
          <span>🛒</span>
          <span>Add All to Cart</span>
        </button>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($wishlistCount === 0): ?>
    <!-- Empty Wishlist State -->
    <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:60px 24px; text-align:center; max-width:640px; margin:20px auto; box-shadow:var(--shadow-sm);">
      <div style="width:80px; height:80px; background:#FFF1F2; color:#E11D48; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:2.2rem; margin-bottom:16px;">
        ♡
      </div>
      <h2 style="font-family:var(--font-serif); font-size:1.6rem; font-weight:700; color:var(--color-text-main); margin-bottom:8px;">
        Your wishlist is empty
      </h2>
      <p style="color:var(--color-text-muted); font-size:0.95rem; line-height:1.6; max-width:420px; margin:0 auto 28px auto;">
        Found something intriguing while browsing? Click the heart icon on any book cover to save it here for later.
      </p>
      <div style="display:flex; justify-content:center; gap:12px;">
        <a href="books.php" class="btn btn-primary" style="padding:10px 24px;">
          <span>Discover Books</span>
          <span>→</span>
        </a>
      </div>
    </div>

  <?php else: ?>

    <!-- Wishlist Books Grid -->
    <div 
      id="wishlistGrid" 
      style="display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:24px;"
    >
      <?php foreach ($wishlistItems as $item): ?>
        <?php 
          $origPrice = (float)$item['price'];
          $discPercent = (float)$item['discount'];
          $finalPrice = calculateDiscountPrice($origPrice, $discPercent);
          $inStock = (int)$item['stock'] > 0;
        ?>
        <div 
          class="wishlist-card" 
          id="wishlistItem-<?= $item['id'] ?>"
          style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-lg); overflow:hidden; display:flex; flex-direction:column; box-shadow:var(--shadow-sm); transition:transform 0.2s, box-shadow 0.2s;"
        >
          <!-- Book Image with Remove action overlay -->
          <div style="position:relative; aspect-ratio:3/4; overflow:hidden; background:#F8FAFC;">
            <a href="book-details.php?id=<?= $item['id'] ?>" style="display:block; width:100%; height:100%;">
              <img 
                src="<?= e($item['cover_image']) ?>" 
                alt="<?= e($item['title']) ?>" 
                style="width:100%; height:100%; object-fit:cover;"
                onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400';"
              >
            </a>

            <!-- Remove Button -->
            <button 
              type="button" 
              onclick="removeFromWishlist(<?= $item['id'] ?>, '<?= e(addslashes($item['title'])) ?>')"
              style="position:absolute; top:10px; right:10px; width:34px; height:34px; border-radius:50%; background:rgba(255,255,255,0.9); border:none; box-shadow:var(--shadow-sm); cursor:pointer; display:flex; align-items:center; justify-content:center; color:#DC2626; font-size:1.1rem;"
              title="Remove from wishlist"
            >
              ✕
            </button>

            <!-- Discount Tag -->
            <?php if ($discPercent > 0): ?>
              <span style="position:absolute; top:10px; left:10px; background:#DC2626; color:#fff; font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:4px;">
                <?= (int)$discPercent ?>% OFF
              </span>
            <?php endif; ?>
          </div>

          <!-- Card Content -->
          <div style="padding:16px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <span style="font-size:0.72rem; font-weight:700; color:var(--color-primary); text-transform:uppercase; letter-spacing:0.04em; display:block; margin-bottom:4px;">
                <?= e($item['category_name']) ?>
              </span>
              <h3 style="font-size:0.95rem; font-weight:700; color:var(--color-text-main); margin:0 0 4px 0; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                <a href="book-details.php?id=<?= $item['id'] ?>" style="color:inherit; text-decoration:none;">
                  <?= e($item['title']) ?>
                </a>
              </h3>
              <p style="font-size:0.82rem; color:var(--color-text-muted); margin:0 0 8px 0;">
                by <?= e($item['author_name']) ?>
              </p>

              <!-- Rating & Stock status -->
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <div style="font-size:0.8rem; color:#D97706; font-weight:600; display:flex; align-items:center; gap:2px;">
                  <span>★</span>
                  <span><?= number_format((float)$item['rating'], 1) ?></span>
                </div>
                
                <?php if ($inStock): ?>
                  <span style="font-size:0.75rem; font-weight:600; color:#059669; background:#ECFDF5; padding:2px 6px; border-radius:4px;">
                    In Stock
                  </span>
                <?php else: ?>
                  <span style="font-size:0.75rem; font-weight:600; color:#DC2626; background:#FEF2F2; padding:2px 6px; border-radius:4px;">
                    Out of Stock
                  </span>
                <?php endif; ?>
              </div>

              <!-- Price -->
              <div style="display:flex; align-items:baseline; gap:8px; margin-bottom:16px;">
                <span style="font-size:1.15rem; font-weight:800; color:var(--color-text-main);">
                  <?= formatPrice($finalPrice) ?>
                </span>
                <?php if ($discPercent > 0): ?>
                  <span style="font-size:0.85rem; color:var(--color-text-light); text-decoration:line-through;">
                    <?= formatPrice($origPrice) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <!-- Action: Move to Cart -->
            <div>
              <?php if ($inStock): ?>
                <button 
                  type="button" 
                  onclick="moveWishlistToCart(<?= $item['id'] ?>, '<?= e(addslashes($item['title'])) ?>')"
                  class="btn btn-primary" 
                  style="width:100%; justify-content:center; padding:9px 12px; font-size:0.85rem;"
                >
                  <span>🛒</span>
                  <span>Move to Cart</span>
                </button>
              <?php else: ?>
                <button 
                  type="button" 
                  disabled
                  class="btn btn-secondary" 
                  style="width:100%; justify-content:center; padding:9px 12px; font-size:0.85rem; opacity:0.6; cursor:not-allowed;"
                >
                  Out of Stock
                </button>
              <?php endif; ?>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</div>

<!-- Wishlist Client-side script -->
<script src="assets/js/wishlist.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
