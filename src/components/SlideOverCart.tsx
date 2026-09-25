import React, { useState } from 'react';
import { Book, CartSummaryData } from '../types';

interface SlideOverCartProps {
  isOpen: boolean;
  onClose: () => void;
  cart: Record<number, number>;
  books: Book[];
  summary: CartSummaryData;
  appliedCoupon: string | null;
  onUpdateQty: (bookId: number, qty: number) => void;
  onRemoveItem: (bookId: number, title: string) => void;
  onApplyCoupon: (code: string) => void;
  onRemoveCoupon: () => void;
  onProceedToCheckout: () => void;
  onNavigateToCatalog: () => void;
  onOpenBookDetail: (book: Book) => void;
}

export const SlideOverCart: React.FC<SlideOverCartProps> = ({
  isOpen,
  onClose,
  cart,
  books,
  summary,
  appliedCoupon,
  onUpdateQty,
  onRemoveItem,
  onApplyCoupon,
  onRemoveCoupon,
  onProceedToCheckout,
  onNavigateToCatalog,
  onOpenBookDetail,
}) => {
  const [couponInput, setCouponInput] = useState('');
  const [couponError, setCouponError] = useState('');

  if (!isOpen) return null;

  const cartEntries = Object.entries(cart)
    .filter(([_, qty]) => qty > 0)
    .map(([bookIdStr, qty]) => {
      const id = parseInt(bookIdStr, 10);
      const book = books.find(b => b.id === id);
      return { id, book, quantity: qty };
    })
    .filter(item => item.book !== undefined) as { id: number; book: Book; quantity: number }[];

  const handleApplyCoupon = (e: React.FormEvent) => {
    e.preventDefault();
    setCouponError('');
    const code = couponInput.trim().toUpperCase();
    if (!code) return;

    if (['WELCOME10', 'BOOKNEST50', 'READMORE'].includes(code)) {
      onApplyCoupon(code);
      setCouponInput('');
    } else {
      setCouponError('Invalid coupon. Try WELCOME10, BOOKNEST50, or READMORE');
    }
  };

  return (
    <div className="fixed inset-0 z-50 overflow-hidden animate-in fade-in duration-200">
      
      {/* Backdrop */}
      <div 
        onClick={onClose}
        className="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
      />

      <div className="fixed inset-y-0 right-0 max-w-full flex pl-10">
        <div className="w-screen max-w-md bg-white shadow-2xl flex flex-col animate-in slide-in-from-right duration-300">
          
          {/* Drawer Header */}
          <div className="p-5 border-b border-slate-200/80 flex items-center justify-between bg-[#FAF8F5]">
            <div className="flex items-center gap-2.5">
              <div className="w-9 h-9 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center shadow-xs">
                <i className="fa-solid fa-bag-shopping text-sm"></i>
              </div>
              <div>
                <h2 className="font-serif font-bold text-lg text-slate-900">
                  Shopping Cart
                </h2>
                <p className="text-[11px] text-slate-500">
                  {summary.itemCount} {summary.itemCount === 1 ? 'item' : 'items'} selected
                </p>
              </div>
            </div>

            <button
              onClick={onClose}
              className="w-8 h-8 rounded-full bg-white hover:bg-slate-100 border border-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition-colors"
            >
              <i className="fa-solid fa-xmark text-sm"></i>
            </button>
          </div>

          {/* Dynamic Free Delivery Meter */}
          <div className="px-5 py-3.5 bg-amber-50/70 border-b border-amber-200/60">
            <div className="flex items-center justify-between text-xs mb-1.5 font-medium">
              <span className="flex items-center gap-1.5 text-amber-950 font-semibold">
                <i className="fa-solid fa-truck-fast text-amber-600"></i>
                {summary.isFreeDelivery ? (
                  <span className="text-emerald-700 font-bold">🎉 Free Campus Delivery Unlocked!</span>
                ) : (
                  <span>
                    Add <span className="font-bold text-amber-900">₹{summary.amountNeededForFreeDelivery}</span> more for Free Delivery
                  </span>
                )}
              </span>
              <span className="text-[11px] text-slate-500">
                ₹{summary.subtotal} / ₹{summary.freeDeliveryThreshold}
              </span>
            </div>

            {/* Progress bar */}
            <div className="w-full h-2 bg-amber-200/70 rounded-full overflow-hidden">
              <div 
                className={`h-full transition-all duration-500 rounded-full ${
                  summary.isFreeDelivery ? 'bg-emerald-500' : 'bg-amber-500'
                }`}
                style={{ width: `${Math.min(100, summary.freeDeliveryProgress)}%` }}
              />
            </div>
          </div>

          {/* Cart Item List */}
          <div className="flex-1 overflow-y-auto p-5 divide-y divide-slate-100">
            {cartEntries.length > 0 ? (
              cartEntries.map(({ id, book, quantity }) => {
                const finalPrice = Math.round(book.price * (1 - book.discount / 100));
                const itemTotal = finalPrice * quantity;

                return (
                  <div key={id} className="py-4 first:pt-0 last:pb-0 flex gap-3.5 group">
                    
                    {/* 3D Book Cover thumbnail */}
                    <div 
                      onClick={() => {
                        onClose();
                        onOpenBookDetail(book);
                      }}
                      className="w-16 h-22 shrink-0 rounded-r-md rounded-l-xs overflow-hidden book-shadow-3d book-spine border border-slate-900/10 cursor-pointer hover:scale-105 transition-transform"
                    >
                      <img 
                        src={book.coverImage} 
                        alt={book.title} 
                        className="w-full h-full object-cover"
                      />
                    </div>

                    {/* Book Info & Quantity Controls */}
                    <div className="flex-1 flex flex-col justify-between">
                      <div>
                        <div className="flex items-start justify-between gap-2">
                          <h4 
                            onClick={() => {
                              onClose();
                              onOpenBookDetail(book);
                            }}
                            className="font-serif font-bold text-sm text-slate-900 hover:text-amber-800 cursor-pointer line-clamp-1"
                          >
                            {book.title}
                          </h4>
                          <button
                            onClick={() => onRemoveItem(id, book.title)}
                            className="text-slate-400 hover:text-rose-600 p-1 transition-colors"
                            title="Remove item"
                          >
                            <i className="fa-regular fa-trash-can text-xs"></i>
                          </button>
                        </div>
                        <p className="text-[11px] text-slate-500 line-clamp-1">
                          {book.author} · <span className="text-amber-700">{book.category}</span>
                        </p>
                      </div>

                      <div className="flex items-center justify-between mt-3 pt-2">
                        {/* Dynamic Quantity Counter */}
                        <div className="flex items-center rounded-lg border border-slate-200 bg-slate-50 p-0.5">
                          <button
                            onClick={() => onUpdateQty(id, quantity - 1)}
                            className="w-6 h-6 rounded-md bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-bold transition-colors shadow-2xs"
                          >
                            -
                          </button>
                          <span className="w-8 text-center text-xs font-bold text-slate-900">
                            {quantity}
                          </span>
                          <button
                            onClick={() => onUpdateQty(id, quantity + 1)}
                            disabled={quantity >= book.stock}
                            className="w-6 h-6 rounded-md bg-white hover:bg-slate-200 text-slate-700 disabled:opacity-40 flex items-center justify-center text-xs font-bold transition-colors shadow-2xs"
                          >
                            +
                          </button>
                        </div>

                        {/* Price */}
                        <div className="text-right">
                          <div className="font-serif font-bold text-sm text-slate-900">
                            ₹{itemTotal}
                          </div>
                          <div className="text-[10px] text-slate-400">
                            ₹{finalPrice} each
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                );
              })
            ) : (
              <div className="h-full flex flex-col items-center justify-center text-center p-6 text-slate-500">
                <div className="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-2xl mb-4">
                  <i className="fa-solid fa-basket-shopping"></i>
                </div>
                <h3 className="font-serif font-bold text-lg text-slate-900 mb-1">
                  Your cart is empty
                </h3>
                <p className="text-xs text-slate-500 max-w-xs mb-6">
                  Explore our curated shelves and find your next inspiring read.
                </p>
                <button
                  onClick={() => {
                    onClose();
                    onNavigateToCatalog();
                  }}
                  className="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-amber-600 text-amber-100 hover:text-slate-950 font-semibold text-xs shadow-md transition-all"
                >
                  Browse Bookstore Catalog →
                </button>
              </div>
            )}
          </div>

          {/* Drawer Footer with Promo Code & Totals */}
          {cartEntries.length > 0 && (
            <div className="p-5 border-t border-slate-200/80 bg-[#FAF8F5] space-y-4">
              
              {/* Promo Code Input */}
              <div>
                {appliedCoupon ? (
                  <div className="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                    <div className="flex items-center gap-2">
                      <i className="fa-solid fa-tag text-emerald-600"></i>
                      <span className="font-bold tracking-wider">{appliedCoupon}</span>
                      <span className="text-emerald-700">(-₹{summary.couponDiscount})</span>
                    </div>
                    <button
                      onClick={onRemoveCoupon}
                      className="text-xs text-rose-600 hover:underline font-medium"
                    >
                      Remove
                    </button>
                  </div>
                ) : (
                  <form onSubmit={handleApplyCoupon} className="flex gap-2">
                    <input
                      type="text"
                      value={couponInput}
                      onChange={(e) => setCouponInput(e.target.value)}
                      placeholder="Coupon code (e.g. WELCOME10)"
                      className="flex-1 py-2 px-3 rounded-xl bg-white border border-slate-200 text-xs text-slate-900 uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-amber-500"
                    />
                    <button
                      type="submit"
                      className="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-amber-300 font-semibold text-xs transition-colors"
                    >
                      Apply
                    </button>
                  </form>
                )}
                {couponError && (
                  <p className="text-[11px] text-rose-600 mt-1">{couponError}</p>
                )}
              </div>

              {/* Price Calculation Summary */}
              <div className="space-y-1.5 text-xs text-slate-600">
                <div className="flex justify-between">
                  <span>Subtotal</span>
                  <span className="font-medium text-slate-900">₹{summary.subtotal}</span>
                </div>
                {summary.couponDiscount > 0 && (
                  <div className="flex justify-between text-emerald-700 font-medium">
                    <span>Coupon Discount</span>
                    <span>-₹{summary.couponDiscount}</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span>Campus Delivery</span>
                  <span>{summary.isFreeDelivery ? <span className="text-emerald-700 font-bold">FREE</span> : `₹${summary.deliveryFee}`}</span>
                </div>
                <div className="flex justify-between pt-2 border-t border-slate-200/80 font-serif text-base font-bold text-slate-950">
                  <span>Total Amount</span>
                  <span>₹{summary.grandTotal}</span>
                </div>
              </div>

              {/* Checkout Action Button */}
              <button
                onClick={() => {
                  onClose();
                  onProceedToCheckout();
                }}
                className="w-full py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-sm shadow-md hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 active:scale-98"
              >
                <span>Proceed to 3-Step Checkout</span>
                <i className="fa-solid fa-arrow-right text-xs"></i>
              </button>
            </div>
          )}

        </div>
      </div>
    </div>
  );
};
