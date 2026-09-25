import React, { useState } from 'react';
import { 
  BarChart3, 
  BookOpen, 
  Package, 
  AlertTriangle, 
  Plus, 
  Search, 
  Edit3, 
  Trash2, 
  Check, 
  DollarSign, 
  ShoppingBag, 
  Truck, 
  ArrowUpRight,
  X,
  FileText,
  Building,
  RefreshCw,
  Printer
} from 'lucide-react';
import { Book } from '../types';
import { PlacedOrder } from './OrdersView';

interface AdminViewProps {
  books: Book[];
  orders: PlacedOrder[];
  onUpdateBookStock: (bookId: number, newStock: number) => void;
  onAddBook: (newBook: Book) => void;
  onUpdateBook: (updatedBook: Book) => void;
  onDeleteBook: (bookId: number) => void;
  onUpdateOrderStatus: (orderId: string, newStatus: PlacedOrder['status']) => void;
  onOpenCodeExplorer?: (file: string) => void;
}

export function AdminView({
  books,
  orders,
  onUpdateBookStock,
  onAddBook,
  onUpdateBook,
  onDeleteBook,
  onUpdateOrderStatus,
  onOpenCodeExplorer
}: AdminViewProps) {
  const [adminTab, setAdminTab] = useState<'dashboard' | 'books' | 'inventory' | 'orders' | 'analytics'>('dashboard');
  const [bookSearch, setBookSearch] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('all');
  const [orderStatusFilter, setOrderStatusFilter] = useState<string>('all');
  const [isBookModalOpen, setIsBookModalOpen] = useState(false);
  const [editingBook, setEditingBook] = useState<Book | null>(null);
  const [viewingOrder, setViewingOrder] = useState<PlacedOrder | null>(null);

  // Form State
  const [formData, setFormData] = useState({
    title: '',
    author: '',
    category: 'Computer Science',
    categorySlug: 'computer-science',
    isbn: '',
    price: 499,
    discount: 0,
    stock: 25,
    publisher: '',
    pages: 320,
    year: 2024,
    language: 'English',
    description: '',
    coverImage: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=600'
  });

  // Calculate Metrics
  const totalStockUnits = books.reduce((sum, b) => sum + b.stock, 0);
  const totalValuation = books.reduce((sum, b) => sum + (b.stock * b.price), 0);
  const lowStockBooks = books.filter(b => b.stock < 10);
  const totalPaidRevenue = orders.reduce((sum, o) => sum + o.total, 0) + 7450; // combined baseline

  // Filtered books
  const filteredBooks = books.filter(b => {
    const matchesSearch = b.title.toLowerCase().includes(bookSearch.toLowerCase()) ||
                          b.author.toLowerCase().includes(bookSearch.toLowerCase()) ||
                          b.isbn.toLowerCase().includes(bookSearch.toLowerCase());
    const matchesCategory = categoryFilter === 'all' || b.category.toLowerCase() === categoryFilter.toLowerCase();
    return matchesSearch && matchesCategory;
  });

  // Unique categories
  const categories = Array.from(new Set(books.map(b => b.category)));

  // Filtered orders
  const filteredOrders = orders.filter(o => {
    if (orderStatusFilter === 'all') return true;
    return o.status === orderStatusFilter;
  });

  const handleOpenAddModal = () => {
    setEditingBook(null);
    setFormData({
      title: '',
      author: '',
      category: 'Computer Science',
      categorySlug: 'computer-science',
      isbn: '978' + Math.floor(1000000000 + Math.random() * 9000000000),
      price: 399,
      discount: 10,
      stock: 30,
      publisher: 'Oxford University Press',
      pages: 350,
      year: 2024,
      language: 'English',
      description: 'Standard university syllabus reading and campus curriculum textbook.',
      coverImage: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=600'
    });
    setIsBookModalOpen(true);
  };

  const handleOpenEditModal = (b: Book) => {
    setEditingBook(b);
    setFormData({
      title: b.title,
      author: b.author,
      category: b.category,
      categorySlug: b.categorySlug || 'academic',
      isbn: b.isbn,
      price: b.price,
      discount: b.discount,
      stock: b.stock,
      publisher: b.publisher || 'University Campus Press',
      pages: b.pages || 300,
      year: b.year || 2023,
      language: b.language || 'English',
      description: b.description,
      coverImage: b.coverImage
    });
    setIsBookModalOpen(true);
  };

  const handleSubmitBook = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingBook) {
      onUpdateBook({
        ...editingBook,
        ...formData
      });
    } else {
      const newId = Math.max(0, ...books.map(b => b.id)) + 1;
      onAddBook({
        id: newId,
        rating: 4.8,
        ...formData
      });
    }
    setIsBookModalOpen(false);
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full animate-in fade-in duration-200">
      
      {/* Top Banner & Header */}
      <div className="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white rounded-2xl p-6 sm:p-8 mb-8 shadow-xl border border-slate-700/60 relative overflow-hidden">
        <div className="absolute right-0 top-0 translate-x-8 -translate-y-8 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none" />
        
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
          <div>
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold uppercase tracking-wider mb-3">
              <Building className="w-3.5 h-3.5" />
              <span>Phase 8: Administrator Suite</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-white font-serif">
              BookNest Campus Admin Portal
            </h1>
            <p className="text-slate-300 text-sm mt-1 max-w-2xl">
              Centralized warehouse stock management, catalog CRUD, real-time campus order dispatching, and sales analytics engine.
            </p>
          </div>

          <div className="flex items-center gap-2 flex-wrap">
            <button 
              onClick={handleOpenAddModal}
              className="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl text-xs sm:text-sm flex items-center gap-2 shadow-lg shadow-blue-600/30 transition-all cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>Add New Book</span>
            </button>
            {onOpenCodeExplorer && (
              <button 
                onClick={() => onOpenCodeExplorer('admin/index.php')}
                className="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-600 font-semibold rounded-xl text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer"
              >
                <FileText className="w-4 h-4 text-blue-400" />
                <span>Admin PHP Sources</span>
              </button>
            )}
          </div>
        </div>

        {/* Sub-Navigation Tabs */}
        <div className="flex items-center gap-1 mt-6 pt-6 border-t border-slate-700/60 overflow-x-auto pb-1">
          {[
            { id: 'dashboard', label: '📊 Dashboard Overview' },
            { id: 'books', label: `📚 Books Catalog (${books.length})` },
            { id: 'inventory', label: `📋 Inventory & Stock (${lowStockBooks.length} Low)` },
            { id: 'orders', label: `📦 Campus Orders (${orders.length})` },
            { id: 'analytics', label: '📈 Store Analytics' }
          ].map(tab => (
            <button
              key={tab.id}
              onClick={() => setAdminTab(tab.id as any)}
              className={`px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold whitespace-nowrap transition-all cursor-pointer ${
                adminTab === tab.id
                  ? 'bg-white text-slate-900 shadow-md'
                  : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
              }`}
            >
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* ================= TAB 1: DASHBOARD ================= */}
      {adminTab === 'dashboard' && (
        <div className="space-y-8 animate-in fade-in duration-200">
          
          {/* KPI Stats Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="flex items-center justify-between text-stone-500 mb-2">
                <span className="text-xs font-bold uppercase tracking-wider">Catalog Titles</span>
                <BookOpen className="w-5 h-5 text-blue-600" />
              </div>
              <div className="text-3xl font-extrabold text-stone-900">{books.length}</div>
              <p className="text-xs text-stone-500 mt-1">Across {categories.length} academic disciplines</p>
            </div>

            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="flex items-center justify-between text-stone-500 mb-2">
                <span className="text-xs font-bold uppercase tracking-wider">Warehouse Units</span>
                <Package className="w-5 h-5 text-indigo-600" />
              </div>
              <div className="text-3xl font-extrabold text-stone-900">{totalStockUnits}</div>
              <p className="text-xs text-emerald-600 font-semibold mt-1">₹{totalValuation.toLocaleString()} inventory valuation</p>
            </div>

            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="flex items-center justify-between text-stone-500 mb-2">
                <span className="text-xs font-bold uppercase tracking-wider">Campus Orders</span>
                <ShoppingBag className="w-5 h-5 text-amber-600" />
              </div>
              <div className="text-3xl font-extrabold text-stone-900">{orders.length}</div>
              <p className="text-xs text-stone-500 mt-1">Live fulfillment & tracking active</p>
            </div>

            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="flex items-center justify-between text-stone-500 mb-2">
                <span className="text-xs font-bold uppercase tracking-wider">Paid Gross Revenue</span>
                <DollarSign className="w-5 h-5 text-emerald-600" />
              </div>
              <div className="text-3xl font-extrabold text-stone-900">₹{totalPaidRevenue.toLocaleString()}</div>
              <p className="text-xs text-emerald-600 font-semibold mt-1">+14.2% student adoption rate</p>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {/* Recent Orders Pipeline */}
            <div className="lg:col-span-2 bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
              <div className="px-6 py-4 border-b border-stone-100 flex items-center justify-between bg-stone-50/50">
                <div>
                  <h3 className="font-bold text-stone-900 text-base">Recent Campus Orders</h3>
                  <p className="text-xs text-stone-500">Live feed from checkout transactions</p>
                </div>
                <button 
                  onClick={() => setAdminTab('orders')}
                  className="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 cursor-pointer"
                >
                  <span>View All ({orders.length})</span>
                  <ArrowUpRight className="w-3.5 h-3.5" />
                </button>
              </div>

              <div className="divide-y divide-stone-100">
                {orders.slice(0, 5).map(ord => (
                  <div key={ord.id} className="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-stone-50/80 transition-colors">
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-mono font-bold text-stone-900 text-sm">#{ord.id}</span>
                        <span className={`px-2 py-0.5 rounded-full text-[11px] font-bold ${
                          ord.status === 'Delivered' ? 'bg-emerald-100 text-emerald-800' :
                          ord.status === 'Shipped' ? 'bg-purple-100 text-purple-800' :
                          ord.status === 'Processing' ? 'bg-blue-100 text-blue-800' :
                          'bg-amber-100 text-amber-800'
                        }`}>
                          {ord.status}
                        </span>
                      </div>
                      <div className="text-xs text-stone-600 mt-1">
                        {ord.address.name} • {ord.items.length} titles ({ord.items.reduce((s, i) => s + i.quantity, 0)} books)
                      </div>
                    </div>

                    <div className="flex items-center gap-3">
                      <div className="text-right">
                        <div className="font-bold text-stone-900 text-sm">₹{ord.total.toFixed(2)}</div>
                        <div className="text-[11px] text-stone-500 capitalize">{ord.paymentMethod}</div>
                      </div>
                      <select 
                        value={ord.status}
                        onChange={(e) => onUpdateOrderStatus(ord.id, e.target.value as any)}
                        className="text-xs font-semibold px-2 py-1 rounded border border-stone-300 bg-white text-stone-700 cursor-pointer"
                      >
                        <option value="Confirmed">Confirmed</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                      </select>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Low Stock Replenishment Alerts */}
            <div className="bg-white rounded-xl border border-stone-200 shadow-sm p-6 flex flex-col justify-between">
              <div>
                <div className="flex items-center gap-2 mb-4">
                  <div className="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <AlertTriangle className="w-4 h-4" />
                  </div>
                  <div>
                    <h3 className="font-bold text-stone-900 text-sm">Stock Replenishment Alerts</h3>
                    <p className="text-xs text-stone-500">Items below 10 units threshold</p>
                  </div>
                </div>

                {lowStockBooks.length === 0 ? (
                  <div className="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium">
                    ✓ All catalog titles have healthy inventory levels (&gt;10 copies).
                  </div>
                ) : (
                  <div className="space-y-3">
                    {lowStockBooks.slice(0, 4).map(b => (
                      <div key={b.id} className="p-3 bg-amber-50/60 border border-amber-200/80 rounded-lg flex items-center justify-between gap-3">
                        <div className="min-w-0">
                          <div className="font-bold text-stone-900 text-xs truncate">{b.title}</div>
                          <div className="text-[11px] text-amber-700 font-semibold">Only {b.stock} units remaining</div>
                        </div>
                        <button 
                          onClick={() => onUpdateBookStock(b.id, b.stock + 15)}
                          className="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded font-bold text-xs whitespace-nowrap cursor-pointer transition-all"
                        >
                          +15 Restock
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              <button 
                onClick={() => setAdminTab('inventory')}
                className="w-full mt-6 py-2.5 rounded-lg border border-stone-300 hover:bg-stone-50 text-stone-700 text-xs font-bold transition-all text-center block cursor-pointer"
              >
                Open Complete Inventory Sheet →
              </button>
            </div>

          </div>

        </div>
      )}

      {/* ================= TAB 2: BOOKS CATALOG ================= */}
      {adminTab === 'books' && (
        <div className="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden animate-in fade-in duration-200">
          
          {/* Filter Toolbar */}
          <div className="p-5 border-b border-stone-100 flex flex-wrap items-center justify-between gap-4 bg-stone-50/50">
            <div className="flex items-center gap-3 flex-1 min-w-[260px] max-w-lg">
              <div className="relative flex-1">
                <Search className="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input 
                  type="text"
                  placeholder="Search titles, authors, or ISBN codes..."
                  value={bookSearch}
                  onChange={(e) => setBookSearch(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 bg-white border border-stone-200 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <select 
                value={categoryFilter}
                onChange={(e) => setCategoryFilter(e.target.value)}
                className="py-2 px-3 bg-white border border-stone-200 rounded-lg text-xs sm:text-sm font-medium text-stone-700 cursor-pointer"
              >
                <option value="all">All Disciplines</option>
                {categories.map(c => (
                  <option key={c} value={c}>{c}</option>
                ))}
              </select>
            </div>

            <div className="flex items-center gap-3">
              <span className="text-xs text-stone-500 font-medium">
                Showing <strong>{filteredBooks.length}</strong> of {books.length} books
              </span>
              <button 
                onClick={handleOpenAddModal}
                className="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-xs flex items-center gap-1.5 shadow-sm cursor-pointer"
              >
                <Plus className="w-4 h-4" />
                <span>Add Book</span>
              </button>
            </div>
          </div>

          {/* Table */}
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs sm:text-sm">
              <thead className="bg-stone-50 text-stone-500 font-bold uppercase tracking-wider text-[11px] border-b border-stone-200">
                <tr>
                  <th className="py-3.5 px-4 sm:px-6">Book Title & Author</th>
                  <th className="py-3.5 px-4">Discipline</th>
                  <th className="py-3.5 px-4">ISBN</th>
                  <th className="py-3.5 px-4">Price & Discount</th>
                  <th className="py-3.5 px-4">Stock</th>
                  <th className="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-stone-100">
                {filteredBooks.map(b => (
                  <tr key={b.id} className="hover:bg-stone-50/60 transition-colors">
                    <td className="py-3 px-4 sm:px-6">
                      <div className="flex items-center gap-3">
                        <img 
                          src={b.coverImage} 
                          alt={b.title} 
                          className="w-10 h-14 object-cover rounded shadow-sm flex-shrink-0"
                        />
                        <div className="min-w-0">
                          <div className="font-bold text-stone-900 truncate max-w-xs">{b.title}</div>
                          <div className="text-xs text-stone-500">{b.author}</div>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-4">
                      <span className="px-2.5 py-1 bg-stone-100 text-stone-700 rounded-md font-semibold text-xs">
                        {b.category}
                      </span>
                    </td>
                    <td className="py-3 px-4 font-mono text-xs text-stone-600">
                      {b.isbn}
                    </td>
                    <td className="py-3 px-4">
                      <span className="font-bold text-stone-900">₹{(b.price * (1 - (b.discount || 0) / 100)).toFixed(0)}</span>
                      {b.discount > 0 && (
                        <span className="ml-1.5 text-xs text-rose-600 font-semibold">({b.discount}% off)</span>
                      )}
                    </td>
                    <td className="py-3 px-4">
                      <span className={`font-bold text-xs ${
                        b.stock < 10 ? 'text-amber-600' : 'text-emerald-700'
                      }`}>
                        {b.stock} units
                      </span>
                    </td>
                    <td className="py-3 px-4 sm:px-6 text-right">
                      <div className="inline-flex items-center gap-1.5">
                        <button 
                          onClick={() => handleOpenEditModal(b)}
                          className="p-1.5 text-stone-500 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors cursor-pointer"
                          title="Edit Book"
                        >
                          <Edit3 className="w-4 h-4" />
                        </button>
                        <button 
                          onClick={() => {
                            if (confirm(`Remove "${b.title}" from store catalog?`)) {
                              onDeleteBook(b.id);
                            }
                          }}
                          className="p-1.5 text-stone-500 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors cursor-pointer"
                          title="Delete Book"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

        </div>
      )}

      {/* ================= TAB 3: INVENTORY ================= */}
      {adminTab === 'inventory' && (
        <div className="space-y-6 animate-in fade-in duration-200">
          
          {/* Inventory KPI Summary */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="text-xs font-bold uppercase text-stone-500">Warehouse Inventory Valuation</div>
              <div className="text-3xl font-extrabold text-stone-900 mt-2">₹{totalValuation.toLocaleString()}</div>
              <p className="text-xs text-stone-500 mt-1">Based on current retail prices</p>
            </div>
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="text-xs font-bold uppercase text-stone-500">Physical Stock Count</div>
              <div className="text-3xl font-extrabold text-stone-900 mt-2">{totalStockUnits} Copies</div>
              <p className="text-xs text-stone-500 mt-1">Ready for student fulfillment</p>
            </div>
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="text-xs font-bold uppercase text-stone-500">Low Stock Warnings</div>
              <div className="text-3xl font-extrabold text-amber-600 mt-2">{lowStockBooks.length} Titles</div>
              <p className="text-xs text-stone-500 mt-1">Under 10 units in stock</p>
            </div>
          </div>

          <div className="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
            <div className="p-4 sm:p-5 border-b border-stone-100 flex items-center justify-between bg-stone-50/50">
              <div>
                <h3 className="font-bold text-stone-900 text-base">Live Stock Level Controller</h3>
                <p className="text-xs text-stone-500">Adjust warehouse copies with instant session persistence</p>
              </div>
              <div className="flex items-center gap-2">
                <button 
                  onClick={() => {
                    books.forEach(b => {
                      if (b.stock < 10) onUpdateBookStock(b.id, b.stock + 20);
                    });
                  }}
                  className="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm cursor-pointer transition-all"
                >
                  <RefreshCw className="w-3.5 h-3.5" />
                  <span>Batch Restock Low Items (+20)</span>
                </button>
              </div>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs sm:text-sm">
                <thead className="bg-stone-50 text-stone-500 font-bold uppercase tracking-wider text-[11px] border-b border-stone-200">
                  <tr>
                    <th className="py-3.5 px-4 sm:px-6">Book Title</th>
                    <th className="py-3.5 px-4">Discipline</th>
                    <th className="py-3.5 px-4">Unit Price</th>
                    <th className="py-3.5 px-4">Inventory Valuation</th>
                    <th className="py-3.5 px-4">Current Stock</th>
                    <th className="py-3.5 px-4 sm:px-6 text-right">Quick Restock Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-stone-100">
                  {books.map(b => (
                    <tr key={b.id} className="hover:bg-stone-50/60 transition-colors">
                      <td className="py-3 px-4 sm:px-6">
                        <div className="font-bold text-stone-900">{b.title}</div>
                        <div className="text-xs text-stone-500">{b.author} • ISBN: {b.isbn}</div>
                      </td>
                      <td className="py-3 px-4">
                        <span className="px-2 py-0.5 bg-stone-100 text-stone-700 rounded text-xs font-semibold">
                          {b.category}
                        </span>
                      </td>
                      <td className="py-3 px-4 font-semibold text-stone-900">
                        ₹{b.price}
                      </td>
                      <td className="py-3 px-4 font-bold text-stone-900">
                        ₹{(b.stock * b.price).toLocaleString()}
                      </td>
                      <td className="py-3 px-4">
                        <div className="flex items-center gap-2">
                          <span className={`px-2 py-0.5 rounded font-extrabold text-xs ${
                            b.stock < 10 
                              ? 'bg-amber-100 text-amber-800' 
                              : 'bg-emerald-100 text-emerald-800'
                          }`}>
                            {b.stock} units
                          </span>
                        </div>
                      </td>
                      <td className="py-3 px-4 sm:px-6 text-right">
                        <div className="inline-flex items-center gap-2">
                          <button 
                            onClick={() => onUpdateBookStock(b.id, Math.max(0, b.stock - 5))}
                            className="px-2 py-1 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded font-bold text-xs cursor-pointer"
                          >
                            -5
                          </button>
                          <button 
                            onClick={() => onUpdateBookStock(b.id, b.stock + 5)}
                            className="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded font-bold text-xs cursor-pointer"
                          >
                            +5
                          </button>
                          <button 
                            onClick={() => onUpdateBookStock(b.id, b.stock + 20)}
                            className="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-bold text-xs cursor-pointer shadow-sm"
                          >
                            +20 Restock
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>

        </div>
      )}

      {/* ================= TAB 4: ORDERS ================= */}
      {adminTab === 'orders' && (
        <div className="space-y-6 animate-in fade-in duration-200">
          
          <div className="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
            <div className="p-4 sm:p-5 border-b border-stone-100 flex flex-wrap items-center justify-between gap-4 bg-stone-50/50">
              <div>
                <h3 className="font-bold text-stone-900 text-base">Campus Orders Pipeline</h3>
                <p className="text-xs text-stone-500">Student deliveries, dispatch timelines, and payment status verification</p>
              </div>

              <div className="flex items-center gap-2">
                <select 
                  value={orderStatusFilter}
                  onChange={(e) => setOrderStatusFilter(e.target.value)}
                  className="py-1.5 px-3 bg-white border border-stone-200 rounded-lg text-xs font-semibold text-stone-700 cursor-pointer"
                >
                  <option value="all">All Statuses ({orders.length})</option>
                  <option value="Confirmed">Confirmed</option>
                  <option value="Processing">Processing</option>
                  <option value="Shipped">Shipped</option>
                  <option value="Delivered">Delivered</option>
                </select>
              </div>
            </div>

            <div className="divide-y divide-stone-100">
              {filteredOrders.length === 0 ? (
                <div className="p-12 text-center text-stone-500 text-sm">
                  No campus orders match the selected status filter.
                </div>
              ) : (
                filteredOrders.map(ord => (
                  <div key={ord.id} className="p-5 sm:px-6 hover:bg-stone-50/60 transition-colors">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                      <div>
                        <div className="flex items-center gap-2.5">
                          <span className="font-mono font-bold text-stone-900 text-sm">#{ord.id}</span>
                          <span className="text-xs text-stone-400">•</span>
                          <span className="text-xs text-stone-600 font-medium">Placed on {ord.date}</span>
                          <span className={`px-2 py-0.5 rounded-full text-[11px] font-bold ${
                            ord.status === 'Delivered' ? 'bg-emerald-100 text-emerald-800' :
                            ord.status === 'Shipped' ? 'bg-purple-100 text-purple-800' :
                            ord.status === 'Processing' ? 'bg-blue-100 text-blue-800' :
                            'bg-amber-100 text-amber-800'
                          }`}>
                            {ord.status}
                          </span>
                        </div>

                        <div className="mt-2 text-xs text-stone-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                          <span><strong>Student:</strong> {ord.address.name}</span>
                          <span><strong>Contact:</strong> {ord.address.phone}</span>
                          <span><strong>Address:</strong> {ord.address.address}, {ord.address.city} - {ord.address.pincode}</span>
                        </div>
                      </div>

                      <div className="flex items-center gap-4">
                        <div className="text-right">
                          <div className="font-extrabold text-stone-900 text-base">₹{ord.total.toFixed(2)}</div>
                          <span className="text-[11px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                            {ord.paymentMethod.toUpperCase()} • PAID
                          </span>
                        </div>

                        <select 
                          value={ord.status}
                          onChange={(e) => onUpdateOrderStatus(ord.id, e.target.value as any)}
                          className="text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-stone-300 bg-white text-stone-800 cursor-pointer shadow-sm"
                        >
                          <option value="Confirmed">Confirmed</option>
                          <option value="Processing">Processing</option>
                          <option value="Shipped">Shipped</option>
                          <option value="Delivered">Delivered</option>
                        </select>

                        <button 
                          onClick={() => setViewingOrder(ord)}
                          className="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-800 rounded-lg text-xs font-bold transition-all cursor-pointer"
                        >
                          Manifest
                        </button>
                      </div>
                    </div>

                    {/* Book items preview */}
                    <div className="mt-3 pt-3 border-t border-stone-100 flex items-center gap-2 overflow-x-auto">
                      {ord.items.map(item => (
                        <div key={item.book.id} className="flex items-center gap-2 px-2.5 py-1 bg-stone-100/70 rounded-md text-xs whitespace-nowrap">
                          <img src={item.book.coverImage} alt="" className="w-5 h-7 object-cover rounded" />
                          <span className="font-semibold text-stone-900 truncate max-w-[140px]">{item.book.title}</span>
                          <span className="text-stone-500 font-bold">×{item.quantity}</span>
                        </div>
                      ))}
                    </div>

                  </div>
                ))
              )}
            </div>
          </div>

        </div>
      )}

      {/* ================= TAB 5: ANALYTICS ================= */}
      {adminTab === 'analytics' && (
        <div className="space-y-6 animate-in fade-in duration-200">
          
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="text-xs font-bold uppercase text-stone-500">Gross Paid Revenue</div>
              <div className="text-3xl font-extrabold text-stone-900 mt-2">₹{totalPaidRevenue.toLocaleString()}</div>
              <p className="text-xs text-emerald-600 font-semibold mt-1">↑ 18.4% monthly increase</p>
            </div>
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="text-xs font-bold uppercase text-stone-500">Average Order Value (AOV)</div>
              <div className="text-3xl font-extrabold text-stone-900 mt-2">
                ₹{orders.length > 0 ? (orders.reduce((s, o) => s + o.total, 0) / orders.length).toFixed(0) : '480'}
              </div>
              <p className="text-xs text-stone-500 mt-1">~2.3 books per transaction</p>
            </div>
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="text-xs font-bold uppercase text-stone-500">Campus Delivery Rate</div>
              <div className="text-3xl font-extrabold text-emerald-700 mt-2">99.2%</div>
              <p className="text-xs text-stone-500 mt-1">Same-day hostel delivery</p>
            </div>
            <div className="bg-white p-5 rounded-xl border border-stone-200 shadow-sm">
              <div className="text-xs font-bold uppercase text-stone-500">Student Retention</div>
              <div className="text-3xl font-extrabold text-indigo-700 mt-2">78.5%</div>
              <p className="text-xs text-stone-500 mt-1">Repeat semester orders</p>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {/* Discipline Inventory Distribution */}
            <div className="bg-white p-6 rounded-xl border border-stone-200 shadow-sm">
              <h3 className="font-bold text-stone-900 text-base mb-4">Catalog Distribution by Discipline</h3>
              <div className="space-y-4">
                {categories.map(cat => {
                  const catBooks = books.filter(b => b.category === cat);
                  const catStock = catBooks.reduce((s, b) => s + b.stock, 0);
                  const pct = Math.min(100, Math.round((catStock / Math.max(1, totalStockUnits)) * 100));
                  return (
                    <div key={cat}>
                      <div className="flex justify-between text-xs font-bold text-stone-800 mb-1">
                        <span>{cat}</span>
                        <span className="text-stone-500 font-medium">{catStock} units ({catBooks.length} titles) • {pct}%</span>
                      </div>
                      <div className="w-full h-2.5 bg-stone-100 rounded-full overflow-hidden">
                        <div 
                          className="h-full bg-blue-600 rounded-full transition-all duration-500"
                          style={{ width: `${Math.max(8, pct)}%` }}
                        />
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* Payment Method Breakdown */}
            <div className="bg-white p-6 rounded-xl border border-stone-200 shadow-sm">
              <h3 className="font-bold text-stone-900 text-base mb-4">Payment Methods Breakdown</h3>
              
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div className="p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                  <div className="text-xs font-bold text-emerald-800 uppercase">UPI / QR Code</div>
                  <div className="text-2xl font-extrabold text-emerald-950 mt-1">68.4%</div>
                  <div className="text-[11px] text-emerald-700 mt-0.5">Google Pay, PhonePe, Paytm</div>
                </div>

                <div className="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                  <div className="text-xs font-bold text-blue-800 uppercase">Credit / Debit Card</div>
                  <div className="text-2xl font-extrabold text-blue-950 mt-1">22.1%</div>
                  <div className="text-[11px] text-blue-700 mt-0.5">MasterCard, Visa, RuPay</div>
                </div>
              </div>

              <div className="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between">
                <div>
                  <div className="text-xs font-bold text-amber-800 uppercase">Campus Cash On Delivery</div>
                  <div className="text-[11px] text-amber-700">Hand-to-hand hostel verification</div>
                </div>
                <div className="text-xl font-extrabold text-amber-950">9.5%</div>
              </div>
            </div>

          </div>

        </div>
      )}

      {/* ================= MODAL: ADD / EDIT BOOK ================= */}
      {isBookModalOpen && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl relative">
            <button 
              onClick={() => setIsBookModalOpen(false)}
              className="absolute top-5 right-5 text-stone-400 hover:text-stone-700 p-1.5 rounded-full hover:bg-stone-100 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>

            <h2 className="text-xl font-bold text-stone-900 font-serif mb-1">
              {editingBook ? 'Edit Book Record' : 'Add New Book to Catalog'}
            </h2>
            <p className="text-xs text-stone-500 mb-6">
              Enter academic course specs, pricing, and initial warehouse stock units.
            </p>

            <form onSubmit={handleSubmitBook} className="space-y-4 text-xs sm:text-sm">
              <div>
                <label className="block font-bold text-stone-700 mb-1">Book Title *</label>
                <input 
                  type="text" 
                  required
                  value={formData.title}
                  onChange={e => setFormData({ ...formData, title: e.target.value })}
                  placeholder="e.g. Design Patterns: Elements of Reusable Object-Oriented Software"
                  className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-bold text-stone-700 mb-1">Author Name *</label>
                  <input 
                    type="text" 
                    required
                    value={formData.author}
                    onChange={e => setFormData({ ...formData, author: e.target.value })}
                    placeholder="e.g. Erich Gamma"
                    className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block font-bold text-stone-700 mb-1">Discipline / Category *</label>
                  <select 
                    value={formData.category}
                    onChange={e => setFormData({ ...formData, category: e.target.value, categorySlug: e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-') })}
                    className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                  >
                    {categories.map(c => (
                      <option key={c} value={c}>{c}</option>
                    ))}
                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                    <option value="Biotechnology">Biotechnology</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-3">
                <div>
                  <label className="block font-bold text-stone-700 mb-1">ISBN Code *</label>
                  <input 
                    type="text" 
                    required
                    value={formData.isbn}
                    onChange={e => setFormData({ ...formData, isbn: e.target.value })}
                    placeholder="9780..."
                    className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-xs"
                  />
                </div>
                <div>
                  <label className="block font-bold text-stone-700 mb-1">Price (₹) *</label>
                  <input 
                    type="number" 
                    required
                    value={formData.price}
                    onChange={e => setFormData({ ...formData, price: parseFloat(e.target.value) || 0 })}
                    className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block font-bold text-stone-700 mb-1">Discount (%)</label>
                  <input 
                    type="number" 
                    min="0"
                    max="90"
                    value={formData.discount}
                    onChange={e => setFormData({ ...formData, discount: parseInt(e.target.value) || 0 })}
                    className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-bold text-stone-700 mb-1">Warehouse Stock *</label>
                  <input 
                    type="number" 
                    required
                    min="0"
                    value={formData.stock}
                    onChange={e => setFormData({ ...formData, stock: parseInt(e.target.value) || 0 })}
                    className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label className="block font-bold text-stone-700 mb-1">Publisher</label>
                  <input 
                    type="text" 
                    value={formData.publisher}
                    onChange={e => setFormData({ ...formData, publisher: e.target.value })}
                    placeholder="Pearson / McGraw Hill"
                    className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <div>
                <label className="block font-bold text-stone-700 mb-1">Cover Image URL</label>
                <input 
                  type="url" 
                  value={formData.coverImage}
                  onChange={e => setFormData({ ...formData, coverImage: e.target.value })}
                  placeholder="https://..."
                  className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block font-bold text-stone-700 mb-1">Course Description & Syllabus Notes</label>
                <textarea 
                  rows={3}
                  value={formData.description}
                  onChange={e => setFormData({ ...formData, description: e.target.value })}
                  className="w-full px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                />
              </div>

              <div className="flex items-center justify-end gap-3 pt-3">
                <button 
                  type="button"
                  onClick={() => setIsBookModalOpen(false)}
                  className="px-4 py-2 border border-stone-300 text-stone-700 rounded-lg font-bold hover:bg-stone-50 cursor-pointer"
                >
                  Cancel
                </button>
                <button 
                  type="submit"
                  className="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-md cursor-pointer"
                >
                  {editingBook ? 'Save Changes' : 'Create Book'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ================= MODAL: ORDER DETAILS & MANIFEST ================= */}
      {viewingOrder && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-in fade-in duration-200">
          <div className="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 shadow-2xl relative">
            <button 
              onClick={() => setViewingOrder(null)}
              className="absolute top-5 right-5 text-stone-400 hover:text-stone-700 p-1.5 rounded-full hover:bg-stone-100 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="flex items-center gap-2 mb-1">
              <span className="font-mono font-bold text-stone-900 text-base">#{viewingOrder.id}</span>
              <span className={`px-2 py-0.5 rounded text-[11px] font-bold ${
                viewingOrder.status === 'Delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'
              }`}>
                {viewingOrder.status}
              </span>
            </div>
            <p className="text-xs text-stone-500 mb-4">
              Placed on {viewingOrder.date}
            </p>

            <div className="bg-stone-50 rounded-xl p-4 border border-stone-200 mb-4 space-y-1 text-xs">
              <div className="font-bold text-stone-900">{viewingOrder.address.name}</div>
              <div className="text-stone-600">📞 {viewingOrder.address.phone}</div>
              <div className="text-stone-600">{viewingOrder.address.address}, {viewingOrder.address.city} - {viewingOrder.address.pincode}</div>
            </div>

            <h4 className="font-bold text-stone-900 text-xs uppercase tracking-wider mb-2">Manifest Items</h4>
            <div className="divide-y divide-stone-100 border border-stone-200 rounded-xl overflow-hidden mb-5">
              {viewingOrder.items.map(item => (
                <div key={item.book.id} className="p-3 flex items-center justify-between gap-3 text-xs bg-white">
                  <div className="flex items-center gap-2.5">
                    <img src={item.book.coverImage} alt="" className="w-8 h-11 object-cover rounded shadow-xs" />
                    <div>
                      <div className="font-bold text-stone-900 max-w-[200px] truncate">{item.book.title}</div>
                      <div className="text-stone-500 font-mono text-[11px]">ISBN: {item.book.isbn}</div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="font-bold text-stone-900">₹{(item.price * item.quantity).toFixed(2)}</div>
                    <div className="text-stone-500 text-[11px]">Qty: {item.quantity} × ₹{item.price}</div>
                  </div>
                </div>
              ))}
            </div>

            <div className="flex items-center justify-between pt-2 border-t border-stone-200">
              <div>
                <span className="text-xs text-stone-500">Total Order Amount:</span>
                <div className="text-lg font-extrabold text-stone-900">₹{viewingOrder.total.toFixed(2)}</div>
              </div>
              <button 
                onClick={() => setViewingOrder(null)}
                className="px-5 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-xs font-bold cursor-pointer"
              >
                Close Manifest
              </button>
            </div>

          </div>
        </div>
      )}

    </div>
  );
}
