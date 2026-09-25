# 📡 BookNest - RESTful API Specification & Developer Guide
**Version:** 1.0.0 (Production Release)  
**Base URL:** `http://localhost/BookNest/api/`  
**Data Format:** JSON (`Content-Type: application/json; charset=utf-8`)  
**Security:** Cryptographic CSRF Verification, Session-Bound RBAC, Prepared Statements

---

## 1. Authentication Endpoints

### `POST /api/auth/login.php`
Authenticates existing student or administrator users and provisions secure session cookies.
- **Request Body:**
  ```json
  {
    "email": "user@booknest.com",
    "password": "user123",
    "csrf_token": "a4f89d..."
  }
  ```
- **Responses:**
  - `200 OK`: `{"success": true, "user": {"id": 2, "name": "Aditya Chauhan", "role": "user"}}`
  - `401 Unauthorized`: `{"success": false, "error": "Invalid email or password."}`

### `POST /api/auth/register.php`
Registers a new campus student account with BCrypt password hashing.
- **Request Body:**
  ```json
  {
    "name": "Jane Doe",
    "email": "jane@campus.edu",
    "password": "secure_password",
    "phone": "+91 98765 43210",
    "csrf_token": "..."
  }
  ```

---

## 2. Storefront, Search & Scanner Endpoints

### `GET /api/search-suggest.php?q={query}`
Debounced live autocomplete lookup returning matched titles, authors, prices, and cover thumbnails.
- **Query Parameters:** `q` (string, min 2 characters)
- **Response Format:**
  ```json
  {
    "success": true,
    "count": 3,
    "results": [
      {
        "id": 1,
        "title": "Clean Code",
        "author": "Robert C. Martin",
        "category": "Computer Science",
        "price": 699,
        "rating": 4.8
      }
    ]
  }
  ```

### `GET /api/scan-lookup.php?isbn={isbn}&q={term}`
Camera Barcode Detector endpoint resolving EAN-13 ISBNs and fuzzy campus textbook queries.
- **Query Parameters:** `isbn` (optional), `q` (optional title/keyword)
- **Response Format:**
  ```json
  {
    "success": true,
    "match_type": "exact_isbn",
    "book": {
      "id": 1,
      "title": "Clean Code",
      "isbn": "9780132350884",
      "stock": 18,
      "final_price": 594
    }
  }
  ```

---

## 3. Cart, Coupons & Checkout Endpoints

### `POST /api/cart-action.php`
Synchronizes cart additions, removals, and quantity modifications with database persistence.
- **Payload:** `action=add|update|remove`, `book_id={id}`, `quantity={int}`, `csrf_token={token}`

### `POST /api/coupon-apply.php`
Validates promotion vouchers against minimum cart order values:
- `WELCOME10`: 10% discount on first order
- `BOOKNEST50`: ₹50 flat discount on orders over ₹499
- `READMORE`: 15% discount on academic textbooks

### `POST /api/order-place.php`
Atomic transaction placing student orders, decrementing warehouse stock, and clearing session cart.
- **Headers:** `X-CSRF-Token: ...`
- **Payload:** `address_id={int}`, `payment_method=UPI|CARD|COD`

---

## 4. Administrator Suite Endpoints

### `POST /api/admin-book-save.php` (Admin Only)
Creates or updates catalog records with thumbnail image path and category mappings.

### `POST /api/admin-order-status.php` (Admin Only)
Dispatches state machine transitions (`Confirmed` → `Processing` → `Shipped` → `Delivered`).

### `GET /api/security-audit.php`
Executes automated penetration audit suite testing SQLi, XSS, CSRF, and session integrity.
