import React, { useState, useRef, useEffect } from 'react';
import { Book } from '../types';

interface EditorialScannerViewProps {
  books: Book[];
  onAddToCart: (book: Book, quantity?: number) => void;
  onQuickView: (book: Book) => void;
}

export const EditorialScannerView: React.FC<EditorialScannerViewProps> = ({
  books,
  onAddToCart,
  onQuickView,
}) => {
  const [isScanning, setIsScanning] = useState(false);
  const [scanStep, setScanStep] = useState<string>('Align textbook or barcode inside reticle');
  const [matchConfidence, setMatchConfidence] = useState<number | null>(null);
  const [scannedResult, setScannedResult] = useState<Book | null>(null);
  const [selectedSample, setSelectedSample] = useState<Book | null>(null);
  const [isCameraActive, setIsCameraActive] = useState(false);
  const [cameraError, setCameraError] = useState<string | null>(null);

  const videoRef = useRef<HTMLVideoElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Sample books to quickly test scan with
  const sampleBooks = books.slice(0, 4);

  // Trigger simulated scan sequence
  const executeScanSequence = (targetBook: Book) => {
    setIsScanning(true);
    setScannedResult(null);
    setMatchConfidence(null);
    setSelectedSample(targetBook);

    // Sequence stages
    setScanStep('Aligning optical viewfinder to book geometry...');
    setTimeout(() => {
      setScanStep('Performing optical text extraction & OCR parsing...');
    }, 600);

    setTimeout(() => {
      setScanStep(`Detecting EAN-13 Barcode: ${targetBook.isbn}...`);
    }, 1200);

    setTimeout(() => {
      setScanStep('Querying BookNest campus catalog index...');
    }, 1800);

    setTimeout(() => {
      setIsScanning(false);
      setScannedResult(targetBook);
      setMatchConfidence(99.4);
      setScanStep('Match confirmed! Catalog record retrieved.');
    }, 2400);
  };

  // Start real camera if user requests
  const toggleCamera = async () => {
    if (isCameraActive) {
      if (videoRef.current && videoRef.current.srcObject) {
        const stream = videoRef.current.srcObject as MediaStream;
        stream.getTracks().forEach(track => track.stop());
        videoRef.current.srcObject = null;
      }
      setIsCameraActive(false);
      return;
    }

    try {
      setCameraError(null);
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' }
      });
      if (videoRef.current) {
        videoRef.current.srcObject = stream;
        videoRef.current.play();
        setIsCameraActive(true);
      }
    } catch (err) {
      setCameraError('Camera access unavailable or declined. Using optical file simulation.');
      setIsCameraActive(false);
    }
  };

  useEffect(() => {
    return () => {
      if (videoRef.current && videoRef.current.srcObject) {
        const stream = videoRef.current.srcObject as MediaStream;
        stream.getTracks().forEach(track => track.stop());
      }
    };
  }, []);

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      // Pick random book or matching book to simulate OCR on upload
      const randomBook = books[Math.floor(Math.random() * books.length)];
      executeScanSequence(randomBook);
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      
      {/* Editorial Header */}
      <div className="max-w-3xl mx-auto text-center mb-10">
        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-semibold mb-3">
          <i className="fa-solid fa-barcode text-amber-600"></i>
          <span>OPTICAL COVER &amp; ISBN SCANNER</span>
        </div>
        <h1 className="font-serif font-black text-3xl sm:text-4xl text-slate-950 tracking-tight">
          Point, Scan, and Learn Instantly
        </h1>
        <p className="text-sm text-slate-600 mt-2 font-sans">
          Use your device camera or upload a book cover image to immediately extract ISBN metadata, verify campus availability, and add to your bag.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left Column: Viewfinder HUD Frame */}
        <div className="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xl">
          
          {/* Top HUD Controls */}
          <div className="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 text-xs">
            <div className="flex items-center gap-2">
              <span className={`w-2.5 h-2.5 rounded-full ${isScanning ? 'bg-amber-500 animate-ping' : 'bg-emerald-500'}`}></span>
              <span className="font-mono text-[11px] text-slate-700 font-semibold uppercase">
                {isScanning ? 'OCR Processing Active' : 'HUD Ready for Capture'}
              </span>
            </div>

            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={toggleCamera}
                className={`px-3 py-1.5 rounded-xl font-medium text-xs flex items-center gap-1.5 transition-colors ${
                  isCameraActive 
                    ? 'bg-rose-50 text-rose-700 border border-rose-200' 
                    : 'bg-slate-100 hover:bg-slate-200 text-slate-700'
                }`}
              >
                <i className={`fa-solid ${isCameraActive ? 'fa-video-slash' : 'fa-camera'} text-[11px]`}></i>
                <span>{isCameraActive ? 'Stop Camera' : 'Live Camera'}</span>
              </button>

              <button
                type="button"
                onClick={() => fileInputRef.current?.click()}
                className="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs flex items-center gap-1.5 transition-colors"
              >
                <i className="fa-solid fa-cloud-arrow-up text-[11px]"></i>
                <span>Upload Cover</span>
              </button>
              <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                onChange={handleFileUpload}
                className="hidden"
              />
            </div>
          </div>

          {cameraError && (
            <div className="mb-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
              <i className="fa-solid fa-circle-exclamation text-amber-600"></i>
              <span>{cameraError}</span>
            </div>
          )}

          {/* Viewfinder Reticle Box */}
          <div className="relative w-full aspect-[4/3] sm:aspect-[16/10] bg-slate-950 rounded-2xl overflow-hidden flex items-center justify-center border-2 border-slate-800 shadow-inner">
            
            {/* Live Camera Video if enabled */}
            <video
              ref={videoRef}
              playsInline
              muted
              className={`absolute inset-0 w-full h-full object-cover ${isCameraActive ? 'opacity-100' : 'opacity-0'}`}
            />

            {/* Simulated background cover if selected */}
            {!isCameraActive && selectedSample && (
              <img
                src={selectedSample.coverImage}
                alt={selectedSample.title}
                className="absolute inset-0 w-full h-full object-contain filter blur-[1px] opacity-40 scale-105"
              />
            )}

            {/* Glowing Corner HUD Reticles */}
            <div className="absolute top-6 left-6 w-8 h-8 border-t-2 border-l-2 border-amber-400 pointer-events-none"></div>
            <div className="absolute top-6 right-6 w-8 h-8 border-t-2 border-r-2 border-amber-400 pointer-events-none"></div>
            <div className="absolute bottom-6 left-6 w-8 h-8 border-b-2 border-l-2 border-amber-400 pointer-events-none"></div>
            <div className="absolute bottom-6 right-6 w-8 h-8 border-b-2 border-r-2 border-amber-400 pointer-events-none"></div>

            {/* Center Crosshair */}
            <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
              <div className="w-12 h-12 border border-dashed border-amber-400/60 rounded-full flex items-center justify-center">
                <div className="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></div>
              </div>
            </div>

            {/* Animated Laser Scan Line */}
            {isScanning && (
              <div className="absolute left-6 right-6 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent shadow-[0_0_15px_#f59e0b] animate-scan-line pointer-events-none" />
            )}

            {/* Idle State Prompt */}
            {!isScanning && !selectedSample && !isCameraActive && (
              <div className="relative z-10 text-center text-slate-400 p-6">
                <i className="fa-solid fa-qrcode text-4xl text-amber-500/80 mb-3 animate-pulse"></i>
                <p className="text-sm font-semibold text-slate-200">
                  Ready to Detect Books
                </p>
                <p className="text-xs text-slate-400 max-w-xs mt-1">
                  Choose a demo textbook below or click &ldquo;Upload Cover&rdquo; to test real-time OCR.
                </p>
              </div>
            )}

            {/* Overlay Status Kicker */}
            <div className="absolute bottom-3 left-4 right-4 bg-slate-900/80 backdrop-blur-md px-3.5 py-2 rounded-xl text-[11px] font-mono text-amber-300 flex items-center justify-between border border-slate-700/60">
              <span className="truncate">{scanStep}</span>
              {matchConfidence && (
                <span className="text-emerald-400 font-bold ml-2 shrink-0">
                  {matchConfidence}% Match
                </span>
              )}
            </div>

          </div>

          {/* Quick-Pick Sample Books for Instant Testing */}
          <div className="mt-6 pt-5 border-t border-slate-100">
            <div className="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center justify-between">
              <span>Quick Test Samples (Click to Simulate Optical Scan):</span>
            </div>

            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
              {sampleBooks.map((sample) => (
                <button
                  key={sample.id}
                  type="button"
                  onClick={() => executeScanSequence(sample)}
                  className={`p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all ${
                    selectedSample?.id === sample.id
                      ? 'bg-amber-50 border-amber-400 ring-2 ring-amber-300/40'
                      : 'bg-slate-50 hover:bg-white border-slate-200'
                  }`}
                >
                  <img
                    src={sample.coverImage}
                    alt={sample.title}
                    className="w-8 h-11 object-cover rounded shadow-2xs shrink-0"
                  />
                  <div className="min-w-0">
                    <div className="font-serif font-bold text-xs text-slate-900 truncate">
                      {sample.title}
                    </div>
                    <div className="text-[10px] text-slate-500 truncate font-mono">
                      {sample.isbn.slice(-6)}
                    </div>
                  </div>
                </button>
              ))}
            </div>
          </div>

        </div>

        {/* Right Column: OCR Result Inspection & Add to Cart Card */}
        <div className="lg:col-span-5 bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xl">
          
          <div className="text-xs font-bold text-amber-700 uppercase tracking-widest mb-3 flex items-center gap-1.5">
            <i className="fa-solid fa-microchip text-xs text-amber-500"></i>
            Database Extraction Result
          </div>

          {scannedResult ? (
            <div className="space-y-6 animate-in fade-in duration-200">
              
              {/* Match Banner */}
              <div className="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-between text-xs">
                <div className="flex items-center gap-2 text-emerald-900 font-semibold">
                  <i className="fa-solid fa-circle-check text-emerald-600"></i>
                  <span>Textbook Identified in Campus Catalog</span>
                </div>
                <span className="font-mono font-bold text-emerald-700">
                  99.4% Confidence
                </span>
              </div>

              {/* Book Overview Card */}
              <div className="flex gap-4">
                <div className="w-24 h-36 shrink-0 rounded-r-md rounded-l-xs overflow-hidden book-shadow-3d book-spine border border-slate-900/10">
                  <img
                    src={scannedResult.coverImage}
                    alt={scannedResult.title}
                    className="w-full h-full object-cover"
                  />
                </div>

                <div className="flex-1 flex flex-col justify-between">
                  <div>
                    <span className="text-xs font-semibold text-amber-700 uppercase">
                      {scannedResult.category}
                    </span>
                    <h3 className="font-serif font-bold text-base text-slate-900 line-clamp-2 mt-0.5">
                      {scannedResult.title}
                    </h3>
                    <p className="text-xs text-slate-600 mt-1">
                      by <span className="text-slate-800 font-medium">{scannedResult.author}</span>
                    </p>
                  </div>

                  <div className="mt-3 flex items-baseline gap-2">
                    <span className="font-serif font-bold text-xl text-slate-900">
                      ₹{Math.round(scannedResult.price * (1 - scannedResult.discount / 100))}
                    </span>
                    {scannedResult.discount > 0 && (
                      <span className="text-xs text-slate-400 line-through">
                        ₹{scannedResult.price}
                      </span>
                    )}
                  </div>
                </div>
              </div>

              {/* Extracted Metadata Grid */}
              <div className="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-200/70">
                <div>
                  <span className="text-slate-400 block text-[10px] uppercase">EAN-13 ISBN</span>
                  <span className="font-mono font-semibold text-slate-900">{scannedResult.isbn}</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[10px] uppercase">Publisher</span>
                  <span className="font-medium text-slate-900 truncate block">{scannedResult.publisher}</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[10px] uppercase">Inventory Status</span>
                  <span className="text-emerald-700 font-semibold">Available ({scannedResult.stock} units)</span>
                </div>
                <div>
                  <span className="text-slate-400 block text-[10px] uppercase">Rating</span>
                  <span className="text-amber-600 font-bold">★ {scannedResult.rating}</span>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex gap-3">
                <button
                  type="button"
                  onClick={() => onAddToCart(scannedResult)}
                  className="flex-1 py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-98"
                >
                  <i className="fa-solid fa-bag-shopping"></i>
                  <span>Add to Bag (₹{Math.round(scannedResult.price * (1 - scannedResult.discount / 100))})</span>
                </button>

                <button
                  type="button"
                  onClick={() => onQuickView(scannedResult)}
                  className="px-4 py-3.5 rounded-2xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold text-xs transition-colors"
                >
                  Quick View
                </button>
              </div>

            </div>
          ) : (
            <div className="h-64 flex flex-col items-center justify-center text-center p-6 text-slate-400">
              <div className="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-2xl text-slate-400 mb-3">
                <i className="fa-solid fa-crosshairs"></i>
              </div>
              <h4 className="font-serif font-bold text-slate-800 text-sm">
                No Scan Captured Yet
              </h4>
              <p className="text-xs text-slate-500 max-w-xs mt-1">
                Align any book cover or click one of the quick test textbook buttons on the left to extract metadata.
              </p>
            </div>
          )}

        </div>

      </div>

    </div>
  );
};
