<?php
// Quick Product Import Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Import Weikfield Products</h1>";
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
    
    echo "<p class='success'>✓ Connected to database!</p>";
    
    // Check if products already exist
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $row = $result->fetch_assoc();
    $existing_count = $row['count'];
    
    echo "<p>Current products in database: <strong>$existing_count</strong></p>";
    
    if ($existing_count > 0) {
        echo "<p class='error'>⚠ Products already exist. Delete them first?</p>";
        echo "<form method='post'>";
        echo "<button type='submit' name='delete' value='yes' style='background: red; color: white; padding: 10px 20px; border: none; cursor: pointer;'>Delete All Products</button>";
        echo "</form>";
        
        if (isset($_POST['delete']) && $_POST['delete'] === 'yes') {
            $conn->query("DELETE FROM products");
            echo "<p class='success'>✓ Deleted all existing products!</p>";
            $existing_count = 0;
        }
    }
    
    if ($existing_count == 0) {
        echo "<h3>Adding 5 Weikfield Products...</h3>";
        
        // Product 1: Button Mushrooms
        $sql = "INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
        ('Weikfield Button Mushrooms - Fresh Pack (200g)', 
        'weikfield-button-mushrooms-fresh-200g',
        'Premium quality fresh button mushrooms, carefully handpicked and packed to ensure maximum freshness. Our button mushrooms are grown in controlled environment farms using the latest agricultural technology. Perfect for salads, pizzas, pasta, and various Indian and continental dishes. Rich in protein, vitamins, and minerals, these mushrooms are a healthy addition to your daily diet.',
        'Fresh, premium quality button mushrooms - 200g pack',
        89.00,
        79.00,
        1,
        '100% Fresh and Natural\nNo Preservatives or Chemicals\nRich in Protein and Vitamins\nCarefully Handpicked\nPerfect for All Cuisines\nHygienically Packed\nFarm Fresh Quality\nLow in Calories',
        1,
        1,
        245,
        89)";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✓ Added: Button Mushrooms (₹79)</p>";
        } else {
            echo "<p class='error'>Error: " . $conn->error . "</p>";
        }
        
        // Product 2: Oyster Mushrooms
        $sql = "INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
        ('Weikfield Oyster Mushrooms - Premium Quality (250g)',
        'weikfield-oyster-mushrooms-premium-250g',
        'Experience the delicate flavor and unique texture of our premium oyster mushrooms. Grown in state-of-the-art facilities, these mushrooms are known for their mild, slightly sweet taste and velvety texture. Oyster mushrooms are packed with nutrients including antioxidants, vitamins B and D, and essential minerals. Ideal for stir-fries, soups, and grilled preparations.',
        'Premium oyster mushrooms with delicate flavor - 250g',
        129.00,
        115.00,
        1,
        'Premium Quality Oyster Mushrooms\nRich in Antioxidants\nHigh Vitamin B & D Content\nDelicate Sweet Flavor\nPerfect for Stir-fries\nVelvety Smooth Texture\nOrganically Grown\nFreshness Guaranteed',
        1,
        1,
        198,
        67)";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✓ Added: Oyster Mushrooms (₹115)</p>";
        }
        
        // Product 3: Dried Shiitake
        $sql = "INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
        ('Weikfield Dried Shiitake Mushrooms - Premium Grade (100g)',
        'weikfield-dried-shiitake-mushrooms-100g',
        'Premium grade dried shiitake mushrooms, carefully selected and processed to retain maximum flavor and nutritional value. Known for their rich, umami taste and meaty texture, shiitake mushrooms are a staple in Asian cuisine. These dried mushrooms have a longer shelf life and can be easily rehydrated for use in soups, broths, stir-fries, and medicinal preparations. Rich in vitamins, minerals, and immune-boosting compounds.',
        'Premium dried shiitake mushrooms - 100g pack',
        299.00,
        269.00,
        2,
        'Premium Grade Shiitake\nRich Umami Flavor\nLong Shelf Life\nEasy to Rehydrate\nImmune Boosting Properties\nHigh in Vitamin D\nMeaty Texture\nAuthentic Asian Flavor',
        1,
        1,
        312,
        124)";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✓ Added: Dried Shiitake (₹269)</p>";
        }
        
        // Product 4: Growing Kit
        $sql = "INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
        ('Weikfield DIY Mushroom Growing Kit - Oyster Variety',
        'weikfield-diy-mushroom-growing-kit-oyster',
        'Grow your own fresh mushrooms at home with our easy-to-use DIY mushroom growing kit! This complete kit includes everything you need to cultivate fresh oyster mushrooms in the comfort of your home. Perfect for beginners, families, and mushroom enthusiasts. The kit comes with pre-colonized substrate, detailed instructions, and support materials. Watch your mushrooms grow in just 7-10 days! A fun, educational, and rewarding experience that provides fresh, organic mushrooms for your kitchen.',
        'Complete DIY kit to grow fresh oyster mushrooms at home',
        599.00,
        549.00,
        4,
        'Complete Growing Kit\nReady to Use Substrate\nDetailed Instructions Included\nHarvest in 7-10 Days\nMultiple Flushes Possible\nEducational & Fun\nPerfect for Beginners\nOrganic & Chemical-Free',
        1,
        1,
        456,
        178)";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✓ Added: Growing Kit (₹549)</p>";
        }
        
        // Product 5: Extract Powder
        $sql = "INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, features, is_featured, is_active, views, downloads) VALUES
        ('Weikfield Mushroom Extract Powder - Immunity Booster (50g)',
        'weikfield-mushroom-extract-powder-immunity-50g',
        'Premium quality mushroom extract powder made from a blend of medicinal mushrooms including Reishi, Cordyceps, and Lion''s Mane. This concentrated powder is rich in beta-glucans, antioxidants, and bioactive compounds known for their immune-boosting and health-promoting properties. Easy to incorporate into your daily routine - add to smoothies, coffee, tea, or warm water. Supports immunity, cognitive function, energy levels, and overall wellness. 100% natural with no artificial additives.',
        'Premium mushroom extract powder for immunity - 50g',
        449.00,
        399.00,
        3,
        'Blend of Medicinal Mushrooms\nRich in Beta-Glucans\nImmunity Booster\nSupports Cognitive Function\n100% Natural Extract\nNo Artificial Additives\nEasy to Use\nConcentrated Formula',
        1,
        1,
        523,
        201)";
        
        if ($conn->query($sql)) {
            echo "<p class='success'>✓ Added: Extract Powder (₹399)</p>";
        }
        
        echo "<hr>";
        echo "<h2 class='success'>✅ All 5 Products Added Successfully!</h2>";
    }
    
    // Verify products
    $result = $conn->query("SELECT id, title, price, discount_price, is_active, is_featured FROM products");
    
    echo "<h3>Products in Database:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Price</th><th>Discount</th><th>Active</th><th>Featured</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>₹" . $row['price'] . "</td>";
        echo "<td>₹" . $row['discount_price'] . "</td>";
        echo "<td>" . ($row['is_active'] ? '✓' : '✗') . "</td>";
        echo "<td>" . ($row['is_featured'] ? '⭐' : '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $conn->close();
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php' target='_blank'>Visit Homepage</a> - You should see 5 products</li>";
    echo "<li><a href='products.php' target='_blank'>View All Products</a></li>";
    echo "<li><a href='admin/products.php' target='_blank'>Admin - Manage Products</a></li>";
    echo "</ul>";
    
    echo "<p><strong>Note:</strong> Delete this file after import for security!</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>
