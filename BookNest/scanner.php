<?php
/**
 * BookNest - Optical & Barcode Physical Book Scanner Interface
 * Phase 7: Real-time Camera Feed, BarcodeDetector API, ISBN Match & Multi-Field Fuzzy Search
 */

$pageTitle = "Scan a Book - BookNest";
$activeNav = "scanner";

require_once __DIR__ . '/includes/header.php';
?>

<style>
@keyframes scanLaser {
  0% { top: 10%; opacity: 0.9; }
  50% { top: 90%; opacity: 1; }
  100% { top: 10%; opacity: 0.9; }
}

.scanner-laser {
  position: absolute;
  left: 5%;
  right: 5%;
  height: 3px;
  background: linear-gradient(90deg, transparent, #EF4444, #F59E0B, #EF4444, transparent);
  box-shadow: 0 0 12px 3px rgba(239, 68, 68, 0.75);
  animation: scanLaser 2.2s ease-in-out infinite;
  display: none;
  z-index: 5;
}

.test-chip {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: var(--radius-full);
  padding: 6px 14px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-text-main);
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.test-chip:hover {
  background: #EFF6FF;
  border-color: #93C5FD;
  color: var(--color-primary);
  transform: translateY(-1px);
}
</style>

<div class="container" style="padding: 40px 20px 80px 20px; max-width: 860px;">
  
  <!-- Header / Breadcrumb -->
  <div style="text-align: center; margin-bottom: 32px;">
    <div style="display:inline-flex; align-items:center; gap:6px; padding:4px 14px; background:var(--color-accent-light); color:var(--color-accent); border-radius:var(--radius-full); font-size:0.8rem; font-weight:700; margin-bottom:12px;">
      <span>📷</span>
      <span>BookNest Optical Vision Scanner • Phase 7</span>
    </div>
    <h1 class="section-title" style="font-size: 2.4rem; margin-bottom: 8px;">Physical Book Scanner</h1>
    <p class="section-desc" style="font-size: 1rem; max-width: 580px; margin: 0 auto;">
      "Have the book in your hands? Point your device camera at the rear barcode or cover, or upload an image to find it instantly in the catalog."
    </p>
  </div>

  <!-- Scanner Viewport Card -->
  <div style="background: #fff; border: 1px solid var(--color-card-border); border-radius: var(--radius-xl); padding: 32px; box-shadow: var(--shadow-lg); margin-bottom: 28px;">
    
    <!-- Viewport Box -->
    <div style="position: relative; width: 100%; aspect-ratio: 16/10; max-height: 400px; background: #0F172A; border-radius: var(--radius-lg); overflow: hidden; display: flex; align-items: center; justify-content: center; color: #fff; margin-bottom: 20px;">
      
      <!-- Video Element -->
      <video id="scannerVideo" playsinline autoplay muted style="width: 100%; height: 100%; object-fit: cover; display: none;"></video>
      <canvas id="scannerCanvas" style="display: none;"></canvas>

      <!-- Laser Animation Line -->
      <div id="scannerLaser" class="scanner-laser"></div>

      <!-- Reticle Target Overlay -->
      <div id="scannerOverlay" style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none;">
        <div style="width: 250px; height: 160px; border: 2px dashed #F59E0B; border-radius: 14px; box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; position: relative;">
          <span style="font-size: 0.75rem; font-weight: 700; color: #FCD34D; background: rgba(0,0,0,0.7); padding: 4px 10px; border-radius: 6px; letter-spacing: 0.5px;">
            Align ISBN Barcode or Cover
          </span>
        </div>
      </div>

      <!-- Idle Placeholder State -->
      <div id="scannerIdle" style="z-index: 2; padding: 24px; text-align: center;">
        <div style="font-size: 3.5rem; margin-bottom: 12px;">📷</div>
        <h3 style="font-size: 1.2rem; font-weight: 700; color: #F8FAFC; margin-bottom: 6px;">Camera Scanner Idle</h3>
        <p style="font-size: 0.88rem; color: #94A3B8; max-width: 440px; margin: 0 auto 18px auto; line-height: 1.5;">
          Click "Start Camera Scanner" below to grant camera access, or choose an image file from your device.
        </p>
      </div>

    </div>

    <!-- Status Badge -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
      <div id="scannerStatusBadge" style="font-size: 0.85rem; font-weight: 600; color: var(--color-text-muted);">
        Ready to scan. Select an option below.
      </div>
      <div style="font-size: 0.75rem; color: #059669; font-weight: 700;">
        ⚡ Instant BarcodeDetector & Levenshtein Matching Active
      </div>
    </div>

    <!-- Scanner Control Buttons -->
    <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; margin-bottom: 28px;">
      
      <button 
        type="button" 
        id="startScanBtn" 
        class="btn btn-primary"
        onclick="startCameraScanner()"
        style="padding: 10px 24px;"
      >
        <span>📹</span>
        <span>Start Camera Scanner</span>
      </button>

      <button 
        type="button" 
        id="stopScanBtn" 
        class="btn btn-secondary"
        onclick="stopCameraScanner()"
        style="display: none; padding: 10px 24px;"
      >
        <span>⏹️</span>
        <span>Stop Camera</span>
      </button>

      <label class="btn btn-secondary" style="cursor: pointer; padding: 10px 22px;">
        <span>📁</span>
        <span>Upload Book Cover / Barcode</span>
        <input type="file" id="bookImageUpload" accept="image/*" style="display:none;">
      </label>

    </div>

    <!-- Interactive Demonstration Test Chips -->
    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: var(--radius-lg); padding: 18px 20px; text-align: left;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
        <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-main); text-transform: uppercase; letter-spacing: 0.5px;">
          ⚡ Quick Academic Demo Barcodes
        </span>
        <span style="font-size: 0.75rem; color: var(--color-text-muted);">
          Click any chip to simulate real camera barcode detection:
        </span>
      </div>

      <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        <button type="button" class="test-chip" onclick="simulateScan('9780735211292', 'Atomic Habits')">
          <span>📗</span>
          <span>Atomic Habits (ISBN 9780735211292)</span>
        </button>

        <button type="button" class="test-chip" onclick="simulateScan('9780132350884', 'Clean Code')">
          <span>💻</span>
          <span>Clean Code (ISBN 9780132350884)</span>
        </button>

        <button type="button" class="test-chip" onclick="simulateScan('9780857197689', 'The Psychology of Money')">
          <span>💰</span>
          <span>Psychology of Money (ISBN 9780857197689)</span>
        </button>

        <button type="button" class="test-chip" onclick="simulateScan('9780441013593', 'Dune')">
          <span>🪐</span>
          <span>Dune (ISBN 9780441013593)</span>
        </button>

        <button type="button" class="test-chip" onclick="simulateScan('9781451648539', 'Steve Jobs')">
          <span>🍎</span>
          <span>Steve Jobs (ISBN 9781451648539)</span>
        </button>

        <button type="button" class="test-chip" onclick="simulateScan('The Great Gatsby Fitzgerald', 'Fuzzy Search')">
          <span>🔍</span>
          <span>Fuzzy Title: "The Great Gatsby Fitzgerald"</span>
        </button>
      </div>
    </div>

    <!-- Scan Match Result Card Container (Dynamically rendered by scanner.js) -->
    <div id="scanResultCard" style="margin-top: 24px;"></div>

  </div>

  <!-- Technical Architecture Card (College Project Viva Highlights) -->
  <div style="background: #fff; border: 1px solid var(--color-card-border); border-radius: var(--radius-xl); padding: 24px; box-shadow: var(--shadow-sm);">
    <h3 style="font-family: var(--font-serif); font-size: 1.2rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 12px;">
      Academic Implementation Architecture
    </h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; font-size: 0.82rem; color: var(--color-text-muted);">
      <div style="padding: 12px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
        <strong style="color: var(--color-text-main); display: block; margin-bottom: 4px;">1. HTML5 MediaStream</strong>
        Accesses high-resolution camera hardware stream with environment (rear-facing) lens priority.
      </div>
      <div style="padding: 12px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
        <strong style="color: var(--color-text-main); display: block; margin-bottom: 4px;">2. Native BarcodeDetector</strong>
        Hardware-accelerated EAN-13, EAN-8, UPC-A barcode decoding directly within the browser frame loop.
      </div>
      <div style="padding: 12px; background: #F8FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
        <strong style="color: var(--color-text-main); display: block; margin-bottom: 4px;">3. Multi-Field Fuzzy Match</strong>
        Server-side fallback normalization and weighted Levenshtein matching for cover OCR and partial queries.
      </div>
    </div>
  </div>

</div>

<!-- Scanner Module Script -->
<script src="assets/js/scanner.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
