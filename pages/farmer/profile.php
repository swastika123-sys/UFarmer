<?php
$pageTitle = 'Farmer Profile';
require_once '../../includes/functions.php';

$farmerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$farmerId) {
    header('Location: ../farmers.php');
    exit();
}

// Get farmer details
global $pdo;
$stmt = $pdo->prepare("SELECT f.*, u.name as owner_name, u.email, u.created_at as user_created 
                       FROM farmers f 
                       JOIN users u ON f.user_id = u.id 
                       WHERE f.id = ?");
$stmt->execute([$farmerId]);
$farmer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$farmer) {
    header('Location: ../farmers.php');
    exit();
}

// Get farmer's products
$products = getFarmerProducts($farmerId);

// Get farmer's reviews
$stmt = $pdo->prepare("SELECT r.*, u.name as customer_name 
                       FROM reviews r 
                       JOIN users u ON r.customer_id = u.id 
                       WHERE r.farmer_id = ? 
                       ORDER BY r.created_at DESC 
                       LIMIT 10");
$stmt->execute([$farmerId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get farmer's average rating and total reviews
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE farmer_id = ?");
$stmt->execute([$farmerId]);
$ratingData = $stmt->fetch(PDO::FETCH_ASSOC);
$avgRating = $ratingData && $ratingData['avg_rating'] !== null ? round($ratingData['avg_rating'], 1) : 0.0;
$totalReviews = $ratingData ? (int)$ratingData['total_reviews'] : 0;

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $reviewer_name = trim($_POST['reviewer_name'] ?? 'Anonymous');
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $farmer_id = $farmerId;
    $errors = [];

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Rating must be between 1 and 5.';
    }
    if ($comment === '') {
        $errors[] = 'Comment cannot be empty.';
    }
    if (empty($errors)) {
        // Insert a new user if not logged in, else use logged in user
        if (isLoggedIn()) {
            $customer_id = $_SESSION['user_id'];
        } else {
            // Create a guest user (or use a fixed guest id if you prefer)
            $guest_email = strtolower(preg_replace('/\s+/', '', $reviewer_name)) . '@guest.local';
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$guest_email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $customer_id = $user['id'];
            } else {
                 $stmt = $pdo->prepare('INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)');
                $stmt->execute([$reviewer_name, $guest_email, password_hash(uniqid(), PASSWORD_DEFAULT), 'customer']);
                $customer_id = $pdo->lastInsertId();
            }
        }
        $stmt = $pdo->prepare('INSERT INTO reviews (customer_id, farmer_id, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([$customer_id, $farmer_id, $rating, $comment]);
        header('Location: profile.php?id=' . $farmer_id . '&review=success');
        exit();
    }
}

include '../../components/header.php';
?>

<section class="farmer-hero py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 text-center">
                <?php 
                // Handle profile image with proper fallback
                $profileImageSrc = '';
                if ($farmer['profile_image']) {
                    if ($farmer['profile_image'] === 'default-farmer.jpg') {
                        $profileImageSrc = SITE_URL . '/assets/images/default-farmer.jpg';
                    } else {
                        $profileImageSrc = UPLOAD_URL . $farmer['profile_image'];
                    }
                } else {
                    $profileImageSrc = SITE_URL . '/assets/images/default-farmer.jpg';
                }
                ?>
                <img src="<?php echo $profileImageSrc; ?>" 
                     alt="<?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'], ENT_QUOTES)); ?>" 
                     class="farmer-profile-image">
                
                <?php if ($farmer['is_verified']): ?>
                    <div class="verified-badge">
                        <i class="fas fa-check-circle"></i> Verified Farmer
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-8">
                <h1 class="display-4"><?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'], ENT_QUOTES)); ?></h1>
                <p class="lead text-muted">by <?php echo htmlspecialchars($farmer['owner_name']); ?></p>
                
                <div class="farmer-stats mb-3">
                    <div class="rating-display">
                        <div class="stars">
                            <?php echo generateStars($avgRating); ?>
                        </div>
                        <span class="rating-text"><?php echo number_format($avgRating, 1); ?> out of 5 (<?php echo $totalReviews; ?> reviews)</span>
                    </div>
                </div>
                
                <div class="farmer-info">
                    <p><i class="fas fa-map-marker-alt text-success"></i> <?php echo htmlspecialchars($farmer['location']); ?></p>
                    <p><i class="fas fa-calendar text-success"></i> Farming since <?php echo date('Y', strtotime($farmer['user_created'])); ?></p>
                    <p><i class="fas fa-phone text-success"></i> <?php echo htmlspecialchars($farmer['phone']); ?></p>
                </div>
                
                <?php if (isLoggedIn() && $_SESSION['user_type'] === 'customer'): ?>
                    <div class="farmer-actions">
                        <button class="btn btn-primary" onclick="showContactModal()">
                            <i class="fas fa-envelope"></i> Contact Farmer
                        </button>
                        <a href="../shop.php?farmer=<?php echo $farmer['id']; ?>" class="btn btn-success">
                            <i class="fas fa-shopping-basket"></i> Shop Products
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="farmer-about py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h2>About Our Farm</h2>
                <div class="farm-description">
                    <?php echo nl2br(htmlspecialchars($farmer['description'])); ?>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="farm-quick-stats card">
                    <div class="card-body">
                        <h5>Quick Stats</h5>
                        <div class="stat-item">
                            <span class="stat-label">Products Available:</span>
                            <span class="stat-value"><?php echo count($products); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Member Since:</span>
                            <span class="stat-value"><?php echo date('M Y', strtotime($farmer['created_at'])); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Customer Rating:</span>
                            <span class="stat-value"><?php echo number_format($avgRating, 1); ?>/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($products)): ?>
