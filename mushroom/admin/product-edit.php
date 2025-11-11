<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Edit Product';
$success = '';
$error = '';

$pdo = getPDOConnection();

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header("Location: products.php");
    exit;
}

// Fetch product data
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit;
}

// Fetch product screenshots
$screenshots_stmt = $pdo->prepare("SELECT * FROM product_screenshots WHERE product_id = ? ORDER BY display_order");
$screenshots_stmt->execute([$product_id]);
$screenshots = $screenshots_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Delete old image if exists
        if ($image && file_exists(PRODUCT_IMAGE_PATH . '/' . $image)) {
            unlink(PRODUCT_IMAGE_PATH . '/' . $image);
        }
        
        $upload_result = uploadFile($_FILES['image'], PRODUCT_IMAGE_PATH, ['jpg', 'jpeg', 'png', 'webp']);
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        } else {
            $error = $upload_result['error'];
        }
    }
    
    // Handle product file upload
    $file_path = $product['file_path'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Delete old file if exists
        if ($file_path && file_exists(PRODUCT_FILE_PATH . '/' . $file_path)) {
            unlink(PRODUCT_FILE_PATH . '/' . $file_path);
        }
        
        $upload_result = uploadFile($_FILES['file'], PRODUCT_FILE_PATH, ['zip', 'pdf', 'jpg', 'jpeg', 'png']);
        if ($upload_result['success']) {
            $file_path = $upload_result['filename'];
        }
    }
    
    if (!$error) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET 
                title = ?, 
                slug = ?, 
                description = ?, 
                short_description = ?, 
                price = ?, 
                discount_price = ?, 
                category_id = ?, 
                image = ?, 
                file_path = ?, 
                features = ?, 
                demo_url = ?, 
                is_featured = ?, 
                is_active = ?,
                updated_at = NOW()
                WHERE id = ?");
            
            if ($stmt->execute([
                $title, $slug, $description, $short_description, $price, $discount_price, 
                $category_id, $image, $file_path, $features, $demo_url, $is_featured, $is_active, $product_id
            ])) {
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
                
                // Handle screenshot deletion
                if (!empty($_POST['delete_screenshots'])) {
                    $delete_ids = array_map('intval', $_POST['delete_screenshots']);
                    $placeholders = rtrim(str_repeat('?,', count($delete_ids)), ',');
                    
                    // Get file paths before deletion
                    $stmt = $pdo->prepare("SELECT image_path FROM product_screenshots WHERE id IN ($placeholders) AND product_id = ?");
                    $stmt->execute(array_merge($delete_ids, [$product_id]));
                    $screenshots_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Delete files
                    foreach ($screenshots_to_delete as $screenshot) {
                        if (file_exists(PRODUCT_IMAGE_PATH . '/' . $screenshot)) {
                            unlink(PRODUCT_IMAGE_PATH . '/' . $screenshot);
                        }
                    }
                    
                    // Delete from database
                    $stmt = $pdo->prepare("DELETE FROM product_screenshots WHERE id IN ($placeholders) AND product_id = ?");
                    $stmt->execute(array_merge($delete_ids, [$product_id]));
                }
                
                $success = 'Product updated successfully!';
                // Refresh product data
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Refresh screenshots
                $screenshots_stmt = $pdo->prepare("SELECT * FROM product_screenshots WHERE product_id = ? ORDER BY display_order");
                $screenshots_stmt->execute([$product_id]);
                $screenshots = $screenshots_stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            $error = 'Failed to update product: ' . $e->getMessage();
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
        color: #4a5568;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .image-preview {
        width: 100%;
        height: 200px;
        object-fit: contain;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .screenshot-preview {
        position: relative;
        margin-bottom: 1rem;
    }
    
    .screenshot-preview img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    
    .screenshot-actions {
        position: absolute;
        top: 5px;
        right: 5px;
        display: flex;
        gap: 5px;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .file-upload {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    
    .file-upload-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-upload-label {
        display: inline-block;
        padding: 0.5rem 1rem;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .file-upload-label:hover {
        background-color: #e9ecef;
    }
    
    @media (max-width: 768px) {
        .form-section {
            padding: 1rem;
        }
        
        .col-md-6, .col-lg-4 {
            margin-bottom: 1rem;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Product</h2>
            <p class="text-secondary mb-0">Update product details and media</p>
        </div>
        <div>
            <a href="products.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Products
            </a>
            <a href="product-add.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New
            </a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- Basic Information -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-info-circle me-2"></i>Basic Information
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Product Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($product['title']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="slug" class="form-label">URL Slug <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo SITE_URL; ?>/product/</span>
                            <input type="text" class="form-control" id="slug" name="slug" 
                                   value="<?php echo htmlspecialchars($product['slug']); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="short_description" class="form-label">Short Description</label>
                <textarea class="form-control" id="short_description" name="short_description" rows="2"><?php echo htmlspecialchars($product['short_description']); ?></textarea>
                <div class="form-text">A brief description shown in product listings (max 200 characters)</div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Full Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="features" class="form-label">Key Features</label>
                <textarea class="form-control" id="features" name="features" rows="3"><?php echo htmlspecialchars($product['features']); ?></textarea>
                <div class="form-text">Enter each feature on a new line</div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="demo_url" class="form-label">Demo URL</label>
                        <input type="url" class="form-control" id="demo_url" name="demo_url" 
                               value="<?php echo htmlspecialchars($product['demo_url']); ?>">
                        <div class="form-text">Link to live demo (if available)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-tag me-2"></i>Pricing
            </h5>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="price" name="price" 
                                   step="0.01" min="0" value="<?php echo number_format($product['price'], 2, '.', ''); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="discount_price" class="form-label">Discounted Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="discount_price" name="discount_price" 
                                   step="0.01" min="0" value="<?php echo $product['discount_price'] ? number_format($product['discount_price'], 2, '.', '') : ''; ?>">
                        </div>
                        <div class="form-text">Leave empty if no discount</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-images me-2"></i>Media
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label">Main Product Image <span class="text-danger">*</span></label>
                        <div class="text-center mb-3">
                            <div id="imagePreviewContainer" class="mb-3">
                                <?php 
                                $imagePath = '';
                                if ($product['image'] && file_exists(PRODUCT_IMAGE_PATH . '/' . $product['image'])) {
                                    $imagePath = SITE_URL . '/uploads/products/' . $product['image'];
                                    echo '<img src="' . $imagePath . '" ';
                                    echo 'class="img-fluid rounded mb-2" id="imagePreview" ';
                                    echo 'style="max-height: 200px; max-width: 100%; display: block;" ';
                                    echo 'alt="Product Image">';
                                } else {
                                    // Inline SVG as a data URL for the placeholder
                                    $svgPlaceholder = 'data:image/svg+xml;base64,' . base64_encode('<?xml version="1.0" encoding="UTF-8"?><svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f8f9fa"/><text x="50%" y="50%" font-family="Arial" font-size="16" text-anchor="middle" dominant-baseline="middle" fill="#6c757d">No Image Available</text><rect width="100%" height="100%" fill="none" stroke="#dee2e6" stroke-width="2" stroke-dasharray="5,5"/></svg>');
                                    
                                    echo '<img src="' . $svgPlaceholder . '" ';
                                    echo 'class="img-fluid rounded mb-2" id="imagePreview" ';
                                    echo 'style="max-height: 200px; max-width: 100%; display: block;" ';
                                    echo 'alt="No Image">';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="file-upload w-100">
                            <label for="image" class="file-upload-label w-100 text-center">
                                <i class="fas fa-upload me-2"></i>Choose Image
                                <input type="file" class="file-upload-input" id="image" name="image" 
                                       accept="image/*" onchange="console.log('File input changed:', this.files[0]); previewImage(this, 'imagePreview');">
                            </label>
                            <div class="form-text">Recommended size: 800x800px, JPG/PNG/WEBP format</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-4">
                        <label class="form-label">Product File</label>
                        <div class="mb-3">
                            <?php if ($product['file_path'] && file_exists(PRODUCT_FILE_PATH . '/' . $product['file_path'])): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-file me-2"></i> 
                                    <?php echo htmlspecialchars($product['file_path']); ?>
                                    <span class="badge bg-secondary ms-2">
                                        <?php echo formatFileSize(filesize(PRODUCT_FILE_PATH . '/' . $product['file_path'])); ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i> No file uploaded
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="file-upload w-100">
                            <label for="file" class="file-upload-label w-100 text-center">
                                <i class="fas fa-upload me-2"></i>Upload File
                                <input type="file" class="file-upload-input" id="file" name="file">
                            </label>
                            <div class="form-text">ZIP, PDF, JPG, PNG files only (max 100MB)</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Screenshots -->
            <div class="mb-3">
                <label class="form-label">Screenshots</label>
                <div class="row" id="screenshotsContainer">
                    <?php foreach ($screenshots as $screenshot): ?>
                        <div class="col-6 col-md-4 col-lg-3 mb-3 screenshot-item" data-id="<?php echo $screenshot['id']; ?>">
                            <div class="screenshot-preview">
                                <img src="<?php echo SITE_URL . '/uploads/products/' . $screenshot['image_path']; ?>" 
                                     class="img-fluid rounded">
                                <div class="screenshot-actions">
                                    <button type="button" class="btn btn-danger btn-sm delete-screenshot" 
                                            data-id="<?php echo $screenshot['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="screenshot_ids[]" value="<?php echo $screenshot['id']; ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <input type="file" id="screenshots" name="screenshots[]" multiple 
                       accept="image/*" style="display: none;" onchange="previewScreenshots(this)">
                <input type="hidden" name="delete_screenshots[]" id="deleteScreenshots">
                
                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('screenshots').click()">
                    <i class="fas fa-plus me-1"></i> Add Screenshots
                </button>
            </div>
        </div>

        <!-- Settings -->
        <div class="form-section">
            <h5 class="section-title">
                <i class="fas fa-cog me-2"></i>Settings
            </h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                               value="1" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_featured">Featured Product</label>
                        <div class="form-text">Show this product in featured section</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                        <div class="form-text">Show this product on the website</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="products.php" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update Product
            </button>
        </div>
    </form>
</div>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const slug = this.value
        .toLowerCase()
        .replace(/[^\w\s-]/g, '') // Remove special chars
        .replace(/\s+/g, '-')      // Replace spaces with -
        .replace(/--+/g, '-')      // Replace multiple - with single -
        .trim();
    document.getElementById('slug').value = slug;
});

// Image preview with better error handling and logging
function previewImage(input, previewId) {
    console.log('Starting image preview...');
    
    // Basic validation
    if (!input || !input.files || input.files.length === 0) {
        console.error('No file selected or input is invalid');
        return;
    }
    
    const file = input.files[0];
    console.log('Processing file:', {
        name: file.name,
        type: file.type,
        size: file.size
    });
    
    // Check file type
    if (!file.type.match('image.*')) {
        alert('Please select an image file (JPEG, PNG, GIF, etc.)');
        return;
    }
    
    // Get or create preview elements
    const container = document.getElementById('imagePreviewContainer');
    if (!container) {
        console.error('Preview container not found');
        return;
    }
    
    // Hide the "no image" placeholder if it exists
    const noImagePreview = document.getElementById('noImagePreview');
    if (noImagePreview) {
        noImagePreview.style.display = 'none';
    }
    
    // Get or create the preview image element
    let preview = document.getElementById(previewId);
    if (!preview) {
        console.log('Creating new preview image element');
        preview = document.createElement('img');
        preview.id = previewId;
        preview.className = 'img-fluid rounded mb-2';
        preview.style.maxHeight = '200px';
        preview.style.maxWidth = '100%';
        preview.style.display = 'none'; // Start hidden
        container.insertBefore(preview, container.firstChild);
    }
    
    // Set up FileReader
    const reader = new FileReader();
    
    reader.onloadstart = function() {
        console.log('Starting to read file...');
    };
    
    reader.onload = function(e) {
        console.log('File read successfully, updating preview...');
        try {
            preview.src = e.target.result;
            preview.style.display = 'block';
            console.log('Preview updated successfully');
        } catch (error) {
            console.error('Error updating preview:', error);
            alert('Error updating image preview. Please try again.');
        }
    };
    
    reader.onerror = function(error) {
        console.error('FileReader error:', error);
        alert('Error reading the file. Please try another image.');
    };
    
    reader.onabort = function() {
        console.warn('File reading was aborted');
    };
    
    reader.onloadend = function() {
        console.log('File reading completed');
    };
    
    // Start reading the file
    try {
        reader.readAsDataURL(file);
    } catch (error) {
        console.error('Error reading file:', error);
        alert('Error processing the image. The file might be corrupted.');
    }
}

// Handle screenshot deletion
const deleteButtons = document.querySelectorAll('.delete-screenshot');
const deleteScreenshotsInput = document.getElementById('deleteScreenshots');
let deletedScreenshots = [];

deleteButtons.forEach(button => {
    button.addEventListener('click', function() {
        const screenshotId = this.getAttribute('data-id');
        const screenshotItem = this.closest('.screenshot-item');
        
        // Add to deleted screenshots array
        if (!deletedScreenshots.includes(screenshotId)) {
            deletedScreenshots.push(screenshotId);
            deleteScreenshotsInput.value = deletedScreenshots.join(',');
        }
        
        // Hide the screenshot item with animation
        screenshotItem.style.opacity = '0';
        setTimeout(() => {
            screenshotItem.style.display = 'none';
        }, 300);
    });
});

// Preview multiple screenshots
function previewScreenshots(input) {
    const container = document.getElementById('screenshotsContainer');
    const files = input.files;
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const randomId = 'screenshot-' + Math.random().toString(36).substr(2, 9);
            
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3 mb-3';
            
            col.innerHTML = `
                <div class="screenshot-preview">
                    <img src="${e.target.result}" class="img-fluid rounded">
                    <div class="screenshot-actions">
                        <button type="button" class="btn btn-danger btn-sm remove-screenshot">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            // Add remove functionality
            const removeBtn = col.querySelector('.remove-screenshot');
            removeBtn.addEventListener('click', function() {
                col.remove();
            });
            
            container.appendChild(col);
        };
        
        reader.readAsDataURL(file);
    }
    
    // Reset the input to allow selecting the same file again
    input.value = '';
}

// Initialize any rich text editors
if (typeof CKEDITOR !== 'undefined') {
    CKEDITOR.replace('description');
    CKEDITOR.replace('features');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
