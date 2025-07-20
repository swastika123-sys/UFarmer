<?php
require_once '../includes/functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit();
}

$currentUser = getCurrentUser();

// Get order details from URL parameters
$orderIds = isset($_GET['orders']) ? explode(',', $_GET['orders']) : [];
$totalAmount = isset($_GET['total']) ? floatval($_GET['total']) : 0;

// Debug: Log the parameters received
error_log("Order Success - Order IDs: " . print_r($orderIds, true));
error_log("Order Success - Total Amount: " . $totalAmount);

// If no order IDs provided, try to get the most recent order for this user
if (empty($orderIds)) {
    error_log("Order Success - No order IDs provided, fetching recent order");
    
    $stmt = $pdo->prepare("
        SELECT id, total_amount 
        FROM orders 
        WHERE customer_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$currentUser['id']]);
    $recentOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($recentOrder) {
        $orderIds = [$recentOrder['id']];
        $totalAmount = $totalAmount ?: $recentOrder['total_amount'];
        error_log("Order Success - Using recent order: #{$recentOrder['id']}");
    } else {
        error_log("Order Success - No recent orders found, redirecting to orders page");
        header('Location: ' . SITE_URL . '/pages/orders.php?message=no_recent_orders');
        exit();
    }
}

// Fetch order details with full transaction information
$orders = [];
$orderItems = [];
if (!empty($orderIds)) {
    $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT o.*, f.farm_name, f.location, f.contact_email, f.contact_phone, 
               u.name as farmer_name, u.email as farmer_email
        FROM orders o 
        JOIN farmers f ON o.farmer_id = f.id 
        JOIN users u ON f.user_id = u.id 
        WHERE o.id IN ($placeholders) AND o.customer_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute(array_merge($orderIds, [$currentUser['id']]));
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch order items for each order
    foreach ($orderIds as $orderId) {
        $itemsStmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.unit, p.category
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$orderId]);
        $orderItems[$orderId] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$pageTitle = 'Order Confirmation';
include '../components/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="text-center mb-4">
                <div class="success-icon mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="text-success">Order Placed Successfully! 🎉</h1>
                <p class="lead text-muted">Thank you for your order. Your fresh farm products are on their way!</p>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Transaction Statement</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Transaction Date:</strong><br>
                            <?php echo date('F j, Y g:i A'); ?>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <strong>Total Amount Paid:</strong><br>
                            <span class="h4 text-success"><?php echo formatPrice($totalAmount); ?></span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Transaction Reference:</strong><br>
                            <?php foreach ($orderIds as $index => $orderId): ?>
                                <span class="badge bg-primary me-1">#TXN-<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <strong>Payment Method:</strong><br>
                            <i class="fas fa-wallet text-success"></i> UFarmer Wallet
                            <br><small class="text-muted">Secure instant payment</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Customer Information:</strong><br>
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($currentUser['name']); ?><br>
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($currentUser['email']); ?>
                    </div>
                </div>
            </div>

            <?php foreach ($orders as $order): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0">
                                <i class="fas fa-store"></i> <?php echo htmlspecialchars($order['farm_name']); ?>
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($order['location']); ?>
                            </small>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle"></i> <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Order #<?php echo $order['id']; ?></strong><br>
                            <small class="text-muted">
                                <i class="fas fa-user"></i> Farmer: <?php echo htmlspecialchars($order['farmer_name']); ?><br>
                                <?php if (isset($order['contact_email']) && $order['contact_email']): ?>
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($order['contact_email']); ?><br>
                                <?php endif; ?>
                                <?php if (isset($order['contact_phone']) && $order['contact_phone']): ?>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($order['contact_phone']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <strong><?php echo formatPrice($order['total_amount']); ?></strong><br>
                            <small class="text-muted">Order Total</small>
                        </div>
                    </div>
                    
                    <!-- Order Items Detail -->
                    <?php if (isset($orderItems[$order['id']]) && !empty($orderItems[$order['id']])): ?>
                    <div class="mb-3">
                        <strong>Items Ordered:</strong>
                        <div class="table-responsive mt-2">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems[$order['id']] as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($item['category']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo $item['quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?>
                                        </td>
                                        <td>
                                            <?php echo formatPrice($item['price']); ?>
                                        </td>
                                        <td class="text-end">
                                            <strong><?php echo formatPrice($item['quantity'] * $item['price']); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order['delivery_address']): ?>
                    <div class="mb-3">
                        <strong><i class="fas fa-shipping-fast"></i> Delivery Details:</strong><br>
                        <div class="bg-light p-3 rounded mt-2">
                            <strong>Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                            
                            <?php if ($order['notes']): ?>
                            <br><br><strong>Special Instructions:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="card mb-4">
                <div class="card-body text-center">
                    <h5><i class="fas fa-info-circle text-info"></i> What's Next?</h5>
                    <p class="mb-3">Your orders have been sent to the respective farmers. They will confirm and prepare your fresh products.</p>
                    
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="step-icon">
                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                                <h6>Order Confirmed</h6>
                                <small class="text-muted">Farmers will review your order</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="step-icon">
                                <i class="fas fa-seedling text-warning mb-2" style="font-size: 2rem;"></i>
                                <h6>Preparing</h6>
                                <small class="text-muted">Fresh harvest & packaging</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="step-icon">
                                <i class="fas fa-truck text-info mb-2" style="font-size: 2rem;"></i>
                                <h6>Delivery</h6>
                                <small class="text-muted">Fresh products at your door</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="orders.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-list"></i> View All Orders
                </a>
                <a href="shop.php" class="btn btn-success btn-lg">
                    <i class="fas fa-shopping-cart"></i> Continue Shopping
                </a>
            </div>

            <div class="alert alert-info mt-4">
                <i class="fas fa-bell"></i>
                <strong>Stay Updated!</strong> You'll receive notifications about your order status updates. 
                You can also track your orders in the <a href="orders.php">Orders section</a>.
            </div>
        </div>
    </div>
</div>

<style>
.success-icon {
    animation: bounce 1s ease-in-out;
}

@keyframes bounce {
    0%, 20%, 60%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    80% {
        transform: translateY(-10px);
    }
}

.step-icon {
    padding: 1rem;
    border-radius: 8px;
    background: #f8f9fa;
    height: 100%;
}

.badge {
    font-size: 0.85em;
}
</style>

<?php include '../components/footer.php'; ?>
