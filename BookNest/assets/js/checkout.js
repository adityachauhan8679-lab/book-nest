/**
 * BookNest - Checkout JavaScript Handler
 */

document.addEventListener('DOMContentLoaded', () => {
  const toggleNewAddrBtn = document.getElementById('toggleNewAddressBtn');
  const newAddrContainer = document.getElementById('newAddressFormContainer');
  const placeOrderBtn = document.getElementById('placeOrderSubmitBtn');
  const paymentCards = document.querySelectorAll('.payment-method-card');

  // Toggle new address form
  if (toggleNewAddrBtn && newAddrContainer) {
    toggleNewAddrBtn.addEventListener('click', () => {
      const isHidden = newAddrContainer.style.display === 'none';
      newAddrContainer.style.display = isHidden ? 'block' : 'none';
      toggleNewAddrBtn.textContent = isHidden ? '- Hide Address Form' : '+ Add New Address';
      if (isHidden) {
        // Deselect radio buttons
        const radios = document.querySelectorAll('input[name="selected_address_id"]');
        radios.forEach(r => r.checked = false);
      }
    });
  }

  // Payment method card active styles
  paymentCards.forEach(card => {
    const radio = card.querySelector('input[type="radio"]');
    if (!radio) return;

    card.addEventListener('click', () => {
      paymentCards.forEach(c => {
        c.style.borderColor = '#CBD5E1';
        c.style.backgroundColor = '#fff';
      });
      card.style.borderColor = 'var(--color-primary)';
      card.style.backgroundColor = 'var(--color-primary-light)';
      radio.checked = true;

      const upiBox = document.getElementById('upiDetailsBox');
      if (upiBox) {
        upiBox.style.display = radio.value === 'UPI' ? 'flex' : 'none';
      }
    });
  });

  // Submit order placement
  if (placeOrderBtn) {
    placeOrderBtn.addEventListener('click', async () => {
      placeOrderBtn.disabled = true;
      const originalText = placeOrderBtn.innerHTML;
      placeOrderBtn.innerHTML = '<span>⏳ Processing order...</span>';

      // Gather address
      const selectedRadio = document.querySelector('input[name="selected_address_id"]:checked');
      let addressId = selectedRadio ? parseInt(selectedRadio.value, 10) : 0;
      let newAddress = null;

      if (!addressId || (newAddrContainer && newAddrContainer.style.display !== 'none')) {
        const name = document.getElementById('newAddrName')?.value.trim();
        const phone = document.getElementById('newAddrPhone')?.value.trim();
        const address = document.getElementById('newAddrLine')?.value.trim();
        const city = document.getElementById('newAddrCity')?.value.trim();
        const state = document.getElementById('newAddrState')?.value.trim();
        const pincode = document.getElementById('newAddrPincode')?.value.trim();

        if (!name || !phone || !address || !pincode) {
          alert('Please enter your complete delivery address (Name, Phone, Address, Pincode).');
          placeOrderBtn.disabled = false;
          placeOrderBtn.innerHTML = originalText;
          return;
        }

        newAddress = { full_name: name, phone, address, city, state, pincode };
      }

      // Gather payment method
      const paymentRadio = document.querySelector('input[name="payment_method"]:checked');
      const paymentMethod = paymentRadio ? paymentRadio.value : 'Cash on Delivery';

      try {
        const res = await fetch('api/order-place.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            address_id: addressId,
            new_address: newAddress,
            payment_method: paymentMethod
          })
        });

        const data = await res.json();

        if (!data.success) {
          alert(data.error || 'Failed to place order.');
          placeOrderBtn.disabled = false;
          placeOrderBtn.innerHTML = originalText;
          return;
        }

        // Redirect to success page
        window.location.href = data.redirect || ('order-success.php?id=' + data.order_id);

      } catch (err) {
        console.error('Order placement error:', err);
        alert('Network error while placing order. Please try again.');
        placeOrderBtn.disabled = false;
        placeOrderBtn.innerHTML = originalText;
      }
    });
  }
});
