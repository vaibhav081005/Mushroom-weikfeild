<?php
// Simple test file to check PHP and database
echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br><br>";

// Test database connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'weikfield_mushroom');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "Database connection FAILED: " . $conn->connect_error . "<br>";
    } else {
        echo "Database connection SUCCESS!<br>";
        echo "Database: " . DB_NAME . "<br>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='index.php'>Go to Homepage</a>";
?>
