<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo "<p>Please login first: <a href='auth/login.php'>Login</a></p>";
    exit;
}

$pageTitle = 'Weight Selection Debug';
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle; ?></title>
    <script src="../assets/js/main.js"></script>
</head>
<body>
    <h2>🐛 Weight Selection Debug</h2>
    
    <h3>Test Product Weight Selection:</h3>
    <div class="product-test" style="border: 1px solid #ccc; padding: 20px; margin: 20px 0;">
        <h4>Test Product - Tomatoes</h4>
        <p>Base Price: $2.00 per kg</p>
        
        <div class="weight-selector">
            <label for="weight_shop_999">Select Weight:</label>
            <select id="weight_shop_999" class="form-control weight-select" data-product-id="999" data-base-price="166">
                <option value="0.25">250g - ₹41</option>
                <option value="0.5">500g - ₹83</option>
                <option value="0.75">750g - ₹124</option>
                <option value="1" selected>1kg - ₹166</option>
                <option value="2">2kg - ₹332</option>
            </select>
        </div>
        
        <div class="price-display" style="margin: 10px 0;">
            <strong class="price">₹166 / 1kg</strong>
        </div>
        
        <button onclick="testAddToCart()">Test Add to Cart</button>
        <button onclick="debugWeightSelection()">Debug Weight Selection</button>
    </div>
    
    <h3>Debug Output:</h3>
    <div id="debug-output" style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-family: monospace;">
        <!-- Debug info will appear here -->
    </div>
    
    <h3>Cart Contents:</h3>
    <div id="cart-display" style="background: #e8f5e9; padding: 10px; margin: 10px 0;">
        <!-- Cart contents will appear here -->
    </div>

    <script>
    // Initialize cart if not exists
    if (typeof cart === 'undefined') {
        class SimpleCart {
            constructor() {
                this.items = JSON.parse(localStorage.getItem('cart') || '[]');
            }
            
            addItemWithWeight(productId, name, totalPrice, weight, unit = 'kg') {
                let cartItems = JSON.parse(localStorage.getItem('cart') || '[]');
                
                const itemKey = `${productId}_${weight}`;
                const existingIndex = cartItems.findIndex(item => item.key === itemKey);
                
                if (existingIndex >= 0) {
                    cartItems[existingIndex].quantity += 1;
                } else {
                    cartItems.push({
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
                
                localStorage.setItem('cart', JSON.stringify(cartItems));
                this.items = cartItems;
                this.updateDisplay();
            }
            
            updateDisplay() {
                const cartDisplay = document.getElementById('cart-display');
                cartDisplay.innerHTML = '<h4>Cart Items:</h4>';
                
                this.items.forEach(item => {
                    cartDisplay.innerHTML += `
                        <div style="margin: 5px 0; padding: 5px; border: 1px solid #ccc;">
                            <strong>${item.name}</strong><br>
                            Price: ₹${item.price} | Weight: ${item.weight}kg | Quantity: ${item.quantity}<br>
                            Total: ₹${(item.price * item.quantity).toFixed(0)}
                        </div>
                    `;
                });
            }
        }
        
        const cart = new SimpleCart();
    }
    
    function testAddToCart() {
        const productId = 999;
        const productName = 'Test Tomatoes';
        const basePrice = 2.00; // USD base price
        const unit = 'kg';
        
        debugLog('Starting addToCartWithWeightShop test...');
        
        addToCartWithWeightShop(productId, productName, basePrice, unit);
    }
    
    function debugWeightSelection() {
        const weightSelect = document.getElementById('weight_shop_999');
        const selectedWeight = parseFloat(weightSelect.value);
        const basePrice = parseFloat(weightSelect.dataset.basePrice); // This is in Indian Rupees (₹)
        const basePriceUSD = 2.00; // Original USD price
        
        debugLog('=== Weight Selection Debug ===');
        debugLog('Selected option value: ' + weightSelect.value);
        debugLog('Selected weight: ' + selectedWeight);
        debugLog('Base price from data-attribute (₹): ' + basePrice);
        debugLog('Base price USD: ' + basePriceUSD);
        debugLog('Calculated price (₹): ' + (basePrice * selectedWeight));
        debugLog('Expected weight text: ' + (selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`));
        
        // Test the actual function logic
        const calculatedPrice = basePriceUSD * selectedWeight;
        const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
        
        debugLog('Function would calculate:');
        debugLog('- Price: ' + calculatedPrice + ' USD');
        debugLog('- Weight text: ' + weightText);
        debugLog('- Final name: Test Tomatoes (' + weightText + ')');
    }
    
    function debugLog(message) {
        const debugOutput = document.getElementById('debug-output');
        debugOutput.innerHTML += message + '<br>';
        console.log(message);
    }
    
    // Copy the actual function from shop.php for testing
    function addToCartWithWeightShop(productId, productName, basePrice, unit = 'kg') {
        debugLog('=== addToCartWithWeightShop called ===');
        debugLog('Parameters: productId=' + productId + ', productName=' + productName + ', basePrice=' + basePrice + ', unit=' + unit);
        
        const weightSelect = document.getElementById(`weight_shop_${productId}`);
        if (!weightSelect) {
            debugLog('ERROR: Weight select element not found!');
            return;
        }
        
        const selectedWeight = parseFloat(weightSelect.value);
        const calculatedPrice = basePrice * selectedWeight;
        const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
        
        debugLog('Selected weight: ' + selectedWeight);
        debugLog('Calculated price (USD): ' + calculatedPrice);
        debugLog('Weight text: ' + weightText);
        debugLog('Final product name: ' + productName + ' (' + weightText + ')');
        
        // Add the item with weight and unit information
        if (typeof cart !== 'undefined' && cart.addItemWithWeight) {
            cart.addItemWithWeight(productId, `${productName} (${weightText})`, calculatedPrice, selectedWeight, unit);
            debugLog('Item added to cart successfully');
        } else {
            debugLog('ERROR: cart.addItemWithWeight function not available');
        }
        
        // Show notification
        debugLog('Notification: Added ' + productName + ' (' + weightText + ') to cart');
    }
    
    // Update cart display on load
    document.addEventListener('DOMContentLoaded', function() {
        cart.updateDisplay();
        
        // Set up weight selector change handler
        const shopWeightSelector = document.getElementById('weight_shop_999');
        shopWeightSelector.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const basePrice = parseFloat(this.dataset.basePrice);
            const selectedWeight = parseFloat(this.value);
            const newPrice = Math.round(basePrice * selectedWeight);
            
            debugLog('Weight changed - New price: ₹' + newPrice);
            
            // Update the displayed price
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
