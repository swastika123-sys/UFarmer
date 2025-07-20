// UFarmer JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeNavigation();
    initializeForms();
    initializeImagePreviews();
    initializeModals();
    initializeTooltips();
});

// Navigation functionality
function initializeNavigation() {
    const navbar = document.querySelector('.navbar');
    
    // Mobile menu toggle (if needed)
    const mobileToggle = document.querySelector('.mobile-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            navbar.classList.toggle('mobile-open');
        });
    }
    
    // Active link highlighting
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
}

// Form validation and enhancement
function initializeForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let message = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required';
    }
    
    // Email validation
    else if (type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }
    }
    
    // Password validation
    else if (field.name === 'password' && value) {
        if (value.length < 6) {
            isValid = false;
            message = 'Password must be at least 6 characters long';
        }
    }
    
    // Confirm password validation
    else if (field.name === 'confirm_password' && value) {
        const password = document.querySelector('input[name="password"]');
        if (password && value !== password.value) {
            isValid = false;
            message = 'Passwords do not match';
        }
    }
    
    // Phone validation
    else if (field.name === 'phone' && value) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
            isValid = false;
            message = 'Please enter a valid phone number';
        }
    }
    
    // Update field appearance
    updateFieldValidation(field, isValid, message);
    return isValid;
}

function updateFieldValidation(field, isValid, message) {
    const formGroup = field.closest('.form-group');
    const feedback = formGroup.querySelector('.invalid-feedback');
    
    if (isValid) {
        field.classList.remove('is-invalid');
        if (feedback) feedback.textContent = '';
    } else {
        field.classList.add('is-invalid');
        if (feedback) {
            feedback.textContent = message;
        } else {
            const feedbackEl = document.createElement('div');
            feedbackEl.className = 'invalid-feedback';
            feedbackEl.textContent = message;
            formGroup.appendChild(feedbackEl);
        }
    }
}

// Image preview functionality
function initializeImagePreviews() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.parentNode.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview';
                        preview.style.maxWidth = '200px';
                        preview.style.maxHeight = '200px';
                        preview.style.marginTop = '10px';
                        preview.style.borderRadius = '5px';
                        input.parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

// Modal functionality
function initializeModals() {
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modals = document.querySelectorAll('.modal');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                showModal(modal);
            }
        });
    });
    
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                hideModal(modal);
            });
        }
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                hideModal(modal);
            }
        });
    });
}

function showModal(modal) {
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideModal(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Tooltip functionality
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

function showTooltip(element) {
    const text = element.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
    `;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    
    element._tooltip = tooltip;
}

function hideTooltip() {
    const tooltips = document.querySelectorAll('.tooltip');
    tooltips.forEach(tooltip => tooltip.remove());
}

// Shopping cart functionality
class ShoppingCart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('cart')) || [];
        this.updateCartDisplay();
    }
    
    addItem(productId, name, price, quantity = 1) {
        const existingItem = this.items.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({
                id: productId,
                name: name,
                price: price,
                quantity: quantity
            });
        }
        
        this.saveCart();
        this.updateCartDisplay();
        this.showCartNotification('Item added to cart!');
    }
    
    removeItem(productId) {
        this.items = this.items.filter(item => item.id !== productId);
        this.saveCart();
        this.updateCartDisplay();
    }
    
    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.id === productId);
        if (item) {
            item.quantity = quantity;
            if (quantity <= 0) {
                this.removeItem(productId);
            } else {
                this.saveCart();
                this.updateCartDisplay();
            }
        }
    }
    
    getTotalItems() {
        return this.items.reduce((total, item) => total + item.quantity, 0);
    }
    
    getTotalPrice() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }
    
    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.items));
    }
    
    updateCartDisplay() {
        const cartCounter = document.querySelector('.cart-counter');
        if (cartCounter) {
            const totalItems = this.getTotalItems();
            cartCounter.textContent = totalItems;
            cartCounter.style.display = totalItems > 0 ? 'block' : 'none';
        }
    }
    
    showCartNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    getItems() {
        return this.items;
    }
    
    clearCart() {
        this.items = [];
        this.saveCart();
        this.updateCartDisplay();
    }
}

// Initialize shopping cart
const cart = new ShoppingCart();

// Star rating functionality
function initializeStarRating() {
    const starRatings = document.querySelectorAll('.star-rating');
    
    starRatings.forEach(rating => {
        const stars = rating.querySelectorAll('.star');
        const input = rating.querySelector('input[type="hidden"]');
        
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const value = index + 1;
                input.value = value;
                updateStars(stars, value);
            });
            
            star.addEventListener('mouseenter', function() {
                const value = index + 1;
                updateStars(stars, value);
            });
        });
        
        rating.addEventListener('mouseleave', function() {
            const value = input.value || 0;
            updateStars(stars, value);
        });
    });
}

function updateStars(stars, rating) {
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('#search-input');
    const searchResults = document.querySelector('#search-results');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                if (searchResults) {
                    searchResults.innerHTML = '';
                }
            }
        });
    }
}

function performSearch(query) {
    // Implement AJAX search functionality
    fetch(`/api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(data);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

function displaySearchResults(results) {
    const searchResults = document.querySelector('#search-results');
    if (!searchResults) return;
    
    if (results.length === 0) {
        searchResults.innerHTML = '<p>No results found</p>';
        return;
    }
    
    const html = results.map(item => `
        <div class="search-result-item">
            <img src="${item.image}" alt="${item.name}">
            <div>
                <h4>${item.name}</h4>
                <p>${item.description}</p>
                <span class="price">$${item.price}</span>
            </div>
        </div>
    `).join('');
    
    searchResults.innerHTML = html;
}

// Filter functionality for shop page
function updateFilter(filterType, filterValue) {
    const url = new URL(window.location);
    
    if (filterValue) {
        url.searchParams.set(filterType, filterValue);
    } else {
        url.searchParams.delete(filterType);
    }
    
    // Redirect to updated URL
    window.location.href = url.toString();
}

// Utility functions
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alert, container.firstChild);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(price * 83);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(date));
}

