<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Categories';
$success = '';
$error = '';

$pdo = getPDOConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    
    if ($category && $category['image']) {
        deleteFile(CATEGORY_IMAGE_PATH . '/' . $category['image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Category deleted successfully';
    } else {
        $error = 'Failed to delete category';
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']);
    $description = sanitize($_POST['description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image'], CATEGORY_IMAGE_PATH, ['jpg', 'jpeg', 'png', 'webp']);
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        }
    }
    
    if ($id > 0) {
        // Update
        $query = "UPDATE categories SET name = ?, slug = ?, description = ?, is_active = ?";
        $params = [$name, $slug, $description, $is_active];
        
        if ($image) {
            $query .= ", image = ?";
            $params[] = $image;
        }
        
        $query .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            $success = 'Category updated successfully';
        }
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image, is_active) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $slug, $description, $image, $is_active])) {
            $success = 'Category added successfully';
        }
    }
}

// Get all categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .category-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }
    
    .category-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .category-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Categories</h2>
            <p class="text-secondary mb-0">Manage product categories</p>
        </div>
        <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#categoryModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i>Add Category
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-4">
                <div class="category-card">
                    <?php if ($category['image']): ?>
                        <img src="<?php echo CATEGORY_IMAGE_URL . '/' . $category['image']; ?>" 
                             class="category-image" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <?php else: ?>
                        <div class="category-image bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image fa-3x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h5>
                    <p class="text-secondary small mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge <?php echo $category['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?php echo $category['id']; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Delete this category?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="categoryId">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="categoryName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" class="form-control" name="slug" id="categorySlug" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="categoryDescription" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="categoryActive" checked>
                        <label class="form-check-label" for="categoryActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categorySlug').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('categoryActive').checked = true;
}

function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categorySlug').value = category.slug;
    document.getElementById('categoryDescription').value = category.description;
    document.getElementById('categoryActive').checked = category.is_active == 1;
    
    new mdb.Modal(document.getElementById('categoryModal')).show();
}

// Auto-generate slug
document.getElementById('categoryName').addEventListener('input', function() {
    if (!document.getElementById('categoryId').value) {
        const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        document.getElementById('categorySlug').value = slug;
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
