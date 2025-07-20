<?php
require_once '../config/database.php';

// Simple test page
session_start();
$_SESSION['user_id'] = 6;
$_SESSION['user_type'] = 'customer';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$status = "Ready to test";
$error = "";

// If this is a POST request, test checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_checkout'])) {
    try {
        // Get product for testing
        $stmt = $pdo->query("SELECT p.*, f.id as farmer_id FROM products p JOIN farmers f ON p.farmer_id = f.id WHERE p.is_active = 1 AND p.stock_quantity > 0 LIMIT 1");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            throw new Exception("No products available");
        }
        
        // Prepare checkout data
        $cartItems = [
            [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
                'unit' => $product['unit']
            ]
        ];
        
        // Simulate checkout POST
        $checkoutData = [
            'csrf_token' => $_SESSION['csrf_token'],
            'cart_items' => json_encode($cartItems),
            'delivery_address' => "123 Test Street\nTest City\n12345",
            'delivery_notes' => 'Browser test checkout'
        ];
        
        // Use cURL to call the checkout API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost/UFarmer/pages/process-checkout.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($checkoutData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Cookie: ' . 'PHPSESSID=' . session_id()
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            throw new Exception("Checkout request failed");
        }
        
        $result = json_decode($response, true);
        if ($result) {
            if ($result['success']) {
                $status = "✅ Checkout successful! Order IDs: " . implode(', ', $result['order_ids']);
            } else {
                $status = "❌ Checkout failed: " . $result['message'];
            }
        } else {
            $status = "❌ Invalid response: " . $response;
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        $status = "❌ Error: " . $error;
    }
}

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([6]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get wallet balance
$stmt = $pdo->prepare("SELECT SUM(amount) as balance FROM wallet_transactions WHERE user_id = ?");
$stmt->execute([6]);
$balance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'] ?: 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>UFarmer Checkout Test</h1>
    
    <div class="status info">
        <strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)<br>
        <strong>Wallet Balance:</strong> $<?php echo number_format($balance, 2); ?> (₹<?php echo number_format($balance * 83, 0); ?>)<br>
        <strong>CSRF Token:</strong> <code><?php echo $_SESSION['csrf_token']; ?></code>
    </div>
    
    <div class="status <?php echo $error ? 'error' : ($status === 'Ready to test' ? 'info' : 'success'); ?>">
        <strong>Status:</strong> <?php echo htmlspecialchars($status); ?>
    </div>
    
    <form method="POST">
        <button type="submit" name="test_checkout">Test Checkout</button>
    </form>
    
    <h2>Manual Checkout Test</h2>
    <p>You can also test the checkout manually by:</p>
    <ol>
        <li><a href="shop.php">Go to Shop</a> - Add products to cart</li>
        <li><a href="cart.php">Go to Cart</a> - Complete checkout</li>
        <li><a href="orders.php">View Orders</a> - Check order history</li>
    </ol>
</body>
</html>
