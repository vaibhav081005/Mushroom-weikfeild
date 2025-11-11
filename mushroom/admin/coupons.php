<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Coupons';
$success = '';
$error = '';

$pdo = getPDOConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Coupon deleted successfully';
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $code = strtoupper(sanitize($_POST['code']));
    $type = sanitize($_POST['type']);
    $value = floatval($_POST['value']);
    $min_purchase = floatval($_POST['min_purchase'] ?? 0);
    $max_discount = floatval($_POST['max_discount'] ?? 0);
    $usage_limit = intval($_POST['usage_limit'] ?? 0);
    $expiry_date = sanitize($_POST['expiry_date']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE coupons SET code = ?, type = ?, value = ?, min_purchase = ?, max_discount = ?, usage_limit = ?, expiry_date = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$code, $type, $value, $min_purchase, $max_discount, $usage_limit, $expiry_date, $is_active, $id])) {
            $success = 'Coupon updated successfully';
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_purchase, max_discount, usage_limit, expiry_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$code, $type, $value, $min_purchase, $max_discount, $usage_limit, $expiry_date, $is_active])) {
            $success = 'Coupon created successfully';
        }
    }
}

// Get all coupons
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .coupon-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .coupon-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    }
    
    .coupon-code {
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: 2px;
        border: 2px dashed white;
        padding: 0.5rem 1rem;
        display: inline-block;
        border-radius: 8px;
        background: rgba(255,255,255,0.1);
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Coupons</h2>
            <p class="text-secondary mb-0">Manage discount coupons</p>
        </div>
        <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#couponModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i>Create Coupon
        </button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($coupons as $coupon): ?>
            <?php
            $is_expired = strtotime($coupon['expiry_date']) < time();
            $usage_percent = $coupon['usage_limit'] > 0 ? ($coupon['used_count'] / $coupon['usage_limit']) * 100 : 0;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="coupon-card">
                        <div class="position-relative">
                            <div class="coupon-code mb-3"><?php echo htmlspecialchars($coupon['code']); ?></div>
                            <div class="h4 mb-2">
                                <?php if ($coupon['type'] === 'percentage'): ?>
                                    <?php echo $coupon['value']; ?>% OFF
                                <?php else: ?>
                                    <?php echo formatPrice($coupon['value']); ?> OFF
                                <?php endif; ?>
                            </div>
                            <?php if ($coupon['min_purchase'] > 0): ?>
                                <div class="small opacity-75">Min. purchase: <?php echo formatPrice($coupon['min_purchase']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Status:</span>
                            <span class="badge <?php echo $coupon['is_active'] && !$is_expired ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $is_expired ? 'Expired' : ($coupon['is_active'] ? 'Active' : 'Inactive'); ?>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Expires:</span>
                            <span><?php echo date('M d, Y', strtotime($coupon['expiry_date'])); ?></span>
                        </div>
                        
                        <?php if ($coupon['usage_limit'] > 0): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary small">Usage:</span>
                                    <span class="small"><?php echo $coupon['used_count']; ?> / <?php echo $coupon['usage_limit']; ?></span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: <?php echo $usage_percent; ?>%"></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <span class="text-secondary small">Used: <?php echo $coupon['used_count']; ?> times</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill" 
                                    onclick="editCoupon(<?php echo htmlspecialchars(json_encode($coupon)); ?>)">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <a href="?delete=<?php echo $coupon['id']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this coupon?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($coupons)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt fa-4x text-secondary mb-3"></i>
                    <h5>No coupons yet</h5>
                    <p class="text-secondary">Create your first discount coupon</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Coupon Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create Coupon</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="couponId">
                    
                    <div class="mb-3">
                        <label class="form-label">Coupon Code</label>
                        <input type="text" class="form-control text-uppercase" name="code" id="couponCode" required>
                        <small class="text-secondary">Use uppercase letters and numbers</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" id="couponType" required>
                                <option value="percentage">Percentage (%)</option>
                                <option value="flat">Flat Amount (₹)</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Value</label>
                            <input type="number" class="form-control" name="value" id="couponValue" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Min Purchase (₹)</label>
                            <input type="number" class="form-control" name="min_purchase" id="minPurchase" step="0.01" value="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Discount (₹)</label>
                            <input type="number" class="form-control" name="max_discount" id="maxDiscount" step="0.01" value="0">
                            <small class="text-secondary">0 = unlimited</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" class="form-control" name="usage_limit" id="usageLimit" value="0">
                            <small class="text-secondary">0 = unlimited</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" name="expiry_date" id="expiryDate" required>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="couponActive" checked>
                        <label class="form-check-label" for="couponActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Create Coupon';
    document.getElementById('couponId').value = '';
    document.getElementById('couponCode').value = '';
    document.getElementById('couponType').value = 'percentage';
    document.getElementById('couponValue').value = '';
    document.getElementById('minPurchase').value = '0';
    document.getElementById('maxDiscount').value = '0';
    document.getElementById('usageLimit').value = '0';
    document.getElementById('expiryDate').value = '';
    document.getElementById('couponActive').checked = true;
}

function editCoupon(coupon) {
    document.getElementById('modalTitle').textContent = 'Edit Coupon';
    document.getElementById('couponId').value = coupon.id;
    document.getElementById('couponCode').value = coupon.code;
    document.getElementById('couponType').value = coupon.type;
    document.getElementById('couponValue').value = coupon.value;
    document.getElementById('minPurchase').value = coupon.min_purchase;
    document.getElementById('maxDiscount').value = coupon.max_discount;
    document.getElementById('usageLimit').value = coupon.usage_limit;
    document.getElementById('expiryDate').value = coupon.expiry_date;
    document.getElementById('couponActive').checked = coupon.is_active == 1;
    
    new mdb.Modal(document.getElementById('couponModal')).show();
}

// Set minimum date to today
document.getElementById('expiryDate').min = new Date().toISOString().split('T')[0];
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
