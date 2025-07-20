<?php
$pageTitle = "My Orders";
require_once '../components/header.php';

if (!isLoggedIn() || $_SESSION['user_type'] !== 'customer') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$currentUser = getCurrentUser();

// Get customer orders with delivery information
$stmt = $pdo->prepare("
    SELECT o.*, f.farm_name, u.name as farmer_name, f.id as farmer_id
    FROM orders o 
    JOIN farmers f ON o.farmer_id = f.id 
    JOIN users u ON f.user_id = u.id 
    WHERE o.customer_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$currentUser['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get farmer average rating
function getFarmerRating($farmerId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
        FROM reviews 
        WHERE farmer_id = ?
    ");
    $stmt->execute([$farmerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'rating' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
        'count' => $result['review_count']
    ];
}
?>

<main class="main-content">
    <div class="container">
        <div class="orders-header">
            <h1><i class="fas fa-clipboard-list"></i> My Orders</h1>
            <p>Track your purchases and order history</p>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders. Start shopping for fresh local produce!</p>
                <a href="shop.php" class="btn btn-primary">
                    <i class="fas fa-seedling"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <?php $farmerRating = getFarmerRating($order['farmer_id'], $pdo); ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h4>Order #<?php echo $order['id']; ?></h4>
                                <p class="order-farm">
                                    <i class="fas fa-store"></i>
                                    <?php echo htmlspecialchars(html_entity_decode($order['farm_name'], ENT_QUOTES)); ?>
                                    by <?php echo htmlspecialchars($order['farmer_name']); ?>
                                </p>
                                
                                <!-- Farmer Rating Display -->
                                <div class="farmer-rating">
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $farmerRating['rating'] ? 'star-filled' : 'star-empty'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-text">
                                        <?php echo $farmerRating['rating']; ?>/5 
                                        (<?php echo $farmerRating['count']; ?> reviews)
                                    </span>
                                </div>
                                
                                <p class="order-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                                </p>
                                
                <!-- Delivery Information -->
                <?php if ($order['delivery_date']): ?>
                    <p class="delivery-schedule">
                        <i class="fas fa-truck"></i>
                        <strong>Delivery:</strong> 
                        <?php 
                        $deliveryDate = date('M j, Y', strtotime($order['delivery_date']));
                        
                        if ($order['delivery_date'] == date('Y-m-d')) {
                            echo "Today (same day delivery)";
                        } else {
                            echo "$deliveryDate";
                        }
                        ?>
                    </p>
                <?php else: ?>
                    <p class="delivery-schedule">
                        <i class="fas fa-truck"></i>
                        <strong>Delivery:</strong> Same day (within 2 hours)
                    </p>
                <?php endif; ?>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <div class="order-total">
                                    <?php echo formatPrice($order['total_amount']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <?php
                            // Get order items
                            $stmt = $pdo->prepare("
                                SELECT oi.*, p.name, p.unit 
                                FROM order_items oi 
                                JOIN products p ON oi.product_id = p.id 
                                WHERE oi.order_id = ?
                            ");
                            $stmt->execute([$order['id']]);
                            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            
                            <div class="order-items">
                                <h5>Items:</h5>
                                <?php foreach ($items as $item): ?>
                                    <div class="order-item">
                                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="item-details">
                                            <?php echo $item['quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?> 
                                            × <?php echo formatPrice($item['price']); ?>
                                        </span>
                                        <span class="item-total">
                                            <?php echo formatPrice($item['quantity'] * $item['price']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="delivery-info">
                                <h6><i class="fas fa-map-marker-alt"></i> Delivery Address:</h6>
                                <p><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
                                
                                <?php if (isset($order['notes']) && $order['notes']): ?>
                                    <h6><i class="fas fa-sticky-note"></i> Special Instructions:</h6>
                                    <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <?php if ($order['status'] === 'pending'): ?>
                                <?php 
                                // Check if order is within 30-minute cancellation window
                                $orderTime = strtotime($order['created_at']);
                                $currentTime = time();
                                $timeDifference = $currentTime - $orderTime;
                                $canCancel = $timeDifference <= 1800; // 30 minutes = 1800 seconds
                                ?>
                                
                                <?php if ($canCancel): ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-times"></i> Cancel Order
                                    </button>
                                <?php else: ?>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Order can no longer be cancelled (30-minute window expired)
                                    </small>
                                <?php endif; ?>
                            <?php elseif ($order['status'] === 'cancelled'): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    This order was cancelled. Refund has been processed to your wallet.
                                </div>
                            <?php endif; ?>
                            <a href="farmer/profile.php?id=<?php echo $order['farmer_id']; ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-store"></i> Visit Farm
                            </a>
                            <?php if ($order['status'] === 'delivered'): ?>
                                <?php
                                // Check if user has already reviewed this order
                                $reviewStmt = $pdo->prepare("SELECT id FROM reviews WHERE customer_id = ? AND order_id = ?");
                                $reviewStmt->execute([$currentUser['id'], $order['id']]);
                                $hasReviewed = $reviewStmt->fetch();
                                ?>
                                
                                <?php if (!$hasReviewed): ?>
                                    <div class="review-prompt mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Order delivered! Share your experience to help other customers.
                                        </small>
                                    </div>
                                    <button class="btn btn-outline-success btn-sm" onclick="openRatingModal(<?php echo $order['id']; ?>, <?php echo $order['farmer_id']; ?>, '<?php echo addslashes($order['farm_name']); ?>')">
                                        <i class="fas fa-star"></i> Rate & Review
                                    </button>
                                <?php else: ?>
                                    <span class="btn btn-outline-secondary btn-sm disabled">
                                        <i class="fas fa-check"></i> Reviewed
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Rating Modal -->
<div id="ratingModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-star"></i> Rate Your Experience</h3>
            <span class="close" onclick="closeRatingModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="ratingForm">
                <input type="hidden" id="orderId" name="order_id">
                <input type="hidden" id="farmerId" name="farmer_id">
                
                <div class="form-group">
                    <label>How was your experience with <span id="farmName"></span>?</label>
                    <div class="rating-input">
                        <i class="fas fa-star rating-star" data-rating="1"></i>
                        <i class="fas fa-star rating-star" data-rating="2"></i>
                        <i class="fas fa-star rating-star" data-rating="3"></i>
                        <i class="fas fa-star rating-star" data-rating="4"></i>
                        <i class="fas fa-star rating-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" id="rating" name="rating" required>
                </div>
                
                <div class="form-group">
                    <label for="review">Share your review (optional)</label>
                    <textarea id="review" name="review" rows="4" placeholder="Tell others about your experience with this farm's products and service..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRatingModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Enhanced styles for the rating system */
.farmer-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
}

.stars {
    display: flex;
    gap: 2px;
}

.star-filled {
    color: #ffd700;
}

.star-empty {
    color: #ddd;
}

.rating-text {
    font-size: 0.9rem;
    color: var(--gray-medium);
}

.delivery-schedule {
    margin: 0.25rem 0;
    color: var(--primary-green);
    font-size: 0.9rem;
}

.delivery-schedule strong {
    color: var(--dark-green);
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: var(--primary-green);
}

.close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.rating-input {
    display: flex;
    gap: 5px;
    margin: 1rem 0;
}

.rating-star {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-star:hover,
.rating-star.active {
    color: #ffd700;
}

.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    resize: vertical;
    font-family: inherit;
}

.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-green);
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--primary-green);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-green-dark);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* Previous styles continue... */
.orders-header {
    margin-bottom: 2rem;
}

.orders-header h1 {
    color: var(--primary-green);
}

.empty-state {
    text-align: center;
    padding: 4rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 4rem;
    color: var(--gray-medium);
    margin-bottom: 1rem;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.order-card:hover {
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}

.order-info h4 {
    color: var(--primary-green);
    margin-bottom: 0.5rem;
}

.order-farm, .order-date {
    margin: 0.25rem 0;
    color: var(--gray-medium);
    font-size: 0.9rem;
}

.order-status {
    text-align: right;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #cce5ff;
    color: #004085;
}

.status-preparing {
    background: #e2e3e5;
    color: #383d41;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d1ecf1;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.order-total {
    font-size: 1.2rem;
    font-weight: bold;
    color: var(--primary-green);
    margin-top: 0.5rem;
}

.order-details {
    padding: 1.5rem;
}

.order-items {
    margin-bottom: 1.5rem;
}

.order-items h5 {
    color: var(--dark-green);
    margin-bottom: 1rem;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.order-item:last-child {
    border-bottom: none;
}

.item-name {
    font-weight: 500;
    flex: 1;
}

.item-details {
    color: var(--gray-medium);
    margin: 0 1rem;
}

.item-total {
    font-weight: bold;
    color: var(--primary-green);
}

.delivery-info {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 5px;
    margin-top: 1rem;
}

.delivery-info h6 {
    color: var(--dark-green);
    margin-bottom: 0.5rem;
}

.delivery-info p {
    margin: 0;
    color: var(--gray-dark);
}

.order-actions {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
}

.btn-outline-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
    padding: 0.75rem 1rem;
    border: 1px solid;
    border-radius: 0.375rem;
    margin: 0;
    font-size: 0.9rem;
}

.alert-info i {
    margin-right: 0.5rem;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .order-status {
        text-align: left;
    }
    
    .order-item {
        flex-direction: column;
        align-items: start;
        gap: 0.25rem;
    }
    
    .order-actions {
        flex-direction: column;
    }
    
    .order-actions .btn {
        width: 100%;
        text-align: center;
    }
    
    .modal-content {
        width: 95%;
        margin: 10% auto;
    }
}
</style>

<script>
let selectedRating = 0;

function openRatingModal(orderId, farmerId, farmName) {
    document.getElementById('orderId').value = orderId;
    document.getElementById('farmerId').value = farmerId;
    document.getElementById('farmName').textContent = farmName;
    document.getElementById('ratingModal').style.display = 'block';
    selectedRating = 0;
    updateStars();
}

function closeRatingModal() {
    document.getElementById('ratingModal').style.display = 'none';
    document.getElementById('ratingForm').reset();
    selectedRating = 0;
    updateStars();
}

// Rating star functionality
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-star');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            document.getElementById('rating').value = selectedRating;
            updateStars();
        });
        
        star.addEventListener('mouseover', function() {
            const hoverRating = parseInt(this.dataset.rating);
            highlightStars(hoverRating);
        });
    });
    
    document.querySelector('.rating-input').addEventListener('mouseleave', function() {
        updateStars();
    });
});

