<?php
/**
 * BookNest - Administrator Dashboard
 * Real-time Store KPIs, Low Stock Warnings & Recent Order Pipeline
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$pageTitle = "Admin Dashboard - BookNest";
$pdo = Database::getConnection();

// Calculate live metrics
$totalBooks = 32;
$totalStock = 450;
$totalOrders = 1;
$totalRevenue = 1298.00;
$totalUsers = 2;
$lowStockCount = 0;
$recentOrders = [];
$lowStockBooks = [];

if ($pdo) {
    try {
        $totalBooks = (int)$pdo->query("SELECT COUNT(*) FROM books WHERE status = 'active'")->fetchColumn();
        $totalStock = (int)$pdo->query("SELECT SUM(stock) FROM books WHERE status = 'active'")->fetchColumn();
        $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $totalRevenue = (float)$pdo->query("SELECT IFNULL(SUM(total_amount), 0) FROM orders WHERE payment_status = 'Paid'")->fetchColumn();
        $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

        // Low stock books (< 10)
        $lowStockStmt = $pdo->query("
            SELECT b.*, a.name AS author_name 
            FROM books b 
            JOIN authors a ON b.author_id = a.id 
            WHERE b.stock < 10 AND b.status = 'active'
            ORDER BY b.stock ASC 
            LIMIT 5
        ");
        $lowStockBooks = $lowStockStmt->fetchAll();
        $lowStockCount = count($lowStockBooks);

        // Recent 5 orders
        $ordStmt = $pdo->query("
            SELECT o.*, u.name AS user_name, u.email AS user_email,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.id DESC
            LIMIT 5
        ");
        $recentOrders = $ordStmt->fetchAll();
    } catch (Exception $e) {
        error_log("Admin metrics error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
  
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-brand">
      <span>📖</span>
      <span>BookNest Admin</span>
    </div>

    <ul class="admin-nav">
      <li class="admin-nav-item"><a href="index.php" class="active">📊 Dashboard</a></li>
      <li class="admin-nav-item"><a href="books.php">📚 Books Catalog</a></li>
      <li class="admin-nav-item"><a href="inventory.php">📋 Inventory & Stock</a></li>
      <li class="admin-nav-item"><a href="orders.php">📦 Campus Orders</a></li>
      <li class="admin-nav-item"><a href="analytics.php">📈 Store Analytics</a></li>
      <li class="admin-nav-item" style="margin-top:auto;"><a href="../index.php">← Back to Storefront</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">
    <div class="admin-header">
      <div>
        <h1 style="font-size:1.8rem; font-weight:800; color:#0F172A; margin:0 0 4px 0;">Store Management Overview</h1>
        <p style="color:#64748B; margin:0; font-size:0.9rem;">Real-time inventory levels, revenue analytics, and order fulfillment pipeline.</p>
      </div>
      <div style="display:flex; gap:10px;">
        <a href="books.php?action=new" class="btn btn-primary btn-sm" style="padding:8px 16px;">
          <span>+ Add New Book</span>
        </a>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="admin-stats-grid">
      <div class="stat-card">
        <div class="label">Catalog Books</div>
        <div class="value"><?= $totalBooks ?></div>
        <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">15 academic & literary genres</div>
      </div>
      <div class="stat-card">
        <div class="label">Warehouse Units</div>
        <div class="value"><?= number_format($totalStock) ?></div>
        <div style="font-size:0.75rem; color:<?= $lowStockCount > 0 ? '#D97706' : '#059669' ?>; margin-top:4px;">
          <?= $lowStockCount ?> titles low in stock
        </div>
      </div>
      <div class="stat-card">
        <div class="label">Campus Orders</div>
        <div class="value"><?= $totalOrders ?></div>
        <div style="font-size:0.75rem; color:#059669; margin-top:4px;">Live tracking active</div>
      </div>
      <div class="stat-card">
        <div class="label">Gross Revenue</div>
        <div class="value">₹<?= number_format($totalRevenue, 2) ?></div>
        <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">Verified UPI & Card payments</div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 340px; gap:24px; align-items:start;">
      
      <!-- Recent Orders Table -->
      <div class="admin-table-container">
        <div style="padding:18px 24px; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
          <h3 style="margin:0; font-size:1.1rem; font-weight:700;">Recent Campus Orders</h3>
          <a href="orders.php" style="font-size:0.82rem; font-weight:600; color:var(--color-primary); text-decoration:none;">View all orders →</a>
        </div>

        <?php if (empty($recentOrders)): ?>
          <div style="padding:40px; text-align:center; color:#64748B;">No orders placed yet.</div>
        <?php else: ?>
          <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.88rem;">
            <thead>
              <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#64748B;">
                <th style="padding:12px 20px;">Order ID</th>
                <th style="padding:12px;">Student</th>
                <th style="padding:12px;">Amount</th>
                <th style="padding:12px;">Status</th>
                <th style="padding:12px 20px; text-align:right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $ord): ?>
                <tr style="border-bottom:1px solid #F1F5F9;">
                  <td style="padding:14px 20px; font-family:var(--font-mono); font-weight:700;">
                    #BN-<?= str_pad((string)$ord['id'], 6, '0', STR_PAD_LEFT) ?>
                  </td>
                  <td style="padding:14px 12px;">
                    <div style="font-weight:600;"><?= e($ord['user_name']) ?></div>
                    <div style="font-size:0.75rem; color:#64748B;"><?= e($ord['user_email']) ?></div>
                  </td>
                  <td style="padding:14px 12px; font-weight:700;">
                    ₹<?= number_format((float)$ord['total_amount'], 2) ?>
                  </td>
                  <td style="padding:14px 12px;">
                    <span style="padding:3px 10px; border-radius:999px; font-size:0.75rem; font-weight:700; background:#ECFDF5; color:#059669;">
                      <?= e($ord['order_status']) ?>
                    </span>
                  </td>
                  <td style="padding:14px 20px; text-align:right;">
                    <a href="orders.php?view=<?= $ord['id'] ?>" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:0.75rem;">
                      Dispatch
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- Low Stock Warnings -->
      <div style="background:#fff; border:1px solid #E2E8F0; border-radius:var(--radius-lg); padding:20px; box-shadow:var(--shadow-sm);">
        <h3 style="font-size:1rem; font-weight:700; margin:0 0 14px 0; display:flex; align-items:center; gap:8px;">
          <span>⚠️</span>
          <span>Stock Replenishment Alerts</span>
        </h3>
        
        <?php if (empty($lowStockBooks)): ?>
          <div style="color:#059669; font-size:0.85rem; padding:12px; background:#ECFDF5; border-radius:8px;">
            ✓ All catalog items have healthy stock levels (&gt;10 copies).
          </div>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach ($lowStockBooks as $lb): ?>
              <div style="padding:10px; background:#FEF3C7; border:1px solid #FDE68A; border-radius:8px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <div style="font-weight:700; font-size:0.85rem; color:#92400E; max-width:180px;" class="truncate-1">
                    <?= e($lb['title']) ?>
                  </div>
                  <div style="font-size:0.75rem; color:#B45309;">
                    Only <?= $lb['stock'] ?> copies remaining
                  </div>
                </div>
                <button 
                  onclick="quickRestock(<?= $lb['id'] ?>, 15)" 
                  class="btn btn-primary btn-sm" 
                  style="font-size:0.75rem; padding:4px 10px; background:#D97706; border-color:#D97706;"
                >
                  +15 Restock
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="inventory.php" style="display:block; text-align:center; font-size:0.82rem; font-weight:600; color:var(--color-primary); margin-top:16px; text-decoration:none;">
          Open Complete Inventory Sheet →
        </a>
      </div>

    </div>

  </main>
</div>

<script>
function quickRestock(bookId, delta) {
  fetch('../api/admin-stock-update.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ book_id: bookId, delta: delta })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('✓ ' + data.message + ' New stock: ' + data.new_stock);
      window.location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  });
}
</script>
</body>
</html>
