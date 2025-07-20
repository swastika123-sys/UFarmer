<?php
$pageTitle = 'Farmer Dashboard';
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

// Get farmer's products
$products = getFarmerProducts($farmer['id']);

// Get recent orders
global $pdo;
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email,
                       (SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as total_items
                       FROM orders o 
                       JOIN users u ON o.customer_id = u.id 
                       WHERE o.farmer_id = ? 
                       ORDER BY o.created_at DESC 
                       LIMIT 10");
$stmt->execute([$farmer['id']]);
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get dashboard stats
$stats = [
    'total_products' => count($products),
    'active_products' => count(array_filter($products, fn($p) => $p['is_active'])),
    'total_orders' => 0,
    'pending_orders' => 0,
    'total_revenue' => 0
];

$statsStmt = $pdo->prepare("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(total_amount) as total_revenue
    FROM orders WHERE farmer_id = ?");
$statsStmt->execute([$farmer['id']]);
$orderStats = $statsStmt->fetch(PDO::FETCH_ASSOC);

if ($orderStats) {
    $stats['total_orders'] = $orderStats['total_orders'];
    $stats['pending_orders'] = $orderStats['pending_orders'];
    $stats['total_revenue'] = $orderStats['total_revenue'] ?: 0;
}

$isWelcome = isset($_GET['welcome']);

include '../../components/header.php';
?>

<?php if ($isWelcome): ?>
<div class="alert alert-success alert-dismissible">
    <strong>Welcome to UFarmer!</strong> Your farmer profile has been created successfully. You can now start adding products and managing your farm.
    <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
</div>
<?php endif; ?>

<div class="dashboard-container">
    <div class="container py-4 mx-auto" style="max-width: 1200px; margin-left: auto; margin-right: auto; padding-left: 2rem; padding-right: 2rem;">
        <!-- Dashboard Header -->
        <div class="dashboard-header mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Welcome back, <?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'], ENT_QUOTES)); ?>! 🌱</h1>
                    <p class="lead text-muted">Manage your farm, products, and orders from your dashboard</p>
                </div>
                <div class="col-md-4 text-md-right text-center">
                    <a href="add-product.php" class="btn btn-success btn-lg">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid mb-4">
            <div class="stat-card card">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p>Total Products</p>
                        <small class="text-success"><?php echo $stats['active_products']; ?> active</small>
                    </div>
                </div>
            </div>
            
            <div class="stat-card card">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Orders</p>
                        <small class="text-warning"><?php echo $stats['pending_orders']; ?> pending</small>
                    </div>
                </div>
            </div>
            
            <div class="stat-card card">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo formatPrice($stats['total_revenue']); ?></h3>
                        <p>Total Revenue</p>
                        <small class="text-info">All time</small>
                    </div>
                </div>
            </div>
            
            <div class="stat-card card">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($farmer['rating'], 1); ?></h3>
                        <p>Rating</p>
                        <small class="text-success"><?php echo $farmer['total_reviews']; ?> reviews</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Orders -->
            <div class="col-md-6">
                <div class="card dashboard-section">
                    <div class="card-header">
                        <h5><i class="fas fa-clipboard-list"></i> Recent Orders</h5>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentOrders)): ?>
                            <div class="empty-state text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h6>No Orders Yet</h6>
                                <p class="text-muted">Orders will appear here when customers start buying your products.</p>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
                                    <div class="order-item">
                                        <div class="order-info">
                                            <h6>Order #<?php echo $order['id']; ?></h6>
                                            <p class="text-muted mb-1"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                            <small class="text-muted"><?php echo timeAgo($order['created_at']); ?></small>
                                        </div>
                                        <div class="order-details">
                                            <span class="order-amount"><?php echo formatPrice($order['total_amount']); ?></span>
                                            <span class="order-status status-<?php echo $order['status']; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Product Management -->
            <div class="col-md-6">
                <div class="card dashboard-section">
                    <div class="card-header">
                        <h5><i class="fas fa-seedling"></i> Your Products</h5>
                        <a href="add-product.php" class="btn btn-sm btn-success">Add Product</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($products)): ?>
                            <div class="empty-state text-center py-4">
                                <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                                <h6>No Products Yet</h6>
                                <p class="text-muted">Start by adding your first product to begin selling.</p>
                                <a href="add-product.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add Your First Product
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="products-list">
                                <?php foreach (array_slice($products, 0, 5) as $product): ?>
                                    <div class="product-item">
                                        <img src="<?php echo $product['image'] ? UPLOAD_URL . $product['image'] : SITE_URL . '/assets/images/default-product.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-thumb">
                                        <div class="product-info">
                                            <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                                            <p class="text-muted mb-1"><?php echo formatPrice($product['price']); ?> / <?php echo htmlspecialchars($product['unit']); ?></p>
                                            <small class="stock-info">Stock: <?php echo $product['stock_quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?></small>
                                        </div>
                                        <div class="product-actions">
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')">Delete</button>
                                            <span class="status-badge <?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($products) > 5): ?>
                                    <div class="text-center mt-3">
                                        <a href="products.php" class="btn btn-outline-primary">View All Products (<?php echo count($products); ?>)</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="add-product.php" class="quick-action-btn">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add Product</span>
                            </a>
                            <a href="discounts.php" class="quick-action-btn">
                                <i class="fas fa-tags"></i>
                                <span>Manage Discounts</span>
                            </a>
                            <a href="images.php" class="quick-action-btn">
                                <i class="fas fa-images"></i>
                                <span>Manage Images</span>
                            </a>
                            <a href="orders.php" class="quick-action-btn">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Manage Orders</span>
                            </a>
                            <a href="profile.php?id=<?php echo $farmer['id']; ?>" class="quick-action-btn">
                                <i class="fas fa-eye"></i>
                                <span>View Public Profile</span>
                            </a>
                            <a href="edit-profile.php" class="quick-action-btn">
                                <i class="fas fa-edit"></i>
                                <span>Edit Profile</span>
                            </a>
                            <a href="reviews.php" class="quick-action-btn">
                                <i class="fas fa-star"></i>
                                <span>Customer Reviews</span>
                            </a>
                            <a href="analytics.php" class="quick-action-btn">
                                <i class="fas fa-chart-bar"></i>
                                <span>View Analytics</span>
                            </a>
                            <a href="messages.php" class="quick-action-btn">
                                <i class="fas fa-envelope"></i>
                                <span>Messages</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    background-color: var(--gray-light);
    min-height: calc(100vh - 160px);
}

