import React, { useState } from 'react';
import { 
  Trash2, 
  Heart, 
  ArrowRight, 
  Tag, 
  Truck, 
  ShieldCheck, 
  RotateCcw, 
  Check, 
  Sparkles,
  ShoppingBag
} from 'lucide-react';
import { Book, CartSummaryData } from '../types';

interface CartViewProps {
  cart: { [id: number]: number };
  books: Book[];
  summary: CartSummaryData;
  appliedCoupon: string | null;
  onUpdateQty: (bookId: number, qty: number) => void;
  onRemoveItem: (bookId: number, title: string) => void;
  onMoveToWishlist: (bookId: number, title: string) => void;
  onClearCart: () => void;
  onApplyCoupon: (code: string) => void;
  onRemoveCoupon: () => void;
  onOpenCheckout: () => void;
  onNavigateToCatalog: () => void;
  onOpenBookDetail: (book: Book) => void;
}

export const CartView: React.FC<CartViewProps> = ({
  cart,
  books,
  summary,
  appliedCoupon,
  onUpdateQty,
  onRemoveItem,
  onMoveToWishlist,
  onClearCart,
  onApplyCoupon,
  onRemoveCoupon,
  onOpenCheckout,
  onNavigateToCatalog,
  onOpenBookDetail
}) => {
  const [couponInput, setCouponInput] = useState(appliedCoupon || '');

  const cartEntries = Object.entries(cart)
    .map(([id, qty]) => {
      const book = books.find(b => b.id === Number(id));
      return book ? { book, qty } : null;
    })
    .filter((item): item is { book: Book; qty: number } => item !== null);

  if (cartEntries.length === 0) {
    return (
      <div className="max-w-4xl mx-auto px-4 py-16 text-center">
        <div className="bg-white border border-[#E8E5DF] rounded-3xl p-12 max-w-lg mx-auto shadow-sm">
          <div className="w-20 h-20 bg-blue-50 text-blue-900 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">
            🛒
          </div>
          <h2 className="font-serif text-2xl font-bold text-zinc-900 mb-2">
            Your cart is currently empty
          </h2>
          <p className="text-sm text-zinc-500 mb-8 max-w-sm mx-auto leading-relaxed">
            Before proceeding to checkout, discover great reads across computer science, literature, business, and personal mastery.
          </p>
          <button
            onClick={onNavigateToCatalog}
            className="px-6 py-3 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-semibold text-sm transition-all shadow-md inline-flex items-center gap-2"
          >
            <span>Explore Book Catalog</span>
            <ArrowRight className="w-4 h-4" />
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 py-10">
      
      {/* Header */}
      <div className="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
          <h1 className="font-serif text-3xl font-bold text-zinc-900">
            Shopping Cart
          </h1>
          <p className="text-sm text-zinc-500 mt-1">
            {summary.itemCount} {summary.itemCount === 1 ? 'item' : 'items'} in your cart
          </p>
        </div>

        <button
          onClick={onClearCart}
          className="text-xs text-rose-600 hover:text-rose-700 font-semibold px-3 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 transition-colors flex items-center gap-1.5"
        >
          <Trash2 className="w-3.5 h-3.5" />
          <span>Clear Cart</span>
        </button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left Column: Items & Delivery Tracker */}
        <div className="lg:col-span-8 space-y-6">
          
          {/* Free Shipping Tracker */}
          <div className="bg-white border border-[#E8E5DF] rounded-2xl p-5 shadow-sm">
            <div className="flex items-center justify-between gap-3 text-xs mb-2">
              <div className="flex items-center gap-2">
                <Truck className={`w-4 h-4 ${summary.isFreeDelivery ? 'text-emerald-600' : 'text-blue-900'}`} />
                <span className="font-semibold text-zinc-800">
                  {summary.isFreeDelivery ? (
                    <span className="text-emerald-600 font-bold">🎉 Congratulations! You have unlocked FREE Delivery!</span>
                  ) : (
                    <span>Add <strong className="text-blue-900">₹{summary.amountNeededForFreeDelivery}</strong> more to qualify for <strong>FREE Delivery</strong></span>
                  )}
                </span>
              </div>
              <span className="font-bold text-zinc-500">{summary.freeDeliveryProgress}%</span>
            </div>
            
            <div className="w-full h-2.5 bg-zinc-100 rounded-full overflow-hidden">
              <div 
                className={`h-full transition-all duration-300 ${summary.isFreeDelivery ? 'bg-emerald-500' : 'bg-blue-900'}`}
                style={{ width: `${summary.freeDeliveryProgress}%` }}
              />
            </div>
          </div>

          {/* Cart Items List */}
          <div className="bg-white border border-[#E8E5DF] rounded-2xl shadow-sm divide-y divide-[#E8E5DF] overflow-hidden">
            {cartEntries.map(({ book, qty }) => {
              const unitPrice = Math.round(book.price * (1 - book.discount / 100));
              const itemTotal = unitPrice * qty;

              return (
                <div key={book.id} className="p-6 flex flex-col sm:flex-row gap-5 items-start sm:items-center">
                  
                  {/* Thumbnail */}
                  <img
                    src={book.coverImage}
                    alt={book.title}
                    onClick={() => onOpenBookDetail(book)}
                    className="w-20 h-28 object-cover rounded-lg border border-zinc-200 shadow-sm flex-shrink-0 cursor-pointer hover:opacity-90 transition-opacity"
                  />

                  {/* Info */}
                  <div className="flex-1 min-w-0">
                    <span className="text-[11px] font-bold text-blue-900 uppercase tracking-wide">
                      {book.category}
                    </span>
                    <h3 
                      onClick={() => onOpenBookDetail(book)}
                      className="font-bold text-base text-zinc-900 hover:text-blue-900 cursor-pointer line-clamp-1 mt-0.5"
                    >
                      {book.title}
                    </h3>
                    <p className="text-xs text-zinc-500 mt-0.5">by {book.author}</p>

                    {book.stock <= 5 ? (
                      <span className="inline-block mt-2 text-[11px] text-amber-700 bg-amber-50 font-bold px-2 py-0.5 rounded border border-amber-200">
                        ⚠️ Only {book.stock} copies left in stock
                      </span>
                    ) : (
                      <span className="inline-block mt-2 text-[11px] text-emerald-700 bg-emerald-50 font-semibold px-2 py-0.5 rounded border border-emerald-200">
                        ✓ In Stock
                      </span>
                    )}

                    {/* Actions on Mobile */}
                    <div className="flex items-center gap-4 mt-3 sm:hidden">
                      <button
                        onClick={() => onMoveToWishlist(book.id, book.title)}
                        className="text-xs text-zinc-500 hover:text-blue-900 flex items-center gap-1"
                      >
                        <Heart className="w-3.5 h-3.5" />
                        <span>Save for Later</span>
                      </button>
                      <button
                        onClick={() => onRemoveItem(book.id, book.title)}
                        className="text-xs text-rose-600 hover:text-rose-700 flex items-center gap-1"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                        <span>Remove</span>
                      </button>
                    </div>
                  </div>

                  {/* Quantity Stepper & Price */}
                  <div className="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 pt-2 sm:pt-0">
                    <div className="text-right">
                      <div className="text-lg font-bold text-zinc-900">₹{itemTotal}</div>
                      <div className="text-xs text-zinc-400">₹{unitPrice} each</div>
                    </div>

                    {/* Stepper */}
                    <div className="flex items-center border border-zinc-200 rounded-lg overflow-hidden bg-zinc-50">
                      <button
                        onClick={() => onUpdateQty(book.id, qty - 1)}
                        className="w-8 h-8 flex items-center justify-center text-zinc-600 hover:bg-zinc-200 font-bold text-sm"
                      >
                        -
                      </button>
                      <span className="w-9 text-center text-xs font-bold text-zinc-800">
                        {qty}
                      </span>
                      <button
                        onClick={() => onUpdateQty(book.id, qty + 1)}
                        className="w-8 h-8 flex items-center justify-center text-zinc-600 hover:bg-zinc-200 font-bold text-sm"
                      >
                        +
                      </button>
                    </div>

                    {/* Actions on Desktop */}
                    <div className="hidden sm:flex items-center gap-3 mt-1">
                      <button
                        onClick={() => onMoveToWishlist(book.id, book.title)}
                        className="text-[11px] text-zinc-500 hover:text-blue-900 flex items-center gap-1 font-medium transition-colors"
                        title="Move to Wishlist"
                      >
                        <Heart className="w-3 h-3" />
                        <span>Wishlist</span>
                      </button>
                      <button
                        onClick={() => onRemoveItem(book.id, book.title)}
                        className="text-[11px] text-rose-600 hover:text-rose-700 flex items-center gap-1 font-medium transition-colors"
                        title="Remove Item"
                      >
                        <Trash2 className="w-3 h-3" />
                        <span>Remove</span>
                      </button>
                    </div>

                  </div>

                </div>
              );
            })}
          </div>

          <div className="flex justify-between items-center text-xs text-zinc-500 pt-2">
            <button
              onClick={onNavigateToCatalog}
              className="text-blue-900 font-bold hover:underline flex items-center gap-1"
            >
              <span>← Continue Shopping</span>
            </button>
            <span>🔒 All orders encrypted & protected</span>
          </div>

        </div>

        {/* Right Column: Order Summary & Coupon Box */}
        <div className="lg:col-span-4 space-y-6 sticky top-24">
          
          <div className="bg-white border border-[#E8E5DF] rounded-3xl p-6 shadow-sm">
            <h2 className="font-serif text-xl font-bold text-zinc-900 pb-4 border-b border-zinc-100">
              Order Summary
            </h2>

            {/* Coupon Code Input */}
            <div className="mt-5 pb-5 border-b border-zinc-100">
              <label className="block text-[11px] font-bold text-zinc-400 uppercase tracking-wider mb-2">
                HAVE A PROMO CODE?
              </label>
              
              <div className="flex gap-2">
                <input
                  type="text"
                  value={couponInput}
                  onChange={(e) => setCouponInput(e.target.value.toUpperCase())}
                  placeholder="e.g. WELCOME10"
                  className="flex-1 px-3 py-2 text-xs border border-zinc-200 rounded-xl uppercase font-mono focus:border-blue-900 focus:ring-1 focus:ring-blue-900 outline-none"
                />
                {appliedCoupon ? (
                  <button
                    onClick={onRemoveCoupon}
                    className="px-3 py-2 text-xs font-semibold text-rose-600 border border-rose-200 hover:bg-rose-50 rounded-xl transition-colors"
                  >
                    Remove
                  </button>
                ) : (
                  <button
                    onClick={() => onApplyCoupon(couponInput)}
                    className="px-4 py-2 text-xs font-semibold bg-zinc-900 hover:bg-black text-white rounded-xl transition-colors"
                  >
                    Apply
                  </button>
                )}
              </div>

              {appliedCoupon && (
                <div className="mt-2 text-xs text-emerald-600 font-medium flex items-center gap-1">
                  <Check className="w-3.5 h-3.5" />
                  <span>Promo <strong>{appliedCoupon}</strong> active</span>
                </div>
              )}

              {/* Quick Promo Chips */}
              <div className="mt-3">
                <span className="text-[11px] text-zinc-400 block mb-1.5">Try sample coupons:</span>
                <div className="flex flex-wrap gap-1.5">
                  {[
                    { code: 'WELCOME10', label: '10% off' },
                    { code: 'BOOKNEST50', label: '₹50 flat' },
                    { code: 'READMORE', label: '15% >₹799' }
                  ].map(c => (
                    <button
                      key={c.code}
                      onClick={() => {
                        setCouponInput(c.code);
                        onApplyCoupon(c.code);
                      }}
                      className="text-[10px] font-mono bg-zinc-100 hover:bg-zinc-200 text-zinc-700 px-2 py-1 rounded border border-zinc-200 transition-colors"
                    >
                      {c.code} ({c.label})
                    </button>
                  ))}
                </div>
              </div>

            </div>

            {/* Calculations Breakdown */}
            <div className="py-5 space-y-3 text-xs">
              <div className="flex justify-between text-zinc-600">
                <span>Items Subtotal</span>
                <span className="font-semibold text-zinc-900">₹{summary.subtotal}</span>
              </div>

              {summary.couponDiscount > 0 && (
                <div className="flex justify-between text-emerald-600 font-semibold">
                  <span>Coupon Savings</span>
                  <span>-₹{summary.couponDiscount}</span>
                </div>
              )}

              <div className="flex justify-between text-zinc-600">
                <span>Estimated Delivery</span>
                <span>
                  {summary.isFreeDelivery ? (
                    <span className="text-emerald-600 font-bold">FREE</span>
                  ) : (
                    <span className="font-semibold text-zinc-900">₹{summary.deliveryFee}</span>
                  )}
                </span>
              </div>

              <div className="flex justify-between text-zinc-600">
                <span>GST (5% tax)</span>
                <span className="font-semibold text-zinc-900">₹{summary.gstAmount}</span>
              </div>

              <div className="pt-3 border-t border-dashed border-zinc-200 flex justify-between items-baseline">
                <div>
                  <div className="text-sm font-bold text-zinc-900">Grand Total</div>
                  <div className="text-[10px] text-zinc-400">Inclusive of all taxes</div>
                </div>
                <div className="text-2xl font-bold text-blue-900">
                  ₹{summary.grandTotal}
                </div>
              </div>

              {summary.totalSavings > 0 && (
                <div className="bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl p-2.5 text-center text-xs font-semibold">
                  🎉 Total Savings on this order: ₹{summary.totalSavings}
                </div>
              )}

            </div>

            {/* Checkout Button */}
            <button
              onClick={onOpenCheckout}
              className="w-full py-3.5 bg-blue-900 hover:bg-blue-950 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-900/20 hover:shadow-xl transition-all flex items-center justify-center gap-2"
            >
              <span>Proceed to Checkout</span>
              <ArrowRight className="w-4 h-4" />
            </button>

            {/* Trust Assurances */}
            <div className="mt-5 pt-4 border-t border-zinc-100 text-[11px] text-zinc-500 space-y-2">
              <div className="flex items-center gap-2">
                <ShieldCheck className="w-3.5 h-3.5 text-zinc-400 flex-shrink-0" />
                <span>100% Genuine, Authenticated Publisher Copies</span>
              </div>
              <div className="flex items-center gap-2">
                <Truck className="w-3.5 h-3.5 text-zinc-400 flex-shrink-0" />
                <span>Fast Dispatched from Regional Campus Hubs</span>
              </div>
              <div className="flex items-center gap-2">
                <RotateCcw className="w-3.5 h-3.5 text-zinc-400 flex-shrink-0" />
                <span>Hassle-Free 7-Day Easy Replacement</span>
              </div>
            </div>

          </div>

        </div>

      </div>

    </div>
  );
};
