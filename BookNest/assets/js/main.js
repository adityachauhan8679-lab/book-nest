/**
 * BookNest - Primary Client JavaScript
 * Handles Toast, Mobile Menu, Wishlist & Cart quick actions
 */

document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initStickyHeader();
  initWishlistButtons();
  initAddToCartButtons();
});

// Toast notification helper
window.showToast = function(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <span>${type === 'success' ? '✓' : 'ℹ'}</span>
    <div>${message}</div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
};

// Mobile menu drawer
function initMobileMenu() {
  const menuBtn = document.getElementById('mobileMenuBtn');
  const drawer = document.getElementById('mobileDrawer');
  const overlay = document.getElementById('mobileOverlay');
  const closeBtn = document.getElementById('mobileDrawerClose');

  if (!menuBtn || !drawer || !overlay) return;

  function openMenu() {
    drawer.classList.add('open');
    overlay.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    drawer.classList.remove('open');
    overlay.style.display = 'none';
    document.body.style.overflow = '';
  }

  menuBtn.addEventListener('click', openMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  overlay.addEventListener('click', closeMenu);
}

// Sticky header shadow
function initStickyHeader() {
  const header = document.querySelector('.site-header');
  if (!header) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
      header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.06)';
    } else {
      header.style.boxShadow = 'none';
    }
  });
}

// Wishlist quick action
function initWishlistButtons() {
  document.querySelectorAll('.book-wishlist-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const bookId = btn.dataset.bookId;
      const isActive = btn.classList.toggle('active');

      const wishlistBadge = document.getElementById('wishlistBadge');
      if (wishlistBadge) {
        let count = parseInt(wishlistBadge.textContent) || 0;
        count = isActive ? count + 1 : Math.max(0, count - 1);
        wishlistBadge.textContent = count;
      }

      showToast(isActive ? 'Added to your wishlist' : 'Removed from wishlist', 'success');
    });
  });
}

// Add to cart quick action
function initAddToCartButtons() {
  document.querySelectorAll('.add-cart-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const bookTitle = btn.dataset.bookTitle || 'Book';
      
      const cartBadge = document.getElementById('cartBadge');
      if (cartBadge) {
        let count = parseInt(cartBadge.textContent) || 0;
        cartBadge.textContent = count + 1;
      }

      // Quick pulse animation
      btn.style.transform = 'scale(0.95)';
      setTimeout(() => btn.style.transform = '', 150);

      showToast(`Added "${bookTitle}" to your cart!`, 'success');
    });
  });
}
