<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site Configuration
define('SITE_URL', 'http://localhost/mushroom');
define('SITE_NAME', 'Weikfield Mushroom Products');

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PRODUCT_IMAGE_PATH', UPLOAD_PATH . '/products');
define('PRODUCT_FILE_PATH', UPLOAD_PATH . '/files');
define('CATEGORY_IMAGE_PATH', UPLOAD_PATH . '/categories');
define('TESTIMONIAL_IMAGE_PATH', UPLOAD_PATH . '/testimonials');

// URL paths
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOAD_URL', SITE_URL . '/uploads');
define('PRODUCT_IMAGE_URL', UPLOAD_URL . '/products');
define('CATEGORY_IMAGE_URL', UPLOAD_URL . '/categories');
define('TESTIMONIAL_IMAGE_URL', UPLOAD_URL . '/testimonials');

// Create upload directories if they don't exist
$directories = [
    UPLOAD_PATH,
    PRODUCT_IMAGE_PATH,
    PRODUCT_FILE_PATH,
    CATEGORY_IMAGE_PATH,
    TESTIMONIAL_IMAGE_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/auth/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

function getUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function getAdmin() {
    if (!isAdmin()) {
        return null;
    }
    
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function formatPrice($price) {
    $pdo = getPDOConnection();
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'currency_symbol'");
    $result = $stmt->fetch();
    $symbol = $result ? $result['setting_value'] : '₹';
    
    return $symbol . number_format($price, 2);
}

function getSetting($key, $default = '') {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    
    return $result ? $result['setting_value'] : $default;
}

function updateSetting($key, $value) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    return $stmt->execute([$value, $key]);
}

function generateOrderNumber() {
    return 'ORD-' . strtoupper(uniqid()) . '-' . time();
}

function sendEmail($to, $subject, $message) {
    // Email configuration
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . getSetting('site_email', 'noreply@weikfield.com') . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    if ($fileError !== 0) {
        return ['success' => false, 'message' => 'Error uploading file'];
    }
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $fileDestination = $destination . '/' . $newFileName;
    
    if (move_uploaded_file($fileTmp, $fileDestination)) {
        return ['success' => true, 'filename' => $newFileName];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

function getCartCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    
    return $result ? $result['count'] : 0;
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];
    
    foreach ($periods as $key => $value) {
        if ($difference >= $value) {
            $time = floor($difference / $value);
            return $time . ' ' . $key . ($time > 1 ? 's' : '') . ' ago';
        }
    }
    
    return 'Just now';
}
?>
