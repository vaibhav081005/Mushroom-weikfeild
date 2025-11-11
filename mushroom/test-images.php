<?php
require_once 'config/config.php';

try {
    // Get database connection
    $db = getDB();
    
    // Get all products with their images
    $stmt = $db->query("SELECT id, title, image FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Product Images Test</h2>";
    echo "<p>Upload Path: " . PRODUCT_IMAGE_PATH . "</p>";
    echo "<p>Upload URL: " . PRODUCT_IMAGE_URL . "</p>";
    
    echo "<h3>Products in Database:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Title</th><th>Image Filename</th><th>File Exists</th><th>Preview</th></tr>";
    
    foreach ($products as $product) {
        $fileExists = !empty($product['image']) && file_exists(PRODUCT_IMAGE_PATH . '/' . $product['image']);
        $imageUrl = !empty($product['image']) ? PRODUCT_IMAGE_URL . '/' . $product['image'] : '';
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($product['id']) . "</td>";
        echo "<td>" . htmlspecialchars($product['title']) . "</td>";
        echo "<td>" . htmlspecialchars($product['image']) . "</td>";
        echo "<td>" . ($fileExists ? '✅ Yes' : '❌ No') . "</td>";
        
        if ($fileExists) {
            echo "<td><img src='" . $imageUrl . "' style='max-width: 100px; max-height: 100px;'></td>";
        } else {
            echo "<td>No image</td>";
        }
        
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Check directory permissions
    echo "<h3>Directory Permissions:</h3>";
    echo "<ul>";
    echo "<li>Uploads directory exists: " . (is_dir(UPLOAD_PATH) ? '✅ Yes' : '❌ No') . "</li>";
    echo "<li>Products directory exists: " . (is_dir(PRODUCT_IMAGE_PATH) ? '✅ Yes' : '❌ No') . "</li>";
    echo "<li>Uploads directory writable: " . (is_writable(UPLOAD_PATH) ? '✅ Yes' : '❌ No') . "</li>";
    echo "<li>Products directory writable: " . (is_writable(PRODUCT_IMAGE_PATH) ? '✅ Yes' : '❌ No') . "</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
