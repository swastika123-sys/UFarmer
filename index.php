<?php
$pageTitle = 'Home';
include 'components/header.php';

// Function to get dynamic farmer rating for homepage
function getHomepageFarmerRating($farmerId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
        FROM reviews 
        WHERE farmer_id = ?
    ");
    $stmt->execute([$farmerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'rating' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
        'count' => $result['review_count'] ?: 0
    ];
}

// Get featured farmers (show newest first as requested)
try {
    $featuredFarmers = getAllFarmers(6, 'created_at DESC');
    $featuredProducts = getAllProducts(8);
} catch (Exception $e) {
    // Log error and set empty arrays
    if (class_exists('DebugLogger')) {
        DebugLogger::error("Error loading data: " . $e->getMessage());
    }
    $featuredFarmers = [];
    $featuredProducts = [];
    
    // Show error notification via JavaScript
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof notificationSystem !== "undefined") {
                notificationSystem.showPHPError("Error loading homepage data. Please try again later.");
            }
        });
    </script>';
}
?>

<section class="hero">
    <!-- Video background as hero background -->
    <video class="hero-video" autoplay muted loop>
        <source src="https://videos.pexels.com/video-files/3616640/3616640-hd_1920_1080_24fps.mp4" type="video/mp4">
    </video>
    <div class="container">
         <h1>Fresh from Local Farmers</h1>
         <p>Discover the finest organic produce, grown with love by local farmers in your community. Support sustainable agriculture and taste the difference quality makes.</p>
         <div class="hero-buttons">
             <a href="pages/shop.php" class="btn btn-primary btn-lg">Shop Now</a>
             <a href="pages/farmers.php" class="btn btn-secondary btn-lg">Meet Our Farmers</a>
         </div>
     </div>
</section>

