<?php
require_once __DIR__ . '/config/config.php';

requireLogin();

$order_number = sanitize($_GET['order'] ?? '');

if (empty($order_number)) {
    redirect(SITE_URL . '/orders.php');
}

$pdo = getPDOConnection();
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$order_number, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect(SITE_URL . '/orders.php');
}

// Get order items
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$order_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($order_number); ?></title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 2rem;
            background: #f5f5f5;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #2e7d32;
        }

        .company-info h1 {
            color: #2e7d32;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2e7d32;
        }

        .billing-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .info-section h6 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2e7d32;
        }

        .invoice-table {
            width: 100%;
            margin-bottom: 2rem;
        }

        .invoice-table th {
            background: #f5f5f5;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .invoice-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .totals-section {
            margin-left: auto;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .total-row.grand-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2e7d32;
            border-top: 2px solid #2e7d32;
            border-bottom: 2px solid #2e7d32;
            margin-top: 0.5rem;
        }

        .invoice-footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #757575;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .invoice-container {
                box-shadow: none;
                padding: 0;
            }

            .print-button {
                display: none;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .invoice-container {
                padding: 1.5rem;
            }

            .invoice-header {
                flex-direction: column;
            }

            .invoice-details {
                text-align: left;
                margin-top: 1rem;
            }

            .billing-info {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .totals-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <button class="btn btn-primary print-button" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print Invoice
    </button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1><?php echo SITE_NAME; ?></h1>
                <p class="mb-0">Mumbai, Maharashtra, India</p>
                <p class="mb-0">Phone: <?php echo getSetting('site_phone', '+91 1234567890'); ?></p>
                <p class="mb-0">Email: <?php echo getSetting('site_email', 'info@weikfield.com'); ?></p>
            </div>
            <div class="invoice-details">
                <div class="invoice-number"><?php echo htmlspecialchars($order_number); ?></div>
                <p class="mb-1"><strong>Invoice Date:</strong> <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                <p class="mb-0"><strong>Status:</strong> 
                    <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </p>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-info">
            <div class="info-section">
                <h6>BILL TO:</h6>
                <p class="mb-1"><strong><?php echo htmlspecialchars($order['billing_name']); ?></strong></p>
                <p class="mb-1"><?php echo htmlspecialchars($order['billing_email']); ?></p>
                <p class="mb-1"><?php echo htmlspecialchars($order['billing_phone']); ?></p>
                <?php if ($order['billing_address']): ?>
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="info-section">
                <h6>PAYMENT DETAILS:</h6>
                <p class="mb-1"><strong>Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                <?php if ($order['transaction_id']): ?>
                    <p class="mb-1"><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                <?php endif; ?>
                <p class="mb-0"><strong>Date:</strong> <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
            </div>
        </div>

        <!-- Order Items -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_title']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo formatPrice($item['price']); ?></td>
                        <td><?php echo formatPrice($item['subtotal']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span><?php echo formatPrice($order['subtotal']); ?></span>
            </div>
            <?php if ($order['discount'] > 0): ?>
                <div class="total-row">
                    <span>Discount:</span>
                    <span class="text-success">-<?php echo formatPrice($order['discount']); ?></span>
                </div>
            <?php endif; ?>
            <div class="total-row">
                <span>Tax:</span>
                <span><?php echo formatPrice($order['tax']); ?></span>
            </div>
            <div class="total-row grand-total">
                <span>Grand Total:</span>
                <span><?php echo formatPrice($order['total']); ?></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p class="mb-2"><strong>Thank you for your business!</strong></p>
            <p class="mb-0">This is a computer-generated invoice and does not require a signature.</p>
            <p class="mb-0">For any queries, please contact us at <?php echo getSetting('site_email', 'info@weikfield.com'); ?></p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
