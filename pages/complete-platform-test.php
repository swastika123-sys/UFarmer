<?php
require_once '../includes/functions.php';

// Ensure user is logged in for testing
if (!isLoggedIn()) {
    echo "<p>⚠️  Please log in as demo@example.com first: <a href='auth/login.php'>Login Here</a></p>";
    exit;
}

$currentUser = getCurrentUser();
echo "<h2>🧪 Complete UFarmer Platform Test</h2>";
echo "<p>✅ Logged in as: {$currentUser['name']} ({$currentUser['email']})</p>";

global $pdo;
?>

<!DOCTYPE html>
<html>
<head>
    <title>UFarmer Platform Test</title>
    <script src="../assets/js/main.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; max-width: 1200px; }
        .test-section { border: 1px solid #ddd; margin: 20px 0; padding: 15px; border-radius: 5px; }
        .test-section h3 { color: #2c5aa0; margin-top: 0; }
        .success { background: #d4edda; color: #155724; }
        .warning { background: #fff3cd; color: #856404; }
        .info { background: #e7f3ff; color: #004085; }
        .error { background: #f8d7da; color: #721c24; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .btn { padding: 8px 15px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; }
    </style>
</head>
<body>

<div class="test-section info">
    <h3>🎯 Test Summary</h3>
    <p>This page tests all the fixed functionality:</p>
    <ul>
        <li>✅ Weight selection (250g, 500g, 1kg, 2kg)</li>
        <li>✅ Proper price calculation and cart integration</li>
        <li>✅ Checkout flow to order-success.php</li>
        <li>✅ Enhanced transaction statement with details</li>
        <li>✅ Order status updated to 'confirmed'</li>
        <li>✅ Order cancellation within 30-minute window</li>
    </ul>
</div>

<div class="test-section">
    <h3>🛍️ Test 1: Weight Selection</h3>
    <p>Test the weight selection functionality with a sample product:</p>
    
    <div style="border: 1px solid #ccc; padding: 15px; margin: 10px 0; background: #f9f9f9;">
        <h4>🥕 Test Product - Organic Carrots</h4>
        <p>Base Price: $2.50 per kg (₹207.50 per kg)</p>
        
        <div class="weight-selector">
            <label for="weight_shop_test">Select Weight:</label>
            <select id="weight_shop_test" class="weight-select" data-product-id="test" data-base-price="207.50">
                <option value="0.25">250g - ₹52</option>
                <option value="0.5">500g - ₹104</option>
                <option value="0.75">750g - ₹156</option>
                <option value="1" selected>1kg - ₹208</option>
                <option value="2">2kg - ₹415</option>
            </select>
        </div>
        
        <div class="price-display" style="margin: 10px 0;">
            <strong class="price">₹208 / 1kg</strong>
        </div>
        
        <button class="btn btn-success" onclick="testWeightSelection()">🧪 Test Add to Cart</button>
        <button class="btn btn-warning" onclick="debugCurrentSelection()">🔍 Debug Selection</button>
    </div>
    
    <div id="weight-test-output" style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-family: monospace;">
        Ready for testing...
    </div>
</div>

<div class="test-section">
    <h3>🛒 Test 2: Cart Integration</h3>
    <div id="cart-display" style="background: #e8f5e9; padding: 10px; margin: 10px 0;">
        <p>Cart contents will appear here...</p>
    </div>
    <button class="btn" onclick="showCartContents()">🔍 Show Cart Contents</button>
    <button class="btn btn-warning" onclick="clearCart()">🗑️ Clear Cart</button>
</div>

<div class="test-section">
    <h3>📋 Test 3: Recent Orders Status</h3>
    <?php
    try {
        $stmt = $pdo->prepare("
            SELECT o.id, o.status, o.total_amount, o.created_at, f.farm_name
            FROM orders o 
            JOIN farmers f ON o.farmer_id = f.id 
            WHERE o.customer_id = ? 
            ORDER BY o.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$currentUser['id']]);
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($recentOrders)) {
            echo "<table>";
            echo "<tr><th>Order ID</th><th>Farm</th><th>Amount</th><th>Status</th><th>Date</th></tr>";
            foreach ($recentOrders as $order) {
                $statusColor = $order['status'] === 'confirmed' ? 'green' : ($order['status'] === 'pending' ? 'orange' : 'red');
                echo "<tr>";
                echo "<td>#{$order['id']}</td>";
                echo "<td>{$order['farm_name']}</td>";
                echo "<td>₹" . number_format($order['total_amount'] * 83, 0) . "</td>";
                echo "<td style='color: {$statusColor}; font-weight: bold;'>{$order['status']}</td>";
                echo "<td>" . date('M j, Y g:i A', strtotime($order['created_at'])) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No orders found. Create a test order first.</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    }
    ?>
</div>

<div class="test-section">
    <h3>🔗 Test 4: Navigation Links</h3>
    <p>Test the complete user flow:</p>
    <div>
        <a href="shop.php" class="btn btn-success" target="_blank">🛍️ Go to Shop</a>
        <a href="cart.php" class="btn" target="_blank">🛒 Go to Cart</a>
        <a href="orders.php" class="btn" target="_blank">📋 View Orders</a>
        <a href="order-success.php" class="btn btn-warning" target="_blank">✅ Test Order Success</a>
        <a href="wallet.php" class="btn" target="_blank">💰 Check Wallet</a>
    </div>
</div>

<div class="test-section">
    <h3>🚀 Test 5: Quick End-to-End Test</h3>
    <p>This will test the complete flow:</p>
    <ol>
        <li>Add item to cart with weight selection</li>
        <li>Go to cart and initiate checkout</li>
        <li>Verify redirect to order-success.php</li>
        <li>Check order appears with 'confirmed' status</li>
    </ol>
    
    <button class="btn btn-success" onclick="startEndToEndTest()">🧪 Start Complete Test</button>
    <div id="e2e-test-output" style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-family: monospace;">
        Click button above to start...
    </div>
</div>

<script>
// Initialize cart if not exists
if (typeof cart === 'undefined') {
    class TestCart {
        constructor() {
            this.items = JSON.parse(localStorage.getItem('cart') || '[]');
        }
        
        addItemWithWeight(productId, name, totalPrice, weight, unit = 'kg') {
            const itemKey = `${productId}_${weight}`;
            const existingIndex = this.items.findIndex(item => item.key === itemKey);
            
            if (existingIndex >= 0) {
                this.items[existingIndex].quantity += 1;
            } else {
                this.items.push({
                    key: itemKey,
                    id: productId,
                    name: name,
                    price: totalPrice,
                    quantity: 1,
                    weight: weight,
                    unit: unit,
                    timestamp: Date.now()
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(this.items));
            this.updateDisplay();
        }
        
        updateDisplay() {
            const cartDisplay = document.getElementById('cart-display');
            cartDisplay.innerHTML = '<h4>🛒 Cart Contents:</h4>';
            
            if (this.items.length === 0) {
                cartDisplay.innerHTML += '<p>Cart is empty</p>';
                return;
            }
            
            this.items.forEach((item, index) => {
                cartDisplay.innerHTML += `
                    <div style="margin: 5px 0; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
                        <strong>${item.name}</strong><br>
                        Price: ₹${(item.price * 83).toFixed(0)} | Weight: ${item.weight}kg | Qty: ${item.quantity}<br>
                        Total: ₹${((item.price * item.quantity) * 83).toFixed(0)}
                    </div>
                `;
            });
            
            const total = this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            cartDisplay.innerHTML += `<p><strong>Cart Total: ₹${(total * 83).toFixed(0)}</strong></p>`;
        }
        
        clear() {
            this.items = [];
            localStorage.setItem('cart', JSON.stringify(this.items));
            this.updateDisplay();
        }
    }
    
    window.cart = new TestCart();
}

function testWeightSelection() {
    const output = document.getElementById('weight-test-output');
    output.innerHTML = '=== Testing Weight Selection ===<br>';
    
    const productId = 'test';
    const productName = 'Organic Carrots';
    const basePrice = 2.50; // USD
    const unit = 'kg';
    
    const weightSelect = document.getElementById('weight_shop_test');
    const selectedWeight = parseFloat(weightSelect.value);
    const calculatedPrice = basePrice * selectedWeight;
    const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
    
    output.innerHTML += `Selected weight: ${selectedWeight}kg<br>`;
    output.innerHTML += `Base price: $${basePrice}<br>`;
    output.innerHTML += `Calculated price: $${calculatedPrice.toFixed(2)}<br>`;
    output.innerHTML += `Weight text: ${weightText}<br>`;
    output.innerHTML += `Final name: ${productName} (${weightText})<br>`;
    
    // Add to cart
    cart.addItemWithWeight(productId, `${productName} (${weightText})`, calculatedPrice, selectedWeight, unit);
    
    output.innerHTML += '<br>✅ Item added to cart successfully!<br>';
}

function debugCurrentSelection() {
    const output = document.getElementById('weight-test-output');
    const weightSelect = document.getElementById('weight_shop_test');
    
    output.innerHTML = '=== Debug Current Selection ===<br>';
    output.innerHTML += `Selected option value: ${weightSelect.value}<br>`;
    output.innerHTML += `Selected option text: ${weightSelect.options[weightSelect.selectedIndex].text}<br>`;
    output.innerHTML += `Data attributes: ${JSON.stringify({
        productId: weightSelect.dataset.productId,
        basePrice: weightSelect.dataset.basePrice
    })}<br>`;
}

function showCartContents() {
    cart.updateDisplay();
}

function clearCart() {
    cart.clear();
}

function startEndToEndTest() {
    const output = document.getElementById('e2e-test-output');
    output.innerHTML = '🚀 Starting End-to-End Test...<br>';
    
    // Step 1: Add item to cart
    output.innerHTML += '1. Adding test item to cart...<br>';
    testWeightSelection();
    
    // Step 2: Show instructions
    output.innerHTML += '2. ✅ Item added to cart<br>';
    output.innerHTML += '3. 📋 Next steps:<br>';
    output.innerHTML += '   - Click "Go to Cart" above<br>';
    output.innerHTML += '   - Fill delivery address<br>';
    output.innerHTML += '   - Click "Pay with Wallet"<br>';
    output.innerHTML += '   - Verify redirect to order-success.php<br>';
    output.innerHTML += '   - Check transaction statement details<br>';
    
    // Auto-update cart display
    setTimeout(() => {
        cart.updateDisplay();
    }, 500);
}

// Set up weight selector change handler
document.addEventListener('DOMContentLoaded', function() {
    cart.updateDisplay();
    
    const weightSelect = document.getElementById('weight_shop_test');
    weightSelect.addEventListener('change', function() {
        const basePrice = parseFloat(this.dataset.basePrice);
        const selectedWeight = parseFloat(this.value);
        const newPrice = Math.round(basePrice * selectedWeight);
        
        const priceElement = document.querySelector('.price');
        if (priceElement) {
            const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
            priceElement.textContent = `₹${newPrice} / ${weightText}`;
        }
    });
});
</script>

</body>
</html>
