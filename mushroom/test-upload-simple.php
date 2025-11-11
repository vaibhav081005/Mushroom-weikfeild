<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple file upload test
$uploadDir = __DIR__ . '/uploads/test/';

// Create test directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['test_image'])) {
        $file = $_FILES['test_image'];
        $targetFile = $uploadDir . basename($file['name']);
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $result = "<div style='color: green;'>File uploaded successfully to: " . htmlspecialchars($targetFile) . "</div>";
            $result .= "<div>File size: " . $file['size'] . " bytes</div>";
            $result .= "<div>File type: " . $file['type'] . "</div>";
            $result .= "<div><img src='uploads/test/" . htmlspecialchars($file['name']) . "' style='max-width: 300px; margin-top: 20px;'></div>";
        } else {
            $error = error_get_last();
            $result = "<div style='color: red;'>Error uploading file: " . ($error ? $error['message'] : 'Unknown error') . "</div>";
            $result .= "<pre>" . print_r($_FILES, true) . "</pre>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple File Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .container { background: #f9f9f9; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .btn { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Simple File Upload Test</h2>
        
        <?php if ($result): ?>
            <div style="margin: 20px 0; padding: 15px; background: #e9ecef; border-radius: 4px;">
                <?php echo $result; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="test_image">Select an image to upload:</label>
                <input type="file" name="test_image" id="test_image" required>
            </div>
            <button type="submit" class="btn">Upload Image</button>
        </form>
        
        <div style="margin-top: 30px; padding: 15px; background: #f0f0f0; border-radius: 4px;">
            <h3>Debug Information:</h3>
            <p>Upload Directory: <?php echo realpath($uploadDir); ?></p>
            <p>Is Writable: <?php echo is_writable($uploadDir) ? 'Yes' : 'No'; ?></p>
            <p>PHP Version: <?php echo phpversion(); ?></p>
            <p>POST Max Size: <?php echo ini_get('post_max_size'); ?></p>
            <p>Upload Max Filesize: <?php echo ini_get('upload_max_filesize'); ?></p>
            <p>Memory Limit: <?php echo ini_get('memory_limit'); ?></p>
        </div>
    </div>
</body>
</html>
