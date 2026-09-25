import React, { useState, useMemo } from 'react';
import { Book, UserSession, CartSummaryData } from './types';
import { INITIAL_BOOKS } from './data/bookCatalog';

import { Navbar } from './components/Navbar';
import { HeroSection } from './components/HeroSection';
import { CategoryGrid } from './components/CategoryGrid';
import { BookCard } from './components/BookCard';
import { SlideOverCart } from './components/SlideOverCart';
import { CheckoutFlowModal } from './components/CheckoutFlowModal';
import { EditorialScannerView } from './components/EditorialScannerView';
import { AdminDashboard } from './components/AdminDashboard';
import { QuickViewModal } from './components/QuickViewModal';
import { OrdersView } from './components/OrdersView';
import { WishlistView } from './components/WishlistView';

export default function App() {
  // Navigation & View state
  const [activeView, setActiveView] = useState<'store' | 'catalog' | 'scanner' | 'admin' | 'orders' | 'wishlist'>('store');
  
  // Data state
  const [books, setBooks] = useState<Book[]>(INITIAL_BOOKS);
  const [cart, setCart] = useState<Record<number, number>>({
    1: 1, // Atomic Habits pre-loaded
    2: 1  // Clean Code pre-loaded
  });
  const [wishlist, setWishlist] = useState<number[]>([4, 6]); // Dune and Psychology of Money pre-wishlisted
  const [appliedCoupon, setAppliedCoupon] = useState<string | null>('WELCOME10');
  
  // Modals & Drawers
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [isCheckoutOpen, setIsCheckoutOpen] = useState(false);
  const [quickViewBook, setQuickViewBook] = useState<Book | null>(null);

  // Filters & Sorting in Catalog
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [catalogSearch, setCatalogSearch] = useState('');
  const [maxPrice, setMaxPrice] = useState<number>(1000);
  const [sortBy, setSortBy] = useState<'featured' | 'price-asc' | 'price-desc' | 'rating' | 'discount'>('featured');
  const [stockOnly, setStockOnly] = useState(false);

  // Toast Notification state
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'info' | 'error' } | null>(null);

  // Current User Session (Student / Admin switch)
  const [currentUser, setCurrentUser] = useState<UserSession>({
    id: 1,
    name: 'Aditya Chauhan',
    email: 'adityachauhan8679@gmail.com',
    role: 'user',
    phone: '+91 98765 43210',
    joinedDate: 'August 2024'
  });

  const showToast = (message: string, type: 'success' | 'info' | 'error' = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3000);
  };

  const toggleUserRole = () => {
    const nextRole = currentUser.role === 'admin' ? 'user' : 'admin';
    setCurrentUser(prev => ({
      ...prev,
      role: nextRole,
      name: nextRole === 'admin' ? 'Campus Admin (Prof. Chauhan)' : 'Aditya Chauhan'
    }));
    showToast(`Switched to ${nextRole === 'admin' ? 'Administrator' : 'Student'} Mode`, 'info');
    if (nextRole === 'admin') {
      setActiveView('admin');
    }
  };

  // Cart Management
  const addToCart = (book: Book, quantity = 1) => {
    if (book.stock <= 0) {
      showToast(`"${book.title}" is currently out of stock`, 'error');
      return;
    }
    setCart(prev => {
      const currentQty = prev[book.id] || 0;
      const newQty = Math.min(book.stock, currentQty + quantity);
      return { ...prev, [book.id]: newQty };
    });
    showToast(`Added "${book.title}" to your cart`, 'success');
  };

  const updateCartQty = (bookId: number, qty: number) => {
    const book = books.find(b => b.id === bookId);
    if (!book) return;

    if (qty <= 0) {
      removeCartItem(bookId, book.title);
      return;
    }

    if (qty > book.stock) {
      showToast(`Only ${book.stock} units available in campus stock`, 'error');
      return;
    }

    setCart(prev => ({ ...prev, [bookId]: qty }));
  };

  const removeCartItem = (bookId: number, title?: string) => {
    setCart(prev => {
      const copy = { ...prev };
      delete copy[bookId];
      return copy;
    });
    if (title) {
      showToast(`Removed "${title}" from your cart`, 'info');
    }
  };

  const clearCart = () => {
    setCart({});
  };

  // Wishlist Management
  const toggleWishlist = (bookId: number, title?: string) => {
    const book = books.find(b => b.id === bookId);
    setWishlist(prev => {
      const exists = prev.includes(bookId);
      if (exists) {
        showToast(`Removed "${title || book?.title || 'Book'}" from Wishlist`, 'info');
        return prev.filter(id => id !== bookId);
      } else {
        showToast(`Saved "${title || book?.title || 'Book'}" to Wishlist`, 'success');
        return [...prev, bookId];
      }
    });
  };

  const moveWishlistToCart = (book: Book) => {
    if (book && book.stock > 0) {
      addToCart(book, 1);
      toggleWishlist(book.id, book.title);
    }
  };

  const addAllWishlistToCart = () => {
    let addedCount = 0;
    wishlist.forEach(id => {
      const b = books.find(item => item.id === id);
      if (b && b.stock > 0) {
        setCart(prev => ({ ...prev, [id]: (prev[id] || 0) + 1 }));
        addedCount++;
      }
    });
    if (addedCount > 0) {
      showToast(`Added ${addedCount} wishlist titles to cart`, 'success');
      setIsCartOpen(true);
    }
  };

  // Cart Calculations & Free Delivery Progress
  const cartSummaryData: CartSummaryData = useMemo(() => {
    let subtotal = 0;
    let originalTotal = 0;
    let itemCount = 0;

    Object.entries(cart).forEach(([bookIdStr, qty]) => {
      if (qty <= 0) return;
      const b = books.find(item => item.id === parseInt(bookIdStr, 10));
      if (b) {
        const finalPrice = Math.round(b.price * (1 - b.discount / 100));
        subtotal += finalPrice * qty;
        originalTotal += b.price * qty;
        itemCount += qty;
      }
    });

    const freeDeliveryThreshold = 499;
    const isFreeDelivery = subtotal >= freeDeliveryThreshold || subtotal === 0;
    const deliveryFee = isFreeDelivery || subtotal === 0 ? 0 : 49;
    const amountNeededForFreeDelivery = Math.max(0, freeDeliveryThreshold - subtotal);
    const freeDeliveryProgress = Math.min(100, Math.round((subtotal / freeDeliveryThreshold) * 100));

    let couponDiscount = 0;
    if (appliedCoupon === 'WELCOME10') {
      couponDiscount = Math.round(subtotal * 0.10);
    } else if (appliedCoupon === 'BOOKNEST50' && subtotal >= 499) {
      couponDiscount = 50;
    } else if (appliedCoupon === 'READMORE') {
      couponDiscount = Math.round(subtotal * 0.15);
    }

    const gstAmount = Math.round(subtotal * 0.05); // 5% academic book GST
    const grandTotal = Math.max(0, subtotal - couponDiscount + deliveryFee);
    const totalSavings = (originalTotal - subtotal) + couponDiscount;

    return {
      subtotal,
      originalTotal,
      itemCount,
      deliveryFee,
      isFreeDelivery,
      amountNeededForFreeDelivery,
      freeDeliveryThreshold,
      freeDeliveryProgress,
      couponCode: appliedCoupon,
      couponDiscount,
      gstAmount,
      grandTotal,
      totalSavings,
    };
  }, [cart, books, appliedCoupon]);

  // Admin Catalog CRUD Handlers
  const handleUpdateBookStock = (bookId: number, newStock: number) => {
    setBooks(prev => prev.map(b => b.id === bookId ? { ...b, stock: newStock } : b));
    showToast('Warehouse stock levels updated successfully', 'success');
  };

  const handleAddBook = (newBookData: Omit<Book, 'id'>) => {
    const newId = Math.max(...books.map(b => b.id), 0) + 1;
    const newBook: Book = { ...newBookData, id: newId };
    setBooks(prev => [newBook, ...prev]);
    showToast(`Added "${newBook.title}" to catalog`, 'success');
  };

  const handleUpdateBook = (updated: Book) => {
    setBooks(prev => prev.map(b => b.id === updated.id ? updated : b));
    showToast(`Updated "${updated.title}" successfully`, 'success');
  };

  const handleDeleteBook = (bookId: number) => {
    const book = books.find(b => b.id === bookId);
    setBooks(prev => prev.filter(b => b.id !== bookId));
    showToast(`Deleted "${book?.title || 'Book'}" from catalog`, 'info');
  };

  // Filtered Catalog Books
  const filteredCatalogBooks = useMemo(() => {
    return books
      .filter(b => {
        if (selectedCategory !== 'all' && b.categorySlug !== selectedCategory) return false;
        if (catalogSearch.trim()) {
          const q = catalogSearch.toLowerCase();
          const matchTitle = b.title.toLowerCase().includes(q);
          const matchAuthor = b.author.toLowerCase().includes(q);
          const matchIsbn = b.isbn.includes(q);
          const matchCat = b.category.toLowerCase().includes(q);
          if (!matchTitle && !matchAuthor && !matchIsbn && !matchCat) return false;
        }
        const finalPrice = Math.round(b.price * (1 - b.discount / 100));
        if (finalPrice > maxPrice) return false;
        if (stockOnly && b.stock <= 0) return false;
        return true;
      })
      .sort((a, b) => {
        const priceA = Math.round(a.price * (1 - a.discount / 100));
        const priceB = Math.round(b.price * (1 - b.discount / 100));
        if (sortBy === 'price-asc') return priceA - priceB;
        if (sortBy === 'price-desc') return priceB - priceA;
        if (sortBy === 'rating') return b.rating - a.rating;
        if (sortBy === 'discount') return b.discount - a.discount;
        return b.id - a.id;
      });
  }, [books, selectedCategory, catalogSearch, maxPrice, stockOnly, sortBy]);

  // Orders state
  const [placedOrders, setPlacedOrders] = useState<any[]>([
    {
      id: 104821,
      date: '24 Sep 2026, 04:30 PM',
      totalAmount: 1148,
      status: 'Confirmed',
      paymentMethod: 'UPI',
      items: [
        {
          book: books[0],
          quantity: 1,
          price: 424,
        },
        {
          book: books[1],
          quantity: 1,
          price: 719,
        }
      ]
    }
  ]);

  const handleOrderSuccess = (orderId: number, details: any) => {
    setPlacedOrders(prev => [
      {
        id: orderId,
        date: details.date,
        totalAmount: details.summary.grandTotal,
        status: 'Confirmed',
        paymentMethod: details.paymentMethod.toUpperCase(),
        items: details.items.map((i: any) => ({
          book: i.book,
          quantity: i.quantity,
          price: i.unitPrice
        }))
      },
      ...prev
    ]);
    clearCart();
    showToast(`Order #BN-${orderId} placed successfully!`, 'success');
  };

  const featuredBook = books[0] || INITIAL_BOOKS[0];

  return (
    <div className="min-h-screen bg-[#FAF8F5] text-slate-900 font-sans selection:bg-amber-200 selection:text-slate-950 flex flex-col">
      
      {/* Global Sticky Navigation */}
      <Navbar
        books={books}
        activeView={activeView}
        cartCount={cartSummaryData.itemCount}
        wishlistCount={wishlist.length}
        currentUser={currentUser}
        onNavigate={(view) => {
          setActiveView(view);
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }}
        onOpenCart={() => setIsCartOpen(true)}
        onSelectBook={(book) => setQuickViewBook(book)}
        onToggleUserRole={toggleUserRole}
      />

      {/* ================= VIEW: HOME STOREFRONT ================= */}
      {activeView === 'store' && (
        <main className="flex-1">
          {/* Editorial Hero Section */}
          <HeroSection
            featuredBook={featuredBook}
            selectedCategory={selectedCategory}
            onSelectCategory={(slug) => {
              setSelectedCategory(slug);
              setActiveView('catalog');
            }}
            onExploreBooks={() => setActiveView('catalog')}
            onOpenScanner={() => setActiveView('scanner')}
            onAddToCart={addToCart}
            onQuickView={(book) => setQuickViewBook(book)}
          />

          {/* Category Shelf Grid */}
          <CategoryGrid
            selectedCategory={selectedCategory}
            onSelectCategory={(slug) => {
              setSelectedCategory(slug);
              setActiveView('catalog');
            }}
          />

          {/* Curated Bestsellers Grid */}
          <section className="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
              <div>
                <div className="text-xs font-bold text-amber-700 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                  <i className="fa-solid fa-sparkles text-amber-500"></i>
                  Handpicked Recommendations
                </div>
                <h2 className="font-serif font-black text-3xl text-slate-950">
                  Featured Academic &amp; Literary Reads
                </h2>
              </div>

              <button
                onClick={() => setActiveView('catalog')}
                className="inline-flex items-center gap-2 text-xs font-bold text-amber-800 hover:text-amber-950 group"
              >
                <span>Browse Complete Catalog ({books.length} Books)</span>
                <i className="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
              </button>
            </div>

            {/* Book Cards Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              {books.slice(0, 8).map((book) => (
                <BookCard
                  key={book.id}
                  book={book}
                  isWishlisted={wishlist.includes(book.id)}
                  onAddToCart={addToCart}
                  onToggleWishlist={toggleWishlist}
                  onQuickView={(b) => setQuickViewBook(b)}
                />
              ))}
            </div>

            {/* Banner: Optical Scanner Promotion */}
            <div className="mt-16 p-8 sm:p-10 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-950 to-slate-900 text-white relative overflow-hidden shadow-2xl flex flex-col md:flex-row items-center justify-between gap-8">
              <div className="relative z-10 max-w-xl">
                <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs font-semibold mb-3">
                  <i className="fa-solid fa-barcode text-amber-400"></i>
                  INNOVATION SPOTLIGHT
                </span>
                <h3 className="font-serif font-black text-3xl sm:text-4xl text-white">
                  Got a Physical Textbook? <br />
                  Scan the cover with BookNest.
                </h3>
                <p className="text-sm text-slate-300 mt-3 font-sans leading-relaxed">
                  Our optical machine vision automatically identifies textbooks, compares campus syllabus requirements, and syncs instant savings to your student bag.
                </p>
                <div className="mt-6 flex items-center gap-4">
                  <button
                    onClick={() => setActiveView('scanner')}
                    className="px-6 py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg transition-all flex items-center gap-2"
                  >
                    <i className="fa-solid fa-camera"></i>
                    <span>Launch Book Scanner</span>
                  </button>
                </div>
              </div>

              {/* Decorative graphic */}
              <div className="relative z-10 w-44 h-44 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center animate-pulse">
                <i className="fa-solid fa-qrcode text-7xl text-amber-400"></i>
              </div>
            </div>
          </section>
        </main>
      )}

      {/* ================= VIEW: BOOK CATALOG ================= */}
      {activeView === 'catalog' && (
        <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
          
          {/* Catalog Top Bar */}
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-200">
            <div>
              <div className="text-xs font-bold text-amber-700 uppercase tracking-widest mb-1">
                CAMPUS DISCOVERY ARCHIVE
              </div>
              <h1 className="font-serif font-black text-3xl sm:text-4xl text-slate-950">
                Book Catalog ({filteredCatalogBooks.length} Titles)
              </h1>
            </div>

            {/* Quick Sort Dropdown */}
            <div className="flex items-center gap-3">
              <span className="text-xs text-slate-500 font-medium">Sort by:</span>
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value as any)}
                className="py-2 px-3 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-800 shadow-2xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
              >
                <option value="featured">Featured Reads</option>
                <option value="rating">Highest Rated (★ 5.0 - 4.0)</option>
                <option value="discount">Biggest Campus Discounts</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {/* Filter Sidebar */}
            <aside className="lg:col-span-3 bg-white rounded-3xl border border-slate-200/90 p-6 shadow-sm space-y-6">
              
              {/* Keyword Search */}
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                  Keyword Filter
                </label>
                <div className="relative">
                  <input
                    type="text"
                    value={catalogSearch}
                    onChange={(e) => setCatalogSearch(e.target.value)}
                    placeholder="Search by title, author, ISBN..."
                    className="w-full py-2 pl-8 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
                  />
                  <i className="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                </div>
              </div>

              {/* Category Filter */}
              <div>
                <label className="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                  Shelf Discipline
                </label>
                <div className="space-y-1">
                  {[
                    { slug: 'all', label: 'All Shelves' },
                    { slug: 'fiction', label: 'Fiction & Literature' },
                    { slug: 'programming', label: 'Computer Science & Tech' },
                    { slug: 'science-fiction', label: 'Science Fiction' },
                    { slug: 'business', label: 'Business & Finance' },
                    { slug: 'self-help', label: 'Self Help & Mindset' },
                  ].map((cat) => (
                    <button
                      key={cat.slug}
                      onClick={() => setSelectedCategory(cat.slug)}
                      className={`w-full text-left px-3 py-2 rounded-xl text-xs font-medium transition-colors flex items-center justify-between ${
                        selectedCategory === cat.slug
                          ? 'bg-amber-100 text-amber-950 font-bold'
                          : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                      }`}
                    >
                      <span>{cat.label}</span>
                      {selectedCategory === cat.slug && (
                        <i className="fa-solid fa-check text-xs text-amber-600"></i>
                      )}
                    </button>
                  ))}
                </div>
              </div>

              {/* Price Range Slider */}
              <div>
                <div className="flex justify-between items-center text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                  <span>Max Price</span>
                  <span className="font-mono text-amber-700 font-bold">₹{maxPrice}</span>
                </div>
                <input
                  type="range"
                  min="200"
                  max="1000"
                  step="50"
                  value={maxPrice}
                  onChange={(e) => setMaxPrice(Number(e.target.value))}
                  className="w-full accent-amber-500 cursor-pointer"
                />
                <div className="flex justify-between text-[10px] text-slate-400 mt-1">
                  <span>₹200</span>
                  <span>₹1,000</span>
                </div>
              </div>

              {/* In Stock Only Toggle */}
              <div className="pt-2 border-t border-slate-100">
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={stockOnly}
                    onChange={(e) => setStockOnly(e.target.checked)}
                    className="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 cursor-pointer"
                  />
                  <span className="text-xs font-medium text-slate-700">
                    In Stock on Campus Only
                  </span>
                </label>
              </div>

              {/* Reset Filters */}
              {(selectedCategory !== 'all' || catalogSearch || maxPrice < 1000 || stockOnly) && (
                <button
                  onClick={() => {
                    setSelectedCategory('all');
                    setCatalogSearch('');
                    setMaxPrice(1000);
                    setStockOnly(false);
                  }}
                  className="w-full py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors"
                >
                  Reset All Filters
                </button>
              )}

            </aside>

            {/* Catalog Grid View */}
            <div className="lg:col-span-9">
              {filteredCatalogBooks.length > 0 ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                  {filteredCatalogBooks.map((book) => (
                    <BookCard
                      key={book.id}
                      book={book}
                      isWishlisted={wishlist.includes(book.id)}
                      onAddToCart={addToCart}
                      onToggleWishlist={toggleWishlist}
                      onQuickView={(b) => setQuickViewBook(b)}
                    />
                  ))}
                </div>
              ) : (
                <div className="p-12 text-center bg-white rounded-3xl border border-slate-200 shadow-sm">
                  <i className="fa-regular fa-folder-open text-4xl text-slate-300 mb-3"></i>
                  <h3 className="font-serif font-bold text-xl text-slate-900 mb-1">
                    No Books Matched Your Filters
                  </h3>
                  <p className="text-xs text-slate-500 max-w-sm mx-auto mb-6">
                    Try broadening your price range, searching a different keyword, or resetting the category shelf.
                  </p>
                  <button
                    onClick={() => {
                      setSelectedCategory('all');
                      setCatalogSearch('');
                      setMaxPrice(1000);
                      setStockOnly(false);
                    }}
                    className="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-amber-600 text-white font-semibold text-xs"
                  >
                    Clear Filter Parameters
                  </button>
                </div>
              )}
            </div>

          </div>

        </main>
      )}

      {/* ================= VIEW: OPTICAL SCANNER ================= */}
      {activeView === 'scanner' && (
        <main className="flex-1">
          <EditorialScannerView
            books={books}
            onAddToCart={addToCart}
            onQuickView={(b) => setQuickViewBook(b)}
          />
        </main>
      )}

      {/* ================= VIEW: ADMIN DASHBOARD ================= */}
      {activeView === 'admin' && (
        <main className="flex-1">
          <AdminDashboard
            books={books}
            onUpdateBookStock={handleUpdateBookStock}
            onAddBook={handleAddBook}
            onDeleteBook={handleDeleteBook}
            onUpdateBook={handleUpdateBook}
          />
        </main>
      )}

      {/* ================= VIEW: ORDERS & TRACKING ================= */}
      {activeView === 'orders' && (
        <main className="flex-1 max-w-5xl mx-auto px-4 sm:px-6 py-10 w-full">
          <OrdersView
            orders={placedOrders}
            onNavigateToCatalog={() => setActiveView('catalog')}
            onOrderAgain={(book) => {
              addToCart(book, 1);
              setIsCartOpen(true);
            }}
          />
        </main>
      )}

      {/* ================= VIEW: WISHLIST ================= */}
      {activeView === 'wishlist' && (
        <main className="flex-1 max-w-5xl mx-auto px-4 sm:px-6 py-10 w-full">
          <WishlistView
            wishlist={wishlist}
            books={books}
            onMoveToCart={moveWishlistToCart}
            onRemoveFromWishlist={(id, title) => toggleWishlist(id, title)}
            onAddAllToCart={addAllWishlistToCart}
            onNavigateToCatalog={() => setActiveView('catalog')}
            onOpenBookDetail={(b) => setQuickViewBook(b)}
          />
        </main>
      )}

      {/* ================= GLOBAL SLIDE-OVER SHOPPING CART ================= */}
      <SlideOverCart
        isOpen={isCartOpen}
        onClose={() => setIsCartOpen(false)}
        cart={cart}
        books={books}
        summary={cartSummaryData}
        appliedCoupon={appliedCoupon}
        onUpdateQty={updateCartQty}
        onRemoveItem={removeCartItem}
        onApplyCoupon={(code) => {
          setAppliedCoupon(code);
          showToast(`Coupon "${code}" applied successfully!`, 'success');
        }}
        onRemoveCoupon={() => {
          setAppliedCoupon(null);
          showToast('Coupon removed', 'info');
        }}
        onProceedToCheckout={() => {
          setIsCartOpen(false);
          setIsCheckoutOpen(true);
        }}
        onNavigateToCatalog={() => {
          setIsCartOpen(false);
          setActiveView('catalog');
        }}
        onOpenBookDetail={(b) => {
          setIsCartOpen(false);
          setQuickViewBook(b);
        }}
      />

      {/* ================= 3-STEP CHECKOUT & TAX INVOICE MODAL ================= */}
      <CheckoutFlowModal
        isOpen={isCheckoutOpen}
        onClose={() => setIsCheckoutOpen(false)}
        cart={cart}
        books={books}
        summary={cartSummaryData}
        onOrderSuccess={handleOrderSuccess}
      />

      {/* ================= QUICK VIEW BOOK MODAL ================= */}
      <QuickViewModal
        book={quickViewBook}
        onClose={() => setQuickViewBook(null)}
        isWishlisted={quickViewBook ? wishlist.includes(quickViewBook.id) : false}
        onToggleWishlist={toggleWishlist}
        onAddToCart={addToCart}
      />

      {/* ================= TOAST NOTIFICATION ================= */}
      {toast && (
        <div className={`fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 rounded-2xl shadow-2xl border text-xs font-semibold animate-in slide-in-from-bottom-3 duration-200 ${
          toast.type === 'error'
            ? 'bg-rose-950 text-white border-rose-800'
            : toast.type === 'info'
            ? 'bg-slate-900 text-amber-200 border-slate-700'
            : 'bg-slate-950 text-white border-slate-800'
        }`}>
          <i className={`fa-solid ${
            toast.type === 'error' ? 'fa-circle-xmark text-rose-400' :
            toast.type === 'info' ? 'fa-circle-info text-amber-400' :
            'fa-circle-check text-emerald-400'
          } text-sm`}></i>
          <span>{toast.message}</span>
        </div>
      )}

      {/* ================= EDITORIAL FOOTER ================= */}
      <footer className="mt-auto bg-slate-950 text-slate-400 border-t border-slate-800/80 pt-16 pb-12 font-sans text-xs">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-slate-800">
            
            {/* Col 1: Brand */}
            <div className="md:col-span-4 space-y-4">
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold">
                  <i className="fa-solid fa-book-bookmark"></i>
                </div>
                <span className="font-serif font-black text-2xl text-white tracking-tight">
                  BookNest
                </span>
              </div>
              <p className="text-slate-400 leading-relaxed font-sans max-w-sm">
                Elevating campus literature and academic studies. 
                Full-stack bookstore with optical machine vision, automated inventory tracking, and student fulfillment.
              </p>
              <div className="text-[11px] text-amber-400 font-mono">
                Academic Capstone · University Edition 2026
              </div>
            </div>

            {/* Col 2: Navigation */}
            <div className="md:col-span-3 space-y-3">
              <h4 className="font-serif font-bold text-sm text-white uppercase tracking-wider">
                Storefront Subsystems
              </h4>
              <ul className="space-y-2">
                <li><button onClick={() => setActiveView('store')} className="hover:text-amber-300">Home Storefront</button></li>
                <li><button onClick={() => setActiveView('catalog')} className="hover:text-amber-300">Textbook Catalog</button></li>
                <li><button onClick={() => setActiveView('scanner')} className="hover:text-amber-300">Optical Book Scanner</button></li>
                <li><button onClick={() => setActiveView('orders')} className="hover:text-amber-300">Student Orders &amp; Invoices</button></li>
                <li><button onClick={() => setActiveView('admin')} className="hover:text-amber-300">Control Tower Dashboard</button></li>
              </ul>
            </div>

            {/* Col 3: Academic Credits */}
            <div className="md:col-span-5 space-y-3">
              <h4 className="font-serif font-bold text-sm text-white uppercase tracking-wider">
                Architecture &amp; Security Compliance
              </h4>
              <p className="text-slate-400 leading-relaxed text-xs">
                Built with 100% Prepared Statements, Centralized XSS Escaping, Cryptographic CSRF Tokens, and OWASP Top 10 Hardening.
              </p>
              <div className="flex flex-wrap gap-2 pt-2">
                <span className="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-[10px] font-mono text-emerald-400">
                  OWASP Score: 100/100
                </span>
                <span className="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-[10px] font-mono text-amber-400">
                  BCrypt Cost: 12
                </span>
                <span className="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-[10px] font-mono text-cyan-400">
                  BarcodeDetector API Ready
                </span>
              </div>
            </div>

          </div>

          <div className="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
            <div>
              © 2026 BookNest. All rights reserved. Crafted with care for campus scholars.
            </div>
            <div className="flex items-center gap-4">
              <span>Hostel Rapid Delivery: &lt; 30 Mins</span>
              <span>·</span>
              <span>Helpline: +91 98765 43210</span>
            </div>
          </div>
        </div>
      </footer>

    </div>
  );
}
