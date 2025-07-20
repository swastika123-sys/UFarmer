<?php
require_once '../includes/functions.php';

echo "<h2>🔄 Updating Old Orders Status</h2>";

try {
    global $pdo;
    
    // Get count of pending orders first
    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
    $pendingCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<p><strong>Found {$pendingCount} orders with 'pending' status</strong></p>";
    
    if ($pendingCount > 0) {
        // Update all pending orders to confirmed
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'confirmed', updated_at = CURRENT_TIMESTAMP WHERE status = 'pending'");
        $result = $updateStmt->execute();
        $updatedCount = $updateStmt->rowCount();
        
        if ($result) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0;'>";
            echo "✅ <strong>Success!</strong> Updated {$updatedCount} orders from 'pending' to 'confirmed' status";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24; margin: 10px 0;'>";
            echo "❌ <strong>Error:</strong> Failed to update orders";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; color: #004085; margin: 10px 0;'>";
        echo "ℹ️ <strong>Info:</strong> No pending orders found. All orders are already confirmed or have other statuses.";
        echo "</div>";
    }
    
    // Show updated order status summary
    echo "<h3>📊 Current Order Status Summary:</h3>";
    $statusStmt = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM orders 
        GROUP BY status 
        ORDER BY status
    ");
    $statusSummary = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 10px;'>Status</th>";
    echo "<th style='padding: 10px;'>Count</th>";
    echo "</tr>";
    
    foreach ($statusSummary as $status) {
        $color = '';
        switch ($status['status']) {
            case 'confirmed':
                $color = 'background: #d4edda; color: #155724;';
                break;
            case 'pending':
                $color = 'background: #fff3cd; color: #856404;';
                break;
            case 'cancelled':
                $color = 'background: #f8d7da; color: #721c24;';
                break;
            default:
                $color = 'background: #e7f3ff; color: #004085;';
        }
        
        echo "<tr style='{$color}'>";
        echo "<td style='padding: 10px; font-weight: bold;'>" . ucfirst($status['status']) . "</td>";
        echo "<td style='padding: 10px; text-align: center;'>{$status['count']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show recent orders
    echo "<h3>📋 Recent Orders (Last 10):</h3>";
    $recentStmt = $pdo->query("
        SELECT o.id, o.status, o.total_amount, o.created_at, u.name as customer_name, f.farm_name
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        JOIN farmers f ON o.farmer_id = f.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $recentOrders = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='padding: 8px;'>Order ID</th>";
    echo "<th style='padding: 8px;'>Customer</th>";
    echo "<th style='padding: 8px;'>Farm</th>";
    echo "<th style='padding: 8px;'>Amount</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "<th style='padding: 8px;'>Created</th>";
    echo "</tr>";
    
    foreach ($recentOrders as $order) {
        $statusColor = $order['status'] === 'confirmed' ? '#155724' : ($order['status'] === 'pending' ? '#856404' : '#721c24');
        echo "<tr>";
        echo "<td style='padding: 8px;'>#{$order['id']}</td>";
        echo "<td style='padding: 8px;'>{$order['customer_name']}</td>";
        echo "<td style='padding: 8px;'>{$order['farm_name']}</td>";
        echo "<td style='padding: 8px;'>₹" . number_format($order['total_amount'] * 83, 0) . "</td>";
        echo "<td style='padding: 8px; color: {$statusColor}; font-weight: bold;'>{$order['status']}</td>";
        echo "<td style='padding: 8px;'>" . date('M j, Y g:i A', strtotime($order['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24; margin: 10px 0;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<h3>🔗 Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='shop.php'>🛍️ Test weight selection in shop</a></li>";
echo "<li><a href='cart.php'>🛒 Test checkout flow</a></li>";
echo "<li><a href='orders.php'>📋 View updated orders</a></li>";
echo "<li><a href='debug-weight-selection.php'>🐛 Debug weight selection</a></li>";
echo "</ul>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
h2, h3 { color: #2c5aa0; }
table { margin: 10px 0; }
th, td { text-align: left; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