function highlightStars(rating) {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

function updateStars() {
    highlightStars(selectedRating);
}

// Form submission
document.getElementById('ratingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (selectedRating === 0) {
        showNotification('Please select a rating', 'error');
        return;
    }
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    fetch('submit-review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Thank you for your review!', 'success');
            closeRatingModal();
            // Refresh page to show updated rating
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Error submitting review', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = 'Submit Review';
        submitBtn.disabled = false;
    });
});

// Previous functions continue...
function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?\n\nNote: Orders can only be cancelled within 30 minutes of placement. A full refund will be processed to your wallet.')) {
        return;
    }
    
    // Show loading state
    const cancelBtn = document.querySelector(`button[onclick="cancelOrder(${orderId})"]`);
    const originalText = cancelBtn.innerHTML;
    cancelBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
    cancelBtn.disabled = true;
    
    // Prepare request data
    const requestData = {
        order_id: orderId
    };
    
    // Add CSRF token if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        requestData.csrf_token = csrfToken.getAttribute('content');
    }
    
    fetch('cancel-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            showNotification(data.message, 'success');
            
            // Update the order card to show cancelled status
            const orderCard = cancelBtn.closest('.order-card');
            if (orderCard) {
                const statusBadge = orderCard.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = 'status-badge status-cancelled';
                    statusBadge.textContent = 'Cancelled';
                }
                
                // Remove the cancel button
                cancelBtn.remove();
                
                // Add cancellation info
                const orderActions = orderCard.querySelector('.order-actions');
                if (orderActions) {
                    const refundInfo = document.createElement('div');
                    refundInfo.className = 'alert alert-info mt-2';
                    refundInfo.innerHTML = `
                        <i class="fas fa-info-circle"></i> 
                        Order cancelled. Refund of ₹${data.refund_amount_inr} processed to your wallet.
                    `;
                    orderActions.appendChild(refundInfo);
                }
            }
            
            // Refresh wallet balance in header if available
            setTimeout(() => {
                location.reload();
            }, 2000);
            
        } else {
            showNotification(data.message, 'error');
            // Restore button state
            cancelBtn.innerHTML = originalText;
            cancelBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while cancelling the order. Please try again.', 'error');
        // Restore button state
        cancelBtn.innerHTML = originalText;
        cancelBtn.disabled = false;
    });
}

function leaveReview(farmerId) {
    window.location.href = `farmer/profile.php?id=${farmerId}#reviews`;
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('ratingModal');
    if (event.target === modal) {
        closeRatingModal();
    }
}
</script>

<?php require_once '../components/footer.php'; ?>
