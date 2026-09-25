import React, { useState } from 'react';
import { Book } from '../types';

interface BookCardProps {
  book: Book;
  isWishlisted: boolean;
  onAddToCart: (book: Book) => void;
  onToggleWishlist: (bookId: number) => void;
  onQuickView: (book: Book) => void;
}

export const BookCard: React.FC<BookCardProps> = ({
  book,
  isWishlisted,
  onAddToCart,
  onToggleWishlist,
  onQuickView,
}) => {
  const [addedAnimation, setAddedAnimation] = useState(false);
  const [heartBounce, setHeartBounce] = useState(false);

  const finalPrice = Math.round(book.price * (1 - book.discount / 100));

  const handleAddToCart = (e: React.MouseEvent) => {
    e.stopPropagation();
    onAddToCart(book);
    setAddedAnimation(true);
    setTimeout(() => setAddedAnimation(false), 1200);
  };

  const handleWishlist = (e: React.MouseEvent) => {
    e.stopPropagation();
    setHeartBounce(true);
    onToggleWishlist(book.id);
    setTimeout(() => setHeartBounce(false), 400);
  };

  // Stock status pill styling
  const getStockBadge = () => {
    if (book.stock <= 0) {
      return (
        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200/80">
          <span className="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
          Out of Stock
        </span>
      );
    }
    if (book.stock <= 10) {
      return (
        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200/80">
          <span className="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
          Only {book.stock} left
        </span>
      );
    }
    return (
      <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200/80">
        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        In Stock ({book.stock})
      </span>
    );
  };

  return (
    <div 
      onClick={() => onQuickView(book)}
      className="group relative bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col cursor-pointer overflow-hidden"
    >
      {/* Top action row: Stock badge & Wishlist Heart */}
      <div className="flex items-center justify-between gap-2 mb-4 z-10">
        <div>{getStockBadge()}</div>

        <button
          type="button"
          onClick={handleWishlist}
          title={isWishlisted ? "Remove from Wishlist" : "Save to Wishlist"}
          className={`w-9 h-9 rounded-full flex items-center justify-center transition-all duration-200 ${
            heartBounce ? 'scale-125' : 'scale-100'
          } ${
            isWishlisted 
              ? 'bg-rose-50 text-rose-600 shadow-sm border border-rose-200' 
              : 'bg-white/90 text-slate-400 hover:text-rose-500 hover:bg-rose-50 border border-slate-200'
          }`}
        >
          <i className={`${isWishlisted ? 'fa-solid text-rose-500' : 'fa-regular'} fa-heart text-sm`}></i>
        </button>
      </div>

      {/* 3D Book Cover Presentation */}
      <div className="relative mx-auto my-2 w-full max-w-[170px] aspect-[2/3] flex items-center justify-center">
        {/* Ambient colored shadow matching cover */}
        <div 
          className="absolute inset-0 rounded-r-lg rounded-l-sm opacity-25 filter blur-xl scale-95 transition-all duration-300 group-hover:scale-105 group-hover:opacity-40"
          style={{ backgroundImage: `url(${book.coverImage})`, backgroundSize: 'cover' }}
        />

        {/* 3D Book with Spine Effect */}
        <div className="relative w-full h-full rounded-r-lg rounded-l-sm overflow-hidden book-shadow-3d book-spine border border-slate-900/10 transition-transform duration-300 group-hover:scale-[1.03]">
          <img 
            src={book.coverImage} 
            alt={book.title}
            className="w-full h-full object-cover select-none"
            loading="lazy"
          />

          {/* Quick View Floating Pill on Hover */}
          <div className="absolute inset-0 bg-slate-950/40 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center p-3">
            <span className="px-3 py-1.5 rounded-full bg-white/95 text-slate-900 text-xs font-semibold shadow-lg flex items-center gap-1.5 hover:bg-white">
              <i className="fa-regular fa-eye text-amber-600"></i>
              Quick Preview
            </span>
          </div>

          {/* Discount Ribbon if applicable */}
          {book.discount > 0 && (
            <div className="absolute top-2 right-2 bg-amber-500 text-slate-950 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-md shadow-md tracking-wider">
              {book.discount}% OFF
            </div>
          )}
        </div>
      </div>

      {/* Book Metadata */}
      <div className="mt-4 flex-1 flex flex-col">
        {/* Category & ISBN */}
        <div className="flex items-center justify-between text-xs text-slate-500 mb-1.5">
          <span className="font-medium text-amber-700 hover:text-amber-800 transition-colors">
            {book.category}
          </span>
          <span className="font-mono text-[11px] text-slate-400">
            {book.isbn.slice(-4)}
          </span>
        </div>

        {/* Title */}
        <h3 className="font-serif font-bold text-slate-900 text-base line-clamp-2 leading-snug group-hover:text-amber-800 transition-colors">
          {book.title}
        </h3>

        {/* Author */}
        <p className="text-xs text-slate-600 mt-1 font-sans">
          by <span className="font-medium text-slate-800">{book.author}</span>
        </p>

        {/* Rating Row */}
        <div className="flex items-center gap-2 mt-2 text-xs">
          <div className="flex items-center text-amber-400">
            <i className="fa-solid fa-star text-xs mr-1"></i>
            <span className="font-bold text-slate-900">{book.rating.toFixed(1)}</span>
          </div>
          <span className="text-slate-300">·</span>
          <span className="text-slate-500 text-[11px]">{book.pages} pages</span>
        </div>

        {/* Price & Add to Cart footer */}
        <div className="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
          <div className="flex flex-col">
            <div className="flex items-baseline gap-1.5">
              <span className="font-serif font-bold text-lg text-slate-900">
                ₹{finalPrice}
              </span>
              {book.discount > 0 && (
                <span className="text-xs text-slate-400 line-through">
                  ₹{book.price}
                </span>
              )}
            </div>
            <span className="text-[10px] text-emerald-700 font-medium">Free Campus Pickup</span>
          </div>

          <button
            type="button"
            disabled={book.stock <= 0}
            onClick={handleAddToCart}
            className={`px-3.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200 flex items-center gap-1.5 shadow-sm active:scale-95 ${
              book.stock <= 0
                ? 'bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200'
                : addedAnimation
                ? 'bg-emerald-600 text-white shadow-emerald-200'
                : 'bg-slate-900 text-amber-50 hover:bg-amber-600 hover:text-slate-950 hover:shadow-amber-500/20'
            }`}
          >
            {addedAnimation ? (
              <>
                <i className="fa-solid fa-check text-xs"></i>
                <span>Added!</span>
              </>
            ) : (
              <>
                <i className="fa-solid fa-bag-shopping text-xs"></i>
                <span>Add</span>
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
};
