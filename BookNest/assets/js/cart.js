/**
 * BookNest - Cart Page JavaScript Engine
 * Provides AJAX-powered real-time updates for quantities, coupons, and subtotal recalibration
 */

document.addEventListener('DOMContentLoaded', () => {
  // Coupon input handlers
  const applyCouponBtn = document.getElementById('applyCouponBtn');
  const removeCouponBtn = document.getElementById('removeCouponBtn');
  const couponInput = document.getElementById('couponInput');
  const clearCartBtn = document.getElementById('clearCartBtn');

  if (applyCouponBtn && couponInput) {
    applyCouponBtn.addEventListener('click', () => {
      const code = couponInput.value.trim();
      if (!code) {
        showToast('Please enter a coupon code', 'warning');
        return;
      }
      applyCoupon(code);
    });

    couponInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        applyCouponBtn.click();
      }
    });
  }

  if (removeCouponBtn) {
    removeCouponBtn.addEventListener('click', () => {
      removeCoupon();
    });
  }

  if (clearCartBtn) {
    clearCartBtn.addEventListener('click', () => {
      if (confirm('Are you sure you want to remove all books from your cart?')) {
        clearCart();
      }
    });
  }
});

/**
 * Update item quantity via AJAX
 */
async function updateCartItemQty(bookId, action, value = null) {
  const row = document.getElementById(`cartItem-${bookId}`);
  if (!row) return;

  const qtyInput = document.getElementById(`qtyInput-${bookId}`);
  const currentQty = parseInt(qtyInput.value, 10) || 1;
  const maxStock = parseInt(row.getAttribute('data-stock'), 10) || 999;
  
  let targetQty = currentQty;
  if (action === 'increase') {
    if (currentQty >= maxStock) {
      showToast(`Only ${maxStock} copies available in stock!`, 'warning');
      return;
    }
    targetQty = currentQty + 1;
  } else if (action === 'decrease') {
    if (currentQty <= 1) {
      if (confirm('Remove this book from your cart?')) {
        removeCartItem(bookId);
      }
      return;
    }
    targetQty = currentQty - 1;
  } else if (action === 'set') {
    targetQty = Math.max(1, Math.min(maxStock, parseInt(value, 10) || 1));
  }

  qtyInput.value = targetQty;

  try {
    const couponInput = document.getElementById('couponInput');
    const couponCode = couponInput ? couponInput.value.trim() : '';

    const res = await fetch('api/cart-update.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        book_id: bookId,
        action: 'set',
        quantity: targetQty,
        coupon: couponCode
      })
    });

    const data = await res.json();
    if (!data.success) {
      showToast(data.error || 'Failed to update item quantity', 'error');
      return;
    }

    if (data.warning) {
      showToast(data.warning, 'warning');
    }

    // Update item line total
    const itemTotalEl = document.getElementById(`itemTotal-${bookId}`);
    if (itemTotalEl && data.itemSubtotal !== undefined) {
      itemTotalEl.textContent = formatCurrency(data.itemSubtotal);
    }

    // Update global cart badge
    updateCartBadge(data.cartCount);

    // Update order summary
    if (data.summary) {
      updateOrderSummaryDOM(data.summary);
    }

  } catch (err) {
    console.error('Error updating cart:', err);
    showToast('Failed to update cart. Please check your connection.', 'error');
  }
}

/**
 * Remove single item from cart
 */
