# 🕉 Gauri Collections — Antique Indian God Statues E-Commerce

A full-stack PHP e-commerce website for **Gauri Collections**, a premium brand selling exquisite antique statues of Indian Gods and Goddesses. Features an attractive, professional design with dark/light theme toggle.

![Homepage Screenshot](https://github.com/user-attachments/assets/18a90599-54c8-432c-b205-f2c390b0c777)

## ✨ Features

### Customer-Facing Website
- **Home Page** — Hero banner, featured products, category browsing, testimonials, newsletter signup
- **Shop** — Product grid with sidebar (category filter, price filter), sorting, pagination, search
- **Product Detail** — Full details (material, origin, era, condition), image gallery, add-to-cart, related products
- **Shopping Cart** — Add/remove items, quantity controls, coupon codes, order summary
- **Checkout** — Shipping form, order summary, place order (COD)
- **User Accounts** — Registration, login, profile management, order history
- **Wishlist** — Save favorite items (logged-in users)
- **About Us** — Company story, mission, values, statistics
- **Contact** — Contact form, company info

### Admin Panel (`/admin/`)
- **Dashboard** — Revenue stats, order counts, recent orders, unread messages
- **Products** — Full CRUD, image upload, search, category filter
- **Categories** — Add/edit/delete categories with sorting
- **Orders** — View all orders, update status, payment status
- **Users** — Manage customers, toggle active/inactive
- **Reviews** — Approve/reject/delete product reviews
- **Messages** — View contact form submissions, mark as read
- **Coupons** — Create percentage/fixed discount coupons
- **Settings** — Site name, contact info, shipping, tax, footer text

### Design & UX
- 🌗 **Dark/Light Theme Toggle** with localStorage persistence
- 📱 **Fully Responsive** — Mobile, tablet, desktop
- ✨ **Smooth Animations** — Scroll reveal, hover effects, transitions
- 🎨 **Premium Aesthetic** — Gold & maroon color scheme, serif headings, luxury feel
- 🔔 **Toast Notifications** — Real-time feedback for cart/wishlist actions

### Security
- CSRF token protection on all forms
- Prepared statements (PDO) for all database queries
- Password hashing with `password_hash()` / `password_verify()`
- XSS prevention with `htmlspecialchars()` output encoding
- File upload validation (type + size)
- Security headers via `.htaccess`

## 🚀 Setup Instructions

### Prerequisites
- PHP 8.0+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled (or any PHP-capable server)

### Installation

1. **Clone the repository** into your web server directory:
   ```bash
   git clone https://github.com/needproject-ceo/trial_project.git
   cd trial_project
   ```

2. **Create the database** — Import the SQL schema:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   This creates the `gauri_collections` database with all tables, sample products, categories, and a default admin user.

3. **Configure the database connection** — Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gauri_collections');
   define('DB_USER', 'root');       // your MySQL username
   define('DB_PASS', '');           // your MySQL password
   define('SITE_URL', 'http://localhost/trial_project'); // your site URL
   ```

4. **Set folder permissions** for uploads:
   ```bash
   chmod 755 uploads/products/
   ```

5. **Open the site** in your browser:
   - Frontend: `http://localhost/trial_project/`
   - Admin Panel: `http://localhost/trial_project/admin/`

### Default Admin Login
- **Email:** `admin@gauricollections.com`
- **Password:** `Admin@123`

> ⚠️ **Important:** Change the admin password after first login.

## 📁 Project Structure

```
trial_project/
├── admin/                  # Admin panel pages
│   ├── index.php          # Dashboard
│   ├── header.php         # Admin layout header
│   ├── footer.php         # Admin layout footer
│   ├── products.php       # Product listing
│   ├── product_form.php   # Add/Edit product
│   ├── categories.php     # Category management
│   ├── orders.php         # Order listing
│   ├── order_detail.php   # Single order view
│   ├── users.php          # User management
│   ├── reviews.php        # Review moderation
│   ├── messages.php       # Contact messages
│   ├── coupons.php        # Coupon management
│   └── settings.php       # Site settings
├── assets/
│   ├── css/style.css      # Main stylesheet (5400+ lines)
│   ├── js/main.js         # Main JavaScript (530+ lines)
│   └── images/            # Static images
├── config/
│   └── database.php       # DB connection & helpers
├── database/
│   └── schema.sql         # Full database schema + seed data
├── includes/
│   ├── header.php         # Public site header
│   └── footer.php         # Public site footer
├── pages/                 # Public-facing pages
│   ├── products.php       # Shop/catalog
│   ├── product_detail.php # Single product
│   ├── cart.php           # Shopping cart
│   ├── cart_actions.php   # Cart AJAX handler
│   ├── checkout.php       # Checkout flow
│   ├── order_confirmation.php
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   ├── logout.php         # Logout handler
│   ├── account.php        # User account/profile
│   ├── wishlist.php       # Wishlist page
│   ├── wishlist_actions.php # Wishlist AJAX handler
│   ├── about.php          # About us
│   ├── contact.php        # Contact page
│   └── newsletter.php     # Newsletter AJAX handler
├── uploads/products/      # Product image uploads
├── .htaccess             # URL rewriting & security
├── index.php             # Home page
└── README.md
```

## 🛠 Tech Stack

| Layer      | Technology                                    |
|------------|-----------------------------------------------|
| Backend    | PHP 8.x (vanilla, no framework)               |
| Database   | MySQL / MariaDB                               |
| Frontend   | HTML5, CSS3, Vanilla JavaScript (ES6+)        |
| Fonts      | Google Fonts (Cinzel, Playfair Display, Poppins) |
| Icons      | Font Awesome 6                                |
| Security   | PDO prepared statements, CSRF tokens, bcrypt  |

## 📝 License

This project is for educational and commercial use. All product descriptions and content are fictional/sample data.
