<?php
/**
 * BookNest - Global Footer Include
 */
$flash = getFlash();
?>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand column -->
      <div class="footer-col">
        <h3 class="footer-brand-title">BookNest</h3>
        <p class="footer-brand-desc">
          Your neighborhood online bookstore. Explore thousands of curated titles across literature, science, engineering, business, and self-mastery.
        </p>
        <div style="display:flex; gap:10px;">
          <span style="font-size:1.2rem;">📚</span>
          <span style="font-size:1.2rem;">☕</span>
          <span style="font-size:1.2rem;">✨</span>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="footer-col">
        <h4 class="footer-title">Browse</h4>
        <ul class="footer-links">
          <li><a href="books.php">All Books</a></li>
          <li><a href="categories.php">Explore Categories</a></li>
          <li><a href="scanner.php">Camera Book Scanner</a></li>
          <li><a href="books.php?sort=trending">Trending This Week</a></li>
          <li><a href="books.php?sort=newest">New Arrivals</a></li>
        </ul>
      </div>

      <!-- Categories -->
      <div class="footer-col">
        <h4 class="footer-title">Popular Genres</h4>
        <ul class="footer-links">
          <li><a href="books.php?category=fiction">Fiction & Literature</a></li>
          <li><a href="books.php?category=programming">Programming & Code</a></li>
          <li><a href="books.php?category=self-help">Self Improvement</a></li>
          <li><a href="books.php?category=cybersecurity">Cybersecurity</a></li>
          <li><a href="books.php?category=mystery-thriller">Mystery & Thriller</a></li>
        </ul>
      </div>

      <!-- Project & Academic Tech Info -->
      <div class="footer-col">
        <h4 class="footer-title">Academic Tech Stack</h4>
        <p style="font-size: 0.85rem; line-height: 1.6; color: #71717A; margin-bottom: 12px;">
          Built with PHP 8+, MySQL (PDO), Vanilla JavaScript, Responsive CSS, and Apache (XAMPP).
        </p>
        <div style="display: inline-block; padding: 6px 12px; background: #27272A; border-radius: 6px; font-size: 0.8rem; color: #E4E4E7; font-family: var(--font-mono);">
          PHP • MySQL • Apache • JS
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© <?= date('Y') ?> BookNest. All rights reserved. Designed for College Full-Stack Submission.</p>
      <p>Find your next great read.</p>
    </div>
  </div>
</footer>

<!-- Toast Notifications Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- Core JavaScript -->
<script src="assets/js/main.js"></script>
<script src="assets/js/search.js"></script>

<?php if ($flash): ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    showToast(<?= json_encode($flash['message']) ?>, <?= json_encode($flash['type']) ?>);
  });
</script>
<?php endif; ?>

</body>
</html>
