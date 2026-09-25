<?php
/**
 * BookNest - Admin Inventory Management
 * Live stock levels, replenishment warnings & inline quantity adjustment
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$pageTitle = "Inventory Management - BookNest Admin";
$pdo = Database::getConnection();

$filter = trim($_GET['status'] ?? 'all');

$sql = "
    SELECT b.*, a.name AS author_name, c.name AS category_name,
           (b.stock * b.price) AS inventory_value
    FROM books b 
    JOIN authors a ON b.author_id = a.id 
    JOIN categories c ON b.category_id = c.id 
    WHERE b.status = 'active'
";

if ($filter === 'low') {
    $sql .= " AND b.stock < 10 AND b.stock > 0";
} else if ($filter === 'out') {
    $sql .= " AND b.stock = 0";
}

$sql .= " ORDER BY b.stock ASC, b.title ASC";

$inventory = [];
$totalValuation = 0.00;
$totalUnits = 0;
$lowCount = 0;
$outCount = 0;

if ($pdo) {
    try {
        $stmt = $pdo->query($sql);
        $inventory = $stmt->fetchAll();

        // Calculate summary KPIs
        $stats = $pdo->query("
            SELECT SUM(stock * price) as total_val, SUM(stock) as total_units,
                   SUM(CASE WHEN stock < 10 AND stock > 0 THEN 1 ELSE 0 END) as low_cnt,
                   SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_cnt
            FROM books WHERE status = 'active'
        ")->fetch();

        $totalValuation = (float)($stats['total_val'] ?? 0);
        $totalUnits = (int)($stats['total_units'] ?? 0);
        $lowCount = (int)($stats['low_cnt'] ?? 0);
        $outCount = (int)($stats['out_cnt'] ?? 0);
    } catch (Exception $e) {
        error_log("Inventory query error: " . $e->getMessage());
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
      <li class="admin-nav-item"><a href="index.php">📊 Dashboard</a></li>
      <li class="admin-nav-item"><a href="books.php">📚 Books Catalog</a></li>
      <li class="admin-nav-item"><a href="inventory.php" class="active">📋 Inventory & Stock</a></li>
      <li class="admin-nav-item"><a href="orders.php">📦 Campus Orders</a></li>
      <li class="admin-nav-item"><a href="analytics.php">📈 Store Analytics</a></li>
      <li class="admin-nav-item" style="margin-top:auto;"><a href="../index.php">← Back to Storefront</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">
    <div class="admin-header">
      <div>
        <h1 style="font-size:1.8rem; font-weight:800; color:#0F172A; margin:0 0 4px 0;">Warehouse Inventory</h1>
        <p style="color:#64748B; margin:0; font-size:0.9rem;">Real-time stock valuation, low inventory threshold alerts, and inline batch restock.</p>
      </div>
    </div>

    <!-- Inventory KPI Cards -->
    <div class="admin-stats-grid">
      <div class="stat-card">
        <div class="label">Total Stock Valuation</div>
        <div class="value">₹<?= number_format($totalValuation, 2) ?></div>
        <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">Across <?= $totalUnits ?> printed units</div>
      </div>
      <div class="stat-card">
        <div class="label">Healthy Inventory</div>
        <div class="value"><?= count($inventory) - $lowCount - $outCount ?></div>
        <div style="font-size:0.75rem; color:#059669; margin-top:4px;">Adequate campus reserves</div>
      </div>
      <div class="stat-card">
        <div class="label">Low Stock Alerts</div>
        <div class="value" style="color:#D97706;"><?= $lowCount ?></div>
        <div style="font-size:0.75rem; color:#D97706; margin-top:4px;">Under 10 copies remaining</div>
      </div>
      <div class="stat-card">
        <div class="label">Out of Stock</div>
        <div class="value" style="color:<?= $outCount > 0 ? '#DC2626' : '#64748B' ?>;"><?= $outCount ?></div>
        <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">Requires publisher reorder</div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
      <div style="display:flex; gap:8px;">
        <a href="inventory.php" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">All Stock</a>
        <a href="inventory.php?status=low" class="btn <?= $filter === 'low' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Low Stock (<?= $lowCount ?>)</a>
        <a href="inventory.php?status=out" class="btn <?= $filter === 'out' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">Out of Stock (<?= $outCount ?>)</a>
      </div>
      <div style="font-size:0.85rem; color:#64748B;">
        Showing <strong><?= count($inventory) ?></strong> inventory line items
      </div>
    </div>

    <!-- Inventory Table with Steppers -->
    <div class="admin-table-container">
      <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.88rem;">
        <thead>
          <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#64748B;">
            <th style="padding:14px 20px;">Book Title</th>
            <th style="padding:14px 12px;">Category</th>
            <th style="padding:14px 12px;">Unit Price</th>
            <th style="padding:14px 12px;">Inventory Value</th>
            <th style="padding:14px 12px;">Health Status</th>
            <th style="padding:14px 20px; text-align:right;">Adjust Quantity</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inventory as $item): ?>
            <tr style="border-bottom:1px solid #F1F5F9;">
              
              <td style="padding:14px 20px;">
                <div style="font-weight:700; color:#0F172A; max-width:280px;" class="truncate-1">
                  <?= e($item['title']) ?>
                </div>
                <div style="font-size:0.75rem; color:#64748B;">
                  ISBN: <?= e($item['isbn']) ?>
                </div>
              </td>

              <td style="padding:14px 12px; font-size:0.8rem; color:#475569;">
                <?= e($item['category_name']) ?>
              </td>

              <td style="padding:14px 12px; font-weight:600;">
                ₹<?= number_format((float)$item['price'], 2) ?>
              </td>

              <td style="padding:14px 12px; font-weight:700; color:#0F172A;">
                ₹<?= number_format((float)$item['inventory_value'], 2) ?>
              </td>

              <td style="padding:14px 12px;">
                <?php if ($item['stock'] == 0): ?>
                  <span style="background:#FEE2E2; color:#DC2626; padding:3px 10px; border-radius:999px; font-size:0.75rem; font-weight:700;">
                    Out of Stock
                  </span>
                <?php elseif ($item['stock'] < 10): ?>
                  <span style="background:#FEF3C7; color:#D97706; padding:3px 10px; border-radius:999px; font-size:0.75rem; font-weight:700;">
                    Low Stock (<?= $item['stock'] ?> left)
                  </span>
                <?php else: ?>
                  <span style="background:#ECFDF5; color:#059669; padding:3px 10px; border-radius:999px; font-size:0.75rem; font-weight:700;">
                    Healthy (<?= $item['stock'] ?> units)
                  </span>
                <?php endif; ?>
              </td>

              <td style="padding:14px 20px; text-align:right;">
                <div style="display:inline-flex; align-items:center; border:1px solid #CBD5E1; border-radius:6px; overflow:hidden; background:#fff;">
                  <button 
                    onclick="adjustStock(<?= $item['id'] ?>, -1)" 
                    style="width:28px; height:28px; border:none; background:#F8FAFC; cursor:pointer; font-weight:700;"
                  >-</button>
                  <span id="stock_val_<?= $item['id'] ?>" style="width:40px; text-align:center; font-weight:700; font-size:0.85rem;">
                    <?= $item['stock'] ?>
                  </span>
                  <button 
                    onclick="adjustStock(<?= $item['id'] ?>, 1)" 
                    style="width:28px; height:28px; border:none; background:#F8FAFC; cursor:pointer; font-weight:700;"
                  >+</button>
                </div>
                <button 
                  onclick="adjustStock(<?= $item['id'] ?>, 10)" 
                  class="btn btn-outline btn-sm" 
                  style="padding:3px 8px; font-size:0.75rem; margin-left:6px;"
                >
                  +10
                </button>
              </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<script>
function adjustStock(bookId, delta) {
  fetch('../api/admin-stock-update.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ book_id: bookId, delta: delta })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const el = document.getElementById('stock_val_' + bookId);
      if (el) el.textContent = data.new_stock;
      window.location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  });
}
</script>
</body>
</html>