// AJAX helper function
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// Checkout functionality
function initializeCheckout() {
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', handleCheckout);
    }
}

async function handleCheckout() {
    const cartItems = cart.getItems();
    const deliveryAddress = document.getElementById('delivery_address')?.value?.trim();
    const deliveryNotes = document.getElementById('delivery_notes')?.value?.trim();
    
    if (cartItems.length === 0) {
        showNotification('Your cart is empty', 'error');
        return;
    }
    
    if (!deliveryAddress) {
        showNotification('Please enter a delivery address', 'error');
        return;
    }
    
    const checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    try {
        const formData = new FormData();
        formData.append('cart_items', JSON.stringify(cartItems));
        formData.append('delivery_address', deliveryAddress);
        formData.append('delivery_notes', deliveryNotes);
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        const response = await fetch('process-checkout.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Clear cart
            cart.clearCart();
            
            // Show success message
            showNotification('Order placed successfully! Total paid: $' + result.total_amount.toFixed(2), 'success');
            
            // Update wallet balance display if it exists
            const walletBalance = document.getElementById('walletBalance');
            if (walletBalance) {
                walletBalance.textContent = '$' + result.remaining_balance.toFixed(2);
            }
            
            // Redirect to orders page after a delay
            setTimeout(() => {
                window.location.href = 'orders.php';
            }, 2000);
            
        } else {
            showNotification(result.message, 'error');
        }
        
    } catch (error) {
        showNotification('An error occurred while processing your order', 'error');
        console.error('Checkout error:', error);
    } finally {
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = '<i class="fas fa-wallet"></i> Pay with Wallet';
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 1000;
        max-width: 300px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Initialize additional features when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeStarRating();
    initializeSearch();
    initializeCheckout();
});

// Weight-based cart functionality
function addToCartWithWeight(productId, productName, basePrice, unit = 'kg') {
    const weightSelect = document.getElementById(`weight_${productId}`);
    const selectedWeight = parseFloat(weightSelect.value);
    const calculatedPrice = basePrice * selectedWeight;
    const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
    
    // Add the item with weight and unit information
    cart.addItemWithWeight(productId, `${productName} (${weightText})`, calculatedPrice, selectedWeight, unit);
    
    // Show notification
    if (typeof notificationSystem !== 'undefined') {
        notificationSystem.showSuccess(`Added ${productName} (${weightText}) to cart`);
    }
}

// Enhanced cart object with weight support
if (typeof cart !== 'undefined') {
    cart.addItemWithWeight = function(productId, name, totalPrice, weight, unit = 'kg', imageUrl = null) {
        // Create unique identifier with weight
        const itemKey = `${productId}_${weight}`;
        
        const existingIndex = this.items.findIndex(item => item.key === itemKey);
        
        if (existingIndex >= 0) {
            this.items[existingIndex].quantity += 1;
        } else {
            // Try to capture product image from the page if not provided
            if (!imageUrl) {
                const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                if (productCard) {
                    const productImage = productCard.querySelector('img, .card-img, .product-image');
                    if (productImage && productImage.src && !productImage.src.includes('default-product.jpg')) {
                        imageUrl = productImage.src;
                    }
                }
            }
            
            this.items.push({
                key: itemKey,
                id: productId,
                name: name,
                price: totalPrice,
                quantity: 1,
                weight: weight,
                unit: unit,
                image: imageUrl,
                timestamp: Date.now()
            });
        }
        
        this.saveCart();
        this.updateCartDisplay();
        this.showCartNotification(`Added ${name} to cart!`);
        
        // Update cart counter specifically
        this.updateCartCounter();
        
        // Load cart items if function exists (for cart page)
        if (typeof loadCartItems === 'function') {
            loadCartItems();
        }
    };
    
    // Add updateCartCounter method if it doesn't exist
    if (!cart.updateCartCounter) {
        cart.updateCartCounter = function() {
            const cartCounter = document.querySelector('.cart-counter');
            if (cartCounter) {
                const totalItems = this.getTotalItems();
                cartCounter.textContent = totalItems;
                cartCounter.style.display = totalItems > 0 ? 'block' : 'none';
            }
        };
    }
}

// Update weight selector prices when changed
document.addEventListener('DOMContentLoaded', function() {
    const weightSelectors = document.querySelectorAll('.weight-select');
    weightSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const basePrice = parseFloat(this.dataset.basePrice);
            const selectedWeight = parseFloat(this.value);
            const newPrice = Math.round(basePrice * selectedWeight);
            
            // Update the displayed price in the product card
            const priceElement = this.closest('.card-body').querySelector('.price-indian');
            if (priceElement) {
                const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
                priceElement.textContent = `₹${newPrice.toLocaleString('en-IN')} / ${weightText}`;
            }
        });
    });
});
