<?php
require_once __DIR__ . '/config/config.php';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    redirect(SITE_URL . '/products.php');
}

$pdo = getPDOConnection();

// Get product details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    redirect(SITE_URL . '/products.php');
}

// Update views
$stmt = $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
$stmt->execute([$product_id]);

// Get screenshots
$stmt = $pdo->prepare("SELECT * FROM product_screenshots WHERE product_id = ? ORDER BY display_order");
$stmt->execute([$product_id]);
$screenshots = $stmt->fetchAll();

// Get related products
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? AND is_active = 1 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();

$page_title = $product['title'];

include __DIR__ . '/includes/header.php';
?>

<style>
    .product-detail-container {
        padding: 2rem 0;
    }

    .product-main-image {
        width: 100%;
        height: 400px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: var(--shadow);
    }

    .product-thumbnails {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        overflow-x: auto;
    }

    .thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .thumbnail:hover, .thumbnail.active {
        border-color: var(--primary-color);
    }

    .product-info {
        background: var(--surface-color);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: var(--shadow);
    }

    .price-section {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .original-price {
        text-decoration: line-through;
        opacity: 0.8;
        font-size: 1.2rem;
    }

    .current-price {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .discount-badge {
        background: var(--accent-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .feature-list {
        list-style: none;
        padding: 0;
    }

    .feature-list li {
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .feature-list li:last-child {
        border-bottom: none;
    }

    .feature-list i {
        color: var(--primary-color);
        margin-right: 0.5rem;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .related-products {
        margin-top: 4rem;
    }

    @media (max-width: 768px) {
        .product-main-image {
            height: 300px;
        }

        .product-info {
            padding: 1.5rem;
        }

        .current-price {
            font-size: 2rem;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<div class="container product-detail-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/products.php">Products</a></li>
            <?php if ($product['category_name']): ?>
                <li class="breadcrumb-item">
                    <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_slug']; ?>">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div style="background: #f8f9fa; min-height: 400px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; border: 1px solid #dee2e6; border-radius: 8px;">
                <?php if (!empty($product['image']) && file_exists(PRODUCT_IMAGE_PATH . '/' . $product['image'])): ?>
                    <img src="<?php echo PRODUCT_IMAGE_URL . '/' . $product['image']; ?>" 
                         class="product-main-image img-fluid" id="mainImage" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>">
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: #6c757d;">
                        <i class="fas fa-image" style="font-size: 48px; opacity: 0.5; margin-bottom: 10px; display: block;"></i>
                        <div>No Image Available</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($screenshots)): ?>
                <div class="product-thumbnails d-flex flex-wrap gap-2">
                    <?php if (!empty($product['image']) && file_exists(PRODUCT_IMAGE_PATH . '/' . $product['image'])): ?>
                        <div class="thumbnail active" style="width: 80px; height: 80px; overflow: hidden; border: 2px solid #0d6efd; border-radius: 4px;">
                            <img src="<?php echo PRODUCT_IMAGE_URL . '/' . $product['image']; ?>" 
                                 class="w-100 h-100 object-fit-cover" 
                                 onclick="changeImage(this.src)">
                        </div>
                    <?php endif; ?>
                    <?php foreach ($screenshots as $screenshot): ?>
                        <?php if (file_exists(PRODUCT_IMAGE_PATH . '/' . $screenshot['image_path'])): ?>
                            <div class="thumbnail" style="width: 80px; height: 80px; overflow: hidden; border: 1px solid #dee2e6; border-radius: 4px;">
                                <img src="<?php echo PRODUCT_IMAGE_URL . '/' . $screenshot['image_path']; ?>" 
                                     class="w-100 h-100 object-fit-cover" 
                                     onclick="changeImage(this.src)">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info">
                <?php if ($product['is_featured']): ?>
                    <span class="badge bg-primary mb-2">
                        <i class="fas fa-star me-1"></i>Featured
                    </span>
                <?php endif; ?>
                
                <?php if ($product['category_name']): ?>
                    <div class="text-secondary mb-2">
                        <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['category_name']); ?>
                    </div>
                <?php endif; ?>

                <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="text-secondary">
                        <i class="fas fa-eye me-1"></i><?php echo number_format($product['views']); ?> views
                    </div>
                    <div class="text-secondary">
                        <i class="fas fa-download me-1"></i><?php echo number_format($product['downloads']); ?> downloads
                    </div>
                </div>

                <!-- Price Section -->
                <div class="price-section">
                    <?php if ($product['discount_price']): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
                            <?php
                            $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                            ?>
                            <span class="discount-badge"><?php echo $discount_percent; ?>% OFF</span>
                        </div>
                        <div class="current-price"><?php echo formatPrice($product['discount_price']); ?></div>
                    <?php else: ?>
                        <div class="current-price"><?php echo formatPrice($product['price']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <h5 class="fw-bold mb-3">Description</h5>
                    <p class="text-secondary"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <!-- Features -->
                <?php if ($product['features']): ?>
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Features</h5>
                        <ul class="feature-list">
                            <?php
                            $features = explode("\n", $product['features']);
                            foreach ($features as $feature):
                                if (trim($feature)):
                            ?>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo htmlspecialchars(trim($feature)); ?>
                                </li>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary btn-lg flex-fill" onclick="addToCart(<?php echo $product['id']; ?>, this)">
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                    <button class="btn btn-accent btn-lg flex-fill" onclick="buyNow(<?php echo $product['id']; ?>)">
                        <i class="fas fa-bolt me-2"></i>Buy Now
                    </button>
                </div>

                <?php if ($product['demo_url']): ?>
                    <a href="<?php echo htmlspecialchars($product['demo_url']); ?>" target="_blank" 
                       class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-external-link-alt me-2"></i>View Demo
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <div class="related-products">
            <h3 class="fw-bold mb-4">Related Products</h3>
            <div class="row g-4">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-6 col-md-3">
                        <div class="card h-100" style="cursor: pointer;" 
                             onclick="window.location.href='<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $related['id']; ?>'">
                            <div style="height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #dee2e6;">
                                <?php if (!empty($related['image']) && file_exists(PRODUCT_IMAGE_PATH . '/' . $related['image'])): ?>
                                    <img src="<?php echo PRODUCT_IMAGE_URL . '/' . $related['image']; ?>" 
                                         class="card-img-top h-100" style="object-fit: contain; max-width: 100%;">
                                <?php else: ?>
                                    <div style="text-align: center; color: #6c757d; padding: 20px;">
                                        <i class="fas fa-image" style="font-size: 36px; opacity: 0.5; display: block; margin-bottom: 10px;"></i>
                                        <small>No Image</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title fw-bold"><?php echo htmlspecialchars($related['title']); ?></h6>
                                <p class="fw-bold mb-0" style="color: var(--primary-color);">
                                    <?php echo formatPrice($related['discount_price'] ?? $related['price']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function changeImage(src) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
        if (thumb.src === src) {
            thumb.classList.add('active');
        }
    });
}

function addToCart(productId, button) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?php echo SITE_URL; ?>/auth/login.php?redirect=' + encodeURIComponent(window.location.href);
        return;
    <?php endif; ?>
    
    fetch('<?php echo SITE_URL; ?>/api/cart-add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to cart!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to add to cart', 'danger');
        }
    });
}

function buyNow(productId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?php echo SITE_URL; ?>/auth/login.php?redirect=' + encodeURIComponent(window.location.href);
        return;
    <?php endif; ?>
    
    fetch('<?php echo SITE_URL; ?>/api/cart-add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '<?php echo SITE_URL; ?>/checkout.php';
        } else {
            showToast(data.message || 'Failed to add to cart', 'danger');
        }
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
