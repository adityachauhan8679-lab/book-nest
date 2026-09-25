<?php
/**
 * BookNest - Multi-Step Checkout Page
 * Handles shipping address selection, UPI/Card/COD payment simulation, and order confirmation
 */

$pageTitle = "Checkout - BookNest";
$activeNav = "cart";

require_once __DIR__ . '/includes/header.php';

$cartItems = getCartItems();
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$currentUser = getCurrentUser();
$userId = $currentUser ? (int)$currentUser['id'] : 0;
$savedAddresses = $userId > 0 ? getUserAddresses($userId) : [];

$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$summary = calculateCartSummary($cartItems, $appliedCoupon);
?>

<div class="container" style="padding: 40px 20px 80px 20px;">
  
  <!-- Breadcrumb -->
  <nav class="breadcrumb" style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:var(--color-text-muted); margin-bottom:24px;">
    <a href="index.php" style="color:var(--color-text-muted); text-decoration:none;">Home</a>
    <span>›</span>
    <a href="cart.php" style="color:var(--color-text-muted); text-decoration:none;">Shopping Cart</a>
    <span>›</span>
    <span style="color:var(--color-text-main); font-weight:600;">Secure Checkout</span>
  </nav>

  <h1 style="font-family:var(--font-serif); font-size:2rem; font-weight:700; color:var(--color-text-main); margin-bottom:28px;">
    Checkout & Order Finalization
  </h1>

  <div style="display:grid; grid-template-columns: 1fr 380px; gap:32px; align-items:start;" class="cart-layout-grid">
    
    <!-- Left Column: Checkout Multi-Step Form -->
    <div style="display:flex; flex-direction:column; gap:24px;">
      
      <!-- Step 1: Shipping Address -->
      <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:28px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; border-bottom:1px solid var(--color-card-border); padding-bottom:12px;">
          <div style="display:flex; align-items:center; gap:10px;">
            <span style="width:28px; height:28px; border-radius:50%; background:var(--color-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700;">
              1
            </span>
            <h2 style="font-size:1.2rem; font-weight:700; margin:0; color:var(--color-text-main);">
              Delivery Address
            </h2>
          </div>
          <?php if (!empty($savedAddresses)): ?>
            <button 
              type="button" 
              id="toggleNewAddressBtn"
              class="btn btn-outline btn-sm"
              style="font-size:0.8rem;"
            >
              + Add New Address
            </button>
          <?php endif; ?>
        </div>

        <?php if (!empty($savedAddresses)): ?>
          <!-- Saved Addresses Selection -->
          <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap:16px; margin-bottom:20px;" id="savedAddressesGrid">
            <?php foreach ($savedAddresses as $idx => $addr): ?>
              <label 
                style="display:block; border:2px solid <?= $addr['is_default'] ? 'var(--color-primary)' : '#E2E8F0' ?>; background:<?= $addr['is_default'] ? 'var(--color-primary-light)' : '#fff' ?>; border-radius:var(--radius-lg); padding:16px; cursor:pointer; position:relative; transition:all 0.2s;"
                class="address-card-label"
              >
                <div style="display:flex; align-items:flex-start; gap:10px;">
                  <input 
                    type="radio" 
                    name="selected_address_id" 
                    value="<?= $addr['id'] ?>"
                    <?= $addr['is_default'] ? 'checked' : '' ?>
                    style="margin-top:3px;"
                  >
                  <div style="font-size:0.88rem; line-height:1.5;">
                    <div style="font-weight:700; color:var(--color-text-main); display:flex; align-items:center; gap:6px;">
                      <span><?= e($addr['full_name']) ?></span>
                      <?php if ($addr['is_default']): ?>
                        <span style="font-size:0.7rem; background:var(--color-primary); color:#fff; padding:1px 6px; border-radius:4px;">Default</span>
                      <?php endif; ?>
                    </div>
                    <div style="color:var(--color-text-muted); font-size:0.82rem; margin-top:2px;">
                      📞 <?= e($addr['phone']) ?>
                    </div>
                    <div style="color:var(--color-text-muted); font-size:0.82rem; margin-top:4px;">
                      <?= e($addr['address']) ?>, <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['pincode']) ?>
                    </div>
                  </div>
                </div>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- New Address Form (Visible if no saved address or toggled) -->
        <div id="newAddressFormContainer" style="display: <?= empty($savedAddresses) ? 'block' : 'none' ?>;">
          <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:14px; color:var(--color-text-main);">
            <?= empty($savedAddresses) ? 'Enter Delivery Address' : 'Add New Delivery Address' ?>
          </h3>
          <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
            <div>
              <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--color-text-muted); margin-bottom:4px;">Full Name *</label>
              <input type="text" id="newAddrName" class="form-control" placeholder="e.g. Aditya Chauhan" value="<?= e($currentUser['name'] ?? '') ?>" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:var(--radius-md);">
            </div>
            <div>
              <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--color-text-muted); margin-bottom:4px;">Mobile Phone *</label>
              <input type="tel" id="newAddrPhone" class="form-control" placeholder="10-digit mobile number" value="<?= e($currentUser['phone'] ?? '+91 91234 56789') ?>" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:var(--radius-md);">
            </div>
          </div>
          <div style="margin-top:14px;">
            <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--color-text-muted); margin-bottom:4px;">Flat, Hostel, Room No., Building Name *</label>
            <input type="text" id="newAddrLine" class="form-control" placeholder="e.g. Room 304, Hall of Residence, North Campus" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:var(--radius-md);">
          </div>
          <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:14px; margin-top:14px;">
            <div>
              <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--color-text-muted); margin-bottom:4px;">City / Campus *</label>
              <input type="text" id="newAddrCity" class="form-control" value="Delhi" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:var(--radius-md);">
            </div>
            <div>
              <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--color-text-muted); margin-bottom:4px;">State *</label>
              <input type="text" id="newAddrState" class="form-control" value="Delhi" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:var(--radius-md);">
            </div>
            <div>
              <label style="display:block; font-size:0.8rem; font-weight:600; color:var(--color-text-muted); margin-bottom:4px;">Pincode *</label>
              <input type="text" id="newAddrPincode" class="form-control" placeholder="110007" value="110007" style="width:100%; padding:9px 12px; border:1px solid #CBD5E1; border-radius:var(--radius-md);">
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Payment Method Selection -->
      <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:28px; box-shadow:var(--shadow-sm);">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px; border-bottom:1px solid var(--color-card-border); padding-bottom:12px;">
          <span style="width:28px; height:28px; border-radius:50%; background:var(--color-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:700;">
            2
          </span>
          <h2 style="font-size:1.2rem; font-weight:700; margin:0; color:var(--color-text-main);">
            Payment Method
          </h2>
        </div>

        <div style="display:flex; flex-direction:column; gap:14px;">
          
          <!-- UPI Payment -->
          <label style="display:block; border:2px solid var(--color-primary); background:var(--color-primary-light); border-radius:var(--radius-lg); padding:16px; cursor:pointer;" class="payment-method-card">
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <div style="display:flex; align-items:center; gap:12px;">
                <input type="radio" name="payment_method" value="UPI" checked style="width:18px; height:18px;">
                <div>
                  <div style="font-weight:700; font-size:0.95rem; color:var(--color-text-main);">
                    Instant UPI / QR Code (Recommended)
                  </div>
                  <div style="font-size:0.8rem; color:var(--color-text-muted);">
                    Google Pay, PhonePe, Paytm, BHIM UPI
                  </div>
                </div>
              </div>
              <span style="font-size:1.5rem;">📱</span>
            </div>

            <!-- UPI details simulation -->
            <div id="upiDetailsBox" style="margin-top:14px; padding-top:14px; border-top:1px dashed #BFDBFE; display:flex; align-items:center; gap:16px;">
              <div style="width:72px; height:72px; background:#fff; border:1px solid #CBD5E1; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; box-shadow:var(--shadow-sm);">
                🏁
              </div>
              <div style="font-size:0.82rem; color:var(--color-text-muted);">
                <div style="font-weight:600; color:var(--color-text-main);">Dynamic Campus QR will generate on place order</div>
                <div>Fastest verification • Zero additional convenience fees</div>
              </div>
            </div>
          </label>

          <!-- Card / NetBanking -->
          <label style="display:block; border:1px solid #CBD5E1; background:#fff; border-radius:var(--radius-lg); padding:16px; cursor:pointer;" class="payment-method-card">
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <div style="display:flex; align-items:center; gap:12px;">
                <input type="radio" name="payment_method" value="Card" style="width:18px; height:18px;">
                <div>
                  <div style="font-weight:700; font-size:0.95rem; color:var(--color-text-main);">
                    Credit / Debit Card / NetBanking
                  </div>
                  <div style="font-size:0.8rem; color:var(--color-text-muted);">
                    Visa, MasterCard, RuPay, Maestro
                  </div>
                </div>
              </div>
              <span style="font-size:1.5rem;">💳</span>
            </div>
          </label>

          <!-- Cash on Delivery -->
          <label style="display:block; border:1px solid #CBD5E1; background:#fff; border-radius:var(--radius-lg); padding:16px; cursor:pointer;" class="payment-method-card">
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <div style="display:flex; align-items:center; gap:12px;">
                <input type="radio" name="payment_method" value="Cash on Delivery" style="width:18px; height:18px;">
                <div>
                  <div style="font-weight:700; font-size:0.95rem; color:var(--color-text-main);">
                    Cash on Delivery (COD)
                  </div>
                  <div style="font-size:0.8rem; color:var(--color-text-muted);">
                    Pay in cash upon physical packet arrival
                  </div>
                </div>
              </div>
              <span style="font-size:1.5rem;">💵</span>
            </div>
          </label>

        </div>
      </div>

    </div>

    <!-- Right Column: Order Review & Finalization -->
    <div>
      <div style="background:#fff; border:1px solid var(--color-card-border); border-radius:var(--radius-xl); padding:24px; box-shadow:var(--shadow-sm); position:sticky; top:90px;">
        <h2 style="font-family:var(--font-serif); font-size:1.3rem; font-weight:700; color:var(--color-text-main); margin-bottom:16px; border-bottom:1px solid var(--color-card-border); padding-bottom:10px;">
          Order Summary (<?= $summary['total_items_count'] ?> items)
        </h2>

        <!-- Items preview list -->
        <div style="max-height:220px; overflow-y:auto; margin-bottom:16px; divide-y divide-gray-100;">
          <?php foreach ($cartItems as $item): ?>
            <div style="display:flex; gap:12px; align-items:center; padding:8px 0; border-bottom:1px solid #F1F5F9;">
              <img src="<?= e($item['cover_image']) ?>" style="width:40px; height:56px; object-fit:cover; border-radius:4px; border:1px solid #E2E8F0;">
              <div style="flex:1; min-width:0;">
                <div style="font-weight:600; font-size:0.85rem; color:var(--color-text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  <?= e($item['title']) ?>
                </div>
                <div style="font-size:0.75rem; color:var(--color-text-muted);">
                  Qty: <?= $item['quantity'] ?> × <?= formatPrice($item['unit_price']) ?>
                </div>
              </div>
              <div style="font-weight:700; font-size:0.9rem; color:var(--color-text-main);">
                <?= formatPrice($item['line_total']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Calculations -->
        <div style="display:flex; flex-direction:column; gap:8px; font-size:0.88rem; border-top:1px solid var(--color-card-border); padding-top:12px;">
          <div style="display:flex; justify-content:space-between; color:var(--color-text-muted);">
            <span>Items Subtotal</span>
            <span style="font-weight:600; color:var(--color-text-main);"><?= formatPrice($summary['subtotal']) ?></span>
          </div>

          <?php if ($summary['coupon_discount'] > 0): ?>
            <div style="display:flex; justify-content:space-between; color:#059669; font-weight:600;">
              <span>Promo (<?= e($summary['applied_coupon']['code']) ?>)</span>
              <span>-<?= formatPrice($summary['coupon_discount']) ?></span>
            </div>
          <?php endif; ?>

          <div style="display:flex; justify-content:space-between; color:var(--color-text-muted);">
            <span>Delivery Charges</span>
            <span>
              <?php if ($summary['is_free_delivery']): ?>
                <strong style="color:#059669;">FREE</strong>
              <?php else: ?>
                <strong><?= formatPrice($summary['delivery_fee']) ?></strong>
              <?php endif; ?>
            </span>
          </div>

          <div style="display:flex; justify-content:space-between; color:var(--color-text-muted);">
            <span>GST Tax (5%)</span>
            <span style="font-weight:600; color:var(--color-text-main);"><?= formatPrice($summary['gst_amount']) ?></span>
          </div>

          <div style="border-top:2px dashed var(--color-card-border); margin-top:8px; padding-top:12px; display:flex; justify-content:space-between; align-items:baseline;">
            <span style="font-weight:800; font-size:1.1rem; color:var(--color-text-main);">Total Payable</span>
            <span style="font-weight:800; font-size:1.45rem; color:var(--color-primary);"><?= formatPrice($summary['grand_total']) ?></span>
          </div>
        </div>

        <!-- Place Order Button -->
        <div style="margin-top:20px;">
          <button 
            type="button" 
            id="placeOrderSubmitBtn"
            class="btn btn-primary"
            style="width:100%; justify-content:center; padding:13px 20px; font-size:1rem; font-weight:700; border-radius:var(--radius-lg); box-shadow:var(--shadow-md);"
          >
            <span>Confirm & Place Order</span>
            <span>→</span>
          </button>
        </div>

        <div style="margin-top:16px; text-align:center; font-size:0.75rem; color:var(--color-text-muted);">
          🔒 SSL 256-Bit Bank Grade Secured Checkout
        </div>
      </div>
    </div>

  </div>

</div>

<!-- Checkout Handler JS -->
<script src="assets/js/checkout.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
