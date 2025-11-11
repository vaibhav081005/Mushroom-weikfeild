<?php
// Add Product Images
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🖼️ Adding Product Images</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; } img { max-width: 200px; margin: 10px; }</style>";

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'weikfield_mushroom';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Product images from Unsplash (high quality mushroom images)
$product_images = [
    1 => 'https://images.unsplash.com/photo-1567450651742-64cc5c0c8c0c?w=800&h=600&fit=crop', // Button mushrooms
    2 => 'https://images.unsplash.com/photo-1565868397984-c5e5c6d6e8f8?w=800&h=600&fit=crop', // Oyster mushrooms
    3 => 'https://images.unsplash.com/photo-1585868241444-c0d4c9a0e3b4?w=800&h=600&fit=crop', // Dried shiitake
    4 => 'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?w=800&h=600&fit=crop', // Growing kit
    5 => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=800&h=600&fit=crop'  // Extract powder
];

echo "<h3>Updating Product Images...</h3>";

foreach ($product_images as $product_id => $image_url) {
    $sql = "UPDATE products SET image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $image_url, $product_id);
    
    if ($stmt->execute()) {
        echo "<p class='success'>✓ Updated product $product_id with image</p>";
        echo "<img src='$image_url' alt='Product $product_id'>";
    }
}

echo "<hr>";
echo "<h2 class='success'>✅ All product images updated!</h2>";
echo "<p><a href='index.php' target='_blank' style='font-size: 1.2rem; color: #2e7d32; font-weight: bold;'>→ View Homepage</a></p>";

$conn->close();
?>
