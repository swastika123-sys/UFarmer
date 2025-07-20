<?php
require_once __DIR__ . '/../includes/functions.php';

// Simple session-based authentication check
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Simple CSRF token check
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$userId = $_SESSION['user_id'];
$cartItems = json_decode($_POST['cart_items'], true);
$deliveryAddress = trim($_POST['delivery_address']);
$deliveryNotes = trim($_POST['delivery_notes']) ?: null;
$deliverySlotData = isset($_POST['delivery_slot']) ? json_decode($_POST['delivery_slot'], true) : null;

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

if (empty($deliveryAddress)) {
    echo json_encode(['success' => false, 'message' => 'Delivery address is required']);
    exit();
}

// Simple wallet balance function
function getSimpleWalletBalance($userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END) as balance 
        FROM wallet_transactions 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (float)$result['balance'] : 0.00;
}

$transactionStarted = false;
try {
    // Check for duplicate orders in the last 30 seconds to prevent double submission
    $stmt = $pdo->prepare("SELECT COUNT(*) as recent_orders FROM orders WHERE customer_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)");
    $stmt->execute([$userId]);
    $recentOrders = $stmt->fetch(PDO::FETCH_ASSOC)['recent_orders'];
    
    if ($recentOrders > 0) {
        throw new Exception("Please wait before placing another order. Recent order detected.");
    }

    $pdo->beginTransaction();
    $transactionStarted = true;
    
    // Group items by farmer and calculate totals
    $farmerOrders = [];
    $totalAmount = 0;
    
    foreach ($cartItems as $item) {
        // Get updated product info (including any discounts)
        $stmt = $pdo->prepare("SELECT p.*, f.user_id as farmer_user_id FROM products p 
                              JOIN farmers f ON p.farmer_id = f.id 
                              WHERE p.id = ? AND p.is_active = 1");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new Exception("Product not found: " . $item['name']);
        }
        
        if ($product['stock_quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for: " . $product['name']);
        }
        
        // Use discounted price if available
        $unitPrice = $product['discounted_price'] ?: $product['price'];
        $itemTotal = $unitPrice * $item['quantity'];
        $totalAmount += $itemTotal;
        
        if (!isset($farmerOrders[$product['farmer_user_id']])) {
            $farmerOrders[$product['farmer_user_id']] = [
                'farmer_id' => $product['farmer_id'],
                'items' => [],
                'total' => 0
            ];
        }
        
        $farmerOrders[$product['farmer_user_id']]['items'][] = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $unitPrice,
            'quantity' => $item['quantity'],
            'total' => $itemTotal
        ];
        
        $farmerOrders[$product['farmer_user_id']]['total'] += $itemTotal;
    }
    
    // Check wallet balance
    $userBalance = getSimpleWalletBalance($userId, $pdo);
    if ($userBalance < $totalAmount) {
        throw new Exception("Insufficient wallet balance. You need $" . number_format($totalAmount - $userBalance, 2) . " more.");
    }
    
    // Process orders for each farmer
    $orderIds = [];
    foreach ($farmerOrders as $farmerUserId => $orderData) {
        $deliverySlotId = null;
        
        // Handle delivery slot if provided
        if ($deliverySlotData && function_exists('bookDeliverySlot')) {
            $slotResult = bookDeliverySlot(
                $orderData['farmer_id'], 
                $deliverySlotData['date'], 
                $deliverySlotData['start_time'], 
                $deliverySlotData['end_time']
            );
            if ($slotResult['success']) {
                $deliverySlotId = $slotResult['slot_id'];
            }
        }
        
        // Create order record - Set as 'confirmed' since payment is processed immediately via wallet
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, farmer_id, total_amount, delivery_address, notes, status)
                                VALUES (?, ?, ?, ?, ?, 'confirmed')");
        $stmt->execute([$userId, $orderData['farmer_id'], $orderData['total'], $deliveryAddress, $deliveryNotes]);
        $orderId = $pdo->lastInsertId();
        $orderIds[] = $orderId;
        
        // Add order items
        foreach ($orderData['items'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Update product stock
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Process wallet payment - simple version
        // Debit from customer
        $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type, reference_id) VALUES (?, 'debit', ?, ?, 'purchase', ?)");
        $stmt->execute([$userId, $orderData['total'], "Order #$orderId - Farm products purchase", $orderId]);
        
        // Credit to farmer
        $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description, reference_type, reference_id) VALUES (?, 'credit', ?, ?, 'sale', ?)");
        $stmt->execute([$farmerUserId, $orderData['total'], "Sale from Order #$orderId", $orderId]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully!',
        'order_ids' => $orderIds,
        'total_amount' => $totalAmount,
        'remaining_balance' => getSimpleWalletBalance($userId, $pdo)
    ]);
    
} catch (Exception $e) {
    if ($transactionStarted) {
        $pdo->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
