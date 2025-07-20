<?php
require_once '../includes/functions.php';

// Ensure user is logged in and is a customer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'customer') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit();
}

header('Content-Type: application/json');

// Add debugging
error_log("POST data: " . print_r($_POST, true));
error_log("Session: " . print_r($_SESSION, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    error_log("CSRF token validation failed. Token: " . ($_POST['csrf_token'] ?? 'not set') . ", Session token: " . ($_SESSION['csrf_token'] ?? 'not set'));
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$userId = $_SESSION['user_id'];
$cartItems = json_decode($_POST['cart_items'], true);
$deliveryAddress = trim($_POST['delivery_address']);
$deliveryNotes = trim($_POST['delivery_notes']) ?: null;

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

if (empty($deliveryAddress)) {
    echo json_encode(['success' => false, 'message' => 'Delivery address is required']);
    exit();
}

$transactionStarted = false;
try {
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
    $userBalance = getUserWalletBalance($userId);
    if ($userBalance < $totalAmount) {
        throw new Exception("Insufficient wallet balance. You need $" . number_format($totalAmount - $userBalance, 2) . " more.");
    }
    
    // Process orders for each farmer
    $orderIds = [];
    foreach ($farmerOrders as $farmerUserId => $orderData) {
        // Create order record
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, farmer_id, total_amount, delivery_address, notes, status) 
                              VALUES (?, ?, ?, ?, ?, 'pending')");
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
        
        // Process wallet payment
        $paymentResult = processWalletPayment(
            $userId, 
            $farmerUserId, 
            $orderData['total'], 
            "Order #$orderId - Farm products purchase",
            $orderId
        );
        
        if (!$paymentResult['success']) {
            throw new Exception($paymentResult['message']);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully!',
        'order_ids' => $orderIds,
        'total_amount' => $totalAmount,
        'remaining_balance' => getUserWalletBalance($userId)
    ]);
    
} catch (Exception $e) {
    if ($transactionStarted) {
        $pdo->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
