<?php
$pageTitle = 'Shop Fresh Produce';
include '../components/header.php';

// Get filter parameters
$farmerFilter = isset($_GET['farmer']) ? intval($_GET['farmer']) : null;
$categoryFilter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : null;
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;

// Build query based on filters
$sql = "SELECT p.*, f.farm_name, u.name as farmer_name, f.id as farmer_id
        FROM products p 
        JOIN farmers f ON p.farmer_id = f.id 
        JOIN users u ON f.user_id = u.id 
        WHERE p.is_active = 1";

$params = [];

if ($farmerFilter) {
    $sql .= " AND f.id = ?";
    $params[] = $farmerFilter;
}

if ($categoryFilter) {
    $sql .= " AND p.category = ?";
    $params[] = $categoryFilter;
}

if ($searchQuery) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR f.farm_name LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$sql .= " ORDER BY p.created_at DESC";

global $pdo;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE is_active = 1 AND category IS NOT NULL ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Get farmers for filter
$farmersStmt = $pdo->query("SELECT f.id, f.farm_name FROM farmers f JOIN products p ON f.id = p.farmer_id WHERE p.is_active = 1 GROUP BY f.id ORDER BY f.farm_name");
$farmers = $farmersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="shop-header py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="section-header">
                    <h1 class="decorative-heading">🛒 Fresh Local Produce 🥕</h1>
                    <div class="heading-decoration"></div>
                    <p class="section-subtitle">Discover seasonal fruits, vegetables, and farm products from local growers</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="search-bar">
                    <form method="GET" action="" class="d-flex">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search products, farmers..." 
                               value="<?php echo htmlspecialchars($searchQuery ?: ''); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="shop-content py-4">
    <div class="container">
        <!-- Horizontal Filters Section -->
        <div class="filters-horizontal mb-4 p-3 rounded">
            <div class="row align-items-center">
                <!-- Clear Filters Button -->
                <div class="col-md-2">
                    <?php if ($farmerFilter || $categoryFilter || $searchQuery): ?>
                        <a href="shop.php" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    <?php else: ?>
                        <label class="form-label mb-0"><strong>🛒 Shop Filters</strong></label>
                    <?php endif; ?>
                </div>
                
                <!-- Farmer Filter -->
                <div class="col-md-3">
                    <label class="form-label">By Farmer</label>
                    <select class="form-control form-control-sm" onchange="updateFilter('farmer', this.value)">
                        <option value="">All Farmers</option>
                        <?php foreach ($farmers as $farmer): ?>
                            <option value="<?php echo $farmer['id']; ?>" 
                                    <?php echo $farmerFilter == $farmer['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'], ENT_QUOTES)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div class="col-md-3">
                    <label class="form-label">By Category</label>
                    <select class="form-control form-control-sm" onchange="updateFilter('category', this.value)">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" 
                                    <?php echo $categoryFilter === $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($category)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-4">
                    <label class="form-label">Quick Links</label>
                    <div class="quick-links d-flex flex-wrap">
                        <a href="?category=vegetables" class="btn btn-outline-success btn-sm">🥕 Vegetables</a>
                        <a href="?category=fruits" class="btn btn-outline-success btn-sm">🍎 Fruits</a>
                        <a href="?category=herbs" class="btn btn-outline-success btn-sm">🌿 Herbs</a>
                        <a href="?category=dairy" class="btn btn-outline-success btn-sm">🥛 Dairy</a>
                    </div>
                </div>
            </div>
            
            <!-- Seasonal Highlights Row -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="seasonal-highlights">
                        <small class="text-muted">
                            <strong>🍂 Seasonal Highlights for <?php echo date('F'); ?>:</strong>
                            <?php
                            $currentMonth = date('F');
                            $seasonalStmt = $pdo->prepare("SELECT DISTINCT name FROM products WHERE seasonal_availability LIKE ? AND is_active = 1 LIMIT 5");
                            $seasonalStmt->execute(["%$currentMonth%"]);
                            $seasonalProducts = $seasonalStmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            if ($seasonalProducts):
                                echo implode(', ', array_map('htmlspecialchars', $seasonalProducts));
                            else:
                                echo 'Check back for seasonal updates!';
                            endif;
                            ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Full Width Products Section -->
        <div class="row">
            <div class="col-12">
                <div class="products-header mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>
                            <?php echo count($products); ?> products found
                            <?php if ($searchQuery): ?>
                                for "<?php echo htmlspecialchars($searchQuery); ?>"
                            <?php endif; ?>
                        </h5>
                        
                        <div class="sort-controls">
                            <select id="sortSelect" class="form-control">
                                <option value="newest">Newest First</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="name">Name A-Z</option>
                                <option value="farmer">By Farmer</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="no-products text-center py-5">
                        <i class="fas fa-shopping-basket fa-5x text-muted mb-3"></i>
                        <h3>No Products Found</h3>
                        <p class="text-muted">
                            <?php if ($searchQuery || $farmerFilter || $categoryFilter): ?>
                                Try adjusting your filters or search terms.
                            <?php else: ?>
                                Our farmers are working hard to stock fresh products. Check back soon!
                            <?php endif; ?>
                        </p>
                        <?php if ($searchQuery || $farmerFilter || $categoryFilter): ?>
                            <a href="shop.php" class="btn btn-outline-green">View All Products</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php
                    // Fallback images mapping for shop page products
                    $productImages = [
                        'Organic Tomatoes' => 'https://images.unsplash.com/photo-1546470427-ac4e015d2fd0?w=300&h=200&fit=crop',
                        'Mixed Salad Greens' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300&h=200&fit=crop',
                        'Fresh Basil' => 'https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=300&h=200&fit=crop',
                        'Organic Carrots' => 'https://images.unsplash.com/photo-1445282768818-728615cc910a?w=300&h=200&fit=crop',
                        'Heirloom Tomatoes' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=300&h=200&fit=crop',
                        'Fresh Strawberries' => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=300&h=200&fit=crop',
                        'Organic Spinach' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=300&h=200&fit=crop',
                        'Mixed Berries' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=300&h=200&fit=crop'
                    ];
                    // Add case-insensitive mapping for fallback lookup
                    $productImagesLower = array_change_key_case($productImages, CASE_LOWER);
                    ?>
                    <div class="products-grid grid grid-2" id="productsGrid">
                        <?php foreach ($products as $product): ?>
                            <?php
                            // Determine image URL: DB image or fallback mapping or default placeholder
                            if ($product['image']) {
                                $imageUrl = UPLOAD_URL . $product['image'];
                            } elseif (isset($productImagesLower[strtolower($product['name'])])) {
                                $imageUrl = $productImagesLower[strtolower($product['name'])];
                            } else {
                                $imageUrl = SITE_URL . '/assets/images/default-product.jpg';
                            }
                            ?>
                            <div class="card product-card" 
                                 data-price="<?php echo $product['price']; ?>" 
                                 data-name="<?php echo strtolower($product['name']); ?>" 
                                 data-farmer="<?php echo strtolower($product['farm_name']); ?>">
                                
                                <div class="product-image-container">
                                    <!-- Added data-skip-fallback to prevent JS fallback override -->
                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-img" loading="lazy" data-skip-fallback>
                                </div>
                                
                                <div class="card-body">
                                    <h4 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="farmer-link">
                                        by <a href="farmer/profile.php?id=<?php echo $product['farmer_id']; ?>" class="text-success">
                                            <?php echo htmlspecialchars(html_entity_decode($product['farm_name'], ENT_QUOTES)); ?>
                                        </a>
                                    </p>
                                    
                                    <?php if ($product['category']): ?>
                                        <span class="category-badge"><?php echo htmlspecialchars(ucfirst($product['category'])); ?></span>
                                    <?php endif; ?>
                                    
                                    <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                    
                                    <div class="product-details">
                                        <div class="price-stock">
                                            <div class="price-info">
                                                <?php if ($product['discount_percentage'] > 0): ?>
                                                    <span class="original-price">₹<?php echo number_format($product['price'] * 83, 0); ?></span>
                                                    <span class="discounted-price">₹<?php echo number_format($product['discounted_price'] * 83, 0); ?></span>
                                                    <span class="discount-badge"><?php echo $product['discount_percentage']; ?>% OFF</span>
                                                <?php else: ?>
                                                    <span class="price price-indian">₹<?php echo number_format($product['price'] * 83, 0); ?></span>
                                                <?php endif; ?>
                                                <span class="unit">/ <?php echo htmlspecialchars($product['unit']); ?></span>
                                            </div>
                                            <span class="stock <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                                <?php if ($product['stock_quantity'] > 0): ?>
                                                    <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock_quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?>)
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle"></i> Out of Stock
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        
                                        <?php if ($product['seasonal_availability']): ?>
                                            <div class="season">
                                                <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($product['seasonal_availability']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Product Reviews Section -->
                                    <div class="product-reviews-section">
                                        <?php
                                        // Get product reviews
                                        $reviewStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE farmer_id = ?");
                                        $reviewStmt->execute([$product['farmer_id']]);
                                        $reviewData = $reviewStmt->fetch(PDO::FETCH_ASSOC);
                                        $avgRating = $reviewData['avg_rating'] ? round($reviewData['avg_rating'], 1) : 0;
                                        $reviewCount = $reviewData['review_count'] ?: 0;
                                        ?>
                                        <div class="product-rating-display">
                                            <div class="stars-display">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star <?php echo $i <= $avgRating ? 'filled' : 'empty'; ?>">⭐</span>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="rating-text"><?php echo $avgRating; ?>/5 (<?php echo $reviewCount; ?> reviews)</span>
                                        </div>
                                        
                                        <?php if (isLoggedIn()): ?>
                                            <button class="btn btn-outline-warning btn-sm review-btn" onclick="openProductReviewModal(<?php echo $product['farmer_id']; ?>, '<?php echo addslashes($product['farm_name']); ?>', '<?php echo addslashes($product['name']); ?>')">
                                                <i class="fas fa-star"></i> Rate Farmer
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <?php if (isLoggedIn() && $_SESSION['user_type'] === 'customer'): ?>
                                            <?php if ($product['stock_quantity'] > 0): ?>
                                                <div class="weight-selector">
                                                    <label for="weight_shop_<?php echo $product['id']; ?>">Select Weight:</label>
                                                    <select id="weight_shop_<?php echo $product['id']; ?>" class="form-control weight-select" data-product-id="<?php echo $product['id']; ?>" data-base-price="<?php echo $product['price'] * 83; ?>">
                                                        <option value="0.25">250g - ₹<?php echo number_format($product['price'] * 83 * 0.25, 0); ?></option>
                                                        <option value="0.5">500g - ₹<?php echo number_format($product['price'] * 83 * 0.5, 0); ?></option>
                                                        <option value="0.75">750g - ₹<?php echo number_format($product['price'] * 83 * 0.75, 0); ?></option>
                                                        <option value="1" selected>1kg - ₹<?php echo number_format($product['price'] * 83, 0); ?></option>
                                                        <option value="2">2kg - ₹<?php echo number_format($product['price'] * 83 * 2, 0); ?></option>
                                                    </select>
                                                </div>
                                                <button class="btn btn-success w-100" onclick="addToCartWithWeightShop(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($product['unit']); ?>')">>
                                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary w-100" disabled>
                                                    <i class="fas fa-times"></i> Out of Stock
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="../pages/auth/login.php" class="btn btn-primary w-100">Login to Buy</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Product Review Modal -->
<div id="productReviewModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-star"></i> Rate Farmer</h3>
            <span class="close" onclick="closeProductReviewModal()">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Debug information -->
            <div id="debugInfo" style="background: #f0f0f0; padding: 10px; margin-bottom: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;">
                <strong>Debug Info:</strong><br>
                Farmer ID: <span id="debugFarmerId">-</span><br>
                Farm Name: <span id="debugFarmName">-</span><br>
                Product: <span id="debugProductName">-</span><br>
                Rating: <span id="debugRating">Not set</span>
            </div>
            
            <form id="productReviewForm">
                <input type="hidden" id="reviewFarmerId" name="farmer_id">
                
                <div class="form-group">
                    <label>How was your experience with <span id="reviewFarmName"></span> for <span id="reviewProductName"></span>?</label>
                    <div class="rating-input">
                        <i class="fas fa-star rating-star" data-rating="1"></i>
                        <i class="fas fa-star rating-star" data-rating="2"></i>
                        <i class="fas fa-star rating-star" data-rating="3"></i>
                        <i class="fas fa-star rating-star" data-rating="4"></i>
                        <i class="fas fa-star rating-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" id="productRating" name="rating" required>
                </div>
                
                <div class="form-group">
                    <label for="productReview">Share your review (optional)</label>
                    <textarea id="productReview" name="comment" rows="4" placeholder="Tell others about your experience with this farmer's products and service..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProductReviewModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.search-bar {
    max-width: 400px;
    margin-left: auto;
}

