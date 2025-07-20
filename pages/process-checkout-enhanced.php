<?php
require_once '../includes/functions.php';

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function for debugging
function logCheckout($message, $data = null) {
    $logFile = '../logs/checkout.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data) {
        $logMessage .= ": " . print_r($data, true);
    }
    file_put_contents($logFile, $logMessage . "\n", FILE_APPEND | LOCK_EX);
}

logCheckout("Checkout process started");

// Ensure user is logged in and is a customer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'customer') {
    logCheckout("User not logged in or not a customer", $_SESSION);
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit();
}

header('Content-Type: application/json');

logCheckout("POST data received", $_POST);
logCheckout("Session data", $_SESSION);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logCheckout("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token'])) {
    logCheckout("CSRF token not provided");
    echo json_encode(['success' => false, 'message' => 'Security token missing']);
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'])) {
    logCheckout("CSRF token validation failed", [
        'provided' => $_POST['csrf_token'],
        'expected' => $_SESSION['csrf_token'] ?? 'not set'
    ]);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

logCheckout("CSRF token verified successfully");

$userId = $_SESSION['user_id'];
$cartItems = json_decode($_POST['cart_items'], true);
$deliveryAddress = trim($_POST['delivery_address']);
$deliveryNotes = trim($_POST['delivery_notes']) ?: null;

logCheckout("Parsed checkout data", [
    'user_id' => $userId,
    'cart_items_count' => count($cartItems ?: []),
    'delivery_address' => $deliveryAddress,
    'delivery_notes' => $deliveryNotes
]);

if (empty($cartItems)) {
    logCheckout("Cart is empty");
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

if (empty($deliveryAddress)) {
    logCheckout("Delivery address is empty");
    echo json_encode(['success' => false, 'message' => 'Delivery address is required']);
    exit();
}

$transactionStarted = false;
try {
    logCheckout("Starting database transaction");
    $pdo->beginTransaction();
    $transactionStarted = true;
    
    // Group items by farmer and calculate totals
    $farmerOrders = [];
    $totalAmount = 0;
    
    logCheckout("Processing cart items", $cartItems);
    
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
        
        logCheckout("Product found", $product);
        
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
    
    logCheckout("Farmer orders grouped", [
        'farmer_count' => count($farmerOrders),
        'total_amount' => $totalAmount
    ]);
    
    // Check wallet balance
    $userBalance = getUserWalletBalance($userId);
    logCheckout("User wallet balance: $userBalance, Required: $totalAmount");
    
    if ($userBalance < $totalAmount) {
        throw new Exception("Insufficient wallet balance. You need $" . number_format($totalAmount - $userBalance, 2) . " more.");
    }
    
    // Process orders for each farmer
    $orderIds = [];
    foreach ($farmerOrders as $farmerUserId => $orderData) {
        logCheckout("Creating order for farmer user ID: $farmerUserId");
        
        // Create order record
        $stmt = $pdo->prepare("INSERT INTO orders (customer_id, farmer_id, total_amount, delivery_address, notes, status) 
                              VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$userId, $orderData['farmer_id'], $orderData['total'], $deliveryAddress, $deliveryNotes]);
        $orderId = $pdo->lastInsertId();
        $orderIds[] = $orderId;
        
        logCheckout("Order created with ID: $orderId");
        
        // Add order items
        foreach ($orderData['items'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Update product stock
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
            
            logCheckout("Added order item and updated stock", $item);
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
        
        logCheckout("Payment processed successfully", $paymentResult);
    }
    
    $pdo->commit();
    logCheckout("Transaction committed successfully");
    
    $response = [
        'success' => true, 
        'message' => 'Order placed successfully!',
        'order_ids' => $orderIds,
        'total_amount' => $totalAmount,
        'remaining_balance' => getUserWalletBalance($userId)
    ];
    
    logCheckout("Success response", $response);
    echo json_encode($response);
    
} catch (Exception $e) {
    if ($transactionStarted) {
        $pdo->rollback();
        logCheckout("Transaction rolled back due to error");
    }
    
    $errorResponse = ['success' => false, 'message' => $e->getMessage()];
    logCheckout("Error response", $errorResponse);
    echo json_encode($errorResponse);
}
?>
