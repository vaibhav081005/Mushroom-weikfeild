<?php
require_once __DIR__ . '/config/config.php';

$page_title = 'Home';

// Fetch featured products
$pdo = getPDOConnection();
$stmt = $pdo->query("SELECT * FROM products WHERE is_featured = 1 AND is_active = 1 ORDER BY created_at DESC LIMIT 6");
$featured_products = $stmt->fetchAll();

// Fetch testimonials
$stmt = $pdo->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3");
$testimonials = $stmt->fetchAll();

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 LIMIT 4");
$categories = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .hero-section {
        background: linear-gradient(135deg, #1a5f3f 0%, #2e7d32 50%, #4caf50 100%);
        color: white;
        padding: 100px 0;
        position: relative;
        overflow: hidden;
        min-height: 600px;
        display: flex;
        align-items: center;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,101.3C1248,85,1344,75,1392,69.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
        background-size: cover;
        opacity: 0.2;
        animation: wave 15s ease-in-out infinite;
    }
    
    @keyframes wave {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .product-card {
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .product-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .product-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #ff5722;
        color: white;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        z-index: 1;
    }
    
    .product-badge.featured {
        background: #ffc107;
        color: #000;
    }

    .product-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    .category-card {
        background: var(--surface-color);
        border-radius: 16px;
        padding: 2.5rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 2px solid transparent;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }
    
    .category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(46, 125, 50, 0.1), transparent);
        transition: left 0.5s;
    }

    .category-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    }
    
    .category-card:hover::before {
        left: 100%;
    }

    .category-icon {
        font-size: 3.5rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease;
    }
    
    .category-card:hover .category-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .stats-section {
        background: linear-gradient(135deg, #f5f5f5 0%, #e8f5e9 100%);
        padding: 60px 0;
    }
    
    .stat-card {
        text-align: center;
        padding: 2rem;
    }
    
    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 60px;
        height: 4px;
        background: var(--primary-color);
        border-radius: 2px;
    }

    .testimonial-card {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 2rem;
        height: 100%;
    }

    .testimonial-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .rating {
        color: #ffc107;
    }

    .stats-section {
        background: var(--surface-color);
        padding: 3rem 0;
        margin: 3rem 0;
        border-radius: 12px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .cta-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 4rem 0;
        border-radius: 12px;
        margin: 3rem 0;
        text-align: center;
    }

    @media (max-width: 768px) {
        .hero-section {
            padding: 40px 0;
        }

        .hero-section h1 {
            font-size: 1.8rem;
        }

        .product-image {
            height: 180px;
        }

        .stat-number {
            font-size: 2rem;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="hero-badge">
                    <i class="fas fa-leaf me-2"></i>100% Natural & Organic
                </div>
                <h1 class="display-3 fw-bold mb-4" style="line-height: 1.2;">
                    Premium Quality<br>
                    <span style="color: #ffeb3b;">Mushroom Products</span>
                </h1>
                <p class="lead mb-4" style="font-size: 1.25rem; opacity: 0.95;">
                    Discover Weikfield's finest quality mushroom products - fresh, dried, extracts, and growing kits. 
                    Farm-fresh quality delivered to your doorstep.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-light btn-lg px-4 py-3" style="font-weight: 600;">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="#featured" class="btn btn-outline-light btn-lg px-4 py-3" style="font-weight: 600;">
                        <i class="fas fa-star me-2"></i>Featured Products
                    </a>
                </div>
                <div class="mt-4 d-flex gap-4 flex-wrap" style="opacity: 0.9;">
                    <div>
                        <i class="fas fa-check-circle me-2"></i>
                        <span>Farm Fresh</span>
                    </div>
                    <div>
                        <i class="fas fa-check-circle me-2"></i>
                        <span>100% Natural</span>
                    </div>
                    <div>
                        <i class="fas fa-check-circle me-2"></i>
                        <span>Fast Delivery</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div style="position: relative;">
                    <img src="https://images.unsplash.com/photo-1565868397984-c5e5c6d6e8f8?w=700&h=500&fit=crop" 
                         alt="Fresh Mushrooms" class="img-fluid rounded-4 shadow-lg" style="border: 8px solid rgba(255,255,255,0.2);">
                    <div style="position: absolute; bottom: 20px; right: 20px; background: white; padding: 1rem 1.5rem; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.2);">
                        <div style="color: #2e7d32; font-weight: 700; font-size: 1.5rem;">₹79</div>
                        <div style="color: #666; font-size: 0.875rem;">Starting from</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<div class="container">
    <div class="stats-section">
        <div class="row">
            <div class="col-6 col-md-3 stat-item mb-4 mb-md-0">
                <div class="stat-number">500+</div>
                <div class="text-secondary">Products</div>
            </div>
            <div class="col-6 col-md-3 stat-item mb-4 mb-md-0">
                <div class="stat-number">10K+</div>
                <div class="text-secondary">Happy Customers</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-number">50K+</div>
                <div class="text-secondary">Downloads</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-number">4.8★</div>
                <div class="text-secondary">Average Rating</div>
            </div>
        </div>
    </div>
</div>

<!-- Categories Section -->
<section class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">Browse Categories</h2>
        <p class="text-secondary">Explore our wide range of mushroom product categories</p>
    </div>
    
    <div class="row g-4">
        <?php foreach ($categories as $category): ?>
        <div class="col-6 col-md-3">
            <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['slug']; ?>" 
               style="text-decoration: none; color: inherit;">
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h5>
                    <p class="text-secondary small mb-0">
                        <?php echo htmlspecialchars(substr($category['description'], 0, 50)); ?>...
                    </p>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products Section -->
<section id="featured" class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">Featured Products</h2>
        <p class="text-secondary">Check out our most popular and trending products</p>
    </div>
    
    <div class="row g-4">
        <?php if (empty($featured_products)): ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-box-open fa-4x text-secondary mb-3"></i>
            <p class="text-secondary">No featured products available at the moment.</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                Browse All Products
            </a>
        </div>
        <?php else: ?>
            <?php foreach ($featured_products as $product): ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card product-card" onclick="window.location.href='<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>'">
                    <?php if ($product['discount_price']): ?>
                        <?php $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>
                        <div class="product-badge"><?php echo $discount_percent; ?>% OFF</div>
                    <?php endif; ?>
                    <?php if ($product['is_featured']): ?>
                        <div class="product-badge featured" style="<?php echo $product['discount_price'] ? 'top: 50px;' : ''; ?>">
                            <i class="fas fa-star me-1"></i>Featured
                        </div>
                    <?php endif; ?>
                    <div style="overflow: hidden; text-align: center; background: #f8f9fa; min-height: 200px; display: flex; align-items: center; justify-content: center;">
                        <?php if (!empty($product['image']) && file_exists(PRODUCT_IMAGE_PATH . '/' . $product['image'])): ?>
                            <img src="<?php echo PRODUCT_IMAGE_URL . '/' . $product['image']; ?>" 
                                 class="product-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; color: #6c757d; border: 2px dashed #dee2e6; padding: 10px;">
                                <div>No Image Available</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-2" style="font-size: 1.1rem;"><?php echo htmlspecialchars($product['title']); ?></h5>
                        <p class="card-text text-secondary small mb-3" style="min-height: 40px;">
                            <?php echo htmlspecialchars(substr($product['short_description'] ?: $product['description'], 0, 80)); ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <?php if ($product['discount_price']): ?>
                                    <div class="text-decoration-line-through text-secondary small">
                                        <?php echo formatPrice($product['price']); ?>
                                    </div>
                                    <div class="fw-bold text-success" style="font-size: 1.5rem;">
                                        <?php echo formatPrice($product['discount_price']); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="fw-bold" style="color: var(--primary-color); font-size: 1.5rem;">
                                        <?php echo formatPrice($product['price']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>, this)" style="border-radius: 50%; width: 45px; height: 45px; padding: 0;">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between text-secondary small">
                            <span><i class="fas fa-eye me-1"></i><?php echo number_format($product['views']); ?> views</span>
                            <span><i class="fas fa-download me-1"></i><?php echo number_format($product['downloads']); ?> sales</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-outline-primary btn-lg">
            View All Products <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<!-- Testimonials Section -->
<?php if (!empty($testimonials)): ?>
<section class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">What Our Customers Say</h2>
        <p class="text-secondary">Real feedback from our satisfied customers</p>
    </div>
    
    <div class="row g-4">
        <?php foreach ($testimonials as $testimonial): ?>
        <div class="col-12 col-md-4">
            <div class="testimonial-card">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?php echo $testimonial['image'] ? TESTIMONIAL_IMAGE_URL . '/' . $testimonial['image'] : 'https://ui-avatars.com/api/?name=' . urlencode($testimonial['name']); ?>" 
                         class="testimonial-avatar me-3" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                    <div>
                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                        <?php if ($testimonial['designation']): ?>
                            <small class="text-secondary"><?php echo htmlspecialchars($testimonial['designation']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="rating mb-2">
                    <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                        <i class="fas fa-star"></i>
                    <?php endfor; ?>
                </div>
                <p class="text-secondary mb-0">
                    "<?php echo htmlspecialchars($testimonial['message']); ?>"
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<div class="container">
    <div class="cta-section">
        <h2 class="fw-bold mb-3">Ready to Get Started?</h2>
        <p class="lead mb-4">Join thousands of satisfied customers and explore our premium mushroom products today!</p>
        <a href="<?php echo SITE_URL; ?>/auth/signup.php" class="btn btn-light btn-lg">
            <i class="fas fa-user-plus me-2"></i>Sign Up Now
        </a>
    </div>
</div>

<script>
function addToCart(productId, button) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?php echo SITE_URL; ?>/auth/login.php';
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
            addToCartAnimation(button);
            showToast('Product added to cart!', 'success');
            // Update cart count
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to add to cart', 'danger');
        }
    })
    .catch(error => {
        showToast('An error occurred', 'danger');
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