.search-bar .d-flex {
    display: flex;
}

.search-bar input {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.search-bar button {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: none;
}

/* Horizontal Filters Layout - Enhanced */
.filters-horizontal {
    background: #ffffff;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.filters-horizontal:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
}

.filters-horizontal .form-label {
    color: var(--dark-green);
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.filters-horizontal .form-control-sm {
    height: calc(2rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: all 0.3s ease;
}

.filters-horizontal .form-control-sm:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

.filters-horizontal .quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filters-horizontal .quick-links .btn {
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    transition: all 0.3s ease;
    border-color: var(--primary-green);
    color: var(--primary-green);
}

.filters-horizontal .quick-links .btn:hover {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.filters-horizontal .seasonal-highlights {
    background: linear-gradient(135deg, #f8f9fa, #e8f5e8);
    border-radius: 8px;
    padding: 0.75rem;
    border-left: 4px solid var(--primary-green);
    margin-top: 0.5rem;
}

.filters-horizontal .seasonal-highlights small {
    display: block;
    line-height: 1.5;
    color: #2e7d32;
}

/* Full-width Products Grid Layout */
.products-header {
    background: var(--white);
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.products-header .d-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sort-controls select {
    min-width: 200px;
    padding: 0.5rem;
    border-radius: 6px;
    border: 1px solid #ced4da;
}

/* Enhanced 2-Column Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    margin: 0;
}

.product-card {
    height: 100%;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

/* Product Card Details */
.product-image-container {
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.product-image-container::after {
    content: "📦";
    font-size: 3rem;
    opacity: 0.3;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
}

.product-image-container .card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    position: relative;
    z-index: 2;
}

.product-card:hover .product-image-container .card-img {
    transform: scale(1.05);
}

.product-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1.25rem;
}

.product-card .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--dark-green);
}

.product-card .product-actions {
    margin-top: auto;
}

.weight-selector {
    margin-bottom: 1rem;
}

.weight-selector label {
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--gray-dark);
    margin-bottom: 0.5rem;
    display: block;
}

.weight-select {
    font-size: 0.9rem;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.price-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.farmer-link {
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.farmer-link a {
    text-decoration: none;
    font-weight: 500;
}

.farmer-link a:hover {
    text-decoration: underline;
}

.category-badge {
    background: var(--secondary-green);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
    margin-bottom: 0.75rem;
}

.product-details {
    margin: 1rem 0;
}

.price-stock {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.price {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--success);
}

.stock {
    font-size: 0.9rem;
    font-weight: 500;
}

.stock.in-stock {
    color: var(--success);
}

.stock.out-of-stock {
    color: var(--danger);
}

.season {
    font-size: 0.9rem;
    color: var(--gray-medium);
}

.product-actions {
    margin-top: 1rem;
}

.no-products {
    background: var(--white);
    border-radius: 8px;
    margin: 2rem 0;
}

/* Modal Styles */
.modal {
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: var(--dark-green);
}

.modal-header .close {
    color: #aaa;
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
}

.modal-header .close:hover,
.modal-header .close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.modal-body {
    margin-top: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark-green);
}

.rating-input {
    display: flex;
    gap: 0.5rem;
}

.rating-star {
    font-size: 1.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.3s;
}

.rating-star.selected {
    color: #ffd700;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}

.modal-actions .btn {
    flex: 1;
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-bar {
        margin: 1rem 0 0 0;
        max-width: 100%;
    }
    
    .filters-horizontal .row {
        flex-direction: column;
    }
    
    .filters-horizontal .col-md-2,
    .filters-horizontal .col-md-3,
    .filters-horizontal .col-md-4 {
        margin-bottom: 1rem;
    }
    
    .filters-horizontal .quick-links {
        justify-content: flex-start;
    }
    
    .filters-horizontal .quick-links .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .products-header .d-flex {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .sort-controls select {
        min-width: 100%;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .price-stock {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>

<script>
function updateFilter(filterType, value) {
    const url = new URL(window.location);
    
    if (value) {
        url.searchParams.set(filterType, value);
    } else {
        url.searchParams.delete(filterType);
    }
    
    window.location.href = url.toString();
}

// Sort functionality
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sortSelect');
    const productsGrid = document.getElementById('productsGrid');
    
    if (sortSelect && productsGrid) {
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            const products = Array.from(productsGrid.children);
            
            products.sort((a, b) => {
                switch(sortBy) {
                    case 'price_low':
                        return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    case 'price_high':
                        return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    case 'farmer':
                        return a.dataset.farmer.localeCompare(b.dataset.farmer);
                    case 'newest':
                    default:
                        return 0; // Keep original order
                }
            });
            
            // Re-append sorted products
            products.forEach(product => productsGrid.appendChild(product));
        });
    }
});

// Add to cart animation
function addToCartWithAnimation(button, productId, name, price) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    setTimeout(() => {
        cart.addItem(productId, name, price);
        button.innerHTML = '<i class="fas fa-check"></i> Added!';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 1000);
    }, 500);
}

// Shop page weight-based cart functionality
function addToCartWithWeightShop(productId, productName, basePrice, unit = 'kg') {
    const weightSelect = document.getElementById(`weight_shop_${productId}`);
    if (!weightSelect) {
        console.error('Weight selector not found for product', productId);
        return;
    }
    
    const selectedWeight = parseFloat(weightSelect.value);
    const calculatedPrice = basePrice * selectedWeight;
    const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
    
    console.log('Adding to cart:', {
        productId,
        productName,
        basePrice,
        selectedWeight,
        calculatedPrice,
        weightText,
        unit
    });
    
    // Capture product image from the current product card
    let productImageUrl = null;
    const productCard = weightSelect.closest('.product-card');
    if (productCard) {
        const productImg = productCard.querySelector('.card-img');
        if (productImg && productImg.src && !productImg.src.includes('default-product.jpg')) {
            productImageUrl = productImg.src;
        }
    }
    
    // Add the item with weight and unit information
    if (typeof cart !== 'undefined' && cart.addItemWithWeight) {
        cart.addItemWithWeight(productId, `${productName} (${weightText})`, calculatedPrice, selectedWeight, unit, productImageUrl);
        
        // Show notification
        if (typeof showNotification === 'function') {
            showNotification(`Added ${productName} (${weightText}) to cart - ${formatPrice ? formatPrice(calculatedPrice) : '$' + calculatedPrice.toFixed(2)}`, 'success');
        } else if (typeof notificationSystem !== 'undefined') {
            notificationSystem.showSuccess(`Added ${productName} (${weightText}) to cart`);
        } else {
            alert(`Added ${productName} (${weightText}) to cart`);
        }
    } else {
        console.error('Cart system not available');
        alert('Error: Cart system not available');
    }
}

// Shop page weight selector updates
document.addEventListener('DOMContentLoaded', function() {
    const shopWeightSelectors = document.querySelectorAll('.weight-select[id^="weight_shop_"]');
    shopWeightSelectors.forEach(selector => {
        selector.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const basePrice = parseFloat(this.dataset.basePrice);
            const selectedWeight = parseFloat(this.value);
            const newPrice = Math.round(basePrice * selectedWeight);
            
            // Update the displayed price in the product card
            const priceElement = this.closest('.card').querySelector('.price');
            if (priceElement) {
                const weightText = selectedWeight < 1 ? `${selectedWeight * 1000}g` : `${selectedWeight}kg`;
                priceElement.textContent = `₹${newPrice.toLocaleString('en-IN')} / ${weightText}`;
            }
        });
    });
});

