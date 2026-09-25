# 🗄️ BookNest - Database Architecture & Schema Specification
**RDBMS Engine:** MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine)  
**Collation:** `utf8mb4_unicode_ci`  
**Total Tables:** 11 Relational Entities with Foreign Key Cascades & Composite Indexes

---

## Entity Relationship Overview

```
[users] ───< [orders] ───< [order_items] >─── [books]
   │             │                               │
   ├──< [addresses]                              ├──< [reviews]
   ├──< [cart] >─────────────────────────────────┤
   └──< [wishlists] >────────────────────────────┤
                                                 ├──> [authors]
                                                 └──> [categories]
```

---

## Relational Tables Specification

### 1. `users`
Stores student customer and administrator credentials.
- `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(120) NOT NULL
- `email`: VARCHAR(191) NOT NULL UNIQUE (Indexed)
- `password`: VARCHAR(255) NOT NULL (BCrypt hash)
- `role`: ENUM('user', 'admin') DEFAULT 'user'
- `phone`: VARCHAR(20) DEFAULT NULL
- `status`: ENUM('active', 'inactive', 'banned') DEFAULT 'active'
- `created_at`, `updated_at`: TIMESTAMP

### 2. `categories`
Organizes books into campus disciplines (Computer Science, Mathematics, Fiction, etc.).
- `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(100) UNIQUE NOT NULL
- `slug`: VARCHAR(120) UNIQUE NOT NULL
- `description`: TEXT
- `image`: VARCHAR(255)

### 3. `authors`
Academic scholars, novelists, and textbook writers.
- `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(150) NOT NULL (Indexed)
- `bio`: TEXT

### 4. `books`
Central catalog entity containing pricing, stock levels, and ISBN identifiers.
- `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `title`: VARCHAR(255) NOT NULL (Indexed)
- `author_id`: INT UNSIGNED NOT NULL (FK -> `authors.id` ON DELETE CASCADE)
- `category_id`: INT UNSIGNED NOT NULL (FK -> `categories.id` ON DELETE RESTRICT)
- `isbn`: VARCHAR(20) UNIQUE NOT NULL (Indexed for Optical Scanner)
- `price`: DECIMAL(10,2) NOT NULL
- `discount`: DECIMAL(5,2) DEFAULT 0.00
- `stock`: INT NOT NULL DEFAULT 0
- `cover_image`: VARCHAR(255)
- `rating`: DECIMAL(3,2) DEFAULT 0.00

### 5. `orders` & `order_items`
Encapsulates student purchasing manifests and transactional line items.
- `orders.id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `orders.user_id`: INT UNSIGNED NOT NULL (FK -> `users.id`)
- `orders.order_status`: ENUM('Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled')
- `orders.payment_method`: ENUM('UPI', 'CARD', 'COD')
- `order_items.order_id`: INT UNSIGNED NOT NULL (FK -> `orders.id` ON DELETE CASCADE)
- `order_items.book_id`: INT UNSIGNED NOT NULL (FK -> `books.id`)
- `order_items.quantity`: INT NOT NULL
- `order_items.price`: DECIMAL(10,2) NOT NULL (Historic price snapshot)

### 6. `reviews`
Verified purchaser reviews with rating histograms (1 to 5 stars).
- `id`: INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `book_id`: INT UNSIGNED NOT NULL (FK -> `books.id` ON DELETE CASCADE)
- `user_id`: INT UNSIGNED NOT NULL (FK -> `users.id` ON DELETE CASCADE)
- `rating`: TINYINT UNSIGNED NOT NULL (1 to 5)
- `comment`: TEXT
- `verified_purchase`: BOOLEAN DEFAULT TRUE

### 7. `cart` & `wishlists`
Cross-device synchronized shopping cart and student wishlist tracking.
- Composite primary keys `(user_id, book_id)` ensuring atomic item uniqueness.

### 8. `addresses` & `coupons`
Student hostel addresses and promotion code validation thresholds.
