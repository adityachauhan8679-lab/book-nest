<?php
/**
 * BookNest - Order Success Confirmation & Tax Invoice Page
 */

$pageTitle = "Order Confirmed - BookNest";
$activeNav = "cart";

require_once __DIR__ . '/includes/header.php';

$orderId = (int)($_GET['id'] ?? 0);
$currentUser = getCurrentUser();
$userId = $currentUser ? (int)$currentUser['id'] : null;

$order = getOrderDetails($orderId, $userId);

if (!$order) {
    echo '<div class="container" style="padding:60px 20px; text-align:center;"><h2>Order not found</h2><p>Please check your orders list.</p><a href="books.php" class="btn btn-primary">Browse Books</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>

<div class="container" style="padding: 40px 20px 80px 20px; max-width: 860px;">

  <!-- Success Celebration Banner -->
  <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:36px 28px; text-align:center; box-shadow:var(--shadow-sm); margin-bottom:28px;">
    <div style="width:68px; height:68px; background:#ECFDF5; color:#059669; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:2.2rem; font-weight:700; margin-bottom:16px;">
      ✓
    </div>
    <h1 style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--color-text-main); margin-bottom:6px;">
      Thank You! Your Order is Confirmed
    </h1>
    <p style="color:var(--color-text-muted); font-size:0.95rem; margin-bottom:18px;">
      We have received your order and our campus fulfillment team is packing your books.
    </p>

    <div style="display:inline-flex; align-items:center; gap:12px; background:#F8FAFC; border:1px solid #E2E8F0; padding:10px 20px; border-radius:var(--radius-full); font-size:0.9rem;">
      <span style="color:var(--color-text-muted);">Order Number:</span>
      <strong style="font-family:var(--font-mono); color:var(--color-primary); font-size:1.05rem;">#BN-<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
    </div>

    <!-- Action Buttons -->
    <div style="display:flex; justify-content:center; gap:12px; margin-top:24px; flex-wrap:wrap;">
      <button 
        type="button" 
        onclick="window.print()" 
        class="btn btn-outline btn-sm"
        style="padding:8px 18px;"
      >
        <span>🖨️</span>
        <span>Print Tax Invoice</span>
      </button>
      <a href="orders.php" class="btn btn-secondary btn-sm" style="padding:8px 18px;">
        <span>📦</span>
        <span>View My Orders</span>
      </a>
      <a href="books.php" class="btn btn-primary btn-sm" style="padding:8px 18px;">
        <span>Continue Shopping →</span>
      </a>
    </div>
  </div>

  <!-- Order Tracking Stepper -->
  <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:28px; box-shadow:var(--shadow-sm); margin-bottom:28px;">
    <h3 style="font-size:1.05rem; font-weight:700; margin-bottom:20px; color:var(--color-text-main);">
      Delivery Tracking Status
    </h3>

    <div style="display:grid; grid-template-columns: repeat(4, 1fr); text-align:center; position:relative; gap:8px;">
      
      <!-- Step 1 -->
      <div>
        <div style="width:36px; height:36px; border-radius:50%; background:#10B981; color:#fff; display:flex; align-items:center; justify-content:center; margin:0 auto 8px auto; font-weight:700;">
          ✓
        </div>
        <div style="font-weight:700; font-size:0.85rem; color:var(--color-text-main);">Order Placed</div>
        <div style="font-size:0.75rem; color:var(--color-text-muted);"><?= date('M d, g:i A', strtotime($order['created_at'])) ?></div>
      </div>

      <!-- Step 2 -->
      <div>
        <div style="width:36px; height:36px; border-radius:50%; background:var(--color-primary); color:#fff; display:flex; align-items:center; justify-content:center; margin:0 auto 8px auto; font-weight:700;">
          📦
        </div>
        <div style="font-weight:700; font-size:0.85rem; color:var(--color-primary);">Processing</div>
        <div style="font-size:0.75rem; color:var(--color-text-muted);">Dispatching soon</div>
      </div>

      <!-- Step 3 -->
      <div style="opacity:0.6;">
        <div style="width:36px; height:36px; border-radius:50%; background:#E2E8F0; color:#64748B; display:flex; align-items:center; justify-content:center; margin:0 auto 8px auto; font-weight:700;">
          🚚
        </div>
        <div style="font-weight:700; font-size:0.85rem; color:var(--color-text-main);">Dispatched</div>
        <div style="font-size:0.75rem; color:var(--color-text-muted);">Estimated tomorrow</div>
      </div>

      <!-- Step 4 -->
      <div style="opacity:0.6;">
        <div style="width:36px; height:36px; border-radius:50%; background:#E2E8F0; color:#64748B; display:flex; align-items:center; justify-content:center; margin:0 auto 8px auto; font-weight:700;">
          🏁
        </div>
        <div style="font-weight:700; font-size:0.85rem; color:var(--color-text-main);">Delivered</div>
        <div style="font-size:0.75rem; color:var(--color-text-muted);">In 3-5 business days</div>
      </div>

    </div>
  </div>

  <!-- Detailed Invoice Receipt -->
  <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:32px; box-shadow:var(--shadow-sm);" id="invoiceSection">
    
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; border-bottom:1px solid var(--color-card-border); padding-bottom:18px;">
      <div>
        <h2 style="font-family:var(--font-serif); font-size:1.4rem; font-weight:700; color:var(--color-text-main); margin:0;">
          BookNest Tax Invoice
        </h2>
        <span style="font-size:0.8rem; color:var(--color-text-muted);">Original Buyer Copy</span>
      </div>

      <div style="text-align:right; font-size:0.82rem; color:var(--color-text-muted);">
        <div><strong>Date:</strong> <?= date('F d, Y', strtotime($order['created_at'])) ?></div>
        <div><strong>Status:</strong> <span style="color:#059669; font-weight:700;"><?= e($order['order_status']) ?></span></div>
        <div><strong>Payment:</strong> <?= e($order['payment_method']) ?> (<?= e($order['payment_status']) ?>)</div>
      </div>
    </div>

    <!-- Delivery Address Details -->
    <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:var(--radius-md); padding:16px; margin-bottom:24px; font-size:0.85rem;">
      <div style="font-weight:700; color:var(--color-text-main); margin-bottom:4px;">
        Delivery Destination:
      </div>
      <div style="color:var(--color-text-main); font-weight:600;">
        <?= e($order['recipient_name'] ?: ($order['user_name'] ?? 'Customer')) ?>
      </div>
      <div style="color:var(--color-text-muted);">
        <?= e($order['address']) ?>, <?= e($order['city']) ?>, <?= e($order['state']) ?> - <?= e($order['pincode']) ?>
      </div>
      <div style="color:var(--color-text-muted);">
        Contact Phone: <?= e($order['recipient_phone'] ?: '+91 91234 56789') ?>
      </div>
    </div>

    <!-- Items Table -->
    <table style="width:100%; border-collapse:collapse; font-size:0.88rem; margin-bottom:24px;">
      <thead>
        <tr style="border-bottom:2px solid #E2E8F0; text-align:left; color:var(--color-text-muted); font-size:0.78rem; text-transform:uppercase;">
          <th style="padding:10px 0;">Item Description</th>
          <th style="padding:10px; text-align:center;">Qty</th>
          <th style="padding:10px; text-align:right;">Unit Price</th>
          <th style="padding:10px 0; text-align:right;">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($order['items'] as $it): ?>
          <tr style="border-bottom:1px solid #F1F5F9;">
            <td style="padding:12px 0;">
              <div style="display:flex; align-items:center; gap:12px;">
                <img src="<?= e($it['cover_image']) ?>" style="width:36px; height:50px; object-fit:cover; border-radius:4px; border:1px solid #CBD5E1;">
                <div>
                  <div style="font-weight:700; color:var(--color-text-main);"><?= e($it['title']) ?></div>
                  <div style="font-size:0.75rem; color:var(--color-text-muted);">by <?= e($it['author_name']) ?> • ISBN: <?= e($it['isbn']) ?></div>
                </div>
              </div>
            </td>
            <td style="padding:12px; text-align:center; font-weight:600;"><?= $it['quantity'] ?></td>
            <td style="padding:12px; text-align:right;"><?= formatPrice((float)$it['price']) ?></td>
            <td style="padding:12px 0; text-align:right; font-weight:700;"><?= formatPrice((float)$it['price'] * (int)$it['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="padding-top:16px; text-align:right; color:var(--color-text-muted); font-weight:600;">Total Amount Paid / Due:</td>
          <td style="padding-top:16px; text-align:right; font-size:1.25rem; font-weight:800; color:var(--color-primary);"><?= formatPrice((float)$order['total_amount']) ?></td>
        </tr>
      </tfoot>
    </table>

  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
