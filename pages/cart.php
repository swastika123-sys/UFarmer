<?php
$pageTitle = 'Shopping Cart';
require_once '../includes/functions.php';

// Ensure user is logged in and is a customer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'customer') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit();
}

include '../components/header.php';
?>

<section class="cart-header py-4 bg-light">
    <div class="container">
        <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
        <p class="lead">Review your items and proceed to checkout</p>
    </div>
</section>

<section class="cart-content py-5">
    <div class="container">
        <div id="cartContainer">
            <!-- Cart items will be loaded here by JavaScript -->
        </div>
        
        <div id="emptyCart" class="text-center py-5" style="display: none;">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Start shopping for fresh, local produce from our farmers!</p>
            <a href="shop.php" class="btn btn-success btn-lg">
                <i class="fas fa-seedling"></i> Start Shopping
            </a>
        </div>
        
        <div id="cartSummary" class="row mt-4" style="display: none;">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5>Delivery Information</h5>
                        <form id="deliveryForm">
                            <div class="form-group">
                                <label for="delivery_address">Delivery Address</label>
                                <textarea id="delivery_address" class="form-control" rows="3" placeholder="Enter your delivery address..." required></textarea>
                            </div>
                            
                            <div class="form-group" id="deliverySlotContainer" style="display: none;">
                                <label for="delivery_slot">Delivery Time Slot</label>
                                <select id="delivery_slot" class="form-control">
                                    <option value="">Select delivery time...</option>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Same-day delivery available for orders placed 2+ hours in advance
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="delivery_notes">Special Instructions (Optional)</label>
                                <textarea id="delivery_notes" class="form-control" rows="2" placeholder="Any special delivery instructions..."></textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5>Order Summary</h5>
                        <div id="orderSummary">
                            <!-- Summary will be populated by JavaScript -->
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total: </strong>
                            <strong id="cartTotal">$0.00</strong>
                        </div>
                        
                        <!-- Wallet Balance Display -->
                        <div class="wallet-info mt-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-wallet"></i> Wallet Balance:</span>
                                <strong class="wallet-balance" id="walletBalance"><?php echo formatPrice(getUserWalletBalance($_SESSION['user_id'])); ?></strong>
                            </div>
                            <div id="walletStatus" class="mt-2"></div>
                        </div>
                        
                        <button id="checkoutBtn" class="btn btn-success btn-lg w-100 mt-3">
                            <i class="fas fa-wallet"></i> Pay with Wallet
                        </button>
                        <p class="text-muted text-center mt-2 small">
                            <i class="fas fa-shield-alt"></i> Secure wallet payment
                        </p>
                        <div class="text-center mt-2">
                            <a href="wallet.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus-circle"></i> Add Money to Wallet
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.cart-item {
    background: var(--white);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #eee;
}

.cart-item-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cart-item-details {
    display: flex;
    align-items: center;
    flex: 1;
}

.cart-item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    object-fit: cover;
    margin-right: 1rem;
}

.cart-item-name {
    font-weight: 600;
    color: var(--dark-green);
    margin-bottom: 0.25rem;
}

