<?php
require_once __DIR__ . '/config/config.php';

requireLogin();

$page_title = 'My Profile';
$user = getUser();
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $pincode = sanitize($_POST['pincode'] ?? '');
        
        if (empty($name)) {
            $error = 'Name is required';
        } else {
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, phone = ?, address = ?, city = ?, state = ?, pincode = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$name, $phone, $address, $city, $state, $pincode, $_SESSION['user_id']])) {
                $success = 'Profile updated successfully';
                $_SESSION['user_name'] = $name;
                $user = getUser();
            } else {
                $error = 'Failed to update profile';
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All password fields are required';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            if (password_verify($current_password, $user['password'])) {
                $pdo = getPDOConnection();
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                    $success = 'Password changed successfully';
                } else {
                    $error = 'Failed to change password';
                }
            } else {
                $error = 'Current password is incorrect';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
    .profile-container {
        padding: 2rem 0;
    }

    .profile-sidebar {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--shadow);
        position: sticky;
        top: 100px;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
        margin: 0 auto 1rem;
        font-weight: 700;
    }

    .profile-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .profile-menu li {
        margin-bottom: 0.5rem;
    }

    .profile-menu a {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        color: var(--text-color);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .profile-menu a:hover, .profile-menu a.active {
        background: var(--primary-color);
        color: white;
    }

    .profile-menu i {
        margin-right: 0.75rem;
        width: 20px;
    }

    .profile-content {
        background: var(--surface-color);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: var(--shadow);
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-color);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--bg-color);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid var(--border-color);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .profile-sidebar {
            position: static;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }
    }
</style>

<div class="container profile-container">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="profile-sidebar">
                <div class="text-center mb-4">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                    <small class="text-secondary"><?php echo htmlspecialchars($user['email']); ?></small>
                </div>

                <ul class="profile-menu">
                    <li>
                        <a href="#profile" class="active" onclick="showTab('profile')">
                            <i class="fas fa-user"></i>
                            <span>Profile Info</span>
                        </a>
                    </li>
                    <li>
                        <a href="#security" onclick="showTab('security')">
                            <i class="fas fa-lock"></i>
                            <span>Security</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/orders.php">
                            <i class="fas fa-shopping-bag"></i>
                            <span>My Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Content -->
        <div class="col-lg-9">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Tab -->
            <div id="profile-tab" class="profile-content">
                <h4 class="section-title">
                    <i class="fas fa-user me-2"></i>Profile Information
                </h4>

                <?php
                // Get user stats
                $pdo = getPDOConnection();
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $total_orders = $stmt->fetch()['count'];

                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND payment_status = 'completed'");
                $stmt->execute([$_SESSION['user_id']]);
                $completed_orders = $stmt->fetch()['count'];

                $stmt = $pdo->prepare("SELECT SUM(total) as total FROM orders WHERE user_id = ? AND payment_status = 'completed'");
                $stmt->execute([$_SESSION['user_id']]);
                $total_spent = $stmt->fetch()['total'] ?? 0;
                ?>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_orders; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $completed_orders; ?></div>
                        <div class="stat-label">Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo formatPrice($total_spent); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                </div>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="text-secondary">Email cannot be changed</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode" 
                                   value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" 
                                   value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state" 
                                   value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </form>
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="profile-content" style="display: none;">
                <h4 class="section-title">
                    <i class="fas fa-lock me-2"></i>Change Password
                </h4>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label">Current Password *</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" class="form-control" name="new_password" 
                               minlength="6" required>
                        <small class="text-secondary">Minimum 6 characters</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" name="confirm_password" 
                               minlength="6" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>

                <hr class="my-4">

                <div class="alert alert-info">
                    <h6 class="fw-bold mb-2">
                        <i class="fas fa-shield-alt me-2"></i>Security Tips
                    </h6>
                    <ul class="mb-0">
                        <li>Use a strong password with letters, numbers, and symbols</li>
                        <li>Don't share your password with anyone</li>
                        <li>Change your password regularly</li>
                        <li>Enable two-factor authentication when available</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    // Hide all tabs
    document.getElementById('profile-tab').style.display = 'none';
    document.getElementById('security-tab').style.display = 'none';
    
    // Remove active class from all menu items
    document.querySelectorAll('.profile-menu a').forEach(a => {
        a.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tab + '-tab').style.display = 'block';
    
    // Add active class to clicked menu item
    event.target.closest('a').classList.add('active');
    
    // Prevent default link behavior
    event.preventDefault();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
