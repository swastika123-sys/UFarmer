<?php
require_once '../config/database.php';

echo "<h2>🔧 Order Status Fix</h2>";

try {
    // Update recent pending orders to confirmed
    $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE status = 'pending' AND DATE(created_at) = CURDATE()");
    $result = $stmt->execute();
    $updated = $stmt->rowCount();
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin: 10px 0;'>";
    echo "✅ <strong>Updated $updated orders from 'pending' to 'confirmed'</strong>";
    echo "</div>";
    
    // Show recent orders with updated status
    echo "<h3>📋 Recent Orders (After Fix):</h3>";
    $stmt = $pdo->query("
        SELECT o.id, o.status, o.total_amount, o.created_at, f.farm_name 
        FROM orders o 
        JOIN farmers f ON o.farmer_id = f.id 
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
        echo "<th style='padding: 8px;'>Farm</th>";
        echo "<th style='padding: 8px;'>Amount</th>";
        echo "<th style='padding: 8px;'>Status</th>";
        echo "<th style='padding: 8px;'>Created</th>";
        echo "</tr>";
        
        foreach ($orders as $order) {
            $statusColor = $order['status'] === 'confirmed' ? 'green' : ($order['status'] === 'pending' ? 'orange' : 'blue');
            echo "<tr>";
            echo "<td style='padding: 8px;'>#{$order['id']}</td>";
            echo "<td style='padding: 8px;'>{$order['farm_name']}</td>";
            echo "<td style='padding: 8px;'>₹" . number_format($order['total_amount'] * 83, 0) . "</td>";
            echo "<td style='padding: 8px; color: $statusColor; font-weight: bold;'>{$order['status']}</td>";
            echo "<td style='padding: 8px;'>" . date('M j, Y g:i A', strtotime($order['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>✅ Fixes Applied:</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>Order Status:</strong> Changed new orders to be created as 'confirmed' instead of 'pending'</li>";
    echo "<li>✅ <strong>Existing Orders:</strong> Updated today's pending orders to 'confirmed' status</li>";
    echo "<li>✅ <strong>Order Success Page:</strong> Fixed redirect logic to show transaction details</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Test Links:</h3>";
    echo "<ul>";
    echo "<li><a href='cart.php'>🛒 Test New Checkout Process</a></li>";
    echo "<li><a href='orders.php'>📋 View Updated Orders</a></li>";
    echo "<li><a href='order-success.php'>✅ Test Order Success Page</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin: 10px 0;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #2c5aa0; }
table { margin: 10px 0; }
th, td { text-align: left; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
