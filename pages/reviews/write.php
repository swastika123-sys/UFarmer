<?php
$pageTitle = "Write Review";
require_once '../../components/header.php';

if (!isLoggedIn() || $_SESSION['user_type'] !== 'customer') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$currentUser = getCurrentUser();
$orderId = $_GET['order_id'] ?? null;
$farmerId = $_GET['farmer_id'] ?? null;

if (!$orderId || !$farmerId) {
    header('Location: ' . SITE_URL . '/pages/orders.php');
    exit;
}

// Verify customer can review this order
if (!canUserReviewOrder($currentUser['id'], $orderId)) {
    $_SESSION['error'] = "You cannot review this order.";
    header('Location: ' . SITE_URL . '/pages/orders.php');
    exit;
}

// Get order and farmer details
$stmt = $pdo->prepare("
    SELECT o.*, f.farm_name, u.name as farmer_name, f.id as farmer_id
    FROM orders o 
    JOIN farmers f ON o.farmer_id = f.id 
    JOIN users u ON f.user_id = u.id 
    WHERE o.id = ? AND o.customer_id = ?
");
$stmt->execute([$orderId, $currentUser['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: ' . SITE_URL . '/pages/orders.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $productQuality = (int)$_POST['product_quality'];
    $delivery = (int)$_POST['delivery'];
    $service = (int)$_POST['service'];
    $wouldRecommend = isset($_POST['would_recommend']);
    
    if ($rating >= 1 && $rating <= 5 && $productQuality >= 1 && $productQuality <= 5 && 
        $delivery >= 1 && $delivery <= 5 && $service >= 1 && $service <= 5) {
        
        if (submitReview($currentUser['id'], $farmerId, $orderId, $rating, $comment, $productQuality, $delivery, $service, $wouldRecommend)) {
            $_SESSION['success'] = "Thank you for your review! It has been submitted successfully.";
            header('Location: ' . SITE_URL . '/pages/orders.php');
            exit;
        } else {
            $error = "Failed to submit review. Please try again.";
        }
    } else {
        $error = "Please provide all required ratings.";
    }
}
?>

<main class="main-content">
    <div class="container">
        <div class="review-header">
            <h1><i class="fas fa-star"></i> Write Review</h1>
            <p>Share your experience with <?php echo htmlspecialchars($order['farm_name']); ?></p>
        </div>

        <div class="review-container">
            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="order-info">
                    <p><strong>Order #<?php echo $order['id']; ?></strong></p>
                    <p><strong>Farm:</strong> <?php echo htmlspecialchars($order['farm_name']); ?></p>
                    <p><strong>Farmer:</strong> <?php echo htmlspecialchars($order['farmer_name']); ?></p>
                    <p><strong>Order Date:</strong> <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                    <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                </div>

                <div class="order-items">
                    <h4>Items Ordered:</h4>
                    <?php foreach ($orderItems as $item): ?>
                        <div class="item">
                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                            <span>Qty: <?php echo $item['quantity']; ?></span>
                            <span>$<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="review-form-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="review-form">
                    <div class="rating-section">
                        <h3>Rate Your Experience</h3>
                        
                        <div class="rating-group">
                            <label>Overall Rating</label>
                            <div class="star-rating" data-rating="0">
                                <input type="hidden" name="rating" value="0">
                                <span class="star" data-value="1">★</span>
                                <span class="star" data-value="2">★</span>
                                <span class="star" data-value="3">★</span>
                                <span class="star" data-value="4">★</span>
                                <span class="star" data-value="5">★</span>
                            </div>
                            <div class="rating-text"></div>
                        </div>

                        <div class="detailed-ratings">
                            <div class="rating-group">
                                <label>Product Quality</label>
                                <div class="star-rating" data-rating="0">
                                    <input type="hidden" name="product_quality" value="0">
                                    <span class="star" data-value="1">★</span>
                                    <span class="star" data-value="2">★</span>
                                    <span class="star" data-value="3">★</span>
                                    <span class="star" data-value="4">★</span>
                                    <span class="star" data-value="5">★</span>
                                </div>
                            </div>

                            <div class="rating-group">
                                <label>Delivery Experience</label>
                                <div class="star-rating" data-rating="0">
                                    <input type="hidden" name="delivery" value="0">
                                    <span class="star" data-value="1">★</span>
                                    <span class="star" data-value="2">★</span>
                                    <span class="star" data-value="3">★</span>
                                    <span class="star" data-value="4">★</span>
                                    <span class="star" data-value="5">★</span>
                                </div>
                            </div>

                            <div class="rating-group">
                                <label>Customer Service</label>
                                <div class="star-rating" data-rating="0">
                                    <input type="hidden" name="service" value="0">
                                    <span class="star" data-value="1">★</span>
                                    <span class="star" data-value="2">★</span>
                                    <span class="star" data-value="3">★</span>
                                    <span class="star" data-value="4">★</span>
                                    <span class="star" data-value="5">★</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="comment-section">
                        <label for="comment">Share Your Experience</label>
                        <textarea 
                            name="comment" 
                            id="comment" 
                            rows="5" 
                            placeholder="Tell other customers about your experience with this farmer. What did you like? How was the quality? Any suggestions?"
                            maxlength="1000"
                        ></textarea>
                        <small class="text-muted">Optional - Max 1000 characters</small>
                    </div>

                    <div class="recommendation-section">
                        <label class="checkbox-label">
                            <input type="checkbox" name="would_recommend" checked>
                            <span class="checkmark"></span>
                            I would recommend this farmer to others
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="<?php echo SITE_URL; ?>/pages/orders.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-star"></i> Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
.review-header {
    text-align: center;
    margin-bottom: 2rem;
}

.review-header h1 {
    color: var(--primary-green);
    margin-bottom: 0.5rem;
}

.review-container {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.order-summary {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    height: fit-content;
}

.order-summary h3 {
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.order-info p {
    margin: 0.5rem 0;
    color: #333;
}

.order-items {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

.order-items h4 {
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.review-form-container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.rating-section {
    margin-bottom: 2rem;
}

.rating-section h3 {
    color: var(--primary-green);
    margin-bottom: 1.5rem;
}

.rating-group {
    margin-bottom: 1.5rem;
}

.rating-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.star-rating {
    display: flex;
    gap: 0.25rem;
    align-items: center;
    margin-bottom: 0.5rem;
}

.star-rating .star {
    font-size: 1.5rem;
    color: #ddd;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}

.star-rating .star:hover,
.star-rating .star.active {
    color: #ffc107;
    transform: scale(1.1);
}

.rating-text {
    font-size: 0.9rem;
    color: #666;
    min-height: 1.2rem;
}

.detailed-ratings {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 1rem;
}

.comment-section {
    margin-bottom: 2rem;
}

.comment-section label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.comment-section textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    line-height: 1.5;
    resize: vertical;
    transition: border-color 0.3s ease;
}

.comment-section textarea:focus {
    outline: none;
    border-color: var(--primary-green);
}

.recommendation-section {
    margin-bottom: 2rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    font-weight: 500;
    color: #333;
}

.checkbox-label input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: var(--primary-green);
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: var(--primary-green);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-green-dark);
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

@media (max-width: 768px) {
    .review-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .detailed-ratings {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingTexts = {
        1: 'Poor',
        2: 'Fair', 
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
    };

    // Initialize all star ratings
    document.querySelectorAll('.star-rating').forEach(rating => {
        const stars = rating.querySelectorAll('.star');
        const input = rating.querySelector('input[type="hidden"]');
        const textElement = rating.parentElement.querySelector('.rating-text');
        let currentRating = 0;

        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const value = parseInt(this.dataset.value);
                currentRating = value;
                input.value = value;
                updateStars(stars, value);
                if (textElement) {
                    textElement.textContent = ratingTexts[value] || '';
                }
            });

            star.addEventListener('mouseenter', function() {
                const value = parseInt(this.dataset.value);
                updateStars(stars, value);
                if (textElement) {
                    textElement.textContent = ratingTexts[value] || '';
                }
            });
        });

        rating.addEventListener('mouseleave', function() {
            updateStars(stars, currentRating);
            if (textElement) {
                textElement.textContent = currentRating ? ratingTexts[currentRating] : '';
            }
        });
    });

    function updateStars(stars, rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    // Form validation
    document.querySelector('.review-form').addEventListener('submit', function(e) {
        const ratings = ['rating', 'product_quality', 'delivery', 'service'];
        let isValid = true;

        ratings.forEach(name => {
            const input = document.querySelector(`input[name="${name}"]`);
            if (!input.value || input.value === '0') {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please provide all required ratings before submitting your review.');
        }
    });
});
</script>

<?php require_once '../../components/footer.php'; ?>
