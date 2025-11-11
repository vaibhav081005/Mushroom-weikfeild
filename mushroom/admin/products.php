<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Products';
$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo = getPDOConnection();
    
    // Get product image to delete
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product && $product['image']) {
        deleteFile(PRODUCT_IMAGE_PATH . '/' . $product['image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Product deleted successfully';
    } else {
        $error = 'Failed to delete product';
    }
}

// Get all products
$pdo = getPDOConnection();
$search = sanitize($_GET['search'] ?? '');
$category = sanitize($_GET['category'] ?? '');

$query = "SELECT p.*, c.name as category_name FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .product-image-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .status-inactive {
        background: #ffebee;
        color: #c62828;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Products</h2>
            <p class="text-secondary mb-0">Manage your product catalog</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/product-add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Product
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Downloads</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-secondary">
                                    <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                    No products found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $product['image'] ? PRODUCT_IMAGE_URL . '/' . $product['image'] : 'https://via.placeholder.com/60'; ?>" 
                                             class="product-image-thumb" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($product['title']); ?></div>
                                        <?php if ($product['is_featured']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-star"></i> Featured
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($product['discount_price']): ?>
                                            <div class="text-decoration-line-through text-secondary small">
                                                <?php echo formatPrice($product['price']); ?>
                                            </div>
                                            <div class="fw-bold text-success">
                                                <?php echo formatPrice($product['discount_price']); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="fw-bold">
                                                <?php echo formatPrice($product['price']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($product['views']); ?></td>
                                    <td><?php echo number_format($product['downloads']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo SITE_URL; ?>/product-detail.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-info" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo SITE_URL; ?>/admin/product-edit.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Are you sure you want to delete this product?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
