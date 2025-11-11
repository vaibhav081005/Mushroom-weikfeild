<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Upload Results:</h3>";
    
    // Check for upload errors
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/products/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Try to create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Try to upload the file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            echo "<p style='color: green;'>File uploaded successfully to: " . htmlspecialchars($targetPath) . "</p>";
            echo "<p>File size: " . $_FILES['image']['size'] . " bytes</p>";
            echo "<p>File type: " . $_FILES['image']['type'] . "</p>";
        } else {
            echo "<p style='color: red;'>Failed to move uploaded file.</p>";
            echo "<p>Error: " . print_r(error_get_last(), true) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Error in file upload. Error code: " . $_FILES['image']['error'] . "</p>";
        echo "<p>Error details: " . getUploadError($_FILES['image']['error']) . "</p>";
    }
}

function getUploadError($error_code) {
    $errors = array(
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
    );
    
    return isset($errors[$error_code]) ? $errors[$error_code] : 'Unknown upload error';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Test File Upload</h2>
    <p>This form will help test if file uploads are working correctly.</p>
    
    <form action="test-upload.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="image">Select image to upload:</label>
            <input type="file" name="image" id="image" required>
        </div>
        <div class="form-group">
            <input type="submit" value="Upload Image" name="submit">
        </div>
    </form>
    
    <h3>Upload Directory Status</h3>
    <?php
    $uploadDir = __DIR__ . '/uploads/products/';
    echo "<p>Upload directory: " . htmlspecialchars($uploadDir) . "</p>";
    
    if (is_dir($uploadDir)) {
        echo "<p class='success'>✓ Directory exists</p>";
        
        if (is_writable($uploadDir)) {
            echo "<p class='success'>✓ Directory is writable</p>";
            
            // Test creating a file
            $testFile = $uploadDir . 'test_' . time() . '.txt';
            if (file_put_contents($testFile, 'test') !== false) {
                echo "<p class='success'>✓ Successfully created test file: " . basename($testFile) . "</p>";
                unlink($testFile);
            } else {
                echo "<p class='error'>✗ Failed to create test file. Error: " . error_get_last()['message'] . "</p>";
            }
        } else {
            echo "<p class='error'>✗ Directory is not writable. Current permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Directory does not exist</p>";
        
        // Try to create the directory
        if (mkdir($uploadDir, 0777, true)) {
            echo "<p class='success'>✓ Successfully created directory</p>";
        } else {
            echo "<p class='error'>✗ Failed to create directory. Error: " . error_get_last()['message'] . "</p>";
        }
    }
    ?>
    
    <h3>PHP Upload Settings</h3>
    <ul>
        <li>file_uploads: <?php echo ini_get('file_uploads') ? 'On' : 'Off'; ?></li>
        <li>upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?></li>
        <li>post_max_size: <?php echo ini_get('post_max_size'); ?></li>
        <li>max_file_uploads: <?php echo ini_get('max_file_uploads'); ?></li>
        <li>upload_tmp_dir: <?php echo ini_get('upload_tmp_dir') ?: 'Not set (using system default)'; ?></li>
        <li>upload_tmp_dir writable: <?php echo is_writable(ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) ? 'Yes' : 'No'; ?></li>
    </ul>
</body>
</html>