<section class="featured-farmers py-5">
    <div class="container">
        <div class="section-header">
            <h2 class="decorative-heading">🌱 Our Featured Farmers 🌱</h2>
            <div class="heading-decoration"></div>
            <p class="section-subtitle">Meet the passionate growers who bring you the freshest produce</p>
        </div>
        
        <?php if (empty($featuredFarmers)): ?>
            <div class="text-center">
                <p>No farmers registered yet. <a href="pages/auth/register.php?type=farmer">Be the first to join!</a></p>
            </div>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($featuredFarmers as $index => $farmer): ?>
                    <?php $homepageRating = getHomepageFarmerRating($farmer['id'], $pdo); ?>
                    <div class="card farmer-card">
                        <?php if ($index < 2): ?>
                            <div class="badge new">New Farmer</div>
                        <?php elseif ($farmer['is_verified']): ?>
                            <div class="badge">Verified</div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <?php
                            // Handle farmer profile image with proper fallback
                            if ($farmer['profile_image']) {
                                $farmerImage = UPLOAD_URL . $farmer['profile_image'];
                            } else {
                                $farmerImage = SITE_URL . '/assets/images/default-farmer.jpg';
                            }
                            ?>
                            
                            <!-- Farm Image Hero -->
                            <div class="farm-hero">
                                <img src="<?php echo $farmerImage; ?>" 
                                     alt="<?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'], ENT_QUOTES)); ?>" 
                                     class="farm-image"
                                     onerror="this.src='<?php echo SITE_URL . '/assets/images/default-farmer.jpg'; ?>'">
                                <div class="farm-overlay">
                                    <div class="farm-type">🌾 Organic Farm</div>
                                </div>
                            </div>
                            
                            <!-- Farm Info -->
                            <div class="farm-info">
                                <!-- Row 1: Header Row - Farm Name + Avatar + Rating -->
                                <div class="farm-header-row">
                                    <h3 class="farm-name"><?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'])); ?></h3>
                                    <div class="farmer-avatar-small">
                                        <?php echo strtoupper(substr($farmer['owner_name'], 0, 2)); ?>
                                    </div>
                                    <div class="rating-compact">
                                        <?php 
                                        $rating = $homepageRating['rating'];
                                        for ($i = 1; $i <= 5; $i++): 
                                        ?>
                                            <span class="star <?php echo $i <= $rating ? 'filled' : 'empty'; ?>">⭐</span>
                                        <?php endfor; ?>
                                        <span class="rating-number-compact"><?php echo number_format($rating, 1); ?></span>
                                    </div>
                                </div>
                                
                                <!-- Row 2: Info Row - Owner + Location in Two Columns -->
                                <div class="farm-info-row">
                                    <div class="owner-info">👨‍🌾 <?php echo htmlspecialchars($farmer['owner_name']); ?></div>
                                    <div class="location-info">📍 <?php echo htmlspecialchars($farmer['location']); ?></div>
                                </div>
                                
                                <!-- Row 3: Stats Row - Horizontal Layout -->
                                <div class="farm-stats-row">
                                    <div class="stat-item-compact">
                                        <span class="stat-icon">🥬</span>
                                        <span class="stat-label">Fresh Produce</span>
                                    </div>
                                    <div class="stat-item-compact">
                                        <span class="stat-icon">🌱</span>
                                        <span class="stat-label">Organic</span>
                                    </div>
                                    <div class="stat-item-compact">
                                        <span class="stat-icon">⭐</span>
                                        <span class="stat-label"><?php echo $homepageRating['count']; ?> Reviews</span>
                                    </div>
                                </div>
                                
                                <!-- Row 4: Description Row - Truncated -->
                                <p class="farm-description"><?php echo htmlspecialchars(substr($farmer['description'], 0, 120)); ?>...</p>
                                
                                <!-- Row 5: Actions Row -->
                                <div class="farm-actions">
                                    <a href="pages/farmer/profile.php?id=<?php echo $farmer['id']; ?>" class="btn btn-farm-primary">
                                        <i class="fas fa-store"></i> Visit Farm
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="pages/farmers.php" class="btn btn-secondary">View All Farmers</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="featured-products py-5 bg-light">
    <div class="container">
        <div class="section-header">
            <h2 class="decorative-heading">🥬 Fresh Products 🍎</h2>
            <div class="heading-decoration"></div>
            <p class="section-subtitle">Seasonal produce picked fresh from local farms</p>
        </div>
        
        <?php if (empty($featuredProducts)): ?>
            <div class="text-center">
                <p>No products available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="card product-card enhanced">
                        <?php
                        // Priority: 1) Database image, 2) Fallback mapping, 3) Default
                        if (!empty($product['image'])) {
                            $productImage = UPLOAD_URL . $product['image'];
                        } else {
                            // Beautiful online product images fallback
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
                            $productImage = isset($productImages[$product['name']]) ? 
                                          $productImages[$product['name']] : 
                                          SITE_URL . '/assets/images/default-product.jpg';
                        }
                        ?>
                        <div class="product-image-container">
                            <img src="<?php echo $productImage; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="card-img"
                                 onerror="this.src='<?php echo SITE_URL; ?>/assets/images/default-product.jpg'">
                            <div class="product-overlay">
                                <span class="product-category"><?php echo ucfirst($product['category']); ?></span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h4 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p class="farm-link">by <strong><?php echo htmlspecialchars(html_entity_decode($product['farm_name'], ENT_QUOTES)); ?></strong></p>
                            <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                            
                            <div class="product-details">
                                <span class="price-indian">₹<?php echo number_format($product['price'] * 83, 0); ?> / <?php echo htmlspecialchars($product['unit']); ?></span>
                                <?php if ($product['seasonal_availability']): ?>
                                    <span class="season"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($product['seasonal_availability']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isLoggedIn() && $_SESSION['user_type'] === 'customer'): ?>
                                <div class="weight-selector">
                                    <label for="weight_<?php echo $product['id']; ?>">Select Weight:</label>
                                    <select id="weight_<?php echo $product['id']; ?>" class="form-control weight-select" data-product-id="<?php echo $product['id']; ?>" data-base-price="<?php echo $product['price'] * 83; ?>">
                                        <option value="0.25">250g - ₹<?php echo number_format($product['price'] * 83 * 0.25, 0); ?></option>
                                        <option value="0.5">500g - ₹<?php echo number_format($product['price'] * 83 * 0.5, 0); ?></option>
                                        <option value="0.75">750g - ₹<?php echo number_format($product['price'] * 83 * 0.75, 0); ?></option>
                                        <option value="1" selected>1kg - ₹<?php echo number_format($product['price'] * 83, 0); ?></option>
                                        <option value="2">2kg - ₹<?php echo number_format($product['price'] * 83 * 2, 0); ?></option>
                                    </select>
                                </div>
                                <button class="btn btn-success w-100 mt-2" onclick="addToCartWithWeight(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($product['unit']); ?>')">>
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <a href="pages/auth/login.php" class="btn btn-primary w-100 mt-2">Login to Buy</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="pages/shop.php" class="btn btn-secondary">View All Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="why-choose-us py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose UFarmer?</h2>
        
        <div class="grid grid-3">
            <div class="feature-card text-center">
                <div class="feature-icon">
                    <i class="fas fa-leaf fa-3x text-success"></i>
                </div>
                <h3>100% Organic</h3>
                <p>All our farmers follow organic and sustainable farming practices, ensuring you get the healthiest produce possible.</p>
            </div>
            
            <div class="feature-card text-center">
                <div class="feature-icon">
                    <i class="fas fa-truck fa-3x text-success"></i>
                </div>
                <h3>Farm to Table</h3>
                <p>Direct from farm to your table. No middlemen, no long storage periods. Just fresh, nutritious food delivered fast.</p>
            </div>
            
            <div class="feature-card text-center">
                <div class="feature-icon">
                    <i class="fas fa-handshake fa-3x text-success"></i>
                </div>
                <h3>Support Local</h3>
                <p>Every purchase directly supports local farmers and strengthens your community's food security and economy.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section py-5 bg-primary">
    <div class="container text-center">
        <h2 class="text-white mb-3">Ready to Join Our Community?</h2>
        <p class="text-white mb-4">Whether you're a farmer looking to sell your produce or a customer seeking fresh, local food, we're here for you.</p>
        
        <div class="cta-buttons">
            <a href="pages/auth/register.php?type=farmer" class="btn btn-outline-light btn-lg">Join as Farmer</a>
            <a href="pages/auth/register.php" class="btn btn-light btn-lg">Shop with Us</a>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>
