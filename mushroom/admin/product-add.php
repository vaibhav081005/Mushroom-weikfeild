<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Add Product';
$success = '';
$error = '';

$pdo = getPDOConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $slug = sanitize($_POST['slug']);
    $description = sanitize($_POST['description']);
    $short_description = sanitize($_POST['short_description']);
    $price = floatval($_POST['price']);
    $discount_price = floatval($_POST['discount_price'] ?? 0);
    $category_id = intval($_POST['category_id']);
    $features = sanitize($_POST['features']);
    $demo_url = sanitize($_POST['demo_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle main image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image'], PRODUCT_IMAGE_PATH, ['jpg', 'jpeg', 'png', 'webp']);
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        } else {
            $error = $upload_result['error'];
        }
    }
    
    // Handle product file upload
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['file'], PRODUCT_FILE_PATH, ['zip', 'pdf', 'jpg', 'jpeg', 'png']);
        if ($upload_result['success']) {
            $file_path = $upload_result['filename'];
        }
    }
    
    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (title, slug, description, short_description, price, discount_price, category_id, image, file_path, features, demo_url, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$title, $slug, $description, $short_description, $price, $discount_price, $category_id, $image, $file_path, $features, $demo_url, $is_featured, $is_active])) {
                $product_id = $pdo->lastInsertId();
                
                // Handle multiple screenshots
                if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
                    foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['screenshots']['error'][$key] === UPLOAD_ERR_OK) {
                            $file_data = [
                                'name' => $_FILES['screenshots']['name'][$key],
                                'type' => $_FILES['screenshots']['type'][$key],
                                'tmp_name' => $tmp_name,
                                'error' => $_FILES['screenshots']['error'][$key],
                                'size' => $_FILES['screenshots']['size'][$key]
                            ];
                            
                            $upload_result = uploadFile($file_data, PRODUCT_IMAGE_PATH, ['jpg', 'jpeg', 'png', 'webp']);
                            if ($upload_result['success']) {
                                $stmt = $pdo->prepare("INSERT INTO product_screenshots (product_id, image_path) VALUES (?, ?)");
                                $stmt->execute([$product_id, $upload_result['filename']]);
                            }
                        }
                    }
                }
                
                $success = 'Product added successfully!';
                header("Location: " . SITE_URL . "/admin/products.php");
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to add product: ' . $e->getMessage();
        }
    }
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .section-title {
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #2e7d32;
    }
    
    .image-preview {
        width: 150px;
        height: 150px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-top: 0.5rem;
    }
    
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Add New Product</h2>
            <p class="text-secondary mb-0">Create a new product listing</p>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/products.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Products
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

    <form method="POST" enctype="multipart/form-data">
        <!-- Basic Information -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-info-circle me-2"></i>Basic Information
            </h5>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Product Title *</label>
                    <input type="text" class="form-control" name="title" id="productTitle" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Category *</label>
                    <select class="form-select" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Slug *</label>
                    <input type="text" class="form-control" name="slug" id="productSlug" required>
                    <small class="text-secondary">URL-friendly version (auto-generated)</small>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea class="form-control" name="short_description" rows="2" maxlength="200"></textarea>
                    <small class="text-secondary">Brief description (max 200 characters)</small>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Full Description *</label>
                    <textarea class="form-control" name="description" rows="6" required></textarea>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Features (one per line)</label>
                    <textarea class="form-control" name="features" rows="5" placeholder="Feature 1&#10;Feature 2&#10;Feature 3"></textarea>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-rupee-sign me-2"></i>Pricing
            </h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Regular Price (₹) *</label>
                    <input type="number" class="form-control" name="price" step="0.01" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Discount Price (₹)</label>
                    <input type="number" class="form-control" name="discount_price" step="0.01">
                    <small class="text-secondary">Leave empty if no discount</small>
                </div>
            </div>
        </div>

        <!-- Media -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-images me-2"></i>Media
            </h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Main Product Image *</label>
                    <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'mainImagePreview')" required>
                    <div id="mainImagePreview" class="image-preview">
                        <i class="fas fa-image fa-3x text-secondary"></i>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Product File (ZIP, PDF, etc.)</label>
                    <input type="file" class="form-control" name="file">
                    <small class="text-secondary">Digital file for download after purchase</small>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Additional Screenshots</label>
                    <input type="file" class="form-control" name="screenshots[]" accept="image/*" multiple>
                    <small class="text-secondary">Select multiple images (optional)</small>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">Demo URL</label>
                    <input type="url" class="form-control" name="demo_url" placeholder="https://example.com/demo">
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-cog me-2"></i>Settings
            </h5>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured">
                <label class="form-check-label" for="isFeatured">
                    <strong>Featured Product</strong>
                    <small class="d-block text-secondary">Display on homepage</small>
                </label>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                <label class="form-check-label" for="isActive">
                    <strong>Active</strong>
                    <small class="d-block text-secondary">Product is visible to customers</small>
                </label>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save me-2"></i>Add Product
            </button>
        </div>
    </form>
</div>

<script>
// Auto-generate slug from title
document.getElementById('productTitle').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
    document.getElementById('productSlug').value = slug;
});

// Image preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
