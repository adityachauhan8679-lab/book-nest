import React, { useState } from 'react';
import { Book } from '../types';

interface QuickViewModalProps {
  book: Book | null;
  onClose: () => void;
  isWishlisted: boolean;
  onToggleWishlist: (bookId: number) => void;
  onAddToCart: (book: Book, quantity: number) => void;
}

export const QuickViewModal: React.FC<QuickViewModalProps> = ({
  book,
  onClose,
  isWishlisted,
  onToggleWishlist,
  onAddToCart,
}) => {
  const [qty, setQty] = useState(1);
  const [isAdded, setIsAdded] = useState(false);

  if (!book) return null;

  const finalPrice = Math.round(book.price * (1 - book.discount / 100));

  const handleAdd = () => {
    onAddToCart(book, qty);
    setIsAdded(true);
    setTimeout(() => setIsAdded(false), 1500);
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-150">
      
      {/* Modal Card */}
      <div className="relative w-full max-w-3xl bg-white rounded-3xl border border-slate-200/90 shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
        
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-white/90 hover:bg-slate-100 border border-slate-200 text-slate-600 hover:text-slate-900 flex items-center justify-center transition-colors shadow-xs"
        >
          <i className="fa-solid fa-xmark text-sm"></i>
        </button>

        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 p-6 sm:p-8 items-center">
          
          {/* Left Column: 3D Book Cover & Ambient Glow */}
          <div className="md:col-span-5 flex flex-col items-center justify-center">
            <div className="relative w-48 sm:w-56 aspect-[2/3] rounded-r-lg rounded-l-xs overflow-hidden book-shadow-3d book-spine border border-slate-900/10">
              <img
                src={book.coverImage}
                alt={book.title}
                className="w-full h-full object-cover"
              />
            </div>

            {book.discount > 0 && (
              <div className="mt-4 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold">
                <i className="fa-solid fa-tag text-amber-600"></i>
                <span>Save {book.discount}% on Campus Edition</span>
              </div>
            )}
          </div>

          {/* Right Column: Book Details & Actions */}
          <div className="md:col-span-7 flex flex-col">
            
            <div className="text-xs font-bold text-amber-700 uppercase tracking-widest mb-1">
              {book.category}
            </div>

            <h2 className="font-serif font-black text-2xl sm:text-3xl text-slate-950 leading-tight">
              {book.title}
            </h2>

            <p className="text-sm text-slate-600 mt-1 font-sans">
              by <span className="text-slate-900 font-semibold">{book.author}</span>
            </p>

            {/* Rating & Stock */}
            <div className="flex items-center gap-3 mt-3 text-xs">
              <div className="flex items-center text-amber-500 font-bold">
                <i className="fa-solid fa-star text-xs mr-1"></i>
                <span>{book.rating}</span>
              </div>
              <span className="text-slate-300">·</span>
              <span className="text-slate-500">{book.pages} pages</span>
              <span className="text-slate-300">·</span>
              <span className={`font-semibold ${book.stock > 0 ? 'text-emerald-700' : 'text-rose-600'}`}>
                {book.stock > 0 ? `In Stock (${book.stock} left)` : 'Out of Stock'}
              </span>
            </div>

            {/* Price Display */}
            <div className="mt-4 flex items-baseline gap-2.5">
              <span className="font-serif font-black text-3xl text-slate-950">
                ₹{finalPrice}
              </span>
              {book.discount > 0 && (
                <span className="text-sm text-slate-400 line-through">
                  ₹{book.price}
                </span>
              )}
            </div>

            {/* Description Synopsis */}
            <div className="mt-4 text-xs text-slate-600 leading-relaxed font-sans line-clamp-4">
              {book.description}
            </div>

            {/* Relational Specs Grid */}
            <div className="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-slate-100 text-xs text-slate-600">
              <div>
                <span className="text-slate-400 block text-[10px] uppercase">ISBN</span>
                <span className="font-mono font-medium text-slate-900">{book.isbn}</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[10px] uppercase">Publisher</span>
                <span className="font-medium text-slate-900 truncate block">{book.publisher}</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[10px] uppercase">Publication Year</span>
                <span className="font-medium text-slate-900">{book.year}</span>
              </div>
              <div>
                <span className="text-slate-400 block text-[10px] uppercase">Language</span>
                <span className="font-medium text-slate-900">{book.language}</span>
              </div>
            </div>

            {/* Action Buttons: Quantity, Add to Cart, Wishlist */}
            <div className="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
              
              {/* Quantity */}
              <div className="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                <button
                  onClick={() => setQty(Math.max(1, qty - 1))}
                  className="w-7 h-7 rounded-lg bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs"
                >
                  -
                </button>
                <span className="w-8 text-center text-xs font-bold text-slate-900">
                  {qty}
                </span>
                <button
                  onClick={() => setQty(Math.min(book.stock, qty + 1))}
                  disabled={qty >= book.stock}
                  className="w-7 h-7 rounded-lg bg-white hover:bg-slate-200 text-slate-700 disabled:opacity-40 flex items-center justify-center font-bold text-xs"
                >
                  +
                </button>
              </div>

              {/* Add to Cart */}
              <button
                onClick={handleAdd}
                disabled={book.stock <= 0}
                className={`flex-1 py-3 px-4 rounded-xl font-bold text-xs flex items-center justify-center gap-2 shadow-md transition-all active:scale-98 ${
                  isAdded
                    ? 'bg-emerald-600 text-white'
                    : 'bg-slate-900 hover:bg-amber-600 text-amber-50 hover:text-slate-950'
                }`}
              >
                {isAdded ? (
                  <>
                    <i className="fa-solid fa-check"></i>
                    <span>Added to Cart!</span>
                  </>
                ) : (
                  <>
                    <i className="fa-solid fa-bag-shopping"></i>
                    <span>Add to Cart (₹{finalPrice * qty})</span>
                  </>
                )}
              </button>

              {/* Wishlist Toggle */}
              <button
                onClick={() => onToggleWishlist(book.id)}
                className={`w-11 h-11 rounded-xl border flex items-center justify-center text-sm transition-colors ${
                  isWishlisted
                    ? 'bg-rose-50 text-rose-600 border-rose-200 shadow-2xs'
                    : 'bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-500 border-slate-200'
                }`}
              >
                <i className={`${isWishlisted ? 'fa-solid text-rose-500' : 'fa-regular'} fa-heart`}></i>
              </button>

            </div>

          </div>

        </div>

      </div>
    </div>
  );
};
