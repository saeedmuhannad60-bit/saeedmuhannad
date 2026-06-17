# Tasty Bites — Online Food Ordering & Restaurant Management System (PHP)

A complete restaurant website with online food ordering and a full admin
management panel, built in plain **PHP 8** with **PDO + SQLite** — no MySQL
server or framework required. The database is created and seeded automatically
on first run.

## Features

### Customer site
- Responsive landing page with hero, feature highlights and featured dishes
- Full menu browsing with category filtering
- Session-based shopping cart (add, update quantities, remove, clear)
- Checkout with delivery details and order validation
- Automatic order totals (subtotal + delivery fee + tax)
- Order tracking by order code with a visual status progress bar

### Admin panel (`/admin`)
- Secure login with hashed passwords and session-based auth
- Dashboard with key stats (orders, active orders, items, revenue)
- Order management: view details, filter by status, update order status
- Menu item management: full create / edit / delete with availability & featured flags
- Category management: create / edit / delete (with safety checks)

### Engineering
- PDO prepared statements everywhere (SQL-injection safe)
- Output escaping helper (`e()`) on all user data (XSS safe)
- CSRF tokens on every state-changing form
- Clean separation: config, db, helpers, views

## Requirements
- PHP 8.0+ with the `pdo_sqlite` extension (bundled with most PHP builds)

## Running locally

```bash
php -S localhost:8000
```

Then open:
- Customer site: <http://localhost:8000/>
- Admin panel: <http://localhost:8000/admin/login.php>

### Default admin credentials
| Username | Password   |
|----------|------------|
| `admin`  | `admin123` |

> Change these after first login (or update the seed in `includes/db.php`).

## Deploying to Apache / Nginx
Point the document root at the project folder. Ensure the `data/` directory is
writable by the web server so the SQLite file can be created:

```bash
chmod -R 775 data
```

For production, place `data/` outside the web root or block direct access to
`*.sqlite` files.

## Project structure

```
.
├── index.php            # Home page
├── menu.php             # Menu + category filter
├── cart.php             # Shopping cart
├── checkout.php         # Place an order
├── track.php            # Track an order by code
├── includes/
│   ├── config.php       # Brand, currency, fees, constants
│   ├── db.php           # PDO connection, schema, seed data
│   ├── functions.php    # Helpers: cart, auth, CSRF, money, escaping
│   ├── header.php       # Public site header
│   └── footer.php       # Public site footer
├── admin/
│   ├── login.php        # Admin login
│   ├── logout.php
│   ├── dashboard.php    # Stats + recent orders
│   ├── orders.php       # Order list + detail + status updates
│   ├── menu.php         # Menu item CRUD
│   ├── categories.php   # Category CRUD
│   └── includes/        # Admin layout partials
├── assets/
│   ├── css/style.css
│   └── js/main.js
└── data/                # SQLite database (auto-created, git-ignored)
```

## Configuration
Edit `includes/config.php` to change the restaurant name, currency symbol,
delivery fee and tax rate.

## Resetting the database
Delete `data/restaurant.sqlite` and reload any page — it will be recreated
and reseeded with sample categories and menu items.
