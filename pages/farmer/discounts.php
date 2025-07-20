<?php
$pageTitle = "Manage Discounts";
require_once '../../components/header.php';

if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$currentUser = getCurrentUser();

// Get farmer info
$stmt = $pdo->prepare("SELECT * FROM farmers WHERE user_id = ?");
$stmt->execute([$currentUser['id']]);
$farmer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$farmer) {
    header('Location: ' . SITE_URL . '/pages/farmer/setup.php');
    exit;
}

$message = '';
$messageType = '';

// Handle discount updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        if ($_POST['action'] === 'update_discount') {
            $productId = intval($_POST['product_id']);
            $discountPercentage = floatval($_POST['discount_percentage']);
            
            // Validate discount percentage
            if ($discountPercentage < 0 || $discountPercentage > 80) {
                $message = "Discount must be between 0% and 80%";
                $messageType = "danger";
            } else {
                if (updateProductDiscount($productId, $discountPercentage)) {
                    $message = "Discount updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update discount";
                    $messageType = "danger";
                }
            }
        }
        
        if ($_POST['action'] === 'bulk_discount') {
            $discountPercentage = floatval($_POST['bulk_discount_percentage']);
            $selectedProducts = $_POST['selected_products'] ?? [];
            
            if ($discountPercentage < 0 || $discountPercentage > 80) {
                $message = "Discount must be between 0% and 80%";
                $messageType = "danger";
            } elseif (empty($selectedProducts)) {
                $message = "Please select at least one product";
                $messageType = "danger";
            } else {
                $successCount = 0;
                foreach ($selectedProducts as $productId) {
                    if (updateProductDiscount($productId, $discountPercentage)) {
                        $successCount++;
                    }
                }
                $message = "Updated discounts for $successCount products";
                $messageType = "success";
            }
        }
    }
}

// Get farmer's products
$stmt = $pdo->prepare("SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->execute([$farmer['id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="main-content">
    <div class="container">
        <div class="dashboard-header">
            <h1><i class="fas fa-tags"></i> Manage Product Discounts</h1>
            <p>Set promotional pricing for your products to attract more customers</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="discount-tools">
            <div class="bulk-actions-card">
                <h3><i class="fas fa-magic"></i> Bulk Discount Actions</h3>
                <form method="POST" class="bulk-discount-form">
                    <?php echo getCSRFInput(); ?>
                    <input type="hidden" name="action" value="bulk_discount">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bulk_discount_percentage">Apply Discount (%):</label>
                            <input type="number" 
                                   id="bulk_discount_percentage" 
                                   name="bulk_discount_percentage" 
                                   min="0" 
                                   max="80" 
                                   step="0.01" 
                                   class="form-control"
                                   placeholder="Enter discount percentage">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-magic"></i> Apply to Selected
                            </button>
                        </div>
                    </div>
                    
                    <div class="quick-actions">
                        <button type="button" onclick="selectAllProducts()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-check-double"></i> Select All
                        </button>
                        <button type="button" onclick="clearSelection()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear Selection
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="products-discount-list">
            <h3><i class="fas fa-list"></i> Your Products</h3>
            
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h4>No Products Yet</h4>
                    <p>Add products to your farm to manage discounts</p>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-discount-card">
                            <div class="product-header">
                                <input type="checkbox" 
                                       name="selected_products[]" 
                                       value="<?php echo $product['id']; ?>"
                                       class="product-checkbox">
                                <img src="<?php echo $product['image'] ? UPLOAD_URL . $product['image'] : SITE_URL . '/assets/images/default-product.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image">
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="original-price">Base Price: $<?php echo number_format($product['price'], 2); ?></p>
                                    <?php if ($product['discount_percentage'] > 0): ?>
                                        <p class="current-discount">
                                            Current Discount: <?php echo $product['discount_percentage']; ?>% 
                                            (Sale Price: $<?php echo number_format($product['discounted_price'], 2); ?>)
                                        </p>
                                    <?php else: ?>
                                        <p class="no-discount">No discount applied</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <form method="POST" class="discount-form">
                                <?php echo getCSRFInput(); ?>
                                <input type="hidden" name="action" value="update_discount">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                
                                <div class="discount-controls">
                                    <div class="form-group">
                                        <label for="discount_<?php echo $product['id']; ?>">Discount %:</label>
                                        <input type="number" 
                                               id="discount_<?php echo $product['id']; ?>" 
                                               name="discount_percentage" 
                                               min="0" 
                                               max="80" 
                                               step="0.01" 
                                               value="<?php echo $product['discount_percentage']; ?>"
                                               class="form-control"
                                               onchange="updatePreview(<?php echo $product['id']; ?>, <?php echo $product['price']; ?>)">
                                    </div>
                                    <div class="preview-price" id="preview_<?php echo $product['id']; ?>">
                                        Sale Price: $<?php echo number_format($product['discounted_price'] ?: $product['price'], 2); ?>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.dashboard-header {
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    color: var(--primary-green);
}

.discount-tools {
    margin-bottom: 2rem;
}

.bulk-actions-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.bulk-actions-card h3 {
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.bulk-discount-form .form-row {
    display: flex;
    gap: 1rem;
    align-items: end;
    margin-bottom: 1rem;
}

.quick-actions {
    display: flex;
    gap: 0.5rem;
}

.products-discount-list h3 {
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 4rem;
    color: var(--gray-medium);
    margin-bottom: 1rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.product-discount-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 1rem;
    transition: transform 0.3s ease;
}

.product-discount-card:hover {
    transform: translateY(-5px);
}

.product-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.product-checkbox {
    transform: scale(1.2);
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
}

.product-info h4 {
    margin: 0;
    color: var(--dark-green);
}

.original-price {
    color: var(--gray-medium);
    margin: 0.25rem 0;
}

.current-discount {
    color: var(--success);
    font-weight: bold;
    margin: 0.25rem 0;
}

.no-discount {
    color: var(--gray-medium);
    font-style: italic;
    margin: 0.25rem 0;
}

.discount-controls {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: center;
}

.discount-controls .form-group {
    margin: 0;
}

.discount-controls label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.preview-price {
    font-weight: bold;
    color: var(--primary-green);
    grid-column: 1 / -1;
    margin-top: 0.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .bulk-discount-form .form-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .discount-controls {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function selectAllProducts() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

function updatePreview(productId, originalPrice) {
    const discountInput = document.getElementById(`discount_${productId}`);
    const previewElement = document.getElementById(`preview_${productId}`);
    
    const discountPercent = parseFloat(discountInput.value) || 0;
    const discountedPrice = originalPrice - (originalPrice * discountPercent / 100);
    
    previewElement.textContent = `Sale Price: $${discountedPrice.toFixed(2)}`;
}

// Update bulk form to include selected products
document.querySelector('.bulk-discount-form').addEventListener('submit', function(e) {
    const selectedProducts = Array.from(document.querySelectorAll('.product-checkbox:checked'))
                                 .map(checkbox => checkbox.value);
    
    // Add selected products as hidden inputs
    selectedProducts.forEach(productId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_products[]';
        input.value = productId;
        this.appendChild(input);
    });
});
</script>

<?php require_once '../../components/footer.php'; ?>
