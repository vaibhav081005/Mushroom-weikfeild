<?php
// Fix Missing Database Columns
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Database Column Fix</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; }</style>";

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'weikfield_mushroom';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("<p class='error'>Connection failed: " . $conn->connect_error . "</p>");
    }
    
    echo "<p class='success'>✓ Connected to database</p>";
    
    // Check if short_description column exists
    $result = $conn->query("SHOW COLUMNS FROM products LIKE 'short_description'");
    
    if ($result->num_rows == 0) {
        echo "<p>Adding 'short_description' column...</p>";
        $sql = "ALTER TABLE products ADD COLUMN short_description TEXT AFTER description";
        if ($conn->query($sql)) {
            echo "<p class='success'>✓ Added short_description column</p>";
        } else {
            echo "<p class='error'>Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p class='success'>✓ short_description column already exists</p>";
    }
    
    // Now add the products
    echo "<hr><h2>Adding Products...</h2>";
    
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
    
    $products_added = 0;
    
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
            echo "<p class='success'>✓ Added: " . htmlspecialchars($product['title']) . " (₹" . $product['discount_price'] . ")</p>";
        } else {
            echo "<p class='error'>Error adding product: " . $stmt->error . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2 class='success'>✅ Successfully added $products_added products!</h2>";
    
    // Show products
    echo "<h3>Products in Database:</h3>";
    $result = $conn->query("SELECT id, title, price, discount_price FROM products");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Price</th><th>Discount</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>₹" . $row['price'] . "</td>";
        echo "<td class='success'>₹" . $row['discount_price'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🎉 All Done! Now visit:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php' target='_blank' style='font-size: 1.2rem; color: #2e7d32; font-weight: bold;'>→ Homepage (See Products)</a></li>";
    echo "<li><a href='products.php' target='_blank'>→ Products Page</a></li>";
    echo "<li><a href='admin/products.php' target='_blank'>→ Admin Products</a></li>";
    echo "</ul>";
    
    echo "<p><strong>Delete this file after use!</strong></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>
