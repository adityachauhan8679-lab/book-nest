<?php
/**
 * BookNest - Shopping Cart Page
 * Comprehensive Cart with Dynamic Free Delivery Tracker, Stock Checks & Live Recalculations
 */

$pageTitle = "Shopping Cart - BookNest";
$activeNav = "cart";

require_once __DIR__ . '/includes/header.php';

$cartItems = getCartItems();
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$summary = calculateCartSummary($cartItems, $appliedCoupon);
?>

<div class="container" style="padding: 40px 20px 80px 20px;">
  
  <!-- Breadcrumb -->
  <nav class="breadcrumb" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--color-text-muted); margin-bottom:24px;">
    <a href="index.php" style="color:var(--color-text-muted); text-decoration:none;">Home</a>
    <span>›</span>
    <a href="books.php" style="color:var(--color-text-muted); text-decoration:none;">Books</a>
    <span>›</span>
    <span style="color:var(--color-text-main); font-weight:600;">Shopping Cart</span>
  </nav>

  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <h1 style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--color-text-main); margin:0;">
      Shopping Cart <span style="font-size:1.1rem; font-family:var(--font-sans); color:var(--color-text-muted); font-weight:500;">(<?= $summary['total_items_count'] ?> <?= $summary['total_items_count'] === 1 ? 'item' : 'items' ?>)</span>
    </h1>

    <?php if (!empty($cartItems)): ?>
      <button 
        type="button" 
        id="clearCartBtn" 
        class="btn btn-outline btn-sm"
        style="color:#DC2626; border-color:#FCA5A5;"
      >
        <span>🗑️</span>
        <span>Clear Cart</span>
      </button>
    <?php endif; ?>
  </div>

  <?php if (empty($cartItems)): ?>
    <!-- Empty Cart State -->
    <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:60px 24px; text-align:center; max-width:680px; margin:20px auto; box-shadow:var(--shadow-sm);">
      <div style="width:80px; height:80px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:2.5rem; margin-bottom:16px;">
        🛒
      </div>
      <h2 style="font-family:var(--font-serif); font-size:1.6rem; font-weight:700; color:var(--color-text-main); margin-bottom:8px;">
        Your cart is currently empty
      </h2>
      <p style="color:var(--color-text-muted); font-size:0.95rem; line-height:1.6; max-width:440px; margin:0 auto 28px auto;">
        Before you can proceed to checkout, you must add books to your shopping cart. Browse our curated collection of bestsellers, tech guides, and classic fiction.
      </p>
      <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
        <a href="books.php" class="btn btn-primary" style="padding:10px 24px;">
          <span>Explore Catalog</span>
          <span>→</span>
        </a>
        <a href="wishlist.php" class="btn btn-secondary" style="padding:10px 20px;">
          <span>View Wishlist</span>
          <span>(<?= getWishlistCount() ?>)</span>
        </a>
      </div>
    </div>

  <?php else: ?>

    <div style="display:grid; grid-template-columns: 1fr 380px; gap:32px; align-items:start;" class="cart-layout-grid">
      
      <!-- Left Column: Items List & Free Shipping Tracker -->
      <div>

        <!-- Free Delivery Progress Bar -->
        <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-lg); padding:18px 20px; margin-bottom:24px; box-shadow:var(--shadow-sm);" id="freeDeliveryTracker">
          <?php 
            $progressPercent = min(100, round(($summary['subtotal'] / $summary['free_delivery_threshold']) * 100));
          ?>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; font-size:0.9rem;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="font-size:1.2rem;">🚚</span>
              <span style="font-weight:600; color:var(--color-text-main);" id="freeDeliveryText">
                <?php if ($summary['is_free_delivery']): ?>
                  <span style="color:#059669;">Congratulations! You have qualified for FREE Delivery!</span>
                <?php else: ?>
                  Add <strong style="color:var(--color-primary);"><?= formatPrice($summary['amount_needed_for_free_delivery']) ?></strong> more to qualify for <strong>FREE Delivery</strong>
                <?php endif; ?>
              </span>
            </div>
            <span style="font-size:0.8rem; color:var(--color-text-muted); font-weight:600;" id="freeDeliveryPercentText">
              <?= $progressPercent ?>%
            </span>
          </div>
          <div style="width:100%; height:8px; background:#E2E8F0; border-radius:999px; overflow:hidden;">
            <div 
              id="freeDeliveryProgressBar" 
              style="width: <?= $progressPercent ?>%; height:100%; background: <?= $summary['is_free_delivery'] ? '#10B981' : 'var(--color-primary)' ?>; transition: width 0.3s ease;"
            ></div>
          </div>
        </div>

        <!-- Cart Items Container -->
        <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-lg); overflow:hidden; box-shadow:var(--shadow-sm);">
          
          <div style="padding:16px 20px; border-bottom:1px solid var(--color-card-border); background:#F8FAFC; display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600; color:var(--color-text-muted);">
            <span>PRODUCT DETAILS</span>
            <span>SUBTOTAL</span>
          </div>

          <div id="cartItemsList">
            <?php foreach ($cartItems as $item): ?>
              <div 
                class="cart-item-row" 
                id="cartItem-<?= $item['id'] ?>"
                data-book-id="<?= $item['id'] ?>"
                data-unit-price="<?= $item['unit_price'] ?>"
                data-stock="<?= $item['stock'] ?>"
                style="padding:24px 20px; border-bottom:1px solid var(--color-card-border); display:flex; gap:20px; align-items:flex-start;"
              >
                <!-- Book Thumbnail -->
                <a href="book-details.php?id=<?= $item['id'] ?>" style="flex-shrink:0;">
                  <img 
                    src="<?= e($item['cover_image']) ?>" 
                    alt="<?= e($item['title']) ?>" 
                    style="width:85px; height:120px; object-fit:cover; border-radius:var(--radius-md); box-shadow:var(--shadow-sm); border:1px solid #E2E8F0;"
                    onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=300';"
                  >
                </a>

                <!-- Book Information & Stepper -->
                <div style="flex:1;">
                  <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                    <div>
                      <span style="font-size:0.75rem; font-weight:700; color:var(--color-primary); text-transform:uppercase; letter-spacing:0.04em;">
                        <?= e($item['category_name']) ?>
                      </span>
                      <h3 style="font-size:1.05rem; font-weight:700; margin:2px 0 4px 0; color:var(--color-text-main); line-height:1.35;">
                        <a href="book-details.php?id=<?= $item['id'] ?>" style="color:inherit; text-decoration:none;">
                          <?= e($item['title']) ?>
                        </a>
                      </h3>
                      <p style="font-size:0.85rem; color:var(--color-text-muted); margin-bottom:8px;">
                        by <?= e($item['author_name']) ?>
                      </p>

                      <!-- Stock notice -->
                      <?php if ($item['stock'] <= 5): ?>
                        <div style="font-size:0.75rem; color:#B45309; font-weight:600; margin-bottom:8px;">
                          ⚠️ Only <?= $item['stock'] ?> copies left in stock!
                        </div>
                      <?php else: ?>
                        <div style="font-size:0.75rem; color:#059669; font-weight:600; margin-bottom:8px;">
                          ✓ In Stock (Ships today)
                        </div>
                      <?php endif; ?>
                    </div>

                    <!-- Line Total -->
                    <div style="text-align:right;">
                      <div class="item-line-total" style="font-weight:700; font-size:1.15rem; color:var(--color-text-main);" id="itemTotal-<?= $item['id'] ?>">
                        <?= formatPrice($item['line_total']) ?>
                      </div>
                      <div style="font-size:0.75rem; color:var(--color-text-muted);">
                        <?= formatPrice($item['unit_price']) ?> / each
                      </div>
                    </div>
                  </div>

                  <!-- Unit price details & Controls -->
                  <div style="display:flex; align-items:center; justify-content:space-between; margin-top:14px; flex-wrap:wrap; gap:12px;">
                    
                    <!-- Quantity Stepper -->
                    <div style="display:flex; align-items:center; border:1px solid #CBD5E1; border-radius:var(--radius-md); overflow:hidden; background:#F8FAFC;">
                      <button 
                        type="button" 
                        class="qty-btn btn-decrease" 
                        onclick="updateCartItemQty(<?= $item['id'] ?>, 'decrease')"
                        style="width:34px; height:34px; border:none; background:transparent; font-size:1.1rem; cursor:pointer; font-weight:700; color:var(--color-text-main);"
                        title="Decrease quantity"
                      >−</button>
                      <input 
                        type="number" 
                        class="qty-input" 
                        id="qtyInput-<?= $item['id'] ?>"
                        value="<?= $item['quantity'] ?>" 
                        min="1" 
                        max="<?= $item['stock'] ?>"
                        onchange="updateCartItemQty(<?= $item['id'] ?>, 'set', this.value)"
                        style="width:44px; height:34px; border:none; text-align:center; font-weight:700; font-size:0.9rem; background:#fff; -moz-appearance: textfield;"
                      >
                      <button 
                        type="button" 
                        class="qty-btn btn-increase" 
                        onclick="updateCartItemQty(<?= $item['id'] ?>, 'increase')"
                        style="width:34px; height:34px; border:none; background:transparent; font-size:1.1rem; cursor:pointer; font-weight:700; color:var(--color-text-main);"
                        title="Increase quantity"
                      >+</button>
                    </div>

                    <!-- Item Actions (Move to wishlist, Remove) -->
                    <div style="display:flex; align-items:center; gap:16px;">
                      <button 
                        type="button" 
                        onclick="moveToWishlist(<?= $item['id'] ?>, '<?= e(addslashes($item['title'])) ?>')"
                        style="background:none; border:none; font-size:0.82rem; color:var(--color-text-muted); cursor:pointer; display:flex; align-items:center; gap:4px; padding:4px 8px; border-radius:4px;"
                        onmouseover="this.style.color='var(--color-primary)'"
                        onmouseout="this.style.color='var(--color-text-muted)'"
                      >
                        <span>♡</span>
                        <span>Save for Later</span>
                      </button>

                      <button 
                        type="button" 
                        onclick="removeCartItem(<?= $item['id'] ?>, '<?= e(addslashes($item['title'])) ?>')"
                        style="background:none; border:none; font-size:0.82rem; color:#DC2626; cursor:pointer; display:flex; align-items:center; gap:4px; padding:4px 8px; border-radius:4px;"
                      >
                        <span>🗑️</span>
                        <span>Remove</span>
                      </button>
                    </div>

                  </div>

                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="padding:16px 20px; background:#FAFAFA; display:flex; justify-content:space-between; align-items:center; font-size:0.88rem;">
            <a href="books.php" style="color:var(--color-primary); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
              <span>←</span>
              <span>Continue Shopping</span>
            </a>
            <span style="color:var(--color-text-muted);">
              Secure encrypted checkout
            </span>
          </div>

        </div>

      </div>

      <!-- Right Column: Order Summary & Coupon -->
      <div>
        
        <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:24px; box-shadow:var(--shadow-sm); position:sticky; top:90px;">
          
          <h2 style="font-family:var(--font-serif); font-size:1.35rem; font-weight:700; color:var(--color-text-main); margin-bottom:20px; border-bottom:1px solid var(--color-card-border); padding-bottom:12px;">
            Order Summary
          </h2>

          <!-- Coupon Code Entry -->
          <div style="margin-bottom:20px;">
            <label style="display:block; font-size:0.82rem; font-weight:600; color:var(--color-text-muted); margin-bottom:6px;">
              HAVE A COUPON CODE?
            </label>
            <div style="display:flex; gap:8px;">
              <input 
                type="text" 
                id="couponInput" 
                placeholder="e.g. WELCOME10"
                value="<?= e($summary['applied_coupon']['code'] ?? '') ?>"
                style="flex:1; padding:9px 12px; border:1px solid #CBD5E1; border-radius:var(--radius-md); font-size:0.85rem; text-transform:uppercase; font-family:var(--font-mono);"
              >
              <?php if (!empty($summary['applied_coupon'])): ?>
                <button 
                  type="button" 
                  id="removeCouponBtn" 
                  class="btn btn-outline btn-sm"
                  style="padding:0 12px; font-size:0.8rem; border-color:#DC2626; color:#DC2626;"
                >
                  Remove
                </button>
              <?php else: ?>
                <button 
                  type="button" 
                  id="applyCouponBtn" 
                  class="btn btn-secondary btn-sm"
                  style="padding:0 16px; font-size:0.85rem; font-weight:600;"
                >
                  Apply
                </button>
              <?php endif; ?>
            </div>

            <!-- Active coupon notification -->
            <div id="couponMessage" style="margin-top:6px; font-size:0.8rem;">
              <?php if (!empty($summary['applied_coupon'])): ?>
                <span style="color:#059669; font-weight:600;">
                  ✓ <?= e($summary['applied_coupon']['description']) ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Suggested Coupon Chips -->
            <div style="margin-top:10px;">
              <span style="font-size:0.75rem; color:var(--color-text-muted); display:block; margin-bottom:4px;">Recommended Coupons:</span>
              <div style="display:flex; flex-wrap:wrap; gap:6px;">
                <button type="button" onclick="quickApplyCoupon('WELCOME10')" style="background:#F1F5F9; border:1px dashed #94A3B8; border-radius:4px; padding:3px 8px; font-size:0.75rem; font-family:var(--font-mono); cursor:pointer;">
                  WELCOME10 (10% off)
                </button>
                <button type="button" onclick="quickApplyCoupon('BOOKNEST50')" style="background:#F1F5F9; border:1px dashed #94A3B8; border-radius:4px; padding:3px 8px; font-size:0.75rem; font-family:var(--font-mono); cursor:pointer;">
                  BOOKNEST50 (₹50 off)
                </button>
                <button type="button" onclick="quickApplyCoupon('READMORE')" style="background:#F1F5F9; border:1px dashed #94A3B8; border-radius:4px; padding:3px 8px; font-size:0.75rem; font-family:var(--font-mono); cursor:pointer;">
                  READMORE (15% off)
                </button>
              </div>
            </div>
          </div>

          <!-- Price Calculations -->
          <div style="border-top:1px solid var(--color-card-border); padding-top:16px; display:flex; flex-direction:column; gap:10px; font-size:0.9rem;">
            
            <div style="display:flex; justify-content:space-between; color:var(--color-text-muted);">
              <span>Items Total (<span id="summaryItemCount"><?= $summary['total_items_count'] ?></span>)</span>
              <span style="color:var(--color-text-main); font-weight:600;" id="summarySubtotal">
                <?= formatPrice($summary['subtotal']) ?>
              </span>
            </div>

            <!-- Coupon Discount line -->
            <div 
              id="summaryCouponRow" 
              style="display: <?= $summary['coupon_discount'] > 0 ? 'flex' : 'none' ?>; justify-content:space-between; color:#059669; font-weight:600;"
            >
              <span>Coupon Discount</span>
              <span id="summaryCouponDiscount">-<?= formatPrice($summary['coupon_discount']) ?></span>
            </div>

            <!-- Delivery charges -->
            <div style="display:flex; justify-content:space-between; color:var(--color-text-muted);">
              <span>Estimated Delivery</span>
              <span id="summaryDelivery">
                <?php if ($summary['is_free_delivery']): ?>
                  <span style="color:#059669; font-weight:600;">FREE</span>
                <?php else: ?>
                  <span style="color:var(--color-text-main); font-weight:600;"><?= formatPrice($summary['delivery_fee']) ?></span>
                <?php endif; ?>
              </span>
            </div>

            <!-- GST Tax -->
            <div style="display:flex; justify-content:space-between; color:var(--color-text-muted);">
              <span>Estimated GST (5%)</span>
              <span style="color:var(--color-text-main); font-weight:600;" id="summaryGst">
                <?= formatPrice($summary['gst_amount']) ?>
              </span>
            </div>

            <!-- Grand Total -->
            <div style="border-top:2px dashed var(--color-card-border); margin-top:8px; padding-top:14px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <span style="font-size:1.1rem; font-weight:800; color:var(--color-text-main);">Grand Total</span>
                <span style="display:block; font-size:0.75rem; color:var(--color-text-muted);">Inclusive of all taxes</span>
              </div>
              <span style="font-size:1.45rem; font-weight:800; color:var(--color-primary);" id="summaryGrandTotal">
                <?= formatPrice($summary['grand_total']) ?>
              </span>
            </div>

            <!-- Savings banner -->
            <?php if ($summary['total_savings'] > 0): ?>
              <div 
                id="summarySavingsBadge" 
                style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:8px 12px; border-radius:var(--radius-md); font-size:0.8rem; text-align:center; font-weight:600; margin-top:4px;"
              >
                🎉 You will save <?= formatPrice($summary['total_savings']) ?> on this order!
              </div>
            <?php endif; ?>

          </div>

          <!-- Checkout CTA -->
          <div style="margin-top:24px;">
            <a 
              href="checkout.php" 
              class="btn btn-primary" 
              style="width:100%; justify-content:center; padding:13px 20px; font-size:1rem; font-weight:700; border-radius:var(--radius-lg); box-shadow:var(--shadow-md);"
            >
              <span>Proceed to Checkout</span>
              <span>→</span>
            </a>
          </div>

          <!-- Trust Badges -->
          <div style="margin-top:20px; border-top:1px solid var(--color-card-border); padding-top:16px; display:flex; flex-direction:column; gap:8px; font-size:0.78rem; color:var(--color-text-muted);">
            <div style="display:flex; align-items:center; gap:8px;">
              <span>🛡️</span>
              <span>100% Original and Genuine Books Guarantee</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span>🚚</span>
              <span>Fast Dispatch with Live Order Tracking</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span>🔄</span>
              <span>Hassle-free 7-Day Replacement Policy</span>
            </div>
          </div>

        </div>

      </div>

    </div>

  <?php endif; ?>

</div>

<!-- Cart Page JavaScript Handler -->
<script src="assets/js/cart.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
