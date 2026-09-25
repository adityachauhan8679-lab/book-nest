<?php
/**
 * BookNest - Administrator Campus Orders & Fulfillment Pipeline
 * Status management, customer dispatch manifests, item inspection & tracking updates
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$pageTitle = "Orders Management - BookNest Admin";
$pdo = Database::getConnection();

$statusFilter = trim($_GET['status'] ?? 'all');
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT o.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS items_count,
           (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS total_units
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE 1=1
";
$params = [];

if ($statusFilter !== 'all' && !empty($statusFilter)) {
    $sql .= " AND o.order_status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $sql .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR o.shipping_phone LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY o.id DESC";

$orders = [];
$stats = [
    'total' => 0,
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'revenue' => 0.00
];

if ($pdo) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        // Calculate pipeline metrics
        $kpiStmt = $pdo->query("
            SELECT 
                COUNT(*) as total_cnt,
                SUM(CASE WHEN order_status = 'Pending' THEN 1 ELSE 0 END) as pending_cnt,
                SUM(CASE WHEN order_status = 'Processing' OR order_status = 'Confirmed' THEN 1 ELSE 0 END) as processing_cnt,
                SUM(CASE WHEN order_status = 'Shipped' THEN 1 ELSE 0 END) as shipped_cnt,
                SUM(CASE WHEN order_status = 'Delivered' THEN 1 ELSE 0 END) as delivered_cnt,
                SUM(CASE WHEN payment_status = 'Paid' THEN total_amount ELSE 0 END) as revenue_val
            FROM orders
        ");
        if ($kpiStmt) {
            $row = $kpiStmt->fetch();
            $stats['total'] = (int)($row['total_cnt'] ?? 0);
            $stats['pending'] = (int)($row['pending_cnt'] ?? 0);
            $stats['processing'] = (int)($row['processing_cnt'] ?? 0);
            $stats['shipped'] = (int)($row['shipped_cnt'] ?? 0);
            $stats['delivered'] = (int)($row['delivered_cnt'] ?? 0);
            $stats['revenue'] = (float)($row['revenue_val'] ?? 0.00);
        }
    } catch (Exception $e) {
        error_log("Admin orders query error: " . $e->getMessage());
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
      <li class="admin-nav-item"><a href="inventory.php">📋 Inventory & Stock</a></li>
      <li class="admin-nav-item"><a href="orders.php" class="active">📦 Campus Orders</a></li>
      <li class="admin-nav-item"><a href="analytics.php">📈 Store Analytics</a></li>
      <li class="admin-nav-item" style="margin-top:auto;"><a href="../index.php">← Back to Storefront</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="admin-main">
    <div class="admin-header">
      <div>
        <h1 style="font-size:1.8rem; font-weight:800; color:#0F172A; margin:0 0 4px 0;">Campus Order Fulfillment</h1>
        <p style="color:#64748B; margin:0; font-size:0.9rem;">Inspect student shipments, advance dispatch status, and manage parcel delivery.</p>
      </div>
      <div>
        <button onclick="window.print()" class="btn btn-outline" style="padding:8px 16px;">
          <span>🖨️ Print Batch Manifest</span>
        </button>
      </div>
    </div>

    <!-- Order Stats Cards -->
    <div class="admin-stats-grid">
      <div class="stat-card">
        <div class="label">Total Orders Placed</div>
        <div class="value"><?= $stats['total'] ?></div>
        <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">All-time store volume</div>
      </div>
      <div class="stat-card">
        <div class="label">Awaiting Dispatch</div>
        <div class="value" style="color:<?= $stats['pending'] > 0 ? '#D97706' : '#0F172A' ?>;"><?= $stats['pending'] ?></div>
        <div style="font-size:0.75rem; color:#D97706; margin-top:4px;">Requires warehouse picking</div>
      </div>
      <div class="stat-card">
        <div class="label">In Transit / Shipped</div>
        <div class="value" style="color:#2563EB;"><?= $stats['shipped'] ?></div>
        <div style="font-size:0.75rem; color:#2563EB; margin-top:4px;">Campus delivery agents active</div>
      </div>
      <div class="stat-card">
        <div class="label">Completed Deliveries</div>
        <div class="value" style="color:#059669;"><?= $stats['delivered'] ?></div>
        <div style="font-size:0.75rem; color:#059669; margin-top:4px;">Fulfilled & confirmed by student</div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:var(--radius-lg); padding:16px 20px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
      <form method="GET" action="orders.php" style="display:flex; gap:10px; flex:1; max-width:540px;">
        <input 
          type="text" 
          name="q" 
          value="<?= e($search) ?>" 
          placeholder="Search by order #, student name, or email..." 
          style="flex:1; border:1px solid #CBD5E1; border-radius:var(--radius-md); padding:8px 12px; font-size:0.85rem;"
        >
        <select name="status" style="border:1px solid #CBD5E1; border-radius:var(--radius-md); padding:8px 12px; font-size:0.85rem;">
          <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
          <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="Confirmed" <?= $statusFilter === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
          <option value="Processing" <?= $statusFilter === 'Processing' ? 'selected' : '' ?>>Processing</option>
          <option value="Shipped" <?= $statusFilter === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
          <option value="Delivered" <?= $statusFilter === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
          <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm" style="padding:8px 16px;">Filter</button>
      </form>

      <div style="font-size:0.85rem; color:#64748B; font-weight:600;">
        Found <strong><?= count($orders) ?></strong> orders
      </div>
    </div>

    <!-- Orders Table -->
    <div class="admin-table-container">
      <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.88rem;">
        <thead>
          <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#64748B;">
            <th style="padding:14px 20px;">Order #</th>
            <th style="padding:14px 12px;">Student & Contact</th>
            <th style="padding:14px 12px;">Items & Units</th>
            <th style="padding:14px 12px;">Amount & Payment</th>
            <th style="padding:14px 12px;">Fulfillment Status</th>
            <th style="padding:14px 12px;">Date</th>
            <th style="padding:14px 20px; text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7" style="padding:40px; text-align:center; color:#64748B;">
                No orders match your current filter query.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $ord): 
              $stColor = '#475569';
              $stBg = '#F1F5F9';
              if ($ord['order_status'] === 'Pending') { $stColor = '#D97706'; $stBg = '#FEF3C7'; }
              else if ($ord['order_status'] === 'Confirmed' || $ord['order_status'] === 'Processing') { $stColor = '#2563EB'; $stBg = '#DBEAFE'; }
              else if ($ord['order_status'] === 'Shipped') { $stColor = '#7C3AED'; $stBg = '#EDE9FE'; }
              else if ($ord['order_status'] === 'Delivered') { $stColor = '#059669'; $stBg = '#D1FAE5'; }
              else if ($ord['order_status'] === 'Cancelled') { $stColor = '#DC2626'; $stBg = '#FEE2E2'; }
            ?>
              <tr style="border-bottom:1px solid #F1F5F9;">
                <td style="padding:14px 20px; font-family:var(--font-mono); font-weight:700;">
                  #<?= e($ord['order_number'] ?: ('BN-' . str_pad((string)$ord['id'], 6, '0', STR_PAD_LEFT))) ?>
                </td>
                <td style="padding:14px 12px;">
                  <div style="font-weight:700; color:#0F172A;"><?= e($ord['shipping_name'] ?: $ord['user_name']) ?></div>
                  <div style="font-size:0.75rem; color:#64748B;"><?= e($ord['user_email']) ?></div>
                  <div style="font-size:0.75rem; color:#64748B;">📞 <?= e($ord['shipping_phone'] ?: $ord['user_phone'] ?: 'N/A') ?></div>
                </td>
                <td style="padding:14px 12px;">
                  <span style="font-weight:600;"><?= $ord['items_count'] ?> titles</span>
                  <div style="font-size:0.75rem; color:#64748B;"><?= $ord['total_units'] ?> total books</div>
                </td>
                <td style="padding:14px 12px;">
                  <div style="font-weight:800; color:#0F172A;">₹<?= number_format((float)$ord['total_amount'], 2) ?></div>
                  <span style="font-size:0.75rem; padding:2px 6px; border-radius:4px; font-weight:600; background:<?= $ord['payment_status'] === 'Paid' ? '#ECFDF5' : '#FEF3C7' ?>; color:<?= $ord['payment_status'] === 'Paid' ? '#059669' : '#D97706' ?>;">
                    <?= e($ord['payment_method']) ?> • <?= e($ord['payment_status']) ?>
                  </span>
                </td>
                <td style="padding:14px 12px;">
                  <select 
                    onchange="updateOrderStatus(<?= $ord['id'] ?>, this.value)"
                    style="padding:4px 8px; border-radius:6px; font-size:0.78rem; font-weight:700; color:<?= $stColor ?>; background:<?= $stBg ?>; border:1px solid rgba(0,0,0,0.1); cursor:pointer;"
                  >
                    <option value="Pending" <?= $ord['order_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Confirmed" <?= $ord['order_status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipped" <?= $ord['order_status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Delivered" <?= $ord['order_status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $ord['order_status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                  </select>
                </td>
                <td style="padding:14px 12px; font-size:0.8rem; color:#64748B;">
                  <?= date('M d, Y', strtotime($ord['created_at'])) ?>
                  <div style="font-size:0.72rem;"><?= date('h:i A', strtotime($ord['created_at'])) ?></div>
                </td>
                <td style="padding:14px 20px; text-align:right;">
                  <button 
                    onclick='viewOrderDetails(<?= json_encode($ord) ?>)' 
                    class="btn btn-outline btn-sm" 
                    style="padding:4px 10px; font-size:0.75rem;"
                  >
                    Details & Manifest
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<!-- Order Detail Modal -->
<div id="orderModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-blur-sm; z-index:100; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:var(--radius-xl); max-width:720px; width:100%; max-height:90vh; overflow-y:auto; padding:28px; position:relative; box-shadow:var(--shadow-xl);">
    
    <button onclick="closeOrderModal()" style="position:absolute; top:20px; right:20px; border:none; background:#F1F5F9; border-radius:50%; width:32px; height:32px; cursor:pointer; font-weight:700;">✕</button>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
      <div>
        <h2 id="modalOrderNumber" style="font-family:var(--font-serif); font-size:1.5rem; font-weight:700; margin:0;">Order #BN-000000</h2>
        <p id="modalOrderDate" style="color:#64748B; font-size:0.82rem; margin:2px 0 0 0;">Placed on ...</p>
      </div>
      <div id="modalStatusBadge"></div>
    </div>

    <!-- Customer & Shipping Summary Grid -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:var(--radius-md); padding:16px; margin-bottom:20px;">
      <div>
        <div style="font-size:0.75rem; font-weight:700; color:#64748B; text-transform:uppercase;">Recipient & Contact</div>
        <div id="modalRecipientName" style="font-weight:700; color:#0F172A; margin-top:4px;"></div>
        <div id="modalRecipientContact" style="font-size:0.82rem; color:#475569;"></div>
      </div>
      <div>
        <div style="font-size:0.75rem; font-weight:700; color:#64748B; text-transform:uppercase;">Hostel & Campus Address</div>
        <div id="modalAddress" style="font-size:0.82rem; color:#334155; margin-top:4px; line-height:1.4;"></div>
      </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; padding-top:16px; border-top:1px solid #E2E8F0;">
      <div>
        <div style="font-size:0.75rem; color:#64748B;">Payment Mode & Total</div>
        <div id="modalPaymentInfo" style="font-weight:800; font-size:1.1rem; color:#0F172A;"></div>
      </div>
      <div style="display:flex; gap:10px;">
        <button onclick="window.print()" class="btn btn-secondary btn-sm" style="padding:8px 16px;">
          Print Packaging Slip
        </button>
        <button onclick="closeOrderModal()" class="btn btn-primary btn-sm" style="padding:8px 18px;">
          Done
        </button>
      </div>
    </div>

  </div>
</div>

<script>
function updateOrderStatus(orderId, newStatus) {
  fetch('../api/admin-order-status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ order_id: orderId, status: newStatus })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('✓ ' + data.message);
      window.location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  });
}

function viewOrderDetails(ord) {
  const num = ord.order_number || ('BN-' + String(ord.id).padStart(6, '0'));
  document.getElementById('modalOrderNumber').textContent = 'Order #' + num;
  document.getElementById('modalOrderDate').textContent = 'Placed on ' + new Date(ord.created_at).toLocaleString();
  document.getElementById('modalRecipientName').textContent = ord.shipping_name || ord.user_name;
  document.getElementById('modalRecipientContact').innerHTML = (ord.user_email || '') + '<br>' + (ord.shipping_phone || ord.user_phone || '');
  
  const addr = [ord.shipping_address, ord.shipping_city, ord.shipping_state, ord.shipping_zip].filter(Boolean).join(', ');
  document.getElementById('modalAddress').textContent = addr || 'Campus Store Pickup';
  
  document.getElementById('modalPaymentInfo').textContent = '₹' + parseFloat(ord.total_amount).toFixed(2) + ' (' + ord.payment_method + ')';
  
  document.getElementById('orderModal').style.display = 'flex';
}

function closeOrderModal() {
  document.getElementById('orderModal').style.display = 'none';
}
</script>
</body>
</html>
