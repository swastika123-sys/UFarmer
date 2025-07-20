<?php
$pageTitle = "Manage Orders";
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$currentUser = getCurrentUser();
$farmer = getFarmerByUserId($currentUser['id']);

if (!$farmer) {
    header('Location: ' . SITE_URL . '/pages/farmer/setup.php');
    exit;
}

$message = '';
$messageType = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        if ($_POST['action'] === 'update_status') {
            $orderId = intval($_POST['order_id']);
            $newStatus = sanitizeInput($_POST['status']);
            
            global $pdo;
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND farmer_id = ?");
                if ($stmt->execute([$newStatus, $orderId, $farmer['id']])) {
                    $message = "Order status updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update order status.";
                    $messageType = "danger";
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }
}

// Get farmer's orders
global $pdo;
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count,
                       (SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as total_quantity
                       FROM orders o 
                       JOIN users u ON o.customer_id = u.id 
                       WHERE o.farmer_id = ? 
                       ORDER BY o.created_at DESC");
$stmt->execute([$farmer['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order statistics
$statsStmt = $pdo->prepare("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(total_amount) as total_revenue
    FROM orders WHERE farmer_id = ?");
$statsStmt->execute([$farmer['id']]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

include '../../components/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-clipboard-list"></i> Manage Orders</h1>
                    <p class="text-muted">Track and manage customer orders for your farm</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'times-circle'; ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Order Statistics -->
        <div class="stats-row mb-4">
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                    <p>Pending Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon confirmed">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['confirmed_orders'] ?? 0; ?></h3>
                    <p>Confirmed Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon delivered">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['delivered_orders'] ?? 0; ?></h3>
                    <p>Delivered Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>₹<?php echo number_format(($stats['total_revenue'] ?? 0) * 83, 0); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Recent Orders</h3>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="empty-state text-center py-5">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                        <h4>No Orders Yet</h4>
                        <p class="text-muted">When customers place orders, they'll appear here for you to manage.</p>
                        <a href="add-product.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Products to Get Started
                        </a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h5>Order #<?php echo $order['id']; ?></h5>
                                        <p class="order-meta">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($order['customer_name']); ?> •
                                            <i class="fas fa-calendar"></i> <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="order-status">
                                        <span class="status-badge <?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-details">
                                    <div class="order-summary">
                                        <p><i class="fas fa-shopping-basket"></i> <?php echo $order['item_count']; ?> item(s) • <?php echo $order['total_quantity']; ?> kg total</p>
                                        <p><i class="fas fa-rupee-sign"></i> <strong>₹<?php echo number_format($order['total_amount'] * 83, 0); ?></strong></p>
                                        <?php if ($order['delivery_address']): ?>
                                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="order-actions">
                                        <form method="POST" class="status-form">
                                            <?php echo getCSRFInput(); ?>
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            
                                            <div class="form-group">
                                                <label for="status_<?php echo $order['id']; ?>">Update Status:</label>
                                                <select id="status_<?php echo $order['id']; ?>" name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                                    <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </div>
                                        </form>
                                        
                                        <div class="contact-customer">
                                            <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-envelope"></i> Email
                                            </a>
                                            <?php if ($order['customer_phone']): ?>
                                                <a href="tel:<?php echo htmlspecialchars($order['customer_phone']); ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-phone"></i> Call
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.page-header h1 {
    color: var(--primary-green);
    margin-bottom: 0.5rem;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-right: 1rem;
}

.stat-icon.pending { background: linear-gradient(135deg, #ffc107, #e0a800); }
.stat-icon.confirmed { background: linear-gradient(135deg, #28a745, #1e7e34); }
.stat-icon.delivered { background: linear-gradient(135deg, #007bff, #0056b3); }
.stat-icon.revenue { background: linear-gradient(135deg, #6f42c1, #563d7c); }

.stat-info h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark-green);
}

.stat-info p {
    margin: 0;
    color: var(--gray-medium);
    font-weight: 500;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.order-info h5 {
    margin: 0;
    color: var(--dark-green);
    font-weight: 600;
}

.order-meta {
    margin: 0.5rem 0 0 0;
    color: var(--gray-medium);
    font-size: 0.9rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.confirmed { background: #d4edda; color: #155724; }
.status-badge.preparing { background: #cce7ff; color: #004085; }
.status-badge.ready { background: #e7f3ff; color: #0c5460; }
.status-badge.delivered { background: #d1ecf1; color: #0c5460; }
.status-badge.cancelled { background: #f8d7da; color: #721c24; }

.order-details {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
}

.order-summary p {
    margin: 0.5rem 0;
    color: var(--gray-dark);
}

.order-actions {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.status-form {
    min-width: 150px;
}

.contact-customer {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.empty-state {
    padding: 3rem;
}

.empty-state i {
    color: var(--gray-medium);
}

@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .order-details {
        flex-direction: column;
        gap: 1rem;
    }
    
    .order-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .contact-customer {
        flex-direction: row;
    }
}
</style>

<?php include '../../components/footer.php'; ?>
