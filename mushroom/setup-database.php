<?php
/**
 * Database Setup Script
 * This will create the database and import the schema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Setup</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; }</style>";

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'weikfield_mushroom';

try {
    // Step 1: Connect to MySQL (without database)
    echo "<h3>Step 1: Connecting to MySQL...</h3>";
    $conn = new mysqli($host, $user, $pass);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "<p class='success'>✓ Connected to MySQL successfully!</p>";
    
    // Step 2: Create database if not exists
    echo "<h3>Step 2: Creating database...</h3>";
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "<p class='success'>✓ Database '$dbname' created/verified!</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Step 3: Select database
    $conn->select_db($dbname);
    echo "<p class='success'>✓ Database selected!</p>";
    
    // Step 4: Read and execute schema.sql
    echo "<h3>Step 3: Importing schema...</h3>";
    $schema_file = __DIR__ . '/database/schema.sql';
    
    if (!file_exists($schema_file)) {
        throw new Exception("Schema file not found: $schema_file");
    }
    
    $sql = file_get_contents($schema_file);
    
    // Split SQL into individual queries
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($queries as $query) {
        if (empty($query) || substr($query, 0, 2) === '--') {
            continue;
        }
        
        if ($conn->query($query)) {
            $success_count++;
        } else {
            $error_count++;
            echo "<p class='error'>Error in query: " . $conn->error . "</p>";
        }
    }
    
    echo "<p class='success'>✓ Executed $success_count queries successfully!</p>";
    if ($error_count > 0) {
        echo "<p class='error'>⚠ $error_count queries had errors</p>";
    }
    
    // Step 5: Import products
    echo "<h3>Step 4: Importing products...</h3>";
    $products_file = __DIR__ . '/database/add_products.sql';
    
    if (file_exists($products_file)) {
        $sql = file_get_contents($products_file);
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        $product_count = 0;
        foreach ($queries as $query) {
            if (empty($query) || substr($query, 0, 2) === '--') {
                continue;
            }
            
            if ($conn->query($query)) {
                $product_count++;
            }
        }
        
        echo "<p class='success'>✓ Imported products successfully!</p>";
    } else {
        echo "<p>⚠ Products file not found (optional)</p>";
    }
    
    // Step 6: Verify tables
    echo "<h3>Step 5: Verifying tables...</h3>";
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "<p class='success'>✓ Found " . count($tables) . " tables:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Step 7: Check admin user
    echo "<h3>Step 6: Checking admin user...</h3>";
    $result = $conn->query("SELECT email FROM admins LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "<p class='success'>✓ Admin user exists: " . $admin['email'] . "</p>";
    } else {
        echo "<p class='error'>⚠ No admin user found</p>";
    }
    
    $conn->close();
    
    echo "<hr>";
    echo "<h2 class='success'>✅ Database Setup Complete!</h2>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php'>Visit Homepage</a></li>";
    echo "<li><a href='admin/login.php'>Admin Login</a> (Email: admin@weikfield.com, Password: password)</li>";
    echo "<li><a href='products.php'>View Products</a></li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<p><strong>Important:</strong> Delete this file (setup-database.php) after setup for security!</p>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Make sure MySQL is running in XAMPP Control Panel</li>";
    echo "<li>Check database credentials in config/database.php</li>";
    echo "<li>Verify schema.sql file exists in database/ folder</li>";
    echo "</ul>";
}
?>