async function removeCartItem(bookId, bookTitle = 'Book') {
  const row = document.getElementById(`cartItem-${bookId}`);
  if (row) {
    row.style.opacity = '0.4';
    row.style.pointerEvents = 'none';
  }

  try {
    const couponInput = document.getElementById('couponInput');
    const couponCode = couponInput ? couponInput.value.trim() : '';

    const res = await fetch('api/cart-remove.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        book_id: bookId,
        coupon: couponCode
      })
    });

    const data = await res.json();
    if (!data.success) {
      if (row) {
        row.style.opacity = '1';
        row.style.pointerEvents = 'auto';
      }
      showToast(data.error || 'Failed to remove book', 'error');
      return;
    }

    // Animate removal
    if (row) {
      row.style.transition = 'all 0.3s ease';
      row.style.transform = 'translateX(-30px)';
      row.style.opacity = '0';
      setTimeout(() => {
        row.remove();
        if (data.isEmpty) {
          window.location.reload();
        }
      }, 300);
    }

    updateCartBadge(data.cartCount);
    if (data.summary) {
      updateOrderSummaryDOM(data.summary);
    }

    showToast(`Removed "${bookTitle}" from cart`, 'info');

  } catch (err) {
    console.error('Error removing cart item:', err);
    if (row) {
      row.style.opacity = '1';
      row.style.pointerEvents = 'auto';
    }
    showToast('Failed to remove item.', 'error');
  }
}

/**
 * Move item from Cart to Wishlist
 */
async function moveToWishlist(bookId, bookTitle = 'Book') {
  try {
    // 1. Add to wishlist
    const wishRes = await fetch('api/wishlist-toggle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ book_id: bookId })
    });
    const wishData = await wishRes.json();

    if (wishData.wishlistCount !== undefined) {
      const wishBadge = document.getElementById('wishlistBadge');
      if (wishBadge) wishBadge.textContent = wishData.wishlistCount;
    }

    // 2. Remove from cart
    await removeCartItem(bookId, bookTitle);
    showToast(`Moved "${bookTitle}" to your wishlist!`, 'success');

  } catch (err) {
    console.error('Move to wishlist error:', err);
    showToast('Could not move item to wishlist.', 'error');
  }
}

/**
 * Clear the entire cart
 */
async function clearCart() {
  try {
    const res = await fetch('api/cart-clear.php', { method: 'POST' });
    const data = await res.json();
    if (data.success) {
      updateCartBadge(0);
      window.location.reload();
    }
  } catch (err) {
    console.error('Clear cart error:', err);
    showToast('Failed to clear cart.', 'error');
  }
}

/**
 * Quick Apply Coupon helper (from chips)
 */
function quickApplyCoupon(code) {
  const couponInput = document.getElementById('couponInput');
  if (couponInput) {
    couponInput.value = code;
    applyCoupon(code);
  }
}

/**
 * Apply coupon code via AJAX
 */
async function applyCoupon(code) {
  const msgEl = document.getElementById('couponMessage');
  try {
    const res = await fetch('api/coupon-apply.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ coupon: code, action: 'apply' })
    });

    const data = await res.json();

    if (!data.success) {
      if (msgEl) {
        msgEl.innerHTML = `<span style="color:#DC2626; font-weight:600;">✗ ${data.error}</span>`;
      }
      showToast(data.error, 'error');
      return;
    }

    if (msgEl) {
      msgEl.innerHTML = `<span style="color:#059669; font-weight:600;">✓ ${data.coupon.description}</span>`;
    }

    showToast(data.message, 'success');

    if (data.summary) {
      updateOrderSummaryDOM(data.summary);
    }

  } catch (err) {
    console.error('Apply coupon error:', err);
    showToast('Failed to apply coupon.', 'error');
  }
}

/**
 * Remove active coupon
 */
async function removeCoupon() {
  const msgEl = document.getElementById('couponMessage');
  const couponInput = document.getElementById('couponInput');

  try {
    const res = await fetch('api/coupon-apply.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'remove' })
    });

    const data = await res.json();
    if (data.success) {
      if (couponInput) couponInput.value = '';
      if (msgEl) msgEl.innerHTML = '';
      showToast('Coupon removed', 'info');

      if (data.summary) {
        updateOrderSummaryDOM(data.summary);
      }
    }
  } catch (err) {
    console.error('Remove coupon error:', err);
  }
}

/**
 * Update Order Summary DOM elements
 */
