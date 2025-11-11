<?php
// System Diagnostic Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 System Diagnostic Check</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #2e7d32; color: white; }
</style>";

$issues = [];
$warnings = [];

// Check 1: PHP Version
echo "<div class='box'>";
echo "<h2>1. PHP Configuration</h2>";
$php_version = phpversion();
echo "<p>PHP Version: <strong>$php_version</strong> ";
if (version_compare($php_version, '7.4', '>=')) {
    echo "<span class='success'>✓ OK</span></p>";
} else {
    echo "<span class='error'>✗ Too old (need 7.4+)</span></p>";
    $issues[] = "PHP version too old";
}
echo "</div>";

// Check 2: Database Connection
echo "<div class='box'>";
echo "<h2>2. Database Connection</h2>";
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'weikfield_mushroom';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        echo "<p class='error'>✗ Connection Failed: " . $conn->connect_error . "</p>";
        $issues[] = "Database connection failed";
    } else {
        echo "<p class='success'>✓ Connected to database successfully!</p>";
        
        // Check tables
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        echo "<p>Tables found: <strong>" . count($tables) . "</strong></p>";
        
        $required_tables = ['products', 'categories', 'users', 'admins', 'orders', 'cart', 'coupons', 'settings'];
        $missing_tables = array_diff($required_tables, $tables);
        
        if (empty($missing_tables)) {
            echo "<p class='success'>✓ All required tables exist</p>";
        } else {
            echo "<p class='error'>✗ Missing tables: " . implode(', ', $missing_tables) . "</p>";
            $issues[] = "Missing database tables";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database Error: " . $e->getMessage() . "</p>";
    $issues[] = "Database error: " . $e->getMessage();
}
echo "</div>";

// Check 3: Products
if (isset($conn) && !$conn->connect_error) {
    echo "<div class='box'>";
    echo "<h2>3. Products Check</h2>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $row = $result->fetch_assoc();
    $product_count = $row['count'];
    
    echo "<p>Total Products: <strong>$product_count</strong> ";
    if ($product_count > 0) {
        echo "<span class='success'>✓</span></p>";
    } else {
        echo "<span class='error'>✗ No products found!</span></p>";
        $issues[] = "No products in database";
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
    $row = $result->fetch_assoc();
    echo "<p>Active Products: <strong>" . $row['count'] . "</strong></p>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE is_featured = 1");
    $row = $result->fetch_assoc();
    echo "<p>Featured Products: <strong>" . $row['count'] . "</strong></p>";
    
    if ($product_count > 0) {
        echo "<h4>Product List:</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Price</th><th>Active</th><th>Featured</th></tr>";
        
        $result = $conn->query("SELECT id, title, price, discount_price, is_active, is_featured FROM products LIMIT 10");
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>₹" . $row['discount_price'] ?: $row['price'] . "</td>";
            echo "<td>" . ($row['is_active'] ? '✓' : '✗') . "</td>";
            echo "<td>" . ($row['is_featured'] ? '⭐' : '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Check 4: Categories
    echo "<div class='box'>";
    echo "<h2>4. Categories Check</h2>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $row = $result->fetch_assoc();
    $category_count = $row['count'];
    
    echo "<p>Total Categories: <strong>$category_count</strong> ";
    if ($category_count > 0) {
        echo "<span class='success'>✓</span></p>";
    } else {
        echo "<span class='warning'>⚠ No categories</span></p>";
        $warnings[] = "No categories found";
    }
    
    if ($category_count > 0) {
        echo "<ul>";
        $result = $conn->query("SELECT id, name, is_active FROM categories");
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['name']) . " " . ($row['is_active'] ? '✓' : '✗') . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    // Check 5: Admin User
    echo "<div class='box'>";
    echo "<h2>5. Admin User Check</h2>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM admins");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<p class='success'>✓ Admin user exists</p>";
        $result = $conn->query("SELECT email, is_active FROM admins LIMIT 1");
        $admin = $result->fetch_assoc();
        echo "<p>Email: <strong>" . $admin['email'] . "</strong></p>";
        echo "<p>Status: " . ($admin['is_active'] ? '<span class="success">Active</span>' : '<span class="error">Inactive</span>') . "</p>";
    } else {
        echo "<p class='error'>✗ No admin user found!</p>";
        $issues[] = "No admin user";
    }
    echo "</div>";
    
    // Check 6: Settings
    echo "<div class='box'>";
    echo "<h2>6. Settings Check</h2>";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM settings");
    $row = $result->fetch_assoc();
    
    echo "<p>Settings entries: <strong>" . $row['count'] . "</strong></p>";
    
    if ($row['count'] > 0) {
        $result = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'currency', 'tax_percentage')");
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li><strong>" . $row['setting_key'] . ":</strong> " . $row['setting_value'] . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
}

// Check 7: File Permissions
echo "<div class='box'>";
echo "<h2>7. File & Directory Check</h2>";

$dirs_to_check = [
    'uploads' => __DIR__ . '/uploads',
    'uploads/products' => __DIR__ . '/uploads/products',
    'uploads/files' => __DIR__ . '/uploads/files',
    'uploads/categories' => __DIR__ . '/uploads/categories'
];

foreach ($dirs_to_check as $name => $path) {
    if (file_exists($path)) {
        if (is_writable($path)) {
            echo "<p>$name: <span class='success'>✓ Exists & Writable</span></p>";
        } else {
            echo "<p>$name: <span class='warning'>⚠ Exists but not writable</span></p>";
            $warnings[] = "$name directory not writable";
        }
    } else {
        echo "<p>$name: <span class='error'>✗ Does not exist</span></p>";
        $warnings[] = "$name directory missing";
    }
}
echo "</div>";

// Check 8: Important Files
echo "<div class='box'>";
echo "<h2>8. Important Files Check</h2>";

$files_to_check = [
    'config/config.php',
    'config/database.php',
    'includes/header.php',
    'includes/footer.php',
    'index.php',
    'products.php',
    'admin/index.php'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p>$file: <span class='success'>✓</span></p>";
    } else {
        echo "<p>$file: <span class='error'>✗ Missing</span></p>";
        $issues[] = "$file is missing";
    }
}
echo "</div>";

