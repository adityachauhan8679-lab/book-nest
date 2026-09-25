import React, { useState } from 'react';
import { 
  X, 
  Star, 
  ShoppingCart, 
  Heart, 
  ShieldCheck, 
  Truck, 
  RotateCcw, 
  Plus, 
  Check, 
  MessageSquare, 
  Sparkles,
  Award
} from 'lucide-react';
import { Book, BookReview, BundleOffer } from '../types';

interface BookDetailModalProps {
  book: Book;
  allBooks: Book[];
  wishlist: number[];
  onClose: () => void;
  onAddToCart: (book: Book, quantity?: number) => void;
  onToggleWishlist: (bookId: number, bookTitle: string) => void;
  onAddBundleToCart: (book1: Book, book2: Book) => void;
}

export const BookDetailModal: React.FC<BookDetailModalProps> = ({
  book,
  allBooks,
  wishlist,
  onClose,
  onAddToCart,
  onToggleWishlist,
  onAddBundleToCart
}) => {
  const [detailQty, setDetailQty] = useState(1);
  const [activeTab, setActiveTab] = useState<'details' | 'reviews'>('details');

  // Reviews state with sample initial verified reviews
  const [reviews, setReviews] = useState<BookReview[]>([
    {
      id: 1,
      bookId: book.id,
      userName: 'Aditya Chauhan',
      rating: 5,
      title: 'Transformed my daily reading routine!',
      comment: 'One of the best books I have read this year. The concepts are actionable and clearly explained. Highly recommended for every university student!',
      isVerified: true,
      date: '2 days ago'
    },
    {
      id: 2,
      bookId: book.id,
      userName: 'Priya Sharma',
      rating: 5,
      title: 'Clear, concise, and deeply practical',
      comment: 'The book arrived in pristine condition with fast campus delivery. Packed with practical insights you can implement immediately.',
      isVerified: true,
      date: '1 week ago'
    },
    {
      id: 3,
      bookId: book.id,
      userName: 'Rohan Verma',
      rating: 4,
      title: 'Great reference and study guide',
      comment: 'Excellent layout and examples. Worth keeping on the desk throughout the semester.',
      isVerified: false,
      date: '2 weeks ago'
    }
  ]);

  // Review form state
  const [showReviewForm, setShowReviewForm] = useState(false);
  const [newRating, setNewRating] = useState(5);
  const [hoverRating, setHoverRating] = useState<number | null>(null);
  const [newTitle, setNewTitle] = useState('');
  const [newComment, setNewComment] = useState('');
  const [submittedFeedback, setSubmittedFeedback] = useState(false);

  // Compute Frequently Bought Together companion
  const companion = allBooks.find(b => b.id !== book.id && (b.categorySlug === book.categorySlug || b.id === (book.id === 1 ? 2 : 1))) || allBooks.find(b => b.id !== book.id);
  
  let bundleOffer: BundleOffer | null = null;
  if (companion) {
    const p1 = Math.round(book.price * (1 - book.discount / 100));
    const p2 = Math.round(companion.price * (1 - companion.discount / 100));
    const origTotal = p1 + p2;
    const bundlePrice = Math.round(origTotal * 0.90); // 10% bundle discount
    bundleOffer = {
      currentBook: book,
      companionBook: companion,
      originalTotal: origTotal,
      bundlePrice: bundlePrice,
      savings: origTotal - bundlePrice,
      discountPercent: 10
    };
  }

  // Calculate rating stats
  const totalReviews = reviews.length;
  const avgRating = totalReviews > 0 
    ? Number((reviews.reduce((acc, r) => acc + r.rating, 0) / totalReviews).toFixed(1))
    : book.rating;

  const starCounts: Record<number, number> = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
  reviews.forEach(r => {
    if (starCounts[r.rating] !== undefined) starCounts[r.rating]++;
  });

  const finalPrice = Math.round(book.price * (1 - book.discount / 100));
  const savings = book.price - finalPrice;
  const isWish = wishlist.includes(book.id);

  const ratingLabels: Record<number, string> = {
    1: '1.0 Star (Poor / Did not finish)',
    2: '2.0 Stars (Fair / Below expectations)',
    3: '3.0 Stars (Average / Good reference)',
    4: '4.0 Stars (Very Good / Highly recommended)',
    5: '5.0 Stars (Masterpiece / Essential Read)'
  };

  const handleReviewSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newComment.trim()) return;

    const newRev: BookReview = {
      id: Date.now(),
      bookId: book.id,
      userName: 'Aditya Chauhan',
      rating: newRating,
      title: newTitle.trim() || undefined,
      comment: newComment.trim(),
      isVerified: true,
      date: 'Just now'
    };

    setReviews([newRev, ...reviews]);
    setNewTitle('');
    setNewComment('');
    setShowReviewForm(false);
    setSubmittedFeedback(true);
    setTimeout(() => setSubmittedFeedback(false), 4000);
  };

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 animate-in fade-in duration-200">
      <div className="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] overflow-y-auto shadow-2xl border border-[#E8E5DF] relative flex flex-col">
        
        {/* Sticky Header with Close */}
        <div className="sticky top-0 bg-white/95 backdrop-blur-md px-6 py-4 border-b border-zinc-100 flex items-center justify-between z-10">
          <div className="flex items-center gap-2">
            <button
              onClick={() => setActiveTab('details')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-all ${
                activeTab === 'details' 
                  ? 'bg-blue-900 text-white shadow-sm' 
                  : 'text-zinc-600 hover:bg-zinc-100'
              }`}
            >
              Book Details & Bundle
            </button>
            <button
              onClick={() => setActiveTab('reviews')}
              className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 ${
                activeTab === 'reviews' 
                  ? 'bg-blue-900 text-white shadow-sm' 
                  : 'text-zinc-600 hover:bg-zinc-100'
              }`}
            >
              <span>Customer Reviews ({reviews.length})</span>
              <span className="bg-amber-400 text-zinc-950 font-bold px-1.5 py-0.2 rounded-full text-[10px]">
                {avgRating}★
              </span>
            </button>
          </div>

          <button 
            onClick={onClose}
            className="w-8 h-8 rounded-full bg-zinc-100 hover:bg-zinc-200 text-zinc-600 flex items-center justify-center transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Tab 1: Book Details & Frequently Bought Together */}
        {activeTab === 'details' && (
          <div className="p-6 sm:p-8 space-y-8">
            <div className="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
              
              {/* Cover Column */}
              <div className="md:col-span-5">
                <div className="aspect-[3/4] rounded-2xl overflow-hidden shadow-lg border border-zinc-200 mb-4 bg-zinc-50 relative group">
                  <img 
                    src={book.coverImage} 
                    alt={book.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  />
                  {book.discount > 0 && (
                    <span className="absolute top-3 left-3 bg-amber-600 text-white font-bold text-xs px-2.5 py-1 rounded-md shadow-md">
                      {book.discount}% OFF
                    </span>
                  )}
                </div>

                <div className="p-4 bg-zinc-50 rounded-2xl border border-zinc-200 text-xs text-zinc-600 space-y-2.5">
                  <div className="flex items-center gap-2 text-emerald-800 font-semibold">
                    <ShieldCheck className="w-4 h-4 text-emerald-600 flex-shrink-0" />
                    <span>100% Authentic Publisher Edition</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Truck className="w-4 h-4 text-blue-600 flex-shrink-0" />
                    <span>Free Campus Delivery on orders over ₹499</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <RotateCcw className="w-4 h-4 text-zinc-500 flex-shrink-0" />
                    <span>7-Day Replacement Guarantee</span>
                  </div>
                </div>
              </div>

              {/* Details Column */}
              <div className="md:col-span-7 flex flex-col">
                <div className="flex items-center gap-2 mb-2">
                  <span className="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-900 font-bold text-xs">
                    {book.category}
                  </span>
                  {book.discount > 0 && (
                    <span className="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-xs">
                      Save ₹{savings}
                    </span>
                  )}
                </div>

                <h2 className="font-serif text-2xl sm:text-3xl font-bold text-zinc-900 leading-snug mb-1">
                  {book.title}
                </h2>
                
                <div className="text-xs text-zinc-500 mb-4">
                  By <strong className="text-zinc-800 font-bold text-sm">{book.author}</strong>
                </div>

                {/* Rating & Stock */}
                <div className="flex items-center gap-3 text-xs mb-5">
                  <button 
                    onClick={() => setActiveTab('reviews')}
                    className="flex items-center text-amber-500 font-bold hover:underline"
                  >
                    <Star className="w-4 h-4 fill-amber-400 text-amber-400 mr-1" />
                    <span>{avgRating}</span>
                    <span className="text-zinc-400 ml-1">({reviews.length} reviews)</span>
                  </button>
                  <span className="text-zinc-300">•</span>
                  <span className="text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded">
                    ● In Stock ({book.stock} copies)
                  </span>
                </div>

                {/* Pricing Box */}
                <div className="p-4 bg-zinc-50 rounded-2xl border border-zinc-200 mb-5">
                  <div className="flex items-baseline gap-2">
                    <span className="text-3xl font-extrabold text-zinc-900">
                      ₹{finalPrice}
                    </span>
                    {book.discount > 0 && (
                      <span className="text-base text-zinc-400 line-through">
                        ₹{book.price}
                      </span>
                    )}
                  </div>
                  {book.discount > 0 && (
                    <div className="text-xs text-emerald-700 font-semibold mt-1">
                      You save ₹{savings} ({book.discount}% discount)
                    </div>
                  )}
                </div>

                {/* Synopsis */}
                <p className="text-xs text-zinc-600 leading-relaxed mb-5">
                  {book.description}
                </p>

                {/* Specifications Grid */}
                <div className="text-xs border-t border-zinc-100 pt-3 mb-6 grid grid-cols-2 gap-2 text-zinc-600 bg-zinc-50/50 p-3 rounded-xl">
                  <div><strong>ISBN:</strong> <code className="font-mono text-zinc-800">{book.isbn}</code></div>
                  <div><strong>Publisher:</strong> {book.publisher}</div>
                  <div><strong>Pages:</strong> {book.pages} pages</div>
                  <div><strong>Language:</strong> {book.language}</div>
                </div>

                {/* Add to Cart Actions */}
                <div className="flex items-center gap-3 mt-auto">
                  <div className="flex items-center border border-zinc-300 rounded-xl overflow-hidden bg-white shadow-sm">
                    <button 
                      onClick={() => setDetailQty(Math.max(1, detailQty - 1))}
                      className="w-9 h-10 flex items-center justify-center text-zinc-600 hover:bg-zinc-100 font-bold"
                    >
                      -
                    </button>
                    <span className="w-10 text-center text-xs font-bold text-zinc-900">{detailQty}</span>
                    <button 
                      onClick={() => setDetailQty(Math.min(book.stock, detailQty + 1))}
                      className="w-9 h-10 flex items-center justify-center text-zinc-600 hover:bg-zinc-100 font-bold"
                    >
                      +
                    </button>
                  </div>

                  <button 
                    onClick={() => {
                      onAddToCart(book, detailQty);
                      onClose();
                    }}
                    className="flex-1 py-3 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold text-xs shadow-md transition-all flex items-center justify-center gap-2"
                  >
                    <ShoppingCart className="w-4 h-4" />
                    <span>Add {detailQty > 1 ? `(${detailQty})` : ''} to Cart</span>
                  </button>

                  <button 
                    onClick={() => onToggleWishlist(book.id, book.title)}
                    className="w-10 h-10 rounded-xl border border-zinc-200 flex items-center justify-center text-zinc-600 hover:text-rose-500 hover:border-rose-300 transition-colors shadow-sm"
                  >
                    <Heart className={`w-4 h-4 ${isWish ? 'fill-rose-500 text-rose-500' : ''}`} />
                  </button>
                </div>

              </div>

            </div>

            {/* Smart Frequently Bought Together Bundle */}
            {bundleOffer && (
              <div className="bg-gradient-to-r from-amber-50/60 to-orange-50/40 border border-amber-200 rounded-3xl p-6 shadow-sm">
                <div className="flex items-center gap-2 mb-4">
                  <span className="bg-amber-500 text-zinc-950 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full flex items-center gap-1">
                    <Sparkles className="w-3 h-3" />
                    <span>Frequently Bought Together</span>
                  </span>
                  <span className="text-xs text-amber-900 font-medium">Extra 10% Smart Bundle Saving</span>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-6">
                  
                  {/* Two Books Duo */}
                  <div className="flex items-center gap-3 flex-wrap">
                    
                    {/* Current Book Preview */}
                    <div className="flex items-center gap-3">
                      <div className="w-14 h-20 rounded-lg overflow-hidden border border-zinc-200 shadow-sm flex-shrink-0">
                        <img src={bundleOffer.currentBook.coverImage} alt={bundleOffer.currentBook.title} className="w-full h-full object-cover" />
                      </div>
                      <div>
                        <div className="text-[11px] font-semibold text-zinc-500">This Book:</div>
                        <div className="text-xs font-bold text-zinc-900 max-w-[180px] line-clamp-1">{bundleOffer.currentBook.title}</div>
                        <div className="text-xs font-extrabold text-blue-900 mt-0.5">₹{Math.round(bundleOffer.currentBook.price * (1 - bundleOffer.currentBook.discount / 100))}</div>
                      </div>
                    </div>

                    <div className="text-xl font-black text-amber-500">+</div>

                    {/* Companion Book Preview */}
                    <div className="flex items-center gap-3">
                      <div className="w-14 h-20 rounded-lg overflow-hidden border border-zinc-200 shadow-sm flex-shrink-0">
                        <img src={bundleOffer.companionBook.coverImage} alt={bundleOffer.companionBook.title} className="w-full h-full object-cover" />
                      </div>
                      <div>
                        <div className="text-[11px] font-semibold text-amber-800">Companion Read:</div>
                        <div className="text-xs font-bold text-zinc-900 max-w-[180px] line-clamp-1">{bundleOffer.companionBook.title}</div>
                        <div className="text-xs font-extrabold text-blue-900 mt-0.5">₹{Math.round(bundleOffer.companionBook.price * (1 - bundleOffer.companionBook.discount / 100))}</div>
                      </div>
                    </div>

                  </div>

                  {/* Pricing & 1-Click Action */}
                  <div className="bg-white border border-amber-200 rounded-2xl p-4 min-w-[240px] flex-1 sm:flex-initial">
                    <div className="text-[11px] text-zinc-500">Combo Price for Both:</div>
                    <div className="flex items-baseline gap-2 mt-0.5">
                      <span className="text-2xl font-black text-zinc-900">₹{bundleOffer.bundlePrice}</span>
                      <span className="text-xs text-zinc-400 line-through">₹{bundleOffer.originalTotal}</span>
                    </div>
                    <div className="text-[11px] text-emerald-700 font-bold mb-3">
                      ✓ Save extra ₹{bundleOffer.savings} (10% combo discount)
                    </div>

                    <button
                      onClick={() => {
                        if (bundleOffer) {
                          onAddBundleToCart(bundleOffer.currentBook, bundleOffer.companionBook);
                          onClose();
                        }
                      }}
                      className="w-full py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-1.5"
                    >
                      <Plus className="w-3.5 h-3.5" />
                      <span>Add Both to Cart (₹{bundleOffer.bundlePrice})</span>
                    </button>
                  </div>

                </div>
              </div>
            )}

          </div>
        )}

        {/* Tab 2: Verified Reviews & Ratings */}
        {activeTab === 'reviews' && (
          <div className="p-6 sm:p-8 space-y-6">
            
            {/* Success notification banner if submitted */}
            {submittedFeedback && (
              <div className="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2 animate-in fade-in">
                <Check className="w-4 h-4 text-emerald-600" />
                <span>Thank you! Your verified student review was successfully published.</span>
              </div>
            )}

            {/* Dashboard: Rating Score & Star Distribution */}
            <div className="grid grid-cols-1 md:grid-cols-12 gap-6 bg-zinc-50 border border-zinc-200 rounded-3xl p-6">
              
              {/* Overall Score */}
              <div className="md:col-span-4 flex flex-col items-center justify-center text-center border-b md:border-b-0 md:border-r border-zinc-200 pb-4 md:pb-0 md:pr-4">
                <div className="text-5xl font-black text-zinc-900 leading-none mb-2">
                  {avgRating}
                </div>
                <div className="flex items-center text-amber-500 text-lg mb-1">
                  {[1, 2, 3, 4, 5].map((s) => (
                    <Star 
                      key={s} 
                      className={`w-4 h-4 ${s <= Math.round(avgRating) ? 'fill-amber-400 text-amber-400' : 'text-zinc-300'}`} 
                    />
                  ))}
                </div>
                <div className="text-xs text-zinc-500 font-medium">
                  Based on {totalReviews} student ratings
                </div>
                <div className="mt-3 inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-100/60 px-2.5 py-0.5 rounded-full">
                  <Award className="w-3 h-3" />
                  <span>100% Verified Community</span>
                </div>
              </div>

              {/* Star Bars */}
              <div className="md:col-span-8 flex flex-col justify-center space-y-2">
                {[5, 4, 3, 2, 1].map((s) => {
                  const cnt = starCounts[s] || 0;
                  const pct = totalReviews > 0 ? Math.round((cnt / totalReviews) * 100) : 0;
                  return (
                    <div key={s} className="flex items-center gap-3 text-xs">
                      <span className="w-10 font-bold text-zinc-700 flex items-center gap-1">
                        {s} <Star className="w-3 h-3 fill-amber-400 text-amber-400" />
                      </span>
                      <div className="flex-1 h-2.5 bg-zinc-200 rounded-full overflow-hidden">
                        <div 
                          className="h-full bg-amber-400 rounded-full transition-all duration-500"
                          style={{ width: `${pct}%` }}
                        />
                      </div>
                      <span className="w-10 text-right text-zinc-500 font-semibold">{pct}%</span>
                      <span className="w-8 text-right text-zinc-400 text-[11px]">({cnt})</span>
                    </div>
                  );
                })}
              </div>

            </div>

            {/* Action Bar */}
            <div className="flex items-center justify-between pt-2">
              <h3 className="font-serif text-lg font-bold text-zinc-900">
                Customer Reviews ({reviews.length})
              </h3>
              <button
                onClick={() => setShowReviewForm(!showReviewForm)}
                className="px-4 py-2 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold text-xs shadow-sm transition-all flex items-center gap-1.5"
              >
                <MessageSquare className="w-3.5 h-3.5" />
                <span>{showReviewForm ? 'Cancel Review' : 'Write a Review'}</span>
              </button>
            </div>

            {/* Interactive Review Submission Form */}
            {showReviewForm && (
              <form onSubmit={handleReviewSubmit} className="bg-white border-2 border-blue-900/40 rounded-2xl p-6 shadow-md space-y-4 animate-in fade-in">
                <h4 className="font-serif text-base font-bold text-zinc-900">
                  Submit Verified Student Review
                </h4>

                {/* Star Picker */}
                <div>
                  <label className="block text-xs font-bold text-zinc-700 mb-1.5">
                    Your Rating *
                  </label>
                  <div className="flex items-center gap-2">
                    <div className="flex items-center gap-1 text-2xl cursor-pointer">
                      {[1, 2, 3, 4, 5].map((val) => (
                        <button
                          type="button"
                          key={val}
                          onMouseEnter={() => setHoverRating(val)}
                          onMouseLeave={() => setHoverRating(null)}
                          onClick={() => setNewRating(val)}
                          className="transition-transform hover:scale-110"
                        >
                          <Star 
                            className={`w-6 h-6 ${
                              val <= (hoverRating ?? newRating)
                                ? 'fill-amber-400 text-amber-400' 
                                : 'text-zinc-200'
                            }`}
                          />
                        </button>
                      ))}
                    </div>
                    <span className="text-xs font-bold text-amber-800 ml-2">
                      {ratingLabels[hoverRating ?? newRating]}
                    </span>
                  </div>
                </div>

                {/* Headline */}
                <div>
                  <label className="block text-xs font-bold text-zinc-700 mb-1">
                    Review Headline (Optional)
                  </label>
                  <input
                    type="text"
                    value={newTitle}
                    onChange={(e) => setNewTitle(e.target.value)}
                    placeholder="e.g. Invaluable reference for our semester project"
                    className="w-full px-3 py-2 border border-zinc-300 rounded-xl text-xs outline-none focus:border-blue-900"
                  />
                </div>

                {/* Comment */}
                <div>
                  <label className="block text-xs font-bold text-zinc-700 mb-1">
                    Your Feedback *
                  </label>
                  <textarea
                    rows={3}
                    required
                    value={newComment}
                    onChange={(e) => setNewComment(e.target.value)}
                    placeholder="Share what you liked, how it helped with coursework or personal habits..."
                    className="w-full px-3 py-2 border border-zinc-300 rounded-xl text-xs outline-none focus:border-blue-900 resize-none"
                  />
                </div>

                <div className="flex gap-2 justify-end">
                  <button
                    type="button"
                    onClick={() => setShowReviewForm(false)}
                    className="px-3 py-1.5 rounded-lg border border-zinc-200 text-zinc-600 text-xs font-semibold hover:bg-zinc-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="px-4 py-1.5 rounded-lg bg-blue-900 hover:bg-blue-950 text-white text-xs font-bold shadow-sm transition-all"
                  >
                    Submit Review
                  </button>
                </div>
              </form>
            )}

            {/* Reviews List */}
            <div className="space-y-4">
              {reviews.map((rev) => (
                <div key={rev.id} className="bg-white border border-zinc-200 rounded-2xl p-5 shadow-sm space-y-2">
                  <div className="flex items-center justify-between gap-4 flex-wrap">
                    <div className="flex items-center gap-2">
                      <div className="w-7 h-7 rounded-full bg-blue-900 text-white text-xs font-bold flex items-center justify-center">
                        {rev.userName.charAt(0)}
                      </div>
                      <span className="font-bold text-xs text-zinc-900">{rev.userName}</span>
                      {rev.isVerified && (
                        <span className="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2 py-0.5 rounded-full">
                          <Check className="w-2.5 h-2.5" />
                          <span>Verified Purchaser</span>
                        </span>
                      )}
                    </div>

                    <div className="flex items-center gap-2">
                      <div className="flex text-amber-400">
                        {[1, 2, 3, 4, 5].map((s) => (
                          <Star 
                            key={s} 
                            className={`w-3 h-3 ${s <= rev.rating ? 'fill-amber-400' : 'text-zinc-200'}`} 
                          />
                        ))}
                      </div>
                      <span className="text-[11px] text-zinc-400">{rev.date}</span>
                    </div>
                  </div>

                  {rev.title && (
                    <h5 className="font-bold text-xs text-zinc-900 mt-1">
                      {rev.title}
                    </h5>
                  )}

                  <p className="text-xs text-zinc-600 leading-relaxed">
                    {rev.comment}
                  </p>
                </div>
              ))}
            </div>

          </div>
        )}

      </div>
    </div>
  );
};
