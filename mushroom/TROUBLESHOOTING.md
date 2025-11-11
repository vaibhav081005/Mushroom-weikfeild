# Troubleshooting Guide - Internal Server Error

## 🔧 Quick Fix Steps

### Step 1: Test Basic PHP
Visit: `http://localhost/mushroom/test.php`

This will tell you if:
- PHP is working
- Database connection is successful

### Step 2: Check Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Verify database `weikfield_mushroom` exists
3. Check if tables are created (should have 17 tables)
4. If database doesn't exist:
   - Create database: `weikfield_mushroom`
   - Import: `database/schema.sql`

### Step 3: Fix .htaccess Issues
The `.htaccess` file has been simplified. If still having issues:

**Option A: Rename .htaccess temporarily**
```bash
# In mushroom folder, rename:
.htaccess → .htaccess.bak
```
Then try accessing the site again.

**Option B: Delete .htaccess**
The site will work without it (just without clean URLs).

### Step 4: Check Apache Error Logs
Location: `C:/xampp/apache/logs/error.log`

Look for the most recent error messages.

### Step 5: Enable PHP Error Display
Edit `c:/xampp/htdocs/mushroom/config/config.php`

Line 42-43 should be:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🐛 Common Issues & Solutions

### Issue 1: Database Not Created
**Error:** Can't connect to database

**Solution:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `weikfield_mushroom`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Select the database
7. Click "Import" tab
8. Choose file: `C:/xampp/htdocs/mushroom/database/schema.sql`
9. Click "Go"

### Issue 2: Apache mod_rewrite Not Enabled
**Error:** Internal Server Error with .htaccess

**Solution:**
1. Open: `C:/xampp/apache/conf/httpd.conf`
2. Find line: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` to uncomment it
4. Save file
5. Restart Apache in XAMPP Control Panel

### Issue 3: .htaccess Syntax Error
**Error:** 500 Internal Server Error

**Solution:**
The .htaccess has been simplified. If still having issues, use this minimal version:

```apache
# Prevent directory listing
Options -Indexes
```

### Issue 4: PHP Version Compatibility
**Error:** Syntax errors or deprecated functions

**Solution:**
You're using PHP 8.2.12 which is compatible. No changes needed.

### Issue 5: File Permissions
**Error:** Can't create directories or upload files

**Solution:**
Make sure the `uploads` folder has write permissions:
- Right-click `uploads` folder
- Properties → Security
- Make sure your user has "Write" permission

### Issue 6: Session Errors
**Error:** Session-related warnings

**Solution:**
Check that `C:/xampp/tmp` folder exists and is writable.

## 📋 Step-by-Step Debugging

### Method 1: Check Each File Individually

1. **Test database connection:**
   ```
   http://localhost/mushroom/test.php
   ```

2. **Test config file:**
   Create `test-config.php`:
   ```php
   <?php
   require_once 'config/config.php';
   echo "Config loaded successfully!";
   ?>
   ```

3. **Test index page:**
   ```
   http://localhost/mushroom/index.php
   ```

### Method 2: Check Apache Error Log

1. Open: `C:/xampp/apache/logs/error.log`
2. Scroll to bottom (most recent errors)
3. Look for PHP errors or .htaccess errors
4. Copy the error message

### Method 3: Simplify .htaccess

Replace entire `.htaccess` content with:
```apache
Options -Indexes
```

If this works, the issue was in .htaccess rules.

## 🔍 What to Check

### ✅ Checklist
- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] Database `weikfield_mushroom` exists
- [ ] Database has 17 tables
- [ ] `config/database.php` has correct credentials
- [ ] `test.php` shows "Database connection SUCCESS"
- [ ] No syntax errors in PHP files
- [ ] `uploads` folder exists and is writable
- [ ] Apache error log shows no critical errors

## 🚀 Quick Recovery

If nothing works, try this clean start:

### Option 1: Fresh Database Import
```sql
-- In phpMyAdmin, run:
DROP DATABASE IF EXISTS weikfield_mushroom;
CREATE DATABASE weikfield_mushroom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE weikfield_mushroom;
-- Then import schema.sql
```

### Option 2: Disable .htaccess
```bash
# Rename the file:
.htaccess → .htaccess.disabled
```

### Option 3: Check PHP Info
Create `info.php`:
```php
<?php phpinfo(); ?>
```
Visit: `http://localhost/mushroom/info.php`

Check:
- PHP version (should be 8.2.12)
- mysqli extension (should be enabled)
- PDO extension (should be enabled)

## 📞 Still Having Issues?

### Check These Files:
1. `C:/xampp/apache/logs/error.log` - Apache errors
2. `C:/xampp/php/php.ini` - PHP configuration
3. `C:/xampp/apache/conf/httpd.conf` - Apache configuration

### Common Error Messages:

**"Call to undefined function mysqli_connect"**
- Solution: Enable mysqli extension in php.ini

**"Access denied for user 'root'@'localhost'"**
- Solution: Check database credentials in `config/database.php`

**"Table 'weikfield_mushroom.products' doesn't exist"**
- Solution: Import `database/schema.sql`

**"Cannot modify header information"**
- Solution: Check for output before `<?php` tags

## ✅ Verification Steps

After fixing, verify:

1. **Homepage loads:** `http://localhost/mushroom`
2. **Products page:** `http://localhost/mushroom/products.php`
3. **Admin login:** `http://localhost/mushroom/admin`
4. **Test database:** `http://localhost/mushroom/test.php`

## 🎯 Most Likely Solutions

Based on the error, try these in order:

1. **Simplify .htaccess** (most common cause)
2. **Check database exists** (second most common)
3. **Enable mod_rewrite** (if using .htaccess)
4. **Check Apache error log** (for specific error)

---

**Need more help?** 
- Check Apache error log: `C:/xampp/apache/logs/error.log`
- Run test.php: `http://localhost/mushroom/test.php`
- Check phpinfo: Create info.php with `<?php phpinfo(); ?>`
