<?php
/**
 * BookNest - Reusable Category Card Component
 * Expects $category array
 */
if (!isset($category) || !is_array($category)) return;

$icons = [
  'Fiction' => '📚',
  'Mystery & Thriller' => '🔍',
  'Romance' => '❤️',
  'Fantasy' => '🧙',
  'Science Fiction' => '🚀',
  'Self Help' => '🧠',
  'Business' => '📈',
  'Technology' => '💻',
  'Programming' => '⚡',
  'Cybersecurity' => '🛡️',
  'History' => '🏛️',
  'Biography' => '✍️',
  'Academic' => '🎓',
  'Children' => '🧸',
  'Comics' => '💥'
];

$icon = $icons[$category['name']] ?? '📖';
$count = $category['book_count'] ?? $category['count'] ?? 0;
$slug = $category['slug'] ?? strtolower(str_replace([' & ', ' '], ['-', '-'], $category['name']));
?>
<a href="books.php?category=<?= urlencode($slug) ?>" class="category-card">
  <div class="category-icon-bubble">
    <span><?= $icon ?></span>
  </div>
  <h3 class="category-name"><?= e($category['name']) ?></h3>
  <span class="category-count"><?= $count > 0 ? (int)$count . ' books' : 'Browse collection' ?></span>
</a>
