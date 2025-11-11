# Quick Installation Guide

## Prerequisites
- XAMPP (or WAMP/LAMP/MAMP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

## Step-by-Step Installation

### 1. Setup Files
1. Extract the project folder to your XAMPP htdocs directory:
   ```
   C:/xampp/htdocs/mushroom
   ```

### 2. Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services

### 3. Create Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on "New" to create a new database
3. Database name: `weikfield_mushroom`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

### 4. Import Database Schema
1. Select the `weikfield_mushroom` database
2. Click on "Import" tab
3. Click "Choose File" and select: `mushroom/database/schema.sql`
4. Click "Go" at the bottom
5. Wait for success message

### 5. Configure Application (Optional)
If you're using different database credentials:
1. Open `mushroom/config/database.php`
2. Update these lines if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'weikfield_mushroom');
   ```

### 6. Access the Website
Open your browser and visit:
- **User Website**: `http://localhost/mushroom`
- **Admin Panel**: `http://localhost/mushroom/admin`

### 7. Login Credentials

#### Admin Login
- URL: `http://localhost/mushroom/admin`
- Email: `admin@weikfield.com`
- Password: `password`

#### User Account
- Create a new account by clicking "Sign Up" on the website

## Post-Installation Steps

### 1. Change Admin Password
1. Login to admin panel
2. Go to Settings or Profile
3. Change the default password immediately

### 2. Configure Payment Gateway (Optional)
1. Login to admin panel
2. Go to Settings
3. Enter your payment gateway credentials:
   - Razorpay: Key ID and Secret
   - Stripe: Public Key and Secret Key
   - PayPal: Client ID and Secret

### 3. Add Products
1. Login to admin panel
2. Go to Products → Add New
3. Fill in product details
4. Upload product image
5. Upload product file (for digital downloads)
6. Save

### 4. Customize Settings
1. Go to Settings in admin panel
2. Update:
   - Site Name
   - Contact Email
   - Phone Number
   - Tax Percentage
   - Currency Settings

## Troubleshooting

### Database Connection Error
- Check if MySQL is running in XAMPP
- Verify database credentials in `config/database.php`
- Ensure database `weikfield_mushroom` exists

### Permission Errors
- Make sure `uploads` folder has write permissions
- On Windows, right-click folder → Properties → Security
- On Linux/Mac: `chmod -R 755 uploads/`

### Page Not Found (404)
- Check if `.htaccess` file exists in root folder
- Enable `mod_rewrite` in Apache configuration
- Verify SITE_URL in `config/config.php`

### Images Not Displaying
- Check if images are uploaded to correct folder
- Verify file permissions on `uploads` directory
- Check browser console for errors

### Payment Gateway Not Working
- Ensure you've entered correct API keys in Settings
- Check if payment gateway is in test/sandbox mode
- Verify your account is active with the payment provider

## Testing the Installation

### Test User Flow
1. Visit `http://localhost/mushroom`
2. Sign up for a new account
3. Browse products
4. Add product to cart
5. Proceed to checkout
6. Complete payment (use demo mode)
7. Check orders page
8. Download purchased product

### Test Admin Flow
1. Login to admin panel
2. Check dashboard statistics
3. Add a new product
4. View orders
5. Manage users
6. Create a coupon code
7. Update settings

## Default Sample Data

The database includes:
- 1 Admin account
- 4 Sample categories
- 5 Sample FAQs
- Default settings

You can modify or delete these as needed.

## Security Recommendations

### For Production Use
1. **Change all default passwords**
2. **Update database credentials**
3. **Enable HTTPS/SSL**
4. **Set strong passwords**
5. **Regular backups**
6. **Update PHP to latest version**
7. **Disable error display**:
   ```php
   // In config/config.php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

## File Structure Overview

```
mushroom/
├── admin/              # Admin panel
├── api/                # API endpoints
├── auth/               # Authentication pages
├── config/             # Configuration files
├── database/           # Database schema
├── includes/           # Common includes
├── uploads/            # User uploads
│   ├── products/       # Product images
│   ├── files/          # Product files
│   └── categories/     # Category images
├── index.php           # Homepage
├── products.php        # Product listing
├── cart.php            # Shopping cart
├── checkout.php        # Checkout
└── README.md           # Documentation
```

## Support

For issues or questions:
- Check the main README.md file
- Review the FAQ section
- Contact: info@weikfield.com

## Next Steps

1. Add your products
2. Customize the design (colors, logo)
3. Configure payment gateway
4. Test the complete purchase flow
5. Set up email notifications
6. Launch your store!

---

**Installation Complete! 🎉**

Your Weikfield Mushroom Products website is now ready to use.
