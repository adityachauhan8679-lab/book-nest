/**
 * BookNest - Wishlist JavaScript Module
 * Handles adding/removing items from wishlist and transferring items to cart
 */

document.addEventListener('DOMContentLoaded', () => {
  console.log('BookNest Wishlist Module ready.');
});

/**
 * Move item from Wishlist to Cart
 */
async function moveWishlistToCart(bookId, bookTitle = 'Book') {
  const card = document.getElementById(`wishlistItem-${bookId}`);
  if (card) {
    card.style.opacity = '0.5';
    card.style.pointerEvents = 'none';
  }

  try {
    // 1. Add to cart
    const cartRes = await fetch('api/cart-add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ book_id: bookId, quantity: 1 })
    });
    const cartData = await cartRes.json();

    if (!cartData.success) {
      if (card) {
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
      }
      showToast(cartData.error || 'Failed to add book to cart', 'error');
      return;
    }

    // Update cart badge
    const cartBadge = document.getElementById('cartBadge');
    if (cartBadge && cartData.cartCount !== undefined) {
      cartBadge.textContent = cartData.cartCount;
    }

    // 2. Remove from wishlist
    const wishRes = await fetch('api/wishlist-toggle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ book_id: bookId })
    });
    const wishData = await wishRes.json();

    const wishBadge = document.getElementById('wishlistBadge');
    if (wishBadge && wishData.wishlistCount !== undefined) {
      wishBadge.textContent = wishData.wishlistCount;
    }

    // Animate removal from wishlist grid
    if (card) {
      card.style.transition = 'all 0.3s ease';
      card.style.transform = 'scale(0.9)';
      card.style.opacity = '0';
      setTimeout(() => {
        card.remove();
        const remaining = document.querySelectorAll('.wishlist-card');
        if (remaining.length === 0) {
          window.location.reload();
        }
      }, 300);
    }

    showToast(`Moved "${bookTitle}" to your cart!`, 'success');

  } catch (err) {
    console.error('Error moving wishlist item to cart:', err);
    if (card) {
      card.style.opacity = '1';
      card.style.pointerEvents = 'auto';
    }
    showToast('Failed to transfer item to cart.', 'error');
  }
}

/**
 * Remove an item directly from wishlist
 */
async function removeFromWishlist(bookId, bookTitle = 'Book') {
  const card = document.getElementById(`wishlistItem-${bookId}`);
  if (card) {
    card.style.opacity = '0.4';
  }

  try {
    const res = await fetch('api/wishlist-toggle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ book_id: bookId })
    });
    const data = await res.json();

    if (!data.success) {
      if (card) card.style.opacity = '1';
      showToast(data.error || 'Failed to update wishlist', 'error');
      return;
    }

    const wishBadge = document.getElementById('wishlistBadge');
    if (wishBadge && data.wishlistCount !== undefined) {
      wishBadge.textContent = data.wishlistCount;
    }

    if (card) {
      card.style.transition = 'all 0.3s ease';
      card.style.transform = 'scale(0.85)';
      card.style.opacity = '0';
      setTimeout(() => {
        card.remove();
        const remaining = document.querySelectorAll('.wishlist-card');
        if (remaining.length === 0) {
          window.location.reload();
        }
      }, 300);
    }

    showToast(`Removed "${bookTitle}" from your wishlist`, 'info');

  } catch (err) {
    console.error('Error removing from wishlist:', err);
    if (card) card.style.opacity = '1';
    showToast('Could not remove item from wishlist.', 'error');
  }
}

/**
 * Transfer all wishlist items to Cart
 */
async function addAllWishlistToCart() {
  const cards = document.querySelectorAll('.wishlist-card');
  if (cards.length === 0) return;

  const btn = document.getElementById('addAllToCartBtn');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span><span>Adding all...</span>';
  }

  let count = 0;
  for (const card of cards) {
    const id = card.id.replace('wishlistItem-', '');
    if (id) {
      try {
        await fetch('api/cart-add.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ book_id: parseInt(id, 10), quantity: 1 })
        });
        await fetch('api/wishlist-toggle.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ book_id: parseInt(id, 10) })
        });
        count++;
      } catch (e) {
        console.error(e);
      }
    }
  }

  showToast(`Successfully added ${count} books to your cart!`, 'success');
  setTimeout(() => {
    window.location.href = 'cart.php';
  }, 800);
}
