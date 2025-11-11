# Weikfield Mushroom Product Selling Website

A complete digital product selling platform built with PHP and MySQL, featuring a responsive design for both mobile and desktop, with dark/light mode support.

## Features

### User Side (Frontend)
- **Authentication**
  - User signup/login with secure password hashing
  - Password reset functionality
  - User profile management
  
- **Product Browsing**
  - Responsive landing page with hero section
  - Product listing with filters (category, price, search)
  - Product detail pages with image gallery
  - Featured products section
  
- **Shopping & Checkout**
  - Shopping cart with quantity management
  - Secure checkout process
  - Multiple payment gateway support (Razorpay/Stripe/PayPal)
  - Coupon/discount code system
  - Order history and download management
  
- **Additional Features**
  - Testimonials section
  - FAQ page
  - Contact form
  - Responsive design (mobile-first)
  - Dark/Light mode toggle
  - Native app-like mobile experience

### Admin Side (Backend)
- **Dashboard**
  - Statistics overview (products, users, orders, revenue)
  - Recent orders list
  - Top selling products
  
- **Product Management**
  - Add/Edit/Delete products
  - Upload product images and files
  - Category management
  - Product status control
  
- **Order Management**
  - View all orders
  - Order details with items
  - Payment status tracking
  - Transaction management
  
- **User Management**
  - View all registered users
  - Block/Unblock users
  - User purchase history
  
- **Coupon Management**
  - Create discount codes (flat/percentage)
  - Set expiry dates and usage limits
  - Track coupon usage
  
- **Settings**
  - Payment gateway configuration
  - Tax settings (GST/VAT)
  - Site branding
  - Email notifications

## Tech Stack

- **Frontend**: PHP, MDBootstrap, Font Awesome, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Payment**: Razorpay/Stripe/PayPal integration ready

## Installation

### Prerequisites
- XAMPP (or any PHP development environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions

1. **Clone or Download the Project**
   ```
   Place the 'mushroom' folder in your XAMPP htdocs directory
   (C:/xampp/htdocs/mushroom)
   ```

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `weikfield_mushroom`
   - Import the SQL schema from `database/schema.sql`
   
   OR run the SQL file directly:
   ```sql
   mysql -u root -p < database/schema.sql
   ```

3. **Configure Database Connection**
   - Open `config/database.php`
   - Update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'weikfield_mushroom');
     ```

4. **Configure Site URL**
   - Open `config/config.php`
   - Update SITE_URL if needed:
     ```php
     define('SITE_URL', 'http://localhost/mushroom');
     ```

5. **Set Permissions**
   - Ensure the `uploads` folder is writable:
     ```
     chmod -R 755 uploads/
     ```

6. **Access the Application**
   - **User Site**: http://localhost/mushroom
   - **Admin Panel**: http://localhost/mushroom/admin

## Default Login Credentials

### Admin Login
- **Email**: admin@weikfield.com
- **Password**: password

### User Account
- Create a new account through the signup page

## Directory Structure

```
mushroom/
├── admin/                  # Admin panel files
│   ├── includes/          # Admin header/footer
│   ├── index.php          # Admin dashboard
│   ├── login.php          # Admin login
│   └── ...
├── api/                   # API endpoints
│   ├── cart-add.php       # Add to cart API
│   ├── payment-success.php # Payment callback
│   └── ...
├── auth/                  # Authentication pages
│   ├── login.php          # User login
│   ├── signup.php         # User registration
│   └── logout.php         # Logout
├── config/                # Configuration files
│   ├── config.php         # Main config
│   └── database.php       # Database config
├── database/              # Database files
│   └── schema.sql         # Database schema
├── includes/              # Common includes
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── uploads/               # Upload directories
│   ├── products/          # Product images
│   ├── files/             # Product files
│   └── ...
├── index.php              # Homepage
├── products.php           # Product listing
├── product-detail.php     # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout page
├── orders.php             # User orders
├── profile.php            # User profile
└── README.md              # This file
```

## Payment Gateway Configuration

### Razorpay Setup
1. Login to admin panel
2. Go to Settings
3. Enter your Razorpay Key ID and Secret
4. Save settings

### Stripe Setup
1. Login to admin panel
2. Go to Settings
3. Enter your Stripe Public Key and Secret Key
4. Save settings

### PayPal Setup
1. Login to admin panel
2. Go to Settings
3. Enter your PayPal Client ID and Secret
4. Save settings

## Features in Detail

### Responsive Design
- **Mobile View**: Native app-like experience with bottom navigation
- **Desktop View**: Professional website layout with top navigation
- **Tablet View**: Optimized for medium screens
- **Adaptive Components**: All elements auto-adjust to screen size

### Dark/Light Mode
- Toggle button in header
- Preference saved in localStorage
- Smooth transitions between themes
- All pages support both modes

### Security Features
- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection ready
- Secure file upload validation

### SEO Friendly
- Clean URLs
- Meta tags support
- Semantic HTML structure
- Fast loading times

## Customization

### Changing Colors
Edit the CSS variables in `includes/header.php`:
```css
:root {
    --primary-color: #2e7d32;
    --secondary-color: #66bb6a;
    --accent-color: #ff6f00;
}
```

### Adding New Payment Gateway
1. Add gateway settings in admin settings page
2. Create payment handler in `payment.php`
3. Add success callback in `api/payment-success.php`

### Email Configuration
Configure SMTP settings in admin panel under Settings to enable email notifications.

## Support

For issues or questions:
- Check the FAQ section
- Contact: info@weikfield.com
- Phone: +91 1234567890

## License

This project is proprietary software for Weikfield.

## Version

Version 1.0.0 - Initial Release

---

**Built with ❤️ for Weikfield Mushroom Products**