.cart-item-farmer {
    color: var(--gray-medium);
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.cart-item-price {
    color: var(--success);
    font-weight: 600;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 1rem;
}

.quantity-btn {
    background: var(--light-green);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.quantity-btn:hover {
    background: var(--secondary-green);
    color: white;
}

.quantity-input {
    width: 60px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.25rem;
}

.remove-btn {
    background: var(--danger);
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.remove-btn:hover {
    background: #c82333;
    transform: scale(1.1);
}

.farmer-group {
    margin-bottom: 2rem;
}

.farmer-group-header {
    background: var(--light-green);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.farmer-group-header h4 {
    margin: 0;
    color: var(--dark-green);
}

.farmer-group-total {
    text-align: right;
    color: var(--gray-medium);
    font-weight: 500;
}

/* Delivery slot styles */
#deliverySlotContainer {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

#deliverySlotContainer label {
    font-weight: 600;
    color: var(--dark-green);
}

#delivery_slot {
    margin-top: 0.5rem;
}

#delivery_slot option:disabled {
    color: #6c757d;
    font-style: italic;
}

.delivery-slot-info {
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #e3f2fd;
    border-radius: 4px;
    border-left: 3px solid #2196f3;
}

/* Wallet integration styles */
.wallet-info {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    border-left: 4px solid var(--primary-green);
}

.wallet-balance {
    color: #333;
    font-size: 1.1rem;
}

.wallet-insufficient {
    background: #f8d7da;
    border-left-color: var(--danger);
}

.wallet-insufficient .wallet-balance {
    color: var(--danger);
}

.wallet-sufficient {
    background: #d4edda;
    border-left-color: var(--success);
}

.wallet-sufficient .wallet-balance {
    color: var(--success);
}

@media (max-width: 768px) {
    .cart-item-info {
        flex-direction: column;
        gap: 1rem;
    }
    
    .cart-item-details {
        width: 100%;
    }
    
    .quantity-controls {
        margin: 0;
    }
}
</style>

<script>
// Store CSRF token for checkout
const csrfToken = '<?php echo generateCSRFToken(); ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
    
    // Checkout button handler
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        const address = document.getElementById('delivery_address').value.trim();
        const notes = document.getElementById('delivery_notes').value.trim();
        
        if (!address) {
            showNotification('Please enter a delivery address', 'error');
            return;
        }

        if (address.length < 10) {
            showNotification('Please enter a complete delivery address (minimum 10 characters)', 'error');
            return;
        }

        if (cart.items.length === 0) {
            showNotification('Your cart is empty', 'error');
            return;
        }

        // Prevent double submission
        if (this.disabled) {
            return;
        }

        // Disable checkout button during processing
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        // Get delivery slot information
        const deliverySlot = document.getElementById('delivery_slot').value;
        const deliverySlotOption = document.getElementById('delivery_slot').selectedOptions[0];
        const deliverySlotData = deliverySlot ? {
            slot_id: deliverySlot,
            date: deliverySlotOption?.dataset.date || null,
            start_time: deliverySlotOption?.dataset.startTime || null,
            end_time: deliverySlotOption?.dataset.endTime || null
        } : null;

        // Prepare checkout data
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('cart_items', JSON.stringify(cart.items));
        formData.append('delivery_address', address);
        formData.append('delivery_notes', notes);
        if (deliverySlotData) {
            formData.append('delivery_slot', JSON.stringify(deliverySlotData));
        }

        console.log('Checkout data:', {
            csrf_token: csrfToken,
            cart_items: cart.items,
            delivery_address: address,
            delivery_notes: notes
        });

        // Process checkout
        fetch('process-checkout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Checkout response:', data);
            if (data.success) {
                showNotification('Order placed successfully!', 'success');
                
                // Clear cart
                cart.items = [];
                cart.saveCart();
                
                // Redirect to success page with order details
                const orderIds = data.order_ids.join(',');
                window.location.href = `order-success.php?orders=${orderIds}&total=${data.total_amount}`;
            } else {
                showNotification(data.message, 'error');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-wallet"></i> Pay with Wallet';
            }
        })
        .catch(error => {
            console.error('Checkout error:', error);
            showNotification('An error occurred during checkout. Please try again.', 'error');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-wallet"></i> Pay with Wallet';
        });
    });
    
    // Debug cart contents
    if (cart.items && cart.items.length > 0) {
        console.log('Cart loaded with', cart.items.length, 'items');
        debugCart();
    }
});