<section class="farmer-products py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Available Products</h2>
        
        <div class="grid grid-4">
            <?php foreach ($products as $product): ?>
                <div class="card product-card">
                    <img src="<?php echo $product['image'] ? UPLOAD_URL . $product['image'] : SITE_URL . '/assets/images/default-product.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="card-img">
                    
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                        
                        <div class="product-details">
                            <span class="price"><?php echo formatPrice($product['price']); ?> / <?php echo htmlspecialchars($product['unit']); ?></span>
                            <?php if ($product['seasonal_availability']): ?>
                                <span class="season"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($product['seasonal_availability']); ?></span>
                            <?php endif; ?>
                            <span class="stock">Stock: <?php echo $product['stock_quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?></span>
                        </div>
                        
                        <?php if (isLoggedIn() && $_SESSION['user_type'] === 'customer'): ?>
                            <button class="btn btn-success w-100 mt-2" onclick="cart.addItem(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <a href="../auth/login.php" class="btn btn-primary w-100 mt-2">Login to Buy</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($reviews)): ?>
<section class="farmer-reviews py-5">
    <div class="container">
        <h2 class="text-center mb-5">Customer Reviews</h2>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card card mb-3">
                    <div class="card-body">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <h6><?php echo htmlspecialchars($review['customer_name']); ?></h6>
                                <div class="stars">
                                    <?php echo generateStars($review['rating']); ?>
                                </div>
                            </div>
                            <span class="review-date"><?php echo timeAgo($review['created_at']); ?></span>
                        </div>
                        <p class="review-comment"><?php echo htmlspecialchars($review['comment']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Review Form for all users -->
<section class="farmer-review-form py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Leave a Review</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach (
                    $errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_GET['review']) && $_GET['review'] === 'success'): ?>
            <div class="alert alert-success">Thank you for your review!</div>
        <?php endif; ?>
        <form method="post" class="review-form" style="max-width: 500px; margin: 0 auto;">
            <?php if (isLoggedIn()): ?>
                <?php 
                $sessionName = isset($_SESSION['name']) && $_SESSION['name'] !== null && $_SESSION['name'] !== '' 
                    ? $_SESSION['name'] 
                    : (isset($_SESSION['user_name']) && $_SESSION['user_name'] ? $_SESSION['user_name'] : '');
                ?>
                <input type="hidden" name="reviewer_name" value="<?php echo htmlspecialchars($sessionName); ?>">
                <div class="form-group mb-3">
                    <label>Your Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($sessionName); ?>" disabled>
                </div>
            <?php else: ?>
                <div class="form-group mb-3">
                    <label for="reviewer_name">Your Name</label>
                    <input type="text" class="form-control" id="reviewer_name" name="reviewer_name" placeholder="Enter your name" value="<?php echo isset($_POST['reviewer_name']) ? htmlspecialchars($_POST['reviewer_name']) : ''; ?>">
                </div>
            <?php endif; ?>
            <div class="form-group mb-3">
                <label for="rating">Rating</label>
                <select class="form-control" id="rating" name="rating" required>
                    <option value="">Select rating</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php if (isset($_POST['rating']) && $_POST['rating'] == $i) echo 'selected'; ?>><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="comment">Comment</label>
                <textarea class="form-control" id="comment" name="comment" rows="4" required><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
            </div>
            <button type="submit" name="submit_review" class="btn btn-success">Submit Review</button>
        </form>
    </div>
