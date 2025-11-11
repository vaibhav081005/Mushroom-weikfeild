<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = intval($data['order_id'] ?? 0);
$payment_id = sanitize($data['payment_id'] ?? '');
$order_number = sanitize($data['order_number'] ?? '');

if ($order_id <= 0 || empty($payment_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $pdo = getPDOConnection();
    
    // Verify order belongs to user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Update order status
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET payment_status = 'completed', 
            order_status = 'completed',
            transaction_id = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$payment_id, $order_id]);
    
    // Set download expiry for order items
    $expiry_days = intval(getSetting('download_expiry_days', 30));
    $expiry_date = date('Y-m-d H:i:s', strtotime("+$expiry_days days"));
    
    $stmt = $pdo->prepare("
        UPDATE order_items 
        SET download_expires_at = ?
        WHERE order_id = ?
    ");
    $stmt->execute([$expiry_date, $order_id]);
    
    // Update product download counts
    $stmt = $pdo->prepare("
        UPDATE products p
        INNER JOIN order_items oi ON p.id = oi.product_id
        SET p.downloads = p.downloads + oi.quantity
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    
    // Send confirmation email
    $subject = 'Order Confirmation - ' . $order_number;
    $message = "
        <h2>Thank you for your order!</h2>
        <p>Your order has been confirmed and payment received.</p>
        <p><strong>Order Number:</strong> $order_number</p>
        <p><strong>Total Amount:</strong> " . formatPrice($order['total']) . "</p>
        <p>You can download your products from your orders page.</p>
        <p><a href='" . SITE_URL . "/orders.php'>View Orders</a></p>
    ";
    sendEmail($order['billing_email'], $subject, $message);
    
    echo json_encode(['success' => true, 'message' => 'Payment successful']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
