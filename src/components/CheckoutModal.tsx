import React, { useState } from 'react';
import { Check, X, ShieldCheck, Truck, CreditCard, QrCode, Banknote, ArrowRight } from 'lucide-react';
import { CartSummaryData } from '../types';

interface CheckoutModalProps {
  isOpen: boolean;
  summary: CartSummaryData;
  onClose: () => void;
  onOrderPlaced: () => void;
}

export const CheckoutModal: React.FC<CheckoutModalProps> = ({
  isOpen,
  summary,
  onClose,
  onOrderPlaced
}) => {
  const [step, setStep] = useState<'details' | 'success'>('details');
  const [paymentMethod, setPaymentMethod] = useState<'upi' | 'card' | 'cod'>('upi');
  const [orderId, setOrderId] = useState('');

  const [form, setForm] = useState({
    name: 'Aditya Chauhan',
    phone: '+91 91234 56789',
    address: 'Room 304, Tagore Hall of Residence, North Campus',
    city: 'Delhi',
    pincode: '110007'
  });

  if (!isOpen) return null;

  const handlePlaceOrder = (e: React.FormEvent) => {
    e.preventDefault();
    const generatedId = `BN-${new Date().getFullYear()}-${Math.floor(100000 + Math.random() * 900000)}`;
    setOrderId(generatedId);
    setStep('success');
    onOrderPlaced();
  };

  return (
    <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-in fade-in duration-150">
      <div className="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl relative max-h-[90vh] overflow-y-auto border border-[#E8E5DF]">
        
        {step === 'details' ? (
          <>
            <button
              onClick={onClose}
              className="absolute top-5 right-5 w-8 h-8 rounded-full bg-zinc-100 hover:bg-zinc-200 text-zinc-500 flex items-center justify-center font-bold"
            >
              ✕
            </button>

            <div className="flex items-center gap-2 mb-1">
              <span className="w-6 h-6 rounded-full bg-blue-100 text-blue-900 text-xs font-bold flex items-center justify-center">
                1
              </span>
              <h2 className="font-serif text-2xl font-bold text-zinc-900">
                Checkout & Shipping
              </h2>
            </div>
            <p className="text-xs text-zinc-500 mb-6">
              Review your delivery details and choose your preferred payment option.
            </p>

            <form onSubmit={handlePlaceOrder} className="space-y-4">
              
              {/* Shipping Address Inputs */}
              <div className="space-y-3 bg-zinc-50 p-4 rounded-2xl border border-zinc-200">
                <div className="text-xs font-bold text-zinc-700 uppercase tracking-wide">
                  Delivery Destination
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-[11px] text-zinc-500 mb-1">Full Name</label>
                    <input
                      type="text"
                      required
                      value={form.name}
                      onChange={(e) => setForm({ ...form, name: e.target.value })}
                      className="w-full px-3 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg outline-none focus:border-blue-900"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] text-zinc-500 mb-1">Phone Number</label>
                    <input
                      type="text"
                      required
                      value={form.phone}
                      onChange={(e) => setForm({ ...form, phone: e.target.value })}
                      className="w-full px-3 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg outline-none focus:border-blue-900"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-[11px] text-zinc-500 mb-1">Address / Hostel / Room</label>
                  <input
                    type="text"
                    required
                    value={form.address}
                    onChange={(e) => setForm({ ...form, address: e.target.value })}
                    className="w-full px-3 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg outline-none focus:border-blue-900"
                  />
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-[11px] text-zinc-500 mb-1">City</label>
                    <input
                      type="text"
                      required
                      value={form.city}
                      onChange={(e) => setForm({ ...form, city: e.target.value })}
                      className="w-full px-3 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg outline-none focus:border-blue-900"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] text-zinc-500 mb-1">Pincode</label>
                    <input
                      type="text"
                      required
                      value={form.pincode}
                      onChange={(e) => setForm({ ...form, pincode: e.target.value })}
                      className="w-full px-3 py-1.5 text-xs bg-white border border-zinc-200 rounded-lg outline-none focus:border-blue-900"
                    />
                  </div>
                </div>
              </div>

              {/* Payment Method Selection */}
              <div className="space-y-2">
                <div className="text-xs font-bold text-zinc-700 uppercase tracking-wide">
                  Payment Method
                </div>

                <div className="grid grid-cols-3 gap-2">
                  <div
                    onClick={() => setPaymentMethod('upi')}
                    className={`p-3 rounded-xl border text-center cursor-pointer transition-all ${
                      paymentMethod === 'upi'
                        ? 'border-blue-900 bg-blue-50/70 text-blue-950 font-bold'
                        : 'border-zinc-200 hover:bg-zinc-50 text-zinc-600'
                    }`}
                  >
                    <QrCode className="w-5 h-5 mx-auto mb-1 text-blue-900" />
                    <div className="text-xs">UPI / QR</div>
                  </div>

                  <div
                    onClick={() => setPaymentMethod('card')}
                    className={`p-3 rounded-xl border text-center cursor-pointer transition-all ${
                      paymentMethod === 'card'
                        ? 'border-blue-900 bg-blue-50/70 text-blue-950 font-bold'
                        : 'border-zinc-200 hover:bg-zinc-50 text-zinc-600'
                    }`}
                  >
                    <CreditCard className="w-5 h-5 mx-auto mb-1 text-blue-900" />
                    <div className="text-xs">Card / NetBanking</div>
                  </div>

                  <div
                    onClick={() => setPaymentMethod('cod')}
                    className={`p-3 rounded-xl border text-center cursor-pointer transition-all ${
                      paymentMethod === 'cod'
                        ? 'border-blue-900 bg-blue-50/70 text-blue-950 font-bold'
                        : 'border-zinc-200 hover:bg-zinc-50 text-zinc-600'
                    }`}
                  >
                    <Banknote className="w-5 h-5 mx-auto mb-1 text-blue-900" />
                    <div className="text-xs">Cash on Delivery</div>
                  </div>
                </div>
              </div>

              {/* Order Final Amount */}
              <div className="p-4 bg-zinc-100 rounded-2xl flex items-center justify-between">
                <div>
                  <span className="text-xs text-zinc-500 block">Total Payable</span>
                  <span className="text-lg font-bold text-blue-900">₹{summary.grandTotal}</span>
                </div>
                <div className="text-right text-[11px] text-zinc-500">
                  {summary.itemCount} items • {summary.isFreeDelivery ? 'Free Delivery' : `₹${summary.deliveryFee} Delivery`}
                </div>
              </div>

              <button
                type="submit"
                className="w-full py-3.5 bg-blue-900 hover:bg-blue-950 text-white font-bold text-sm rounded-xl shadow-lg transition-all flex items-center justify-center gap-2"
              >
                <span>Confirm & Place Order</span>
                <ArrowRight className="w-4 h-4" />
              </button>

            </form>
          </>
        ) : (
          <div className="text-center py-6">
            <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
              ✓
            </div>
            <h2 className="font-serif text-2xl font-bold text-zinc-900 mb-1">
              Order Confirmed!
            </h2>
            <p className="text-xs text-zinc-500 mb-6">
              Thank you for shopping with BookNest. We have received your order.
            </p>

            <div className="bg-zinc-50 rounded-2xl p-5 border border-zinc-200 text-left text-xs space-y-2 mb-6">
              <div className="flex justify-between">
                <span className="text-zinc-500">Order Number:</span>
                <span className="font-mono font-bold text-zinc-900">{orderId}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-zinc-500">Payment:</span>
                <span className="font-semibold text-zinc-900 uppercase">{paymentMethod}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-zinc-500">Amount Paid / Due:</span>
                <span className="font-bold text-blue-900">₹{summary.grandTotal}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-zinc-500">Expected Delivery:</span>
                <span className="font-semibold text-emerald-700">3-5 Business Days</span>
              </div>
            </div>

            <button
              onClick={onClose}
              className="w-full py-3 bg-blue-900 hover:bg-blue-950 text-white font-bold text-xs rounded-xl"
            >
              Continue Browsing
            </button>
          </div>
        )}

      </div>
    </div>
  );
};
