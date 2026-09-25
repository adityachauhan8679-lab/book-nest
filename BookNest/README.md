# 📖 BookNest — Modern Online Bookstore (College Project)

> **"Find your next great read."**
> A modern, full-stack online bookstore with physical book scanning, category filtering, cart, wishlist, orders, reader reviews, and an administrator management suite.

---

## 🛠️ Academic Technology Stack
* **Backend:** PHP 8+ (PDO Prepared Statements, Password Hashing, Sessions)
* **Frontend:** Semantic HTML5, Responsive CSS3 (Warm Modern Bookish Palette), Vanilla JavaScript (ES6 Modules)
* **Database:** MySQL 5.7+ / MariaDB 10.4+ (Indexed relational schema, foreign key constraints)
* **Local Server:** XAMPP / Apache / phpMyAdmin
* **IDE:** Visual Studio Code

---

## 🚀 Quick Start with XAMPP (Local Installation)

### Step 1: Copy to XAMPP `htdocs`
1. Download or copy the entire `BookNest` folder.
2. Paste it into your XAMPP web root directory:
   * **Windows:** `C:\xampp\htdocs\BookNest`
   * **macOS:** `/Applications/XAMPP/xamppfiles/htdocs/BookNest`
   * **Linux:** `/opt/lampp/htdocs/BookNest`

### Step 2: Start Apache & MySQL in XAMPP
1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### Step 3: Create Database & Seed Sample Data
1. Open your browser and navigate to:
   ```text
   http://localhost/phpmyadmin
   ```
2. Click on the **Databases** tab, type `booknest` in the database name field, and click **Create**.
3. Click on the newly created `booknest` database in the left sidebar.
4. Go to the **Import** tab:
   * Choose File: `BookNest/database/schema.sql` → Click **Go** (executes tables & foreign keys).
   * Choose File: `BookNest/database/seed.sql` → Click **Go** (inserts 15 categories, authors, 32 books, and demo accounts).

Alternatively, using MySQL command line in the XAMPP Shell:
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

### Step 4: Verify Database Connection
Check `config/database.php`. Standard XAMPP defaults work out of the box:
* **Host:** `127.0.0.1` (or `localhost`)
* **Database:** `booknest`
* **Username:** `root`
* **Password:** *(empty)*

### Step 5: Launch BookNest
Open your browser and navigate to:
```text
http://localhost/BookNest/index.php
```

---

## 🔑 Demo Login Credentials

| Role | Email | Password | Access Area |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@booknest.com` | `Admin@123` | `/admin` (Full store management, inventory, orders, analytics) |
| **Normal User** | `user@booknest.com` | `User@123` | Storefront, Wishlist, Cart, Checkout, Order Tracking |

---

## 📂 Phase 1 Structure Overview
```text
BookNest/
├── index.php                  # Modern Homepage with Hero, Categories, Featured, Scanner CTA
├── config/
│   └── database.php           # PDO database connection with error handling & singleton
├── database/
│   ├── schema.sql             # Complete 13-table relational schema with indexes & FKs
│   └── seed.sql               # 15 Categories, Authors, 32 Real Books, Demo Users & Orders
├── includes/
│   ├── header.php             # HTML head, typography, styles, navbar inclusion
│   ├── navbar.php             # Responsive navigation with cart/wishlist badges & search
│   ├── footer.php             # Modern bookstore footer, tech badges, toast script
│   └── functions.php          # XSS sanitization (e()), INR formatting (₹), CSRF, rating stars
├── components/
│   ├── book-card.php          # Reusable book card (cover, rating, price, discount, wishlist, cart)
│   ├── category-card.php      # Category card with emoji badges and book count
│   ├── rating.php             # Star rating renderer
│   ├── pagination.php         # Reusable pagination links
│   └── toast.php              # Dynamic toast notification alerts
├── assets/
│   ├── css/
│   │   ├── style.css          # Design system (warm paper aesthetic, clean typography)
│   │   ├── responsive.css     # Mobile drawer, tablet layouts, breakpoints
│   │   └── admin.css          # Admin panel styling
│   └── js/
│       └── main.js            # Mobile menu drawer, sticky header, live cart & wishlist counters
```

---

## 📋 Development Roadmap
* **✅ PHASE 1:** Folder structure, MySQL Schema, Seed Data, PDO Connection, Responsive Layout, Navbar, Footer, Homepage, Components.
* **✅ PHASE 2:** User Authentication (Registration, Login, Password Hashing via BCrypt, Sessions, Profile, RBAC & CSRF).
* **✅ PHASE 3:** Catalog, Real-time Search Autocomplete API, Multi-Faceted Filters (Genre, Author, Price Slider, Rating, Stock), Book Details page with "You May Also Like" recommendations.
* **✅ PHASE 4:** Cart, Wishlist, Local & Database Synchronized Quantity Management, Dynamic Free Delivery Bar.
* **✅ PHASE 5:** 3-Step Checkout, Shipping Address Book, Demo UPI/Card/COD Payment, Orders & Tracking Timeline, Printable Invoices.
* **✅ PHASE 6:** Verified Reviews & Badges, 5-Star Histogram, "Frequently Bought Together" 10% Smart Combos & Rule-Based Recommendation Engine.
* **✅ PHASE 7:** **BookNest Optical Scanner** (HTML5 MediaStream camera feed, BarcodeDetector API for EAN-13 ISBN barcodes, multi-field fuzzy search, instant 1-click cart addition).
* **✅ PHASE 8:** Complete Administrator Suite (Dashboard KPIs, Books Catalog CRUD, Warehouse Inventory Controller, Campus Orders Fulfillment Pipeline, Store Analytics & Reports).
* **✅ PHASE 9:** **Security Audit & Hardening** (100% Prepared Statements, Centralized `e()` XSS escaping, Cryptographic CSRF Tokens, Session Fixation Immunity, Sliding-Window Rate Limiting, OWASP Top 10 Compliance).
* **✅ PHASE 10:** **Final UI Polish, Accessibility & Production Readiness** (WCAG AA Contrast, Keyboard Navigation, ARIA Landmarks, Dynamic Empty States, Edge-Case Fault Tolerance, Complete Academic Documentation).

---

## 🎓 Academic Submission & Local XAMPP Setup Guide

### 1. Database Provisioning
1. Launch **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Navigate to `http://localhost/phpmyadmin`.
3. Create a new database named `booknest_db` with `utf8mb4_unicode_ci` collation.
4. Import `BookNest/database/schema.sql`, followed by `BookNest/database/seed.sql`.

### 2. Virtual Directory Setup
1. Move or clone the `BookNest/` folder into your XAMPP webroot:
   - **Windows:** `C:\xampp\htdocs\BookNest`
   - **macOS (XAMPP-VM / MAMP):** `/Applications/XAMPP/htdocs/BookNest`
   - **Linux:** `/opt/lampp/htdocs/BookNest`
2. Open your web browser and navigate to:
   `http://localhost/BookNest/`

### 3. Demo Credentials
* **Administrator Account:** `admin@booknest.com` / `admin123` (Access to Admin Dashboard, Inventory, Catalog CRUD)
* **Student Account:** `user@booknest.com` / `user123` (Access to Storefront, Optical Scanner, Cart, Wishlist, Orders)
