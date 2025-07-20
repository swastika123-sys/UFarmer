<?php
require_once '../config/database.php';

echo "<h2>Order Table Enhancement</h2>";

try {
    // Check if updated_at column exists in orders table
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'updated_at'");
    
    if ($stmt->rowCount() == 0) {
        echo "Adding updated_at column to orders table...<br>";
        $pdo->exec("ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✅ updated_at column added successfully<br>";
    } else {
        echo "✅ updated_at column already exists<br>";
    }
    
    // Check order statuses
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Order status options:</strong> {$column['Type']}</p>";
    
    // Check current orders
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Order Status Distribution:</h3>";
    if (empty($statusCounts)) {
        echo "<p>No orders found in database.</p>";
    } else {
        echo "<ul>";
        foreach ($statusCounts as $status) {
            echo "<li>{$status['status']}: {$status['count']} orders</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>Recent Orders (Last 10):</h3>";
    $stmt = $pdo->query("
        SELECT o.id, o.status, o.total_amount, o.created_at, 
               u.name as customer_name, f.farm_name,
               TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_ago
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        JOIN farmers f ON o.farmer_id = f.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($recentOrders)) {
        echo "<p>No orders found.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>Order ID</th>";
        echo "<th style='padding: 8px;'>Customer</th>";
        echo "<th style='padding: 8px;'>Farm</th>";
        echo "<th style='padding: 8px;'>Amount</th>";
        echo "<th style='padding: 8px;'>Status</th>";
        echo "<th style='padding: 8px;'>Minutes Ago</th>";
        echo "<th style='padding: 8px;'>Can Cancel?</th>";
        echo "</tr>";
        
        foreach ($recentOrders as $order) {
            $canCancel = ($order['status'] === 'pending' && $order['minutes_ago'] <= 30);
            echo "<tr>";
            echo "<td style='padding: 8px;'>#{$order['id']}</td>";
            echo "<td style='padding: 8px;'>{$order['customer_name']}</td>";
            echo "<td style='padding: 8px;'>{$order['farm_name']}</td>";
            echo "<td style='padding: 8px;'>₹" . number_format($order['total_amount'] * 83, 0) . "</td>";
            echo "<td style='padding: 8px; color: " . ($order['status'] === 'cancelled' ? 'red' : ($order['status'] === 'pending' ? 'orange' : 'green')) . ";'>{$order['status']}</td>";
            echo "<td style='padding: 8px;'>{$order['minutes_ago']}</td>";
            echo "<td style='padding: 8px;'>" . ($canCancel ? "✅ Yes" : "❌ No") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<br><p>✅ <strong>Order cancellation system is ready!</strong></p>";
    echo "<p><a href='../pages/orders.php'>→ View Orders Page</a></p>";
    echo "<p><a href='../pages/test-cancel-order.php'>→ Test Order Cancellation</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border: 1px solid #ddd; margin: 10px 0; }
th, td { text-align: left; }
h2, h3 { color: #2c5aa0; }
</style>
