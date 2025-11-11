<?php
require_once __DIR__ . '/config/config.php';

requireLogin();

$item_id = intval($_GET['item'] ?? 0);

if ($item_id <= 0) {
    redirect(SITE_URL . '/orders.php');
}

$pdo = getPDOConnection();

// Get order item with product details
$stmt = $pdo->prepare("
    SELECT oi.*, o.user_id, o.payment_status, p.file_path, p.title 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE oi.id = ?
");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    redirect(SITE_URL . '/orders.php');
}

// Verify ownership
if ($item['user_id'] != $_SESSION['user_id']) {
    redirect(SITE_URL . '/orders.php');
}

// Check payment status
if ($item['payment_status'] !== 'completed') {
    $_SESSION['error'] = 'Payment not completed for this order';
    redirect(SITE_URL . '/orders.php');
}

// Check download expiry
if ($item['download_expires_at'] && strtotime($item['download_expires_at']) < time()) {
    $_SESSION['error'] = 'Download link has expired';
    redirect(SITE_URL . '/orders.php');
}

// Update download count
$stmt = $pdo->prepare("UPDATE order_items SET download_count = download_count + 1 WHERE id = ?");
$stmt->execute([$item_id]);

// For demo purposes, we'll create a dummy file or redirect to a file
// In production, you would serve the actual file from PRODUCT_FILE_PATH

$file_path = PRODUCT_FILE_PATH . '/' . $item['file_path'];

// If file exists, serve it
if ($item['file_path'] && file_exists($file_path)) {
    // Set headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($item['file_path']) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache');
    
    // Read and output file
    readfile($file_path);
    exit;
} else {
    // Demo mode - create a text file with product info
    $filename = sanitize($item['title']) . '_' . time() . '.txt';
    
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo "===========================================\n";
    echo "WEIKFIELD MUSHROOM PRODUCTS\n";
    echo "===========================================\n\n";
    echo "Product: " . $item['title'] . "\n";
    echo "Order Item ID: " . $item['id'] . "\n";
    echo "Download Date: " . date('Y-m-d H:i:s') . "\n";
    echo "Download Count: " . ($item['download_count'] + 1) . "\n\n";
    echo "Thank you for your purchase!\n\n";
    echo "This is a demo download file.\n";
    echo "In production, the actual product file would be downloaded here.\n\n";
    echo "For support, contact: info@weikfield.com\n";
    echo "===========================================\n";
    
    exit;
}
?>
