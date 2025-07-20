<?php
require_once '../config/database.php';

echo "<h2>🔍 Checkout Flow Diagnosis</h2>";

// Check recent orders
echo "<h3>📋 Recent Orders (Last 10):</h3>";
$stmt = $pdo->query("
    SELECT o.id, o.status, o.total_amount, o.created_at, f.farm_name, u.name as customer_name,
           TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_ago
    FROM orders o 
    JOIN farmers f ON o.farmer_id = f.id 
    JOIN users u ON o.customer_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($orders)) {
    echo "<p>No orders found.</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Order ID</th>";
    echo "<th style='padding: 8px;'>Customer</th>";
    echo "<th style='padding: 8px;'>Farm</th>";
    echo "<th style='padding: 8px;'>Amount</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "<th style='padding: 8px;'>Created</th>";
    echo "<th style='padding: 8px;'>Minutes Ago</th>";
    echo "<th style='padding: 8px;'>Action</th>";
    echo "</tr>";
    
    foreach ($orders as $order) {
        $statusColor = $order['status'] === 'pending' ? 'orange' : ($order['status'] === 'confirmed' ? 'green' : 'blue');
        echo "<tr>";
        echo "<td style='padding: 8px;'>#{$order['id']}</td>";
        echo "<td style='padding: 8px;'>{$order['customer_name']}</td>";
        echo "<td style='padding: 8px;'>{$order['farm_name']}</td>";
        echo "<td style='padding: 8px;'>₹" . number_format($order['total_amount'] * 83, 0) . "</td>";
        echo "<td style='padding: 8px; color: $statusColor; font-weight: bold;'>{$order['status']}</td>";
        echo "<td style='padding: 8px;'>" . date('M j, Y g:i A', strtotime($order['created_at'])) . "</td>";
        echo "<td style='padding: 8px;'>{$order['minutes_ago']}</td>";
        echo "<td style='padding: 8px;'>";
        if ($order['status'] === 'pending') {
            echo "<button onclick=\"updateOrderStatus({$order['id']}, 'confirmed')\" style='background: green; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>Mark Confirmed</button>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check if process-checkout.php is working
echo "<h3>🧪 Test Checkout Process:</h3>";
echo "<p>Let's test if the issue is in the redirect or the order creation...</p>";

if (isset($_POST['fix_orders'])) {
    echo "<h4>🔧 Fixing Order Status:</h4>";
    
    // Update recent pending orders to confirmed
    $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE status = 'pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)");
    $result = $stmt->execute();
    $updated = $stmt->rowCount();
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin: 10px 0;'>";
    echo "✅ Updated $updated orders from 'pending' to 'confirmed'";
    echo "</div>";
    
    echo "<script>setTimeout(() => location.reload(), 1000);</script>";
}

if (isset($_POST['test_redirect'])) {
    echo "<h4>🔄 Testing Order Success Redirect:</h4>";
    
    // Get the most recent order
    $stmt = $pdo->query("SELECT id FROM orders ORDER BY created_at DESC LIMIT 1");
    $lastOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lastOrder) {
        $testUrl = "order-success.php?orders={$lastOrder['id']}&total=25.00";
        echo "<p><strong>Test Success URL:</strong> <a href='$testUrl' target='_blank'>$testUrl</a></p>";
        echo "<iframe src='$testUrl' width='100%' height='300' style='border: 1px solid #ccc;'></iframe>";
    } else {
        echo "<p>No orders found to test with.</p>";
    }
}

// Check error logs
echo "<h3>📝 Recent Error Logs:</h3>";
$errorLog = '/Applications/XAMPP/xamppfiles/logs/error_log';
if (file_exists($errorLog)) {
    $logs = file_get_contents($errorLog);
    $recentLogs = array_slice(explode("\n", $logs), -10);
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
    echo htmlspecialchars(implode("\n", $recentLogs));
    echo "</pre>";
} else {
    echo "<p>No error log found at $errorLog</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3, h4 { color: #2c5aa0; }
table { margin: 10px 0; }
th, td { text-align: left; }
button { cursor: pointer; }
.btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
.btn-success { background: #28a745; }
.btn-warning { background: #ffc107; color: black; }
</style>

<script>
function updateOrderStatus(orderId, status) {
    if (confirm(`Update Order #${orderId} to ${status}?`)) {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `update_order=1&order_id=${orderId}&status=${status}`
        })
        .then(() => location.reload());
    }
}

<?php
if (isset($_POST['update_order'])) {
    $orderId = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $orderId])) {
        echo "alert('Order #$orderId updated to $status');";
    }
}
?>
</script>

<form method="POST" style="margin: 20px 0;">
    <button type="submit" name="fix_orders" class="btn btn-success">🔧 Fix Pending Orders (Mark as Confirmed)</button>
    <button type="submit" name="test_redirect" class="btn btn-warning">🧪 Test Order Success Redirect</button>
</form>

<h3>🔗 Navigation Links:</h3>
<ul>
    <li><a href="cart.php">🛒 Cart Page</a></li>
    <li><a href="orders.php">📋 Orders Page</a></li>
    <li><a href="order-success.php">✅ Order Success Page</a></li>
    <li><a href="shop.php">🛍️ Shop Page</a></li>
</ul>
