<?php
// Check and create upload directories with proper permissions
echo "Checking upload directories...\n";

$basePath = __DIR__;
$directories = [
    'uploads',
    'uploads/products',
    'uploads/files',
    'uploads/categories',
    'uploads/testimonials'
];

foreach ($directories as $dir) {
    $path = $basePath . '/' . $dir;
    if (!file_exists($path)) {
        if (mkdir($path, 0777, true)) {
            echo "Created directory: $path\n";
            // Try to set full permissions
            chmod($path, 0777);
        } else {
            echo "Failed to create directory: $path\n";
        }
    } else {
        echo "Directory exists: $path\n";
        // Try to ensure write permissions
        if (is_writable($path)) {
            echo " - Is writable\n";
        } else {
            echo " - Not writable, attempting to fix...\n";
            if (chmod($path, 0777)) {
                echo " - Successfully set permissions\n";
            } else {
                echo " - Failed to set permissions\n";
            }
        }
    }
}

echo "\nChecking PHP configuration for file uploads:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";

echo "\nTest file upload permissions:\n";
$testFile = __DIR__ . '/uploads/test_write.txt';
if (file_put_contents($testFile, 'test') !== false) {
    echo "- Successfully wrote to $testFile\n";
    unlink($testFile);
} else {
    echo "- Failed to write to $testFile\n";
    echo "  Error: " . error_get_last()['message'] . "\n";
}

// Check if we can create a file in the products directory
$testProductFile = __DIR__ . '/uploads/products/test_product.txt';
if (file_put_contents($testProductFile, 'test') !== false) {
    echo "- Successfully wrote to $testProductFile\n";
    unlink($testProductFile);
} else {
    echo "- Failed to write to $testProductFile\n";
    echo "  Error: " . error_get_last()['message'] . "\n";
}

echo "\nDirectory check complete. Please check the output above for any issues.\n";
?>