// Product review modal functionality
function openProductReviewModal(farmerId, farmName, productName) {
    console.log('Opening review modal for:', { farmerId, farmName, productName });
    
    // Set form values
    document.getElementById('reviewFarmerId').value = farmerId;
    document.getElementById('reviewFarmName').textContent = farmName;
    document.getElementById('reviewProductName').textContent = productName;
    
    // Update debug info
    document.getElementById('debugFarmerId').textContent = farmerId;
    document.getElementById('debugFarmName').textContent = farmName;
    document.getElementById('debugProductName').textContent = productName;
    document.getElementById('debugRating').textContent = 'Not set';
    
    // Show modal
    document.getElementById('productReviewModal').style.display = 'block';
    console.log('Modal should be visible now');
    
    // Reset rating
    document.querySelectorAll('#productReviewModal .rating-star').forEach(star => {
        star.classList.remove('selected');
        star.style.color = '#ddd';
    });
    document.getElementById('productRating').value = '';
    console.log('Rating reset');
}

function closeProductReviewModal() {
    console.log('Closing review modal');
    document.getElementById('productReviewModal').style.display = 'none';
    document.getElementById('productReviewForm').reset();
}

// Handle rating selection for product review
document.addEventListener('DOMContentLoaded', function() {
    console.log('Shop page rating system initialized');
    const ratingStars = document.querySelectorAll('#productReviewModal .rating-star');
    console.log('Found rating stars:', ratingStars.length);
    
    ratingStars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            console.log('Star clicked with rating:', rating);
            document.getElementById('productRating').value = rating;
            
            // Update debug info
            document.getElementById('debugRating').textContent = rating + ' stars';
            
            ratingStars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('selected');
                    s.style.color = '#ffd700';
                } else {
                    s.classList.remove('selected');
                    s.style.color = '#ddd';
                }
            });
            console.log('Rating updated to:', rating);
        });
    });
    
    // Handle form submission
    document.getElementById('productReviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Review form submitted');
        
        const formData = new FormData(this);
        // Create a mock order ID for general farmer review
        formData.append('order_id', 0);
        
        console.log('Form data:', Object.fromEntries(formData));
        
        fetch('submit-review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                alert('Thank you for your review!');
                closeProductReviewModal();
                location.reload(); // Refresh to show updated rating
            } else {
                alert('Error submitting review: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting review. Please try again.');
        });
    });
});
</script>

<?php include '../components/footer.php'; ?>
