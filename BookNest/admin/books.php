<?php
/**
 * BookNest - Admin Books Management & Catalog CRUD
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$pageTitle = "Manage Books - BookNest Admin";
$pdo = Database::getConnection();

$search = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');

$sql = "
    SELECT b.*, a.name AS author_name, c.name AS category_name 
    FROM books b 
    JOIN authors a ON b.author_id = a.id 
    JOIN categories c ON b.category_id = c.id 
    WHERE b.status = 'active'
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR a.name LIKE ? OR b.isbn LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($categoryFilter)) {
    $sql .= " AND c.slug = ?";
    $params[] = $categoryFilter;
}

$sql .= " ORDER BY b.id DESC";

$books = [];
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll();

        $categories = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC")->fetchAll();
    } catch (Exception $e) {
        error_log("Admin books query error: " . $e->getMessage());
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
      <li class="admin-nav-item"><a href="books.php" class="active">📚 Books Catalog</a></li>
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
        <h1 style="font-size:1.8rem; font-weight:800; color:#0F172A; margin:0 0 4px 0;">Books Catalog Management</h1>
        <p style="color:#64748B; margin:0; font-size:0.9rem;">Add new editions, edit metadata, update prices, or remove archived titles.</p>
      </div>
      <div>
        <button onclick="openBookModal()" class="btn btn-primary" style="padding:10px 20px;">
          <span>+ Add New Book</span>
        </button>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div style="background:#fff; border:1px solid #E2E8F0; border-radius:var(--radius-lg); padding:16px 20px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
      <form method="GET" action="books.php" style="display:flex; gap:10px; flex:1; max-width:540px;">
        <input 
          type="text" 
          name="q" 
          value="<?= e($search) ?>" 
          placeholder="Search by title, author, or ISBN..." 
          style="flex:1; border:1px solid #CBD5E1; border-radius:var(--radius-md); padding:8px 12px; font-size:0.85rem;"
        >
        <select name="category" style="border:1px solid #CBD5E1; border-radius:var(--radius-md); padding:8px 12px; font-size:0.85rem;">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= e($cat['slug']) ?>" <?= $categoryFilter === $cat['slug'] ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary btn-sm" style="padding:8px 16px;">Filter</button>
      </form>

      <div style="font-size:0.85rem; color:#64748B; font-weight:600;">
        Showing <strong><?= count($books) ?></strong> titles
      </div>
    </div>

    <!-- Books Table -->
    <div class="admin-table-container">
      <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.88rem;">
        <thead>
          <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0; color:#64748B;">
            <th style="padding:14px 20px;">Book</th>
            <th style="padding:14px 12px;">Category</th>
            <th style="padding:14px 12px;">ISBN</th>
            <th style="padding:14px 12px;">Price & Discount</th>
            <th style="padding:14px 12px;">Stock</th>
            <th style="padding:14px 20px; text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($books as $b): 
            $fPrice = calculateDiscountPrice((float)$b['price'], (float)$b['discount']);
          ?>
            <tr style="border-bottom:1px solid #F1F5F9;">
              
              <td style="padding:14px 20px; display:flex; align-items:center; gap:12px;">
                <div style="width:42px; height:56px; border-radius:6px; overflow:hidden; border:1px solid #E2E8F0; flex-shrink:0;">
                  <img src="<?= e($b['cover_image']) ?>" alt="<?= e($b['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
                </div>
                <div>
                  <div style="font-weight:700; color:#0F172A; max-width:260px;" class="truncate-1">
                    <?= e($b['title']) ?>
                  </div>
                  <div style="font-size:0.75rem; color:#64748B;">
                    By <?= e($b['author_name']) ?>
                  </div>
                </div>
              </td>

              <td style="padding:14px 12px;">
                <span style="background:#F1F5F9; color:#475569; padding:3px 8px; border-radius:6px; font-size:0.75rem; font-weight:600;">
                  <?= e($b['category_name']) ?>
                </span>
              </td>

              <td style="padding:14px 12px; font-family:var(--font-mono); font-size:0.82rem; color:#475569;">
                <?= e($b['isbn']) ?>
              </td>

              <td style="padding:14px 12px;">
                <span style="font-weight:800; color:#0F172A;">₹<?= $fPrice ?></span>
                <?php if ($b['discount'] > 0): ?>
                  <span style="font-size:0.75rem; color:#DC2626; margin-left:4px;">(<?= (int)$b['discount'] ?>% off)</span>
                <?php endif; ?>
              </td>

              <td style="padding:14px 12px;">
                <span style="font-weight:700; color:<?= $b['stock'] < 10 ? '#D97706' : '#059669' ?>;">
                  <?= $b['stock'] ?> units
                </span>
              </td>

              <td style="padding:14px 20px; text-align:right;">
                <div style="display:inline-flex; gap:6px;">
                  <button 
                    onclick='editBook(<?= json_encode($b) ?>)' 
                    class="btn btn-outline btn-sm" 
                    style="padding:4px 10px; font-size:0.75rem;"
                  >
                    Edit
                  </button>
                  <button 
                    onclick="deleteBook(<?= $b['id'] ?>, '<?= addslashes($b['title']) ?>')" 
                    class="btn btn-outline btn-sm" 
                    style="padding:4px 10px; font-size:0.75rem; color:#DC2626; border-color:#FCA5A5;"
                  >
                    Delete
                  </button>
                </div>
              </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<!-- Modal for Book Add / Edit -->
<div id="bookModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-blur-sm; z-index:100; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:var(--radius-xl); max-width:640px; width:100%; max-height:90vh; overflow-y:auto; padding:28px; position:relative; box-shadow:var(--shadow-xl);">
    
    <button onclick="closeBookModal()" style="position:absolute; top:20px; right:20px; border:none; background:#F1F5F9; border-radius:50%; width:32px; height:32px; cursor:pointer; font-weight:700;">✕</button>

    <h2 id="modalTitle" style="font-family:var(--font-serif); font-size:1.5rem; font-weight:700; margin:0 0 18px 0;">Add Book to Catalog</h2>

    <form id="bookForm" onsubmit="saveBook(event)" style="display:flex; flex-direction:column; gap:16px;">
      <input type="hidden" id="bookId" name="id" value="0">

      <div>
        <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Book Title *</label>
        <input type="text" id="bTitle" name="title" required style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Author Name *</label>
          <input type="text" id="bAuthor" name="author" required placeholder="e.g. James Clear" style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
        </div>

        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Category *</label>
          <select id="bCategory" name="category_id" required style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">ISBN Code *</label>
          <input type="text" id="bIsbn" name="isbn" required placeholder="9780..." style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
        </div>
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Price (₹) *</label>
          <input type="number" step="0.01" id="bPrice" name="price" required placeholder="499.00" style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
        </div>
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Discount (%)</label>
          <input type="number" id="bDiscount" name="discount" value="0" min="0" max="90" style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Warehouse Stock *</label>
          <input type="number" id="bStock" name="stock" required value="25" min="0" style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
        </div>
        <div>
          <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Publisher</label>
          <input type="text" id="bPublisher" name="publisher" placeholder="Prentice Hall" style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
        </div>
      </div>

      <div>
        <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Cover Image URL</label>
        <input type="url" id="bCover" name="cover_image" placeholder="https://images.unsplash.com/..." style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem;">
      </div>

      <div>
        <label style="display:block; font-size:0.8rem; font-weight:700; margin-bottom:4px;">Synopsis & Course Notes</label>
        <textarea id="bDesc" name="description" rows="3" style="width:100%; padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; font-size:0.9rem; resize:vertical;"></textarea>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:10px;">
        <button type="button" onclick="closeBookModal()" class="btn btn-secondary btn-sm" style="padding:8px 16px;">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm" style="padding:8px 20px;">Save Book Record</button>
      </div>
    </form>

  </div>
</div>

<script>
function openBookModal() {
  document.getElementById('modalTitle').textContent = 'Add Book to Catalog';
  document.getElementById('bookForm').reset();
  document.getElementById('bookId').value = '0';
  document.getElementById('bookModal').style.display = 'flex';
}

function closeBookModal() {
  document.getElementById('bookModal').style.display = 'none';
}

function editBook(b) {
  document.getElementById('modalTitle').textContent = 'Edit Book Record';
  document.getElementById('bookId').value = b.id;
  document.getElementById('bTitle').value = b.title;
  document.getElementById('bAuthor').value = b.author_name;
  document.getElementById('bCategory').value = b.category_id;
  document.getElementById('bIsbn').value = b.isbn;
  document.getElementById('bPrice').value = b.price;
  document.getElementById('bDiscount').value = b.discount;
  document.getElementById('bStock').value = b.stock;
  document.getElementById('bPublisher').value = b.publisher || '';
  document.getElementById('bCover').value = b.cover_image;
  document.getElementById('bDesc').value = b.description || '';
  document.getElementById('bookModal').style.display = 'flex';
}

function saveBook(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  fetch('../api/admin-book-save.php', {
    method: 'POST',
    body: formData
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

function deleteBook(id, title) {
  if (!confirm(`Are you sure you want to delete "${title}" from the store catalog?`)) return;

  fetch('../api/admin-book-delete.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: id })
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
</script>
</body>
</html>