function loadCartItems() {
    const cartContainer = document.getElementById('cartContainer');
    const emptyCart = document.getElementById('emptyCart');
    const cartSummary = document.getElementById('cartSummary');
    
    if (cart.items.length === 0) {
        cartContainer.innerHTML = '';
        emptyCart.style.display = 'block';
        cartSummary.style.display = 'none';
        return;
    }
    
    emptyCart.style.display = 'none';
    cartSummary.style.display = 'flex';
    
    // Group items by farmer (simulated)
    const groupedItems = groupItemsByFarmer(cart.items);
    
    let cartHTML = '';
    let totalAmount = 0;
    let allFarmersOfferDelivery = true;
    
    Object.keys(groupedItems).forEach(farmerName => {
        const items = groupedItems[farmerName];
        const farmerTotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        totalAmount += farmerTotal;
        
        cartHTML += `
            <div class="farmer-group">
                <div class="farmer-group-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="fas fa-seedling"></i> ${farmerName}</h4>
                        <span class="farmer-group-total">Subtotal: ${formatPrice(farmerTotal)}</span>
                    </div>
                </div>
        `;
        
        items.forEach(item => {
            cartHTML += createCartItemHTML(item);
        });
        
        cartHTML += '</div>';
    });
    
    cartContainer.innerHTML = cartHTML;
    document.getElementById('cartTotal').textContent = formatPrice(totalAmount);
    updateOrderSummary(groupedItems, totalAmount);
    
    // Show delivery slot selection if farmers offer delivery
    updateDeliverySlotOptions(groupedItems);
    
    // Add event listeners for quantity changes and remove buttons
    addCartEventListeners();
}

function groupItemsByFarmer(items) {
    // In a real app, you'd fetch farmer names from the database
    // For demo purposes, we'll simulate different farmers
    const farmerNames = ['Green Valley Farm', 'Sunshine Acres', 'Harvest Moon Farm', 'Meadowbrook Dairy'];
    
    const grouped = {};
    items.forEach((item, index) => {
        const farmerName = farmerNames[index % farmerNames.length];
        if (!grouped[farmerName]) {
            grouped[farmerName] = [];
        }
        grouped[farmerName].push(item);
    });
    
    return grouped;
}

