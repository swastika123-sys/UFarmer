<?php
$pageTitle = 'Analytics Dashboard';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a farmer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL);
    exit();
}

$farmer = getFarmerByUserId($_SESSION['user_id']);

// If no farmer profile exists, redirect to setup
if (!$farmer) {
    header('Location: setup.php');
    exit();
}

global $pdo;

// Get date range (default to last 30 days)
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
$start_date = date('Y-m-d', strtotime("-{$days} days"));
$end_date = date('Y-m-d');

// Sales Analytics
$salesStmt = $pdo->prepare("
    SELECT 
        DATE(o.created_at) as order_date,
        COUNT(*) as orders_count,
        SUM(o.total_amount) as daily_revenue,
        AVG(o.total_amount) as avg_order_value
    FROM orders o 
    WHERE o.farmer_id = ? 
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY DATE(o.created_at)
    ORDER BY order_date ASC
");
$salesStmt->execute([$farmer['id'], $start_date, $end_date]);
$dailySales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

// Overall Stats
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        MIN(total_amount) as min_order,
        MAX(total_amount) as max_order
    FROM orders 
    WHERE farmer_id = ? 
    AND DATE(created_at) BETWEEN ? AND ?
");
$statsStmt->execute([$farmer['id'], $start_date, $end_date]);
$overallStats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Product Performance
$productStmt = $pdo->prepare("
    SELECT 
        p.name,
        p.image,
        SUM(oi.quantity) as total_sold,
        SUM(oi.price * oi.quantity) as revenue,
        COUNT(DISTINCT oi.order_id) as orders_count,
        AVG(oi.price) as avg_price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.farmer_id = ?
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id, p.name, p.image
    ORDER BY total_sold DESC
    LIMIT 10
");
$productStmt->execute([$farmer['id'], $start_date, $end_date]);
$topProducts = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Customer Analytics
$customerStmt = $pdo->prepare("
    SELECT 
        u.name as customer_name,
        u.email,
        COUNT(*) as orders_count,
        SUM(o.total_amount) as total_spent,
        MAX(o.created_at) as last_order
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    WHERE o.farmer_id = ?
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY u.id, u.name, u.email
    ORDER BY total_spent DESC
    LIMIT 10
");
$customerStmt->execute([$farmer['id'], $start_date, $end_date]);
$topCustomers = $customerStmt->fetchAll(PDO::FETCH_ASSOC);

// Order Status Distribution
$statusStmt = $pdo->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE farmer_id = ?
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
");
$statusStmt->execute([$farmer['id'], $start_date, $end_date]);
$statusDistribution = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare chart data
$chartLabels = [];
$chartRevenue = [];
$chartOrders = [];

foreach ($dailySales as $sale) {
    $chartLabels[] = date('M j', strtotime($sale['order_date']));
    $chartRevenue[] = $sale['daily_revenue'];
    $chartOrders[] = $sale['orders_count'];
}

include '../../components/header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="dashboard.php" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <div>
                <h2 class="mb-0">📊 Analytics Dashboard</h2>
                <p class="text-muted mb-0">Track your farm's performance and sales data</p>
            </div>
        </div>
        
        <!-- Date Range Filter -->
        <div class="date-filter">
            <select class="form-select" onchange="window.location.href='?days='+this.value">
                <option value="7" <?php echo $days === 7 ? 'selected' : ''; ?>>Last 7 days</option>
                <option value="30" <?php echo $days === 30 ? 'selected' : ''; ?>>Last 30 days</option>
                <option value="90" <?php echo $days === 90 ? 'selected' : ''; ?>>Last 3 months</option>
                <option value="365" <?php echo $days === 365 ? 'selected' : ''; ?>>Last year</option>
            </select>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-content">
                    <h3><?php echo $overallStats['total_orders'] ?: 0; ?></h3>
                    <p>Total Orders</p>
                    <small class="text-muted">Last <?php echo $days; ?> days</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon revenue">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stats-content">
                    <h3><?php echo formatPrice($overallStats['total_revenue'] ?: 0); ?></h3>
                    <p>Total Revenue</p>
                    <small class="text-muted">Last <?php echo $days; ?> days</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon avg">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stats-content">
                    <h3><?php echo formatPrice($overallStats['avg_order_value'] ?: 0); ?></h3>
                    <p>Avg Order Value</p>
                    <small class="text-muted">Per order</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon rating">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stats-content">
                    <h3><?php echo number_format($farmer['rating'], 1); ?></h3>
                    <p>Farm Rating</p>
                    <small class="text-muted"><?php echo $farmer['total_reviews']; ?> reviews</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-area"></i> Sales Trend</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($dailySales)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h6>No sales data available</h6>
                            <p class="text-muted">Start selling products to see analytics here</p>
                        </div>
                    <?php else: ?>
                        <canvas id="salesChart" height="100"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Status Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-pie-chart"></i> Order Status</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($statusDistribution)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No orders yet</p>
                        </div>
                    <?php else: ?>
                        <div class="status-list">
                            <?php foreach ($statusDistribution as $status): ?>
                                <div class="status-item">
                                    <div class="status-info">
                                        <span class="status-label"><?php echo ucfirst($status['status']); ?></span>
                                        <span class="status-count"><?php echo $status['count']; ?> orders</span>
                                    </div>
                                    <div class="status-revenue">
                                        <?php echo formatPrice($status['revenue']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Products -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-trophy"></i> Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($topProducts)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-seedling fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No product sales yet</p>
                        </div>
                    <?php else: ?>
                        <div class="product-list">
                            <?php foreach ($topProducts as $index => $product): ?>
                                <div class="product-item">
                                    <div class="product-rank">#<?php echo $index + 1; ?></div>
                                    <img src="<?php echo $product['image'] ? UPLOAD_URL . $product['image'] : SITE_URL . '/assets/images/default-product.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="product-thumb">
                                    <div class="product-info">
                                        <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <div class="product-stats">
                                            <span><?php echo $product['total_sold']; ?> kg sold</span>
                                            <span><?php echo formatPrice($product['revenue']); ?> revenue</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> Top Customers</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($topCustomers)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-user-friends fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No customer data yet</p>
                        </div>
                    <?php else: ?>
                        <div class="customer-list">
                            <?php foreach ($topCustomers as $index => $customer): ?>
                                <div class="customer-item">
                                    <div class="customer-avatar">
                                        <?php echo strtoupper(substr($customer['customer_name'], 0, 2)); ?>
                                    </div>
                                    <div class="customer-info">
                                        <h6><?php echo htmlspecialchars($customer['customer_name']); ?></h6>
                                        <div class="customer-stats">
                                            <span><?php echo $customer['orders_count']; ?> orders</span>
                                            <span>Last: <?php echo timeAgo($customer['last_order']); ?></span>
                                        </div>
                                    </div>
                                    <div class="customer-spent">
                                        <?php echo formatPrice($customer['total_spent']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    height: 100%;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-right: 1rem;
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
}

.stats-icon.revenue {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.stats-icon.avg {
    background: linear-gradient(135deg, #17a2b8, #6f42c1);
}

.stats-icon.rating {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
}

.stats-content h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark-green);
}

.stats-content p {
    margin: 0;
    color: var(--gray-medium);
    font-weight: 500;
}

.date-filter .form-select {
    min-width: 150px;
}

.card-header {
    background: linear-gradient(135deg, var(--light-green), rgba(76, 175, 80, 0.1));
    border-bottom: 1px solid #eee;
}

.card-header h5 {
    margin: 0;
    color: var(--dark-green);
}

.status-item, .product-item, .customer-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.status-item:last-child, .product-item:last-child, .customer-item:last-child {
    border-bottom: none;
}

.status-info {
    flex: 1;
}

.status-label {
    display: block;
    font-weight: 600;
    color: var(--dark-green);
}

.status-count {
    color: var(--gray-medium);
    font-size: 0.9rem;
}

.status-revenue {
    font-weight: 600;
    color: var(--success);
}

.product-rank {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--primary-green);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 1rem;
}

.product-thumb {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 1rem;
}

.product-info {
    flex: 1;
}

.product-info h6 {
    margin: 0 0 0.25rem 0;
    color: var(--dark-green);
}

.product-stats span {
    display: block;
    color: var(--gray-medium);
    font-size: 0.9rem;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-green);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 1rem;
}

.customer-info {
    flex: 1;
}

.customer-info h6 {
    margin: 0 0 0.25rem 0;
    color: var(--dark-green);
}

.customer-stats span {
    display: block;
    color: var(--gray-medium);
    font-size: 0.9rem;
}

.customer-spent {
    font-weight: 600;
    color: var(--success);
}

@media (max-width: 768px) {
    .d-flex.align-items-center.justify-content-between {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .date-filter {
        width: 100%;
    }
    
    .date-filter .form-select {
        width: 100%;
    }
    
    .stats-card {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
}
</style>

<?php if (!empty($dailySales)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?php echo json_encode($chartRevenue); ?>,
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }, {
            label: 'Orders',
            data: <?php echo json_encode($chartOrders); ?>,
            borderColor: '#2196F3',
            backgroundColor: 'rgba(33, 150, 243, 0.1)',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (₹)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Orders'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Sales Performance Over Time'
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include '../../components/footer.php'; ?>
