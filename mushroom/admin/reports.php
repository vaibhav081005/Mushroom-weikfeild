<?php
require_once __DIR__ . '/../config/config.php';

requireAdmin();

$page_title = 'Reports';

$pdo = getPDOConnection();

// Get date range
$start_date = sanitize($_GET['start_date'] ?? date('Y-m-01'));
$end_date = sanitize($_GET['end_date'] ?? date('Y-m-d'));

// Sales Overview
$sales_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN payment_status = 'completed' THEN total ELSE 0 END) as total_revenue,
    SUM(CASE WHEN payment_status = 'completed' THEN tax ELSE 0 END) as total_tax,
    AVG(CASE WHEN payment_status = 'completed' THEN total ELSE NULL END) as avg_order_value
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?";

$stmt = $pdo->prepare($sales_query);
$stmt->execute([$start_date, $end_date]);
$sales_stats = $stmt->fetch();

// Top Products
$top_products = $pdo->prepare("SELECT p.title, p.price, COUNT(oi.id) as sales_count, SUM(oi.price * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.payment_status = 'completed' AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY oi.product_id
    ORDER BY sales_count DESC
    LIMIT 10");
$top_products->execute([$start_date, $end_date]);
$top_products = $top_products->fetchAll();

// Daily Sales
$daily_sales = $pdo->prepare("SELECT DATE(created_at) as date, 
    COUNT(*) as orders,
    SUM(CASE WHEN payment_status = 'completed' THEN total ELSE 0 END) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC");
$daily_sales->execute([$start_date, $end_date]);
$daily_sales = $daily_sales->fetchAll();

// Payment Methods
$payment_methods = $pdo->prepare("SELECT payment_method, 
    COUNT(*) as count,
    SUM(CASE WHEN payment_status = 'completed' THEN total ELSE 0 END) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY payment_method");
$payment_methods->execute([$start_date, $end_date]);
$payment_methods = $payment_methods->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<style>
    .report-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        height: 100%;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Reports & Analytics</h2>
            <p class="text-secondary mb-0">Sales and performance reports</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filter
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="report-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">Total Orders</div>
                        <div class="h4 fw-bold mb-0"><?php echo number_format($sales_stats['total_orders']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="report-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">Completed</div>
                        <div class="h4 fw-bold mb-0"><?php echo number_format($sales_stats['completed_orders']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="report-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">Total Revenue</div>
                        <div class="h4 fw-bold mb-0"><?php echo formatPrice($sales_stats['total_revenue']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="report-card">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="text-secondary small">Avg Order Value</div>
                        <div class="h4 fw-bold mb-0"><?php echo formatPrice($sales_stats['avg_order_value']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top Products -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Top Selling Products</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sales</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                                        <td><?php echo number_format($product['sales_count']); ?></td>
                                        <td class="fw-bold"><?php echo formatPrice($product['revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Payment Methods</h5>
                    <?php foreach ($payment_methods as $method): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-capitalize"><?php echo htmlspecialchars($method['payment_method']); ?></span>
                                <span class="fw-bold"><?php echo formatPrice($method['revenue']); ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: <?php echo ($method['revenue'] / $sales_stats['total_revenue']) * 100; ?>%"></div>
                            </div>
                            <small class="text-secondary"><?php echo $method['count']; ?> orders</small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Daily Sales -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Daily Sales</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($daily_sales as $day): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($day['date'])); ?></td>
                                        <td><?php echo number_format($day['orders']); ?></td>
                                        <td class="fw-bold"><?php echo formatPrice($day['revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