function createCartItemHTML(item) {
    // Ensure we have a unit, default to 'kg' if missing
    const unit = item.unit || 'kg';
    
    // Determine product image with fallback mapping
    let imageUrl = `${SITE_URL}/assets/images/default-product.jpg`;
    
    // Check if item has a stored image
    if (item.image && item.image !== 'null' && item.image !== '') {
        imageUrl = `${SITE_URL}/uploads/${item.image}`;
    } else {
        // Use fallback mapping for known products
        const productImageMap = {
            'organic tomatoes': 'https://images.unsplash.com/photo-1546470427-ac4e015d2fd0?w=300&h=200&fit=crop',
            'mixed salad greens': 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300&h=200&fit=crop',
            'fresh basil': 'https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=300&h=200&fit=crop',
            'organic carrots': 'https://images.unsplash.com/photo-1445282768818-728615cc910a?w=300&h=200&fit=crop',
            'heirloom tomatoes': 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=300&h=200&fit=crop',
            'fresh strawberries': 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=300&h=200&fit=crop',
            'organic spinach': 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=300&h=200&fit=crop',
            'mixed berries': 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&h=200&fit=crop'
        };
        
        // Try to match product name with fallback images
        const itemNameLower = item.name.toLowerCase();
        for (const [productName, imageUrl_] of Object.entries(productImageMap)) {
            if (itemNameLower.includes(productName) || productName.includes(itemNameLower.split(' ')[0])) {
                imageUrl = imageUrl_;
                break;
            }
        }
    }
    
    return `
        <div class="cart-item" data-product-id="${item.id}">
            <div class="cart-item-info">
                <div class="cart-item-details">
                    <img src="${imageUrl}" alt="${item.name}" class="cart-item-image" onerror="this.src='${SITE_URL}/assets/images/default-product.jpg'" data-skip-fallback>
                    <div>
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${formatPrice(item.price)} / ${unit}</div>
                    </div>
                </div>
                
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" class="quantity-input" value="${item.quantity}" min="1" onchange="updateQuantity(${item.id}, this.value)">
                    <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <div class="item-total">
                    ${formatPrice(item.price * item.quantity)}
                </div>
                
                <button class="remove-btn" onclick="removeItem(${item.id})" title="Remove item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
}

function updateOrderSummary(groupedItems, totalAmount) {
    const summaryContainer = document.getElementById('orderSummary');
    let summaryHTML = '';
    
    Object.keys(groupedItems).forEach(farmerName => {
        const items = groupedItems[farmerName];
        const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);
        const farmerTotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        summaryHTML += `
            <div class="d-flex justify-content-between mb-2">
                <span>${farmerName} (${itemCount} items)</span>
                <span>${formatPrice(farmerTotal)}</span>
            </div>
        `;
    });
    
    summaryContainer.innerHTML = summaryHTML;
}

function addCartEventListeners() {
    // Event listeners are added through onclick attributes in the HTML
    // This function could be used for additional event handling if needed
}

// Update delivery slot options based on cart contents
function updateDeliverySlotOptions(groupedItems) {
    const deliverySlotContainer = document.getElementById('deliverySlotContainer');
    const deliverySlotSelect = document.getElementById('delivery_slot');
    
    // Check if any farmers offer delivery (simulate for demo)
    const farmersWithDelivery = Object.keys(groupedItems).filter(farmer => {
        // In real implementation, check farmer delivery capabilities from database
        return Math.random() > 0.3; // 70% of farmers offer delivery
    });
    
    if (farmersWithDelivery.length > 0) {
        deliverySlotContainer.style.display = 'block';
        loadDeliverySlots(farmersWithDelivery[0]); // Use first farmer's slots for demo
    } else {
        deliverySlotContainer.style.display = 'none';
    }
}

// Load available delivery slots for farmers
function loadDeliverySlots(farmerName) {
    const deliverySlotSelect = document.getElementById('delivery_slot');
    
    // Generate demo time slots (in real app, fetch from API)
    const slots = generateDeliverySlots();
    
    deliverySlotSelect.innerHTML = '<option value="">Select delivery time...</option>';
    
    slots.forEach(slot => {
        const option = document.createElement('option');
        option.value = slot.id;
        option.textContent = slot.display;
        option.dataset.date = slot.date;
        option.dataset.startTime = slot.startTime;
        option.dataset.endTime = slot.endTime;
        
        if (!slot.available) {
            option.disabled = true;
            option.textContent += ' (Unavailable)';
        }
        
        deliverySlotSelect.appendChild(option);
    });
}

// Generate demo delivery slots
function generateDeliverySlots() {
    const slots = [];
    const today = new Date();
    
    // Generate slots for today and next 3 days
    for (let dayOffset = 0; dayOffset < 4; dayOffset++) {
        const date = new Date(today);
        date.setDate(today.getDate() + dayOffset);
        
        const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        
        // Generate time slots for each day
        const timeSlots = [
            { start: '09:00', end: '11:00', label: '9 AM - 11 AM' },
            { start: '11:00', end: '13:00', label: '11 AM - 1 PM' },
            { start: '14:00', end: '16:00', label: '2 PM - 4 PM' },
            { start: '16:00', end: '18:00', label: '4 PM - 6 PM' },
            { start: '18:00', end: '20:00', label: '6 PM - 8 PM' }
        ];
        
        timeSlots.forEach((slot, index) => {
            const slotDate = new Date(date);
            const [startHour, startMin] = slot.start.split(':');
            slotDate.setHours(parseInt(startHour), parseInt(startMin));
            
            // Check if slot is available (2 hours in advance for same day)
            const now = new Date();
            const twoHoursLater = new Date(now.getTime() + 2 * 60 * 60 * 1000);
            const available = dayOffset > 0 || slotDate > twoHoursLater;
            
            slots.push({
                id: `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}_${slot.start}`,
                display: `${dayName}, ${dateStr} - ${slot.label}`,
                date: date.toISOString().split('T')[0],
                startTime: slot.start,
                endTime: slot.end,
                available: available
            });
        });
    }
    
    return slots;
}

// ...existing code...

function updateQuantity(productId, newQuantity) {
    const quantity = parseInt(newQuantity);
    if (quantity < 1) {
        removeItem(productId);
        return;
    }
    
    cart.updateQuantity(productId, quantity);
    loadCartItems();
    updateWalletStatus(); // Update wallet status when quantity changes
}

function removeItem(productId) {
    if (confirm('Remove this item from your cart?')) {
        cart.removeItem(productId);
        loadCartItems();
    }
}

function updateWalletStatus() {
    const walletBalanceText = document.getElementById('walletBalance').textContent;
    const cartTotalText = document.getElementById('cartTotal').textContent;
    
    // Extract numeric values from formatted currency strings
    const walletBalance = parseFloat(walletBalanceText.replace(/[₹,\s]/g, ''));
    const cartTotal = parseFloat(cartTotalText.replace(/[₹,\s]/g, ''));
    
    const walletInfo = document.querySelector('.wallet-info');
    const walletStatus = document.getElementById('walletStatus');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    console.log('Wallet Balance:', walletBalance, 'Cart Total:', cartTotal); // Debug log
    
    if (cartTotal === 0 || isNaN(cartTotal)) {
        walletStatus.innerHTML = '';
        walletInfo.className = 'wallet-info mt-3 mb-3';
        checkoutBtn.disabled = true;
        return;
    }
    
    if (isNaN(walletBalance)) {
        walletStatus.innerHTML = '<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Unable to check wallet balance</small>';
        walletInfo.className = 'wallet-info mt-3 mb-3';
        checkoutBtn.disabled = true;
        return;
    }
    
    if (walletBalance >= cartTotal) {
        walletStatus.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Sufficient balance for purchase</small>';
        walletInfo.className = 'wallet-info wallet-sufficient mt-3 mb-3';
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = '<i class="fas fa-wallet"></i> Pay with Wallet';
    } else {
        const shortfall = cartTotal - walletBalance;
        walletStatus.innerHTML = `<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Need ₹${shortfall.toLocaleString('en-IN', {maximumFractionDigits: 0})} more</small>`;
        walletInfo.className = 'wallet-info wallet-insufficient mt-3 mb-3';
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-times"></i> Insufficient Balance';
    }
}

// Ensure formatPrice function is available in cart context
if (typeof formatPrice === 'undefined') {
    function formatPrice(price) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 0
        }).format(price * 83);
    }
}

// Add function to refresh wallet balance from server
function refreshWalletBalance() {
    // In a real app, this would make an AJAX call to get current balance
    // For now, we'll just ensure the display is correct
    const walletBalanceElement = document.getElementById('walletBalance');
    if (walletBalanceElement) {
        const currentText = walletBalanceElement.textContent;
        console.log('Current wallet balance display:', currentText);
    }
}

// Add cart data migration function
function migrateCartData() {
    let cartItems = JSON.parse(localStorage.getItem('cart') || '[]');
    let needsUpdate = false;
    
    cartItems = cartItems.map(item => {
        // Fix missing or incorrect unit data
        if (!item.unit || item.unit === 'lb' || item.unit === 'each') {
            item.unit = 'kg'; // Default to kg for legacy items
            needsUpdate = true;
        }
        
        // Ensure price is a number
        if (typeof item.price === 'string') {
            item.price = parseFloat(item.price);
            needsUpdate = true;
        }
        
        // Ensure quantity is a number
        if (typeof item.quantity === 'string') {
            item.quantity = parseInt(item.quantity);
            needsUpdate = true;
        }
        
        return item;
    });
    
    if (needsUpdate) {
        localStorage.setItem('cart', JSON.stringify(cartItems));
        console.log('Cart data migrated to fix legacy items');
    }
    
    return cartItems;
}

// Update cart initialization
if (typeof cart === 'undefined') {
    window.cart = {
        items: migrateCartData(),
        // ...existing cart methods...
    };
} else {
    cart.items = migrateCartData();
}

// Debug function to inspect cart contents
function debugCart() {
    console.log('=== CART DEBUG ===');
    console.log('Cart items:', cart.items);
    cart.items.forEach((item, index) => {
        console.log(`Item ${index}:`, {
            id: item.id,
            name: item.name,
            price: item.price,
            unit: item.unit,
            quantity: item.quantity
        });
    });
    console.log('=== END DEBUG ===');
}

// Function to clear cart and fix any corrupted data
function clearCart() {
    if (confirm('Clear all items from cart? This will remove any corrupted data.')) {
        localStorage.removeItem('cart');
        cart.items = [];
        loadCartItems();
        console.log('Cart cleared');
    }
}

// Make sure the global variables are available
const SITE_URL = '<?php echo SITE_URL; ?>';
</script>

<?php include '../components/footer.php'; ?>