// Summary
echo "<div class='box' style='background: " . (empty($issues) ? '#e8f5e9' : '#ffebee') . ";'>";
echo "<h2>📊 Summary</h2>";

if (empty($issues) && empty($warnings)) {
    echo "<h3 class='success'>✅ Everything looks good!</h3>";
    echo "<p>Your system is properly configured.</p>";
} else {
    if (!empty($issues)) {
        echo "<h3 class='error'>❌ Critical Issues Found: " . count($issues) . "</h3>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li class='error'>$issue</li>";
        }
        echo "</ul>";
    }
    
    if (!empty($warnings)) {
        echo "<h3 class='warning'>⚠ Warnings: " . count($warnings) . "</h3>";
        echo "<ul>";
        foreach ($warnings as $warning) {
            echo "<li class='warning'>$warning</li>";
        }
        echo "</ul>";
    }
}

echo "</div>";

// Auto-fix for missing products
if (isset($_GET['autofix']) && $_GET['autofix'] === 'products' && isset($conn)) {
    echo "<div class='box' style='background: #fff3e0;'>";
    echo "<h2>🔧 Auto-Fixing: Adding Products...</h2>";
    
    $products_added = 0;
    
    // Add 5 products
    $products = [
        [
            'title' => 'Weikfield Button Mushrooms - Fresh Pack (200g)',
            'slug' => 'weikfield-button-mushrooms-fresh-200g',
            'description' => 'Premium quality fresh button mushrooms, carefully handpicked and packed to ensure maximum freshness. Our button mushrooms are grown in controlled environment farms using the latest agricultural technology. Perfect for salads, pizzas, pasta, and various Indian and continental dishes. Rich in protein, vitamins, and minerals, these mushrooms are a healthy addition to your daily diet.',
            'short_description' => 'Fresh, premium quality button mushrooms - 200g pack',
            'price' => 89.00,
            'discount_price' => 79.00,
            'category_id' => 1,
            'features' => "100% Fresh and Natural\nNo Preservatives or Chemicals\nRich in Protein and Vitamins\nCarefully Handpicked\nPerfect for All Cuisines\nHygienically Packed\nFarm Fresh Quality\nLow in Calories",
            'views' => 245,
            'downloads' => 89
        ],
        [
            'title' => 'Weikfield Oyster Mushrooms - Premium Quality (250g)',
            'slug' => 'weikfield-oyster-mushrooms-premium-250g',
            'description' => 'Experience the delicate flavor and unique texture of our premium oyster mushrooms. Grown in state-of-the-art facilities, these mushrooms are known for their mild, slightly sweet taste and velvety texture. Oyster mushrooms are packed with nutrients including antioxidants, vitamins B and D, and essential minerals. Ideal for stir-fries, soups, and grilled preparations.',
            'short_description' => 'Premium oyster mushrooms with delicate flavor - 250g',
            'price' => 129.00,
            'discount_price' => 115.00,
            'category_id' => 1,
            'features' => "Premium Quality Oyster Mushrooms\nRich in Antioxidants\nHigh Vitamin B & D Content\nDelicate Sweet Flavor\nPerfect for Stir-fries\nVelvety Smooth Texture\nOrganically Grown\nFreshness Guaranteed",
            'views' => 198,
            'downloads' => 67
        ],
        [
            'title' => 'Weikfield Dried Shiitake Mushrooms - Premium Grade (100g)',
            'slug' => 'weikfield-dried-shiitake-mushrooms-100g',
            'description' => 'Premium grade dried shiitake mushrooms, carefully selected and processed to retain maximum flavor and nutritional value. Known for their rich, umami taste and meaty texture, shiitake mushrooms are a staple in Asian cuisine. These dried mushrooms have a longer shelf life and can be easily rehydrated for use in soups, broths, stir-fries, and medicinal preparations.',
            'short_description' => 'Premium dried shiitake mushrooms - 100g pack',
            'price' => 299.00,
            'discount_price' => 269.00,
            'category_id' => 2,
            'features' => "Premium Grade Shiitake\nRich Umami Flavor\nLong Shelf Life\nEasy to Rehydrate\nImmune Boosting Properties\nHigh in Vitamin D\nMeaty Texture\nAuthentic Asian Flavor",
            'views' => 312,
            'downloads' => 124
        ],
        [
            'title' => 'Weikfield DIY Mushroom Growing Kit - Oyster Variety',
            'slug' => 'weikfield-diy-mushroom-growing-kit-oyster',
            'description' => 'Grow your own fresh mushrooms at home with our easy-to-use DIY mushroom growing kit! This complete kit includes everything you need to cultivate fresh oyster mushrooms in the comfort of your home. Perfect for beginners, families, and mushroom enthusiasts. Watch your mushrooms grow in just 7-10 days!',
            'short_description' => 'Complete DIY kit to grow fresh oyster mushrooms at home',
            'price' => 599.00,
            'discount_price' => 549.00,
            'category_id' => 4,
            'features' => "Complete Growing Kit\nReady to Use Substrate\nDetailed Instructions Included\nHarvest in 7-10 Days\nMultiple Flushes Possible\nEducational & Fun\nPerfect for Beginners\nOrganic & Chemical-Free",
            'views' => 456,
            'downloads' => 178
        ],
        [
            'title' => 'Weikfield Mushroom Extract Powder - Immunity Booster (50g)',
            'slug' => 'weikfield-mushroom-extract-powder-immunity-50g',
            'description' => 'Premium quality mushroom extract powder made from a blend of medicinal mushrooms including Reishi, Cordyceps, and Lion\'s Mane. This concentrated powder is rich in beta-glucans, antioxidants, and bioactive compounds known for their immune-boosting and health-promoting properties. 100% natural with no artificial additives.',
            'short_description' => 'Premium mushroom extract powder for immunity - 50g',
            'price' => 449.00,
            'discount_price' => 399.00,
            'category_id' => 3,
            'features' => "Blend of Medicinal Mushrooms\nRich in Beta-Glucans\nImmunity Booster\nSupports Cognitive Function\n100% Natural Extract\nNo Artificial Additives\nEasy to Use\nConcentrated Formula",
            'views' => 523,
            'downloads' => 201
        ]
    ];
    
    foreach ($products as $product) {
        $stmt = $conn->prepare("INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, ?, ?)");
        $stmt->bind_param("ssssddisii", 
            $product['title'],
            $product['slug'],
            $product['description'],
            $product['short_description'],
            $product['price'],
            $product['discount_price'],
            $product['category_id'],
            $product['features'],
            $product['views'],
            $product['downloads']
        );
        
        if ($stmt->execute()) {
            $products_added++;
            echo "<p class='success'>✓ Added: " . htmlspecialchars($product['title']) . "</p>";
        }
    }
    
    echo "<h3 class='success'>✅ Successfully added $products_added products!</h3>";
    echo "<p><a href='check-system.php' class='btn'>← Back to Diagnostic</a> | <a href='index.php' target='_blank'>View Homepage →</a></p>";
    echo "</div>";
}

// Quick Actions
echo "<div class='box'>";
echo "<h2>🚀 Quick Actions</h2>";

if (!empty($issues) && in_array("No products in database", $issues)) {
    echo "<p><a href='?autofix=products' style='display: inline-block; background: #2e7d32; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 1.1rem;'>🔧 AUTO-FIX: Add Products Now</a></p>";
    echo "<hr>";
}

echo "<ul>";
echo "<li><a href='import-products-now.php' style='color: #2e7d32; font-weight: bold;'>→ Import Products (Alternative Method)</a></li>";
echo "<li><a href='index.php' target='_blank'>→ View Homepage</a></li>";
echo "<li><a href='products.php' target='_blank'>→ View Products Page</a></li>";
echo "<li><a href='admin/login.php' target='_blank'>→ Admin Login</a></li>";
echo "</ul>";
echo "</div>";

if (isset($conn)) {
    $conn->close();
}
?>
