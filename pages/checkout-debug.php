<?php
require_once '../includes/functions.php';

// Create a test checkout scenario
session_start();

// If not logged in, simulate login for testing
if (!isLoggedIn()) {
    $_SESSION['user_id'] = 6; // Demo customer
    $_SESSION['user_type'] = 'customer';
    $_SESSION['logged_in'] = true;
}

$currentUser = getCurrentUser();
echo "<h1>Checkout Debug Page</h1>";
echo "<p>User: {$currentUser['name']} ({$currentUser['email']})</p>";

$balance = getUserWalletBalance($currentUser['id']);
echo "<p>Wallet Balance: " . formatPrice($balance) . "</p>";

// Generate and display CSRF token
$csrfToken = generateCSRFToken();
echo "<p>CSRF Token: <code>$csrfToken</code></p>";

// Get some products
$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 AND stock_quantity > 0 LIMIT 3");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Available Products</h2>";
foreach ($products as $product) {
    echo "<div>";
    echo "<h3>{$product['name']}</h3>";
    echo "<p>Price: " . formatPrice($product['price']) . " per {$product['unit']}</p>";
    echo "<p>Stock: {$product['stock_quantity']}</p>";
    echo "<button onclick='addToCart({$product['id']}, \"{$product['name']}\", {$product['price']}, \"{$product['unit']}\")'>Add to Cart</button>";
    echo "</div><hr>";
}

?>

<script>
// Simple cart for testing
let testCart = [];

function addToCart(id, name, price, unit) {
    testCart.push({
        id: id,
        name: name,
        price: price,
        quantity: 1,
        unit: unit
    });
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartDiv = document.getElementById('cart-items');
    cartDiv.innerHTML = '';
    let total = 0;
    
    testCart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        cartDiv.innerHTML += `<div>${item.name}: ${item.quantity} ${item.unit} × <?php echo '₹'; ?>${(item.price * 83).toFixed(0)} = <?php echo '₹'; ?>${(itemTotal * 83).toFixed(0)}</div>`;
    });
    
    cartDiv.innerHTML += `<strong>Total: <?php echo '₹'; ?>${(total * 83).toFixed(0)}</strong>`;
}

function testCheckout() {
    const address = document.getElementById('delivery_address').value.trim();
    const notes = document.getElementById('delivery_notes').value.trim();
    
    if (!address) {
        alert('Please enter delivery address');
        return;
    }
    
    if (testCart.length === 0) {
        alert('Cart is empty');
        return;
    }
    
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrfToken; ?>');
    formData.append('cart_items', JSON.stringify(testCart));
    formData.append('delivery_address', address);
    formData.append('delivery_notes', notes);
    
    console.log('Sending checkout data:', {
        csrf_token: '<?php echo $csrfToken; ?>',
        cart_items: testCart,
        delivery_address: address,
        delivery_notes: notes
    });
    
    fetch('process-checkout-enhanced.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Parsed response:', data);
            
            if (data.success) {
                alert('Order placed successfully! Order IDs: ' + data.order_ids.join(', '));
                testCart = [];
                updateCartDisplay();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            alert('Server error: ' + text);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Network error: ' + error.message);
    });
}
</script>

<h2>Test Cart</h2>
<div id="cart-items"></div>

<h2>Checkout Test</h2>
<div>
    <label>Delivery Address:</label><br>
    <textarea id="delivery_address" rows="3" cols="50">123 Test Street
Test City, Test State
12345</textarea><br><br>
    
    <label>Delivery Notes:</label><br>
    <textarea id="delivery_notes" rows="2" cols="50">Test checkout - please handle with care</textarea><br><br>
    
    <button onclick="testCheckout()">Test Checkout</button>
</div>

<script>
updateCartDisplay();
</script>
