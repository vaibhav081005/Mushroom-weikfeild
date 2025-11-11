<?php
require_once __DIR__ . '/config/config.php';

$page_title = 'Products';

// Get filters
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'latest');
$min_price = floatval($_GET['min_price'] ?? 0);
$max_price = floatval($_GET['max_price'] ?? 999999);

// Build query
$pdo = getPDOConnection();
$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.is_active = 1";
$params = [];

if ($search) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND c.slug = ?";
    $params[] = $category;
}

if ($min_price > 0) {
    $query .= " AND (p.discount_price >= ? OR (p.discount_price IS NULL AND p.price >= ?))";
    $params[] = $min_price;
    $params[] = $min_price;
}

if ($max_price < 999999) {
    $query .= " AND (p.discount_price <= ? OR (p.discount_price IS NULL AND p.price <= ?))";
    $params[] = $max_price;
    $params[] = $max_price;
}

// Sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY COALESCE(p.discount_price, p.price) ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY COALESCE(p.discount_price, p.price) DESC";
        break;
    case 'popular':
        $query .= " ORDER BY p.downloads DESC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .filters-section {
        background: var(--surface-color);
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
    }

    .filter-group {
        margin-bottom: 1rem;
    }

    .filter-group:last-child {
        margin-bottom: 0;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .product-card {
        background: var(--surface-color);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }

    .product-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
    }

    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: var(--accent-color);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .featured-badge {
        background: var(--primary-color);
    }

    .product-body {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .product-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--text-secondary);
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }

        .product-image {
            height: 180px;
        }

        .product-body {
            padding: 1rem;
        }

        .filters-section {
            padding: 1rem;
        }
    }
</style>

<div class="container my-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="fw-bold mb-2">Our Products</h1>
        <p class="text-secondary">
            <?php echo count($products); ?> products found
            <?php if ($search): ?>
                for "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </p>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" action="" id="filterForm">
            <div class="row g-3">
                <!-- Search -->
                <div class="col-12 col-md-4">
                    <div class="filter-group">
                        <label class="form-label small fw-bold">Search</label>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <!-- Category -->
                <div class="col-6 col-md-3">
                    <div class="filter-group">
                        <label class="form-label small fw-bold">Category</label>
                        <select class="form-select" name="category" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['slug']; ?>" 
                                        <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Sort -->
                <div class="col-6 col-md-3">
                    <div class="filter-group">
                        <label class="form-label small fw-bold">Sort By</label>
                        <select class="form-select" name="sort" onchange="this.form.submit()">
                            <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Latest</option>
                            <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Popular</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div class="col-12 col-md-2">
                    <div class="filter-group">
                        <label class="form-label small fw-bold d-none d-md-block">&nbsp;</label>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-redo me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h4 class="fw-bold mb-2">No Products Found</h4>
            <p class="text-secondary mb-3">Try adjusting your filters or search terms</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">
                View All Products
            </a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card" onclick="window.location.href='<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>'">
                    <div style="position: relative; min-height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                        <?php if (!empty($product['image']) && file_exists(PRODUCT_IMAGE_PATH . '/' . $product['image'])): ?>
                            <img src="<?php echo PRODUCT_IMAGE_URL . '/' . $product['image']; ?>" 
                                 class="product-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; color: #6c757d; border: 2px dashed #dee2e6; padding: 10px;">
                                <div>No Image Available</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['is_featured']): ?>
                            <span class="product-badge featured-badge">Featured</span>
                        <?php elseif ($product['discount_price']): ?>
                            <?php
                            $discount_percent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                            ?>
                            <span class="product-badge"><?php echo $discount_percent; ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-body">
                        <?php if ($product['category_name']): ?>
                            <small class="text-secondary">
                                <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product['category_name']); ?>
                            </small>
                        <?php endif; ?>
                        
                        <h5 class="fw-bold mt-2 mb-2"><?php echo htmlspecialchars($product['title']); ?></h5>
                        
                        <p class="text-secondary small mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        
                        <div class="product-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($product['discount_price']): ?>
                                        <div>
                                            <span class="text-decoration-line-through text-secondary small">
                                                <?php echo formatPrice($product['price']); ?>
                                            </span>
                                        </div>
                                        <div class="fw-bold" style="color: var(--primary-color); font-size: 1.25rem;">
                                            <?php echo formatPrice($product['discount_price']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="fw-bold" style="color: var(--primary-color); font-size: 1.25rem;">
                                            <?php echo formatPrice($product['price']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); addToCart(<?php echo $product['id']; ?>, this)">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
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
            addToCartAnimation(button);
            showToast('Product added to cart!', 'success');
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
