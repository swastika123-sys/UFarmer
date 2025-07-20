<?php
$pageTitle = 'Our Farmers';
include '../components/header.php';

// Get all farmers with newest first as requested
$farmers = getAllFarmers(null, 'created_at DESC');

// Function to get dynamic farmer rating
function getDynamicFarmerRating($farmerId, $pdo) {
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
?>

<section class="page-header py-5 bg-light">
    <div class="container">
        <div class="section-header">
            <h1 class="decorative-heading">👨‍🌾 Meet Our Local Farmers 🌾</h1>
            <div class="heading-decoration"></div>
            <p class="section-subtitle">Discover the passionate growers behind your fresh, organic produce</p>
        </div>
    </div>
</section>

<section class="farmers-grid py-5">
    <div class="container">
        <?php if (empty($farmers)): ?>
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-seedling fa-5x text-muted mb-3"></i>
                    <h3>No Farmers Yet</h3>
                    <p class="text-muted">We're growing our community of local farmers. Check back soon!</p>
                    <a href="../pages/auth/register.php?type=farmer" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Join as a Farmer
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="filters mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Found <?php echo count($farmers); ?> farmers</h5>
                    <div class="filter-controls">
                        <select id="sortSelect" class="form-control">
                            <option value="newest">Newest First</option>
                            <option value="rating">Highest Rated</option>
                            <option value="name">Alphabetical</option>
                            <option value="location">By Location</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-3" id="farmersGrid">
                <?php foreach ($farmers as $index => $farmer): ?>
                    <?php $dynamicRating = getDynamicFarmerRating($farmer['id'], $pdo); ?>
                    <div class="card farmer-card" data-rating="<?php echo $dynamicRating['rating']; ?>" data-name="<?php echo strtolower($farmer['farm_name']); ?>" data-location="<?php echo strtolower($farmer['location']); ?>">
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
                                     alt="<?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'])); ?>" 
                                     class="farm-image"
                                     onerror="this.src='<?php echo SITE_URL; ?>/assets/images/default-farmer.jpg'">
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
                                        $rating = $dynamicRating['rating'];
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
                                        <span class="stat-label"><?php echo $dynamicRating['count']; ?> Reviews</span>
                                    </div>
                                </div>
                                
                                <!-- Row 4: Description Row - Truncated -->
                                <p class="farm-description"><?php echo htmlspecialchars(substr($farmer['description'], 0, 120)); ?>...</p>
                                
                                <!-- Join Date -->
                                <div class="join-info">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> Joined <?php echo timeAgo($farmer['created_at']); ?>
                                    </small>
                                </div>
                                
                                <!-- Row 5: Actions Row -->
                                <div class="farm-actions">
                                    <a href="farmer/profile.php?id=<?php echo $farmer['id']; ?>" class="btn btn-farm-primary">
                                        <i class="fas fa-store"></i> Visit Farm
                                    </a>
                                    
                                    <?php
                                    $productCount = count(getFarmerProducts($farmer['id']));
                                    if ($productCount > 0):
                                    ?>
                                        <a href="shop.php?farmer=<?php echo $farmer['id']; ?>" class="btn btn-farm-secondary">
                                            <i class="fas fa-shopping-basket"></i> Shop (<?php echo $productCount; ?>)
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="join-cta py-5 bg-primary">
    <div class="container text-center">
        <h2 class="text-white mb-3">Are You a Local Farmer?</h2>
        <p class="text-white mb-4">Join our community and connect directly with customers who value fresh, local produce.</p>
        
        <div class="benefits mb-4">
            <div class="grid grid-3 text-white">
                <div class="benefit">
                    <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                    <h4>Better Prices</h4>
                    <p>No middlemen means better profits for you</p>
                </div>
                <div class="benefit">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h4>Direct Connection</h4>
                    <p>Build relationships with your customers</p>
                </div>
                <div class="benefit">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h4>Grow Your Business</h4>
                    <p>Expand your reach in the local community</p>
                </div>
            </div>
        </div>
        
        <a href="../pages/auth/register.php?type=farmer" class="btn btn-light btn-lg">
            <i class="fas fa-seedling"></i> Join as a Farmer
        </a>
    </div>
</section>

<style>
.farmer-card {
    transition: all 0.3s ease;
    height: 100%;
}

.farmer-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.farmer-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.farmer-actions .btn {
    flex: 1;
    min-width: 120px;
}

.farmer-details {
    margin: 1rem 0;
    padding: 1rem 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}

.join-date {
    font-size: 0.9rem;
}

.empty-state {
    padding: 4rem 2rem;
}

.filters {
    background: var(--white);
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.filter-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.filter-controls select {
    min-width: 150px;
}

.benefits .benefit {
    padding: 1rem;
}

.benefits i {
    opacity: 0.9;
}

@media (max-width: 768px) {
    .farmer-actions {
        flex-direction: column;
    }
    
    .farmer-actions .btn {
        width: 100%;
    }
    
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .benefits .grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sortSelect');
    const farmersGrid = document.getElementById('farmersGrid');
    
    if (sortSelect && farmersGrid) {
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            const farmers = Array.from(farmersGrid.children);
            
            farmers.sort((a, b) => {
                switch(sortBy) {
                    case 'rating':
                        return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    case 'location':
                        return a.dataset.location.localeCompare(b.dataset.location);
                    case 'newest':
                    default:
                        return 0; // Keep original order (newest first)
                }
            });
            
            // Re-append sorted farmers
            farmers.forEach(farmer => farmersGrid.appendChild(farmer));
        });
    }
});
</script>

<?php include '../components/footer.php'; ?>
