<?php
require_once '../includes/functions.php';

// Set response header for JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if user is logged in and is a customer
    if (!isLoggedIn() || $_SESSION['user_type'] !== 'customer') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['order_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Order ID is required']);
        exit;
    }

    $orderId = intval($input['order_id']);
    $currentUser = getCurrentUser();
    
    // Verify CSRF token if provided
    if (isset($input['csrf_token']) && !verifyCSRFToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit;
    }

    global $pdo;
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Get order details and verify ownership
        $stmt = $pdo->prepare("
            SELECT o.*, f.user_id as farmer_user_id, u.name as farmer_name, f.farm_name
            FROM orders o 
            JOIN farmers f ON o.farmer_id = f.id 
            JOIN users u ON f.user_id = u.id 
            WHERE o.id = ? AND o.customer_id = ?
        ");
        $stmt->execute([$orderId, $currentUser['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception('Order not found or you do not have permission to cancel it');
        }
        
        // Check if order can be cancelled
        if ($order['status'] !== 'pending') {
            throw new Exception('Only pending orders can be cancelled. Current status: ' . ucfirst($order['status']));
        }
        
        // Check if order is recent enough to cancel (within 30 minutes)
        $orderTime = strtotime($order['created_at']);
        $currentTime = time();
        $timeDifference = $currentTime - $orderTime;
        
        if ($timeDifference > 1800) { // 30 minutes = 1800 seconds
            throw new Exception('Order can only be cancelled within 30 minutes of placement');
        }
        
        // Get order items for stock restoration
        $itemsStmt = $pdo->prepare("
            SELECT oi.*, p.name as product_name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$orderId]);
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restore product stock
        foreach ($orderItems as $item) {
            $updateStockStmt = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity + ? 
                WHERE id = ?
            ");
            $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Process refund to customer wallet
        $refundStmt = $pdo->prepare("
            INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type, reference_id) 
            VALUES (?, 'credit', ?, ?, 'refund', ?)
        ");
        $refundDescription = "Refund for cancelled Order #{$orderId} - {$order['farm_name']}";
        $refundStmt->execute([$currentUser['id'], $order['total_amount'], $refundDescription, $orderId]);
        
        // Reverse farmer's earning (debit their wallet)
        $farmerDebitStmt = $pdo->prepare("
            INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type, reference_id) 
            VALUES (?, 'debit', ?, ?, 'refund', ?)
        ");
        $farmerDebitDescription = "Refund processed for cancelled Order #{$orderId}";
        $farmerDebitStmt->execute([$order['farmer_user_id'], $order['total_amount'], $farmerDebitDescription, $orderId]);
        
        // Update order status to cancelled
        $cancelStmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $cancelStmt->execute([$orderId]);
        
        // Add cancellation note to order
        $noteStmt = $pdo->prepare("
            UPDATE orders 
            SET notes = CONCAT(COALESCE(notes, ''), '\n[CANCELLED] Order cancelled by customer on ', NOW(), '. Refund processed.') 
            WHERE id = ?
        ");
        $noteStmt->execute([$orderId]);
        
        // Commit transaction
        $pdo->commit();
        
        // Prepare success response
        $response = [
            'success' => true,
            'message' => 'Order cancelled successfully. Refund of ₹' . number_format($order['total_amount'] * 83, 0) . ' has been processed to your wallet.',
            'order_id' => $orderId,
            'refund_amount' => $order['total_amount'],
            'refund_amount_inr' => number_format($order['total_amount'] * 83, 0)
        ];
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