function updateOrderSummaryDOM(summary) {
  // Items subtotal
  const subtotalEl = document.getElementById('summarySubtotal');
  const itemCountEl = document.getElementById('summaryItemCount');
  if (subtotalEl) subtotalEl.textContent = formatCurrency(summary.subtotal);
  if (itemCountEl) itemCountEl.textContent = summary.total_items_count;

  // Coupon row
  const couponRow = document.getElementById('summaryCouponRow');
  const couponDiscountEl = document.getElementById('summaryCouponDiscount');
  if (couponRow && couponDiscountEl) {
    if (summary.coupon_discount > 0) {
      couponRow.style.display = 'flex';
      couponDiscountEl.textContent = `-${formatCurrency(summary.coupon_discount)}`;
    } else {
      couponRow.style.display = 'none';
    }
  }

  // Delivery fee
  const deliveryEl = document.getElementById('summaryDelivery');
  if (deliveryEl) {
    if (summary.is_free_delivery) {
      deliveryEl.innerHTML = '<span style="color:#059669; font-weight:600;">FREE</span>';
    } else {
      deliveryEl.innerHTML = `<span style="color:var(--color-text-main); font-weight:600;">${formatCurrency(summary.delivery_fee)}</span>`;
    }
  }

  // GST
  const gstEl = document.getElementById('summaryGst');
  if (gstEl) gstEl.textContent = formatCurrency(summary.gst_amount);

  // Grand Total
  const grandTotalEl = document.getElementById('summaryGrandTotal');
  if (grandTotalEl) grandTotalEl.textContent = formatCurrency(summary.grand_total);

  // Savings Badge
  const savingsBadge = document.getElementById('summarySavingsBadge');
  if (savingsBadge) {
    if (summary.total_savings > 0) {
      savingsBadge.style.display = 'block';
      savingsBadge.innerHTML = `🎉 You will save ${formatCurrency(summary.total_savings)} on this order!`;
    } else {
      savingsBadge.style.display = 'none';
    }
  }

  // Free delivery progress bar
  const progressBar = document.getElementById('freeDeliveryProgressBar');
  const percentText = document.getElementById('freeDeliveryPercentText');
  const deliveryText = document.getElementById('freeDeliveryText');

  if (progressBar && percentText && deliveryText) {
    const percent = Math.min(100, Math.round((summary.subtotal / summary.free_delivery_threshold) * 100));
    progressBar.style.width = `${percent}%`;
    percentText.textContent = `${percent}%`;

    if (summary.is_free_delivery) {
      progressBar.style.background = '#10B981';
      deliveryText.innerHTML = '<span style="color:#059669;">Congratulations! You have qualified for FREE Delivery!</span>';
    } else {
      progressBar.style.background = 'var(--color-primary)';
      deliveryText.innerHTML = `Add <strong style="color:var(--color-primary);">${formatCurrency(summary.amount_needed_for_free_delivery)}</strong> more to qualify for <strong>FREE Delivery</strong>`;
    }
  }
}

/**
 * Format number as Indian Rupee (₹)
 */
function formatCurrency(amount) {
  return '₹' + Number(amount).toLocaleString('en-IN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

/**
 * Update navbar badge
 */
function updateCartBadge(count) {
  const badge = document.getElementById('cartBadge');
  if (badge) {
    badge.textContent = count;
  }
}

/**
 * Toast Notification utility
 */
function showToast(message, type = 'info') {
  let toastContainer = document.getElementById('toastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.style.position = 'fixed';
    toastContainer.style.bottom = '24px';
    toastContainer.style.right = '24px';
    toastContainer.style.zIndex = '9999';
    toastContainer.style.display = 'flex';
    toastContainer.style.flexDirection = 'column';
    toastContainer.style.gap = '8px';
    document.body.appendChild(toastContainer);
  }

  const toast = document.createElement('div');
  const bg = type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : type === 'warning' ? '#F59E0B' : '#3B82F6';
  toast.style.background = bg;
  toast.style.color = '#fff';
  toast.style.padding = '12px 18px';
  toast.style.borderRadius = '8px';
  toast.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
  toast.style.fontSize = '0.88rem';
  toast.style.fontWeight = '500';
  toast.style.transition = 'all 0.3s ease';
  toast.textContent = message;

  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}
