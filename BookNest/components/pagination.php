<?php
/**
 * BookNest - Pagination Component
 * Expects $currentPage, $totalPages, $baseUrl
 */
if (!isset($totalPages) || $totalPages <= 1) return;
?>
<nav class="pagination-nav" aria-label="Book pagination">
  <ul class="pagination-list" style="display:flex; justify-content:center; gap:8px; list-style:none; margin: 40px 0;">
    <?php if ($currentPage > 1): ?>
      <li>
        <a href="<?= e($baseUrl) ?>page=<?= $currentPage - 1 ?>" class="btn btn-secondary btn-sm">‹ Prev</a>
      </li>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <li>
        <a 
          href="<?= e($baseUrl) ?>page=<?= $p ?>" 
          class="btn <?= $p === $currentPage ? 'btn-primary' : 'btn-secondary' ?> btn-sm"
        >
          <?= $p ?>
        </a>
      </li>
    <?php endfor; ?>

    <?php if ($currentPage < $totalPages): ?>
      <li>
        <a href="<?= e($baseUrl) ?>page=<?= $currentPage + 1 ?>" class="btn btn-secondary btn-sm">Next ›</a>
      </li>
    <?php endif; ?>
  </ul>
</nav>
