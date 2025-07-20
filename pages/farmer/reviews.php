<?php
$pageTitle = 'Customer Reviews';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a farmer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL);
    exit();
}

$farmer = getFarmerByUserId($_SESSION['user_id']);

// If no farmer profile exists, redirect to setup
if (!$farmer) {
    header('Location: setup.php');
    exit();
}

global $pdo;

// Get filter parameters
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$time_filter = $_GET['time'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build where conditions
$where_conditions = ['r.farmer_id = ?'];
$params = [$farmer['id']];

if ($rating_filter > 0) {
    $where_conditions[] = 'r.rating = ?';
    $params[] = $rating_filter;
}

if ($status_filter !== 'all') {
    // Skip status filter since review_status column doesn't exist
    // $where_conditions[] = 'r.review_status = ?';
    // $params[] = $status_filter;
}

// Time filter
$time_condition = '';
switch ($time_filter) {
    case 'week':
        $time_condition = 'AND r.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
        break;
    case 'month':
        $time_condition = 'AND r.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
        break;
    case 'year':
        $time_condition = 'AND r.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
        break;
}

$where_clause = implode(' AND ', $where_conditions) . ' ' . $time_condition;

// Get reviews with pagination
$stmt = $pdo->prepare("
    SELECT r.*, u.name as customer_name, u.email as customer_email,
           o.id as order_id, o.created_at as order_date,
           GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as products_ordered
    FROM reviews r 
    JOIN users u ON r.customer_id = u.id 
    LEFT JOIN orders o ON r.order_id = o.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE $where_clause
    GROUP BY r.id
    ORDER BY r.created_at DESC 
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM reviews r 
    WHERE $where_clause
");
$countStmt->execute($params);
$total_reviews = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_reviews / $per_page);

// Get rating statistics
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1,
        SUM(CASE WHEN product_quality_rating IS NOT NULL THEN product_quality_rating ELSE 0 END) / COUNT(*) as avg_product_quality,
        SUM(CASE WHEN delivery_rating IS NOT NULL THEN delivery_rating ELSE 0 END) / COUNT(*) as avg_delivery,
        SUM(CASE WHEN service_rating IS NOT NULL THEN service_rating ELSE 0 END) / COUNT(*) as avg_service,
        SUM(CASE WHEN would_recommend = 1 THEN 1 ELSE 0 END) as recommendations
    FROM reviews 
    WHERE farmer_id = ?
");
$statsStmt->execute([$farmer['id']]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

include '../../components/header.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <a href="dashboard.php" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <div>
                    <h1 class="mb-0"><i class="fas fa-star text-warning"></i> Customer Reviews</h1>
                    <p class="text-muted mb-0">Manage and respond to customer feedback</p>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="exportReviews()">
                    <i class="fas fa-download"></i> Export Reviews
                </button>
                <button class="btn btn-success" onclick="showInsightsModal()">
                    <i class="fas fa-chart-line"></i> Insights
                </button>
            </div>
        </div>

        <!-- Rating Statistics Overview -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-chart-pie text-primary"></i> Rating Overview
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="rating-summary text-center">
                                    <div class="avg-rating-display">
                                        <span class="rating-number"><?php echo number_format($stats['avg_rating'], 1); ?></span>
                                        <div class="stars-large">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= round($stats['avg_rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-muted">Based on <?php echo $stats['total_reviews']; ?> reviews</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="rating-breakdown">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <?php 
                                        $count = $stats["rating_$i"];
                                        $percentage = $stats['total_reviews'] > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
                                        ?>
                                        <div class="rating-bar-row d-flex align-items-center mb-2">
                                            <span class="rating-label"><?php echo $i; ?> stars</span>
                                            <div class="rating-bar mx-2">
                                                <div class="rating-bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                            </div>
                                            <span class="rating-count"><?php echo $count; ?></span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-thumbs-up text-success"></i> Customer Satisfaction
                        </h5>
                        
                        <div class="satisfaction-metrics">
                            <div class="metric-item">
                                <div class="metric-label">Product Quality</div>
                                <div class="metric-value">
                                    <span class="rating"><?php echo number_format($stats['avg_product_quality'], 1); ?>/5</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?php echo ($stats['avg_product_quality'] / 5) * 100; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="metric-item">
                                <div class="metric-label">Delivery Experience</div>
                                <div class="metric-value">
                                    <span class="rating"><?php echo number_format($stats['avg_delivery'], 1); ?>/5</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-info" style="width: <?php echo ($stats['avg_delivery'] / 5) * 100; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="metric-item">
                                <div class="metric-label">Customer Service</div>
                                <div class="metric-value">
                                    <span class="rating"><?php echo number_format($stats['avg_service'], 1); ?>/5</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-warning" style="width: <?php echo ($stats['avg_service'] / 5) * 100; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="recommendation-rate mt-3">
                                <h6>Recommendation Rate</h6>
                                <div class="recommendation-display">
                                    <span class="percentage"><?php echo $stats['total_reviews'] > 0 ? round(($stats['recommendations'] / $stats['total_reviews']) * 100) : 0; ?>%</span>
                                    <small class="text-muted">of customers recommend you</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select">
                            <option value="0" <?php echo $rating_filter == 0 ? 'selected' : ''; ?>>All Ratings</option>
                            <option value="5" <?php echo $rating_filter == 5 ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo $rating_filter == 4 ? 'selected' : ''; ?>>4 Stars</option>
                            <option value="3" <?php echo $rating_filter == 3 ? 'selected' : ''; ?>>3 Stars</option>
                            <option value="2" <?php echo $rating_filter == 2 ? 'selected' : ''; ?>>2 Stars</option>
                            <option value="1" <?php echo $rating_filter == 1 ? 'selected' : ''; ?>>1 Star</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Time Period</label>
                        <select name="time" class="form-select">
                            <option value="all" <?php echo $time_filter == 'all' ? 'selected' : ''; ?>>All Time</option>
                            <option value="week" <?php echo $time_filter == 'week' ? 'selected' : ''; ?>>Last Week</option>
                            <option value="month" <?php echo $time_filter == 'month' ? 'selected' : ''; ?>>Last Month</option>
                            <option value="year" <?php echo $time_filter == 'year' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="hidden" <?php echo $status_filter == 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="reviews.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-comments"></i> Customer Reviews 
                    <span class="badge bg-secondary"><?php echo $total_reviews; ?> total</span>
                </h5>

                <?php if (empty($reviews)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-star-half-alt fa-3x text-muted mb-3"></i>
                        <h6>No Reviews Found</h6>
                        <p class="text-muted">
                            <?php if ($rating_filter > 0 || $time_filter !== 'all' || $status_filter !== 'all'): ?>
                                Try adjusting your filters to see more reviews.
                            <?php else: ?>
                                Start getting reviews from your customers by delivering great products and service!
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item card mb-3">
                                <div class="card-body">
                                    <div class="review-header d-flex justify-content-between align-items-start mb-3">
                                        <div class="customer-info">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="customer-avatar me-3">
                                                    <?php echo strtoupper(substr($review['customer_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($review['customer_name']); ?></h6>
                                                    <small class="text-muted"><?php echo timeAgo($review['created_at']); ?></small>
                                                </div>
                                            </div>
                                            
                                            <div class="rating-display mb-2">
                                                <div class="stars me-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <span class="rating-text"><?php echo $review['rating']; ?>/5</span>
                                                
                                                <?php if ($review['verified_purchase']): ?>
                                                    <span class="badge bg-success ms-2">
                                                        <i class="fas fa-check"></i> Verified Purchase
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="review-actions">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#" onclick="respondToReview(<?php echo $review['id']; ?>)">
                                                        <i class="fas fa-reply"></i> Respond
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="#" onclick="markAsHelpful(<?php echo $review['id']; ?>)">
                                                        <i class="fas fa-thumbs-up"></i> Mark Helpful
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-warning" href="#" onclick="hideReview(<?php echo $review['id']; ?>)">
                                                        <i class="fas fa-eye-slash"></i> Hide Review
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($review['comment']): ?>
                                        <div class="review-comment mb-3">
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Detailed Ratings -->
                                    <?php if ($review['product_quality_rating'] || $review['delivery_rating'] || $review['service_rating']): ?>
                                        <div class="detailed-ratings mb-3">
                                            <small class="text-muted d-block mb-2">Detailed Ratings:</small>
                                            <div class="row">
                                                <?php if ($review['product_quality_rating']): ?>
                                                    <div class="col-md-4">
                                                        <div class="rating-detail">
                                                            <span class="label">Product Quality:</span>
                                                            <span class="value"><?php echo $review['product_quality_rating']; ?>/5</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($review['delivery_rating']): ?>
                                                    <div class="col-md-4">
                                                        <div class="rating-detail">
                                                            <span class="label">Delivery:</span>
                                                            <span class="value"><?php echo $review['delivery_rating']; ?>/5</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($review['service_rating']): ?>
                                                    <div class="col-md-4">
                                                        <div class="rating-detail">
                                                            <span class="label">Service:</span>
                                                            <span class="value"><?php echo $review['service_rating']; ?>/5</span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Order Information -->
                                    <div class="order-info">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    Order #<?php echo $review['order_id']; ?> 
                                                    (<?php echo date('M j, Y', strtotime($review['order_date'])); ?>)
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if ($review['products_ordered']): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-box"></i>
                                                        Products: <?php echo htmlspecialchars($review['products_ordered']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Farmer Response -->
                                    <div id="response-<?php echo $review['id']; ?>" class="farmer-response mt-3" style="display: none;">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Your Response:</h6>
                                                <form onsubmit="submitResponse(event, <?php echo $review['id']; ?>)">
                                                    <div class="mb-3">
                                                        <textarea class="form-control" rows="3" placeholder="Thank you for your feedback..." required></textarea>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="fas fa-paper-plane"></i> Send Response
                                                        </button>
                                                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelResponse(<?php echo $review['id']; ?>)">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Reviews pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&rating=<?php echo $rating_filter; ?>&time=<?php echo $time_filter; ?>&status=<?php echo $status_filter; ?>">Previous</a>
                                </li>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&rating=<?php echo $rating_filter; ?>&time=<?php echo $time_filter; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&rating=<?php echo $rating_filter; ?>&time=<?php echo $time_filter; ?>&status=<?php echo $status_filter; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Insights Modal -->
<div class="modal fade" id="insightsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line text-primary"></i> Review Insights
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Performance Trends</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-arrow-up text-success"></i> Average rating improved by 0.2 points this month</li>
                            <li><i class="fas fa-star text-warning"></i> 85% of customers rate you 4+ stars</li>
                            <li><i class="fas fa-thumbs-up text-info"></i> <?php echo round(($stats['recommendations'] / max($stats['total_reviews'], 1)) * 100); ?>% recommend rate</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Common Keywords</h6>
                        <div class="keyword-cloud">
                            <span class="badge bg-success me-1">Fresh</span>
                            <span class="badge bg-success me-1">Quality</span>
                            <span class="badge bg-info me-1">Fast Delivery</span>
                            <span class="badge bg-warning me-1">Organic</span>
                            <span class="badge bg-secondary me-1">Friendly</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avg-rating-display .rating-number {
    font-size: 3rem;
    font-weight: bold;
    color: var(--warning);
}

.stars-large .fa-star {
    font-size: 1.5rem;
}

.rating-breakdown .rating-bar-row {
    font-size: 0.9rem;
}

.rating-bar {
    flex: 1;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%);
    transition: width 0.3s ease;
}

.satisfaction-metrics .metric-item {
    margin-bottom: 1rem;
}

.satisfaction-metrics .metric-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.satisfaction-metrics .metric-value {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.satisfaction-metrics .rating {
    font-weight: 600;
    min-width: 3rem;
}

.satisfaction-metrics .progress {
    flex: 1;
    height: 6px;
}

.recommendation-display .percentage {
    font-size: 2rem;
    font-weight: bold;
    color: var(--success);
}

.customer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.review-item {
    border: 1px solid #e9ecef;
    transition: box-shadow 0.2s ease;
}

.review-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.rating-detail {
    font-size: 0.85rem;
}

.rating-detail .label {
    color: #6c757d;
}

.rating-detail .value {
    font-weight: 600;
    color: var(--warning);
}

.farmer-response .card {
    background: #f8f9fa !important;
    border: 1px solid #dee2e6;
}

.keyword-cloud .badge {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
    .review-header {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .review-actions {
        margin-top: 1rem;
    }
    
    .rating-breakdown {
        margin-top: 1rem;
    }
}
</style>

<script>
function respondToReview(reviewId) {
    const responseDiv = document.getElementById(`response-${reviewId}`);
    responseDiv.style.display = responseDiv.style.display === 'none' ? 'block' : 'none';
}

function cancelResponse(reviewId) {
    document.getElementById(`response-${reviewId}`).style.display = 'none';
}

function submitResponse(event, reviewId) {
    event.preventDefault();
    const form = event.target;
    const responseText = form.querySelector('textarea').value;
    
    // Here you would typically send the response to the server
    showNotification('Response sent successfully!', 'success');
    cancelResponse(reviewId);
}

function markAsHelpful(reviewId) {
    showNotification('Review marked as helpful', 'info');
}

function hideReview(reviewId) {
    if (confirm('Are you sure you want to hide this review? It will no longer be visible to customers.')) {
        showNotification('Review hidden successfully', 'warning');
        // Here you would typically send a request to hide the review
    }
}

function exportReviews() {
    showNotification('Exporting reviews...', 'info');
    // Here you would typically trigger a download of review data
}

function showInsightsModal() {
    const modal = new bootstrap.Modal(document.getElementById('insightsModal'));
    modal.show();
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}
</script>

<?php require_once '../../components/footer.php'; ?>