</section>

<!-- Contact Modal -->
<div id="contactModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Contact <?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'], ENT_QUOTES)); ?></h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="contactForm">
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" class="form-control" rows="5" placeholder="Ask about products, availability, or anything else..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>

<style>
.farmer-profile-image {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--accent-green);
    margin-bottom: 1rem;
}

.verified-badge {
    background: var(--success);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.farmer-stats {
    margin: 1rem 0;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.farmer-info p {
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.farmer-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.farm-description {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--gray-dark);
}

.farm-quick-stats {
    position: sticky;
    top: 100px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-weight: 500;
}

.stat-value {
    color: var(--primary-green);
    font-weight: 600;
}

.product-card {
    margin-bottom: 1.2rem;
    width: 100%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    background: #fff;
}

.product-card .card-img {
    width: 100%;
    height: 120px;
    object-fit: contain;
    border-radius: 10px 10px 0 0;
}

.grid.grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

.product-card .card-body {
    padding: 0.8rem 1rem 1rem 1rem;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product-details {
    margin: 0.7rem 0 0.5rem 0;
}

.product-details .price {
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--success);
    display: block;
}

.product-details .season,
.product-details .stock {
    font-size: 0.9rem;
    color: var(--gray-medium);
    display: block;
    margin-top: 0.25rem;
}

.reviews-grid {
    max-width: 800px;
    margin: 0 auto;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.reviewer-info h6 {
    margin-bottom: 0.25rem;
    color: var(--dark-green);
}

.review-date {
    font-size: 0.9rem;
    color: var(--gray-medium);
}

.review-comment {
    margin-bottom: 0;
    line-height: 1.6;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-medium);
}

.modal-body {
    padding: 1.5rem;
}

.grid.grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

@media (max-width: 900px) {
    .grid.grid-4 {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
    .product-card .card-img {
        height: 120px;
    }
}

@media (max-width: 768px) {
    .farmer-actions {
        flex-direction: column;
    }
    
    .farmer-profile-image {
        width: 150px;
        height: 150px;
    }
    
    .stat-item {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .review-header {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
function showContactModal() {
    document.getElementById('contactModal').style.display = 'flex';
}

// Close modal when clicking close button or outside
document.querySelector('.modal-close').addEventListener('click', function() {
    document.getElementById('contactModal').style.display = 'none';
});

document.getElementById('contactModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});

// Handle contact form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const message = document.getElementById('message').value;
    
    // Here you would typically send the message via AJAX
    // For now, we'll just show a success message
    alert('Message sent! The farmer will get back to you soon.');
    document.getElementById('contactModal').style.display = 'none';
    this.reset();
});
</script>

<?php include '../../components/footer.php'; ?>
