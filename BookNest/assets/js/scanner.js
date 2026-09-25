/**
 * BookNest - Optical & Barcode Scanner Engine
 * Handles MediaStream camera integration, BarcodeDetector API, drag-and-drop image analysis, and catalog matching
 */

document.addEventListener('DOMContentLoaded', () => {
  const startBtn = document.getElementById('startScanBtn');
  const stopBtn = document.getElementById('stopScanBtn');
  const video = document.getElementById('scannerVideo');
  const canvas = document.getElementById('scannerCanvas');
  const idleView = document.getElementById('scannerIdle');
  const laser = document.getElementById('scannerLaser');
  const statusBadge = document.getElementById('scannerStatusBadge');
  const fileInput = document.getElementById('bookImageUpload');
  const resultCard = document.getElementById('scanResultCard');

  let stream = null;
  let scanning = false;
  let barcodeDetector = null;
  let scanAnimationId = null;

  // Initialize BarcodeDetector API if supported
  if ('BarcodeDetector' in window) {
    try {
      barcodeDetector = new window.BarcodeDetector({
        formats: ['ean_13', 'ean_8', 'upc_a', 'code_128', 'qr_code']
      });
      console.log('BookNest: Native BarcodeDetector API initialized.');
    } catch (e) {
      console.warn('BarcodeDetector format error:', e);
    }
  }

  // Start Camera Stream
  window.startCameraScanner = async function() {
    if (scanning) return;

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('Camera access is not supported on this browser or requires an HTTPS / localhost connection.');
      return;
    }

    try {
      statusBadge.textContent = 'Requesting camera access...';
      stream = await navigator.mediaDevices.getUserMedia({
        video: {
          facingMode: { ideal: 'environment' },
          width: { ideal: 1280 },
          height: { ideal: 720 }
        },
        audio: false
      });

      video.srcObject = stream;
      video.style.display = 'block';
      if (idleView) idleView.style.display = 'none';
      if (laser) laser.style.display = 'block';
      if (startBtn) startBtn.style.display = 'none';
      if (stopBtn) stopBtn.style.display = 'inline-flex';

      video.play();
      scanning = true;
      statusBadge.textContent = '● Scanning live feed... Point camera at ISBN barcode or book cover.';

      // Start detection loop
      startScanningLoop();
    } catch (err) {
      console.error('Camera access failed:', err);
      statusBadge.textContent = 'Camera unavailable: ' + (err.message || 'Permission denied');
      alert('Camera access permission was denied or unavailable. You can also upload a book image below or click a sample test barcode.');
    }
  };

  // Stop Camera Stream
  window.stopCameraScanner = function() {
    scanning = false;
    if (scanAnimationId) cancelAnimationFrame(scanAnimationId);
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
    video.srcObject = null;
    video.style.display = 'none';
    if (idleView) idleView.style.display = 'block';
    if (laser) laser.style.display = 'none';
    if (startBtn) startBtn.style.display = 'inline-flex';
    if (stopBtn) stopBtn.style.display = 'none';
    statusBadge.textContent = 'Camera stopped. Ready to scan.';
  };

  // Continuous frame analysis
  function startScanningLoop() {
    if (!scanning) return;

    if (barcodeDetector && video.readyState >= 2) {
      barcodeDetector.detect(video)
        .then(barcodes => {
          if (barcodes.length > 0) {
            const rawVal = barcodes[0].rawValue;
            console.log('Detected barcode:', rawVal);
            stopCameraScanner();
            lookupBook(rawVal);
            return;
          }
          if (scanning) {
            scanAnimationId = requestAnimationFrame(startScanningLoop);
          }
        })
        .catch(err => {
          if (scanning) {
            scanAnimationId = requestAnimationFrame(startScanningLoop);
          }
        });
    } else {
      // Periodic check if video is playing
      if (scanning) {
        scanAnimationId = requestAnimationFrame(startScanningLoop);
      }
    }
  }

  // Handle uploaded image file
  if (fileInput) {
    fileInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;

      statusBadge.textContent = `Analyzing uploaded file "${file.name}"...`;
      const img = new Image();
      img.onload = () => {
        if (barcodeDetector) {
          barcodeDetector.detect(img)
            .then(barcodes => {
              if (barcodes.length > 0) {
                lookupBook(barcodes[0].rawValue);
              } else {
                // If barcode not found in image, test sample name matching
                fuzzyLookupFromFileName(file.name);
              }
            })
            .catch(() => fuzzyLookupFromFileName(file.name));
        } else {
          fuzzyLookupFromFileName(file.name);
        }
      };
      img.src = URL.createObjectURL(file);
    });
  }

  function fuzzyLookupFromFileName(name) {
    // Extract readable words from filename (e.g. "atomic_habits_cover.jpg" -> "atomic habits")
    const cleanName = name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ');
    lookupBook(cleanName);
  }

  // Query Backend API
  window.lookupBook = function(query) {
    if (!query) return;
    statusBadge.textContent = `Querying BookNest catalog for "${query}"...`;

    fetch('api/scan-lookup.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ query: query })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        displayScanResult(data.book, data.match_type, data.confidence);
        statusBadge.textContent = `✓ Match found: "${data.book.title}" (${data.confidence}% confidence)`;
      } else {
        statusBadge.textContent = `No match found for "${query}".`;
        alert(data.message || 'No match found in catalog.');
      }
    })
    .catch(err => {
      console.error('Scan lookup error:', err);
      statusBadge.textContent = 'Lookup network error. Please try again.';
    });
  };

  // Render Result Card in DOM
  function displayScanResult(book, matchType, confidence) {
    if (!resultCard) return;

    resultCard.innerHTML = `
      <div style="background: #fff; border: 2px solid var(--color-primary); border-radius: var(--radius-xl); padding: 24px; box-shadow: var(--shadow-xl); animation: fadeIn 0.3s ease; text-align: left; display: grid; grid-template-columns: 120px 1fr; gap: 20px; align-items: start;">
        
        <div style="aspect-ratio: 3/4; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm); border: 1px solid #E2E8F0;">
          <img src="${book.cover_image}" alt="${book.title}" style="width:100%; height:100%; object-fit:cover;">
        </div>

        <div>
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
            <span style="background:#ECFDF5; color:#059669; border:1px solid #A7F3D0; font-size:0.75rem; font-weight:700; padding:2px 10px; border-radius:var(--radius-full);">
              ✓ ${matchType === 'exact_isbn' ? 'Exact 100% ISBN Match' : confidence + '% Fuzzy Cover Match'}
            </span>
            <span style="font-size:0.75rem; color:var(--color-text-muted);">
              ISBN: <code style="font-family:var(--font-mono); font-weight:700;">${book.isbn}</code>
            </span>
          </div>

          <h3 style="font-family:var(--font-serif); font-size:1.3rem; font-weight:700; color:var(--color-text-main); margin:0 0 4px 0; line-height:1.3;">
            ${book.title}
          </h3>
          <div style="font-size:0.85rem; color:var(--color-text-muted); margin-bottom:12px;">
            By <strong style="color:var(--color-text-main);">${book.author_name}</strong> • Genre: ${book.category_name}
          </div>

          <div style="display:flex; align-items:baseline; gap:8px; margin-bottom:14px;">
            <span style="font-size:1.4rem; font-weight:800; color:var(--color-text-main);">
              ${book.formatted_price}
            </span>
            ${book.discount > 0 ? `<span style="font-size:0.85rem; color:var(--color-text-muted); text-decoration:line-through;">₹${book.price}</span>` : ''}
            <span style="font-size:0.8rem; color:${book.stock > 0 ? '#059669' : '#DC2626'}; font-weight:600; margin-left:6px;">
              ● ${book.stock > 0 ? 'In Stock (' + book.stock + ' copies)' : 'Out of Stock'}
            </span>
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button onclick="addScannedBookToCart(${book.id})" class="btn btn-primary btn-sm" style="padding:8px 18px;">
              <span>🛒 Add to Cart</span>
            </button>
            <a href="book-details.php?id=${book.id}" class="btn btn-secondary btn-sm" style="padding:8px 18px;">
              <span>📖 View Details</span>
            </a>
          </div>
        </div>

      </div>
    `;

    resultCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  // Quick Cart Add helper
  window.addScannedBookToCart = function(bookId) {
    fetch('api/cart-add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ book_id: bookId, quantity: 1 })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('🎉 Book added to cart successfully!');
        window.location.href = 'cart.php';
      } else {
        alert(data.error || 'Failed to add item to cart');
      }
    });
  };

  // Quick simulation helper for demonstration chips
  window.simulateScan = function(isbn, title) {
    statusBadge.textContent = `Simulating barcode scan for "${title}" (${isbn})...`;
    setTimeout(() => {
      lookupBook(isbn);
    }, 400);
  };
});
