<?php
/**
 * BookNest - Orders History Page
 */

$pageTitle = "My Orders - BookNest";
$activeNav = "orders";

require_once __DIR__ . '/includes/header.php';

$currentUser = getCurrentUser();
$userId = $currentUser ? (int)$currentUser['id'] : 0;

$orders = $userId > 0 ? getUserOrders($userId) : [];
?>

<div class="container" style="padding: 40px 20px 80px 20px; max-width: 960px;">

  <!-- Breadcrumb -->
  <nav class="breadcrumb" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--color-text-muted); margin-bottom:24px;">
    <a href="index.php" style="color:var(--color-text-muted); text-decoration:none;">Home</a>
    <span>›</span>
    <span style="color:var(--color-text-main); font-weight:600;">My Orders</span>
  </nav>

  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
    <div>
      <h1 style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--color-text-main); margin:0 0 6px 0;">
        My Orders <span style="font-size:1.1rem; font-family:var(--font-sans); color:var(--color-text-muted); font-weight:500;">(<?= count($orders) ?> <?= count($orders) === 1 ? 'order' : 'orders' ?>)</span>
      </h1>
      <p style="color:var(--color-text-muted); font-size:0.9rem; margin:0;">
        Track package shipments, view delivery addresses, and download tax invoices.
      </p>
    </div>

    <a href="books.php" class="btn btn-secondary btn-sm">
      <span>+ Browse Books</span>
    </a>
  </div>

  <?php if (empty($orders)): ?>
    <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:60px 24px; text-align:center; box-shadow:var(--shadow-sm);">
      <div style="width:72px; height:72px; background:#F8FAFC; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:2.2rem; margin-bottom:16px;">
        📦
      </div>
      <h2 style="font-family:var(--font-serif); font-size:1.5rem; font-weight:700; color:var(--color-text-main); margin-bottom:8px;">
        You haven't placed any orders yet
      </h2>
      <p style="color:var(--color-text-muted); font-size:0.95rem; margin-bottom:24px;">
        Explore our curated catalog of bestselling literature, programming guides, and textbooks.
      </p>
      <a href="books.php" class="btn btn-primary" style="padding:10px 24px;">
        Start Shopping →
      </a>
    </div>

  <?php else: ?>

    <div style="display:flex; flex-direction:column; gap:20px;">
      <?php foreach ($orders as $order): ?>
        <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); overflow:hidden; box-shadow:var(--shadow-sm);">
          
          <!-- Order Card Header -->
          <div style="background:#F8FAFC; border-bottom:1px solid var(--color-card-border); padding:16px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; font-size:0.85rem;">
            <div style="display:flex; gap:24px; flex-wrap:wrap;">
              <div>
                <span style="color:var(--color-text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">ORDER PLACED</span>
                <span style="font-weight:600; color:var(--color-text-main);"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
              </div>
              <div>
                <span style="color:var(--color-text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">TOTAL AMOUNT</span>
                <span style="font-weight:700; color:var(--color-text-main);"><?= formatPrice((float)$order['total_amount']) ?></span>
              </div>
              <div>
                <span style="color:var(--color-text-muted); display:block; font-size:0.75rem; text-transform:uppercase;">SHIP TO</span>
                <span style="font-weight:600; color:var(--color-text-main);"><?= e($order['recipient_name'] ?: ($currentUser['name'] ?? 'Customer')) ?></span>
              </div>
            </div>

            <div style="display:flex; align-items:center; gap:12px;">
              <span style="font-family:var(--font-mono); font-size:0.85rem; font-weight:700; color:var(--color-primary);">
                #BN-<?= str_pad((string)$order['id'], 6, '0', STR_PAD_LEFT) ?>
              </span>
              <a href="order-success.php?id=<?= $order['id'] ?>" class="btn btn-outline btn-sm" style="font-size:0.78rem; padding:4px 10px;">
                View Invoice
              </a>
            </div>
          </div>

          <!-- Order Card Body -->
          <div style="padding:20px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
              <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#10B981;"></span>
                <span style="font-weight:700; font-size:0.95rem; color:#059669; text-transform:uppercase; letter-spacing:0.03em;">
                  <?= e($order['order_status']) ?>
                </span>
                <span style="color:var(--color-text-muted); font-size:0.82rem;">
                  • Payment: <?= e($order['payment_method']) ?> (<?= e($order['payment_status']) ?>)
                </span>
              </div>
              <div style="font-size:0.85rem; color:var(--color-text-muted);">
                <?= $order['total_items'] ?> <?= $order['total_items'] == 1 ? 'item' : 'items' ?> ordered for delivery to <?= e($order['city']) ?> (<?= e($order['pincode']) ?>)
              </div>
            </div>

            <div>
              <a href="order-success.php?id=<?= $order['id'] ?>" class="btn btn-secondary btn-sm">
                <span>🚚 Track Order</span>
              </a>
            </div>
          </div>

        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
