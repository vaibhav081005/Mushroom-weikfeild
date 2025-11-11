<?php
require_once __DIR__ . '/config/config.php';

requireLogin();

$page_title = 'Shopping Cart';
$user_id = $_SESSION['user_id'];

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cart_id = intval($_POST['cart_id'] ?? 0);
    
    $pdo = getPDOConnection();
    
    if ($action === 'update' && $cart_id > 0) {
        $quantity = intval($_POST['quantity'] ?? 1);
        $quantity = max(1, $quantity);
        
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $user_id]);
    } elseif ($action === 'remove' && $cart_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
    }
    
    redirect(SITE_URL . '/cart.php');
}

// Get cart items
$pdo = getPDOConnection();
$stmt = $pdo->prepare("
    SELECT c.*, p.title, p.price, p.discount_price, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? AND p.is_active = 1
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $price = $item['discount_price'] ?? $item['price'];
    $subtotal += $price * $item['quantity'];
}

$tax_percentage = floatval(getSetting('tax_percentage', 18));
$tax = ($subtotal * $tax_percentage) / 100;
$total = $subtotal + $tax;

include __DIR__ . '/includes/header.php';
?>

<style>
    .cart-container {
        min-height: 60vh;
    }

    .cart-item {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
    }

    .cart-item:hover {
        box-shadow: var(--shadow-hover);
    }

    .cart-item-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }

    .quantity-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quantity-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid var(--primary-color);
        background: transparent;
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .quantity-btn:hover {
        background: var(--primary-color);
        color: white;
    }

    .quantity-input {
        width: 60px;
        text-align: center;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        padding: 0.25rem;
    }

    .cart-summary {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        position: sticky;
        top: 100px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }

    .summary-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .summary-total {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .empty-cart {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-cart i {
        font-size: 5rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .cart-item {
            padding: 1rem;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
        }

        .cart-summary {
            position: static;
            margin-top: 2rem;
        }
    }
</style>

<div class="container my-4 cart-container">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
    </h1>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3 class="fw-bold mb-3">Your cart is empty</h3>
            <p class="text-secondary mb-4">Add some products to get started!</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="<?php echo $item['image'] ? PRODUCT_IMAGE_URL . '/' . $item['image'] : 'https://via.placeholder.com/100'; ?>" 
                                     class="cart-item-image" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            </div>
                            
                            <div class="col">
                                <h5 class="fw-bold mb-2">
                                    <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $item['product_id']; ?>" 
                                       style="text-decoration: none; color: inherit;">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </a>
                                </h5>
                                
                                <div class="mb-2">
                                    <?php if ($item['discount_price']): ?>
                                        <span class="text-decoration-line-through text-secondary me-2">
                                            <?php echo formatPrice($item['price']); ?>
                                        </span>
                                        <span class="fw-bold" style="color: var(--primary-color);">
                                            <?php echo formatPrice($item['discount_price']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="fw-bold" style="color: var(--primary-color);">
                                            <?php echo formatPrice($item['price']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <form method="POST" class="quantity-control">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        
                                        <button type="submit" class="quantity-btn" 
                                                onclick="this.form.quantity.value = Math.max(1, parseInt(this.form.quantity.value) - 1)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        
                                        <input type="number" name="quantity" class="quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" min="1" readonly>
                                        
                                        <button type="submit" class="quantity-btn" 
                                                onclick="this.form.quantity.value = parseInt(this.form.quantity.value) + 1">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Remove this item from cart?')">
                                            <i class="fas fa-trash me-1"></i>Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-auto text-end">
                                <div class="fw-bold" style="font-size: 1.25rem; color: var(--primary-color);">
                                    <?php
                                    $price = $item['discount_price'] ?? $item['price'];
                                    echo formatPrice($price * $item['quantity']);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h4 class="fw-bold mb-4">Order Summary</h4>
                    
                    <div class="summary-row">
                        <span>Subtotal (<?php echo count($cart_items); ?> items)</span>
                        <span class="fw-bold"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Tax (<?php echo $tax_percentage; ?>%)</span>
                        <span class="fw-bold"><?php echo formatPrice($tax); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="fw-bold">Total</span>
                        <span class="summary-total"><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-primary w-100 btn-lg mt-3">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>
                    
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