.dashboard-header h1 {
    color: var(--dark-green);
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card .card-body {
    display: flex;
    align-items: center;
    padding: 1.5rem;
}

.stat-icon {
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1rem;
}

.stat-info h3 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-green);
}

.stat-info p {
    margin: 0;
    color: var(--gray-medium);
    font-weight: 500;
}

.dashboard-section .card-header {
    background: var(--white);
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dashboard-section .card-header h5 {
    margin: 0;
    color: var(--dark-green);
}

.order-item, .product-item {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.order-item:last-child, .product-item:last-child {
    border-bottom: none;
}

.order-info, .product-info {
    flex: 1;
}

.order-info h6, .product-info h6 {
    margin: 0 0 0.25rem 0;
    color: var(--dark-green);
}

.order-details {
    text-align: right;
}

.order-amount {
    display: block;
    font-weight: 600;
    color: var(--success);
    margin-bottom: 0.25rem;
}

.order-status {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-preparing { background: #cce5ff; color: #004085; }
.status-ready { background: #d1ecf1; color: #0c5460; }
.status-delivered { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.product-thumb {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 1rem;
}

.product-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-end;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactive {
    background: #f8d7da;
    color: #721c24;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background: var(--white);
    border: 2px solid #eee;
    border-radius: 8px;
    text-decoration: none;
    color: var(--gray-dark);
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    border-color: var(--primary-green);
    background: var(--light-green);
    color: var(--dark-green);
    text-decoration: none;
    transform: translateY(-2px);
}

.quick-action-btn i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--primary-green);
}

.quick-action-btn span {
    font-weight: 500;
}

.empty-state {
    color: var(--gray-medium);
}

.alert-dismissible .close {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: inherit;
    opacity: 0.7;
    cursor: pointer;
}

.alert-dismissible .close:hover {
    opacity: 1;
}

@media (max-width: 768px) {
    .dashboard-header .row {
        text-align: center;
    }
    
    .dashboard-header h1 {
        font-size: 1.8rem;
        text-align: center;
    }
    
    .dashboard-header .lead {
        text-align: center;
    }
    
    .dashboard-header .col-md-4 {
        margin-top: 1rem;
        text-align: center !important;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .stat-card .card-body {
        padding: 1rem;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .stat-info h3 {
        font-size: 1.5rem;
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .order-item, .product-item {
        flex-wrap: wrap;
    }
    
    .product-actions {
        width: 100%;
        flex-direction: row;
        justify-content: space-between;
        margin-top: 0.5rem;
    }
}
</style>

<script>
function deleteProduct(productId, productName) {
    if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete-product.php';
        
        const productIdInput = document.createElement('input');
        productIdInput.type = 'hidden';
        productIdInput.name = 'product_id';
        productIdInput.value = productId;
        
        form.appendChild(productIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../../components/footer.php'; ?>
