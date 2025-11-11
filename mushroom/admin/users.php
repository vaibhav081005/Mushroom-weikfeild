<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Users';
$success = '';
$error = '';

$pdo = getPDOConnection();

// Handle block/unblock
if (isset($_GET['toggle_block'])) {
    $id = intval($_GET['toggle_block']);
    $stmt = $pdo->prepare("UPDATE users SET is_blocked = NOT is_blocked WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'User status updated successfully';
    }
}

// Get filters
$search = sanitize($_GET['search'] ?? '');
$status = sanitize($_GET['status'] ?? '');

// Get users
$query = "SELECT u.*, 
          COUNT(DISTINCT o.id) as total_orders,
          SUM(CASE WHEN o.payment_status = 'completed' THEN o.total ELSE 0 END) as total_spent
          FROM users u
          LEFT JOIN orders o ON u.id = o.user_id
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status === 'blocked') {
    $query .= " AND u.is_blocked = 1";
} elseif ($status === 'active') {
    $query .= " AND u.is_blocked = 0";
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get statistics
$stats = $pdo->query("SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN is_blocked = 0 THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked_users
    FROM users")->fetch();

include __DIR__ . '/includes/header.php';
?>

<style>
    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2e7d32, #66bb6a);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Users</h2>
            <p class="text-secondary mb-0">Manage registered users</p>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary mb-2">Total Users</div>
                    <div class="h3 fw-bold"><?php echo number_format($stats['total_users']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary mb-2">Active Users</div>
                    <div class="h3 fw-bold text-success"><?php echo number_format($stats['active_users']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary mb-2">Blocked Users</div>
                    <div class="h3 fw-bold text-danger"><?php echo number_format($stats['blocked_users']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Users</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="blocked" <?php echo $status === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Search
                    </button>
                    <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                        </div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td><?php echo number_format($user['total_orders']); ?></td>
                                <td class="fw-bold"><?php echo formatPrice($user['total_spent']); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['is_blocked'] ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $user['is_blocked'] ? 'Blocked' : 'Active'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?toggle_block=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm <?php echo $user['is_blocked'] ? 'btn-success' : 'btn-warning'; ?>"
                                       onclick="return confirm('<?php echo $user['is_blocked'] ? 'Unblock' : 'Block'; ?> this user?')">
                                        <i class="fas fa-<?php echo $user['is_blocked'] ? 'check' : 'ban'; ?>"></i>
                                        <?php echo $user['is_blocked'] ? 'Unblock' : 'Block'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
