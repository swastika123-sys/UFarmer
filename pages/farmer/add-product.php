<?php
$pageTitle = "Add New Product";
require_once '../../includes/functions.php';

if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$currentUser = getCurrentUser();
$farmer = getFarmerByUserId($currentUser['id']);

if (!$farmer) {
    header('Location: ' . SITE_URL . '/pages/farmer/setup.php');
    exit;
}

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $category = sanitizeInput($_POST['category']);
        $price = floatval($_POST['price']);
        $unit = sanitizeInput($_POST['unit']);
        $stockQuantity = intval($_POST['stock_quantity']);
        $seasonalAvailability = sanitizeInput($_POST['seasonal_availability']);
        
        if (empty($name) || empty($description) || empty($category) || empty($unit) || $price <= 0 || $stockQuantity < 0) {
            $message = "Please fill in all required fields with valid values.";
            $messageType = "danger";
        } else {
            // Handle image upload
            $productImage = null;
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $productImage = uploadFile($_FILES['product_image'], 'products');
                if (!$productImage) {
                    $message = "Failed to upload product image. Product added without image.";
                    $messageType = "warning";
                }
            }
            
            // Add product to database
            global $pdo;
            try {
                $stmt = $pdo->prepare("INSERT INTO products (farmer_id, name, description, category, price, unit, stock_quantity, seasonal_availability, image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                if ($stmt->execute([$farmer['id'], $name, $description, $category, $price, $unit, $stockQuantity, $seasonalAvailability, $productImage])) {
                    $message = "Product added successfully!";
                    $messageType = "success";
                    // Clear form data
                    $_POST = [];
                } else {
                    $message = "Failed to add product. Please try again.";
                    $messageType = "danger";
                }
            } catch (Exception $e) {
                $message = "An error occurred: " . $e->getMessage();
                $messageType = "danger";
            }
        }
    } else {
        $message = "Security token mismatch. Please try again.";
        $messageType = "danger";
    }
}

include '../../components/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-plus-circle"></i> Add New Product</h1>
                    <p class="text-muted">Add fresh produce to your farm's product catalog</p>
                </div>
                <div class="col-md-4 text-md-right">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-shopping-basket"></i> Product Details</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?php echo getCSRFInput(); ?>
                            <input type="hidden" name="action" value="add_product">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">Product Name *</label>
                                        <input type="text" 
                                               id="name" 
                                               name="name" 
                                               class="form-control" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                               placeholder="e.g., Organic Tomatoes"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category" class="form-label">Category *</label>
                                        <select id="category" name="category" class="form-control" required>
                                            <option value="">Select Category</option>
                                            <option value="vegetables" <?php echo (isset($_POST['category']) && $_POST['category'] === 'vegetables') ? 'selected' : ''; ?>>Vegetables</option>
                                            <option value="fruits" <?php echo (isset($_POST['category']) && $_POST['category'] === 'fruits') ? 'selected' : ''; ?>>Fruits</option>
                                            <option value="herbs" <?php echo (isset($_POST['category']) && $_POST['category'] === 'herbs') ? 'selected' : ''; ?>>Herbs & Spices</option>
                                            <option value="dairy" <?php echo (isset($_POST['category']) && $_POST['category'] === 'dairy') ? 'selected' : ''; ?>>Dairy Products</option>
                                            <option value="nuts" <?php echo (isset($_POST['category']) && $_POST['category'] === 'nuts') ? 'selected' : ''; ?>>Nuts & Seeds</option>
                                            <option value="specialty" <?php echo (isset($_POST['category']) && $_POST['category'] === 'specialty') ? 'selected' : ''; ?>>Specialty Items</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-label">Description *</label>
                                <textarea id="description" 
                                          name="description" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="Describe your product, its quality, growing methods, etc."
                                          required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="price" class="form-label">Price (₹) *</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <input type="number" 
                                                   id="price" 
                                                   name="price" 
                                                   class="form-control" 
                                                   value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>"
                                                   step="0.01" 
                                                   min="0.01" 
                                                   placeholder="0.00"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="unit" class="form-label">Unit *</label>
                                        <select id="unit" name="unit" class="form-control" required>
                                            <option value="">Select Unit</option>
                                            <option value="kg" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'kg') ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                            <option value="g" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'g') ? 'selected' : ''; ?>>Gram (g)</option>
                                            <option value="piece" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'piece') ? 'selected' : ''; ?>>Piece</option>
                                            <option value="dozen" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'dozen') ? 'selected' : ''; ?>>Dozen</option>
                                            <option value="liter" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'liter') ? 'selected' : ''; ?>>Liter (L)</option>
                                            <option value="bunch" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'bunch') ? 'selected' : ''; ?>>Bunch</option>
                                            <option value="pack" <?php echo (isset($_POST['unit']) && $_POST['unit'] === 'pack') ? 'selected' : ''; ?>>Pack</option>
                                        </select>
                                        <small class="form-text text-muted">How do you sell this product?</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                        <input type="number" 
                                               id="stock_quantity" 
                                               name="stock_quantity" 
                                               class="form-control" 
                                               value="<?php echo isset($_POST['stock_quantity']) ? $_POST['stock_quantity'] : ''; ?>"
                                               min="0" 
                                               placeholder="0"
                                               required>
                                        <small class="form-text text-muted">Available quantity in selected unit</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="seasonal_availability" class="form-label">Seasonal Availability</label>
                                <input type="text" 
                                       id="seasonal_availability" 
                                       name="seasonal_availability" 
                                       class="form-control" 
                                       value="<?php echo isset($_POST['seasonal_availability']) ? htmlspecialchars($_POST['seasonal_availability']) : ''; ?>"
                                       placeholder="e.g., Summer, Year-round, Winter only">
                                <small class="form-text text-muted">Optional: When is this product available?</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="product_image" class="form-label">Product Image</label>
                                <input type="file" 
                                       id="product_image" 
                                       name="product_image" 
                                       class="form-control" 
                                       accept="image/*">
                                <small class="form-text text-muted">Upload a high-quality image of your product (max 5MB)</small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus"></i> Add Product
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-lightbulb"></i> Tips for Success</h4>
                    </div>
                    <div class="card-body">
                        <div class="tip-item">
                            <i class="fas fa-camera text-primary"></i>
                            <div>
                                <strong>High-Quality Photos</strong>
                                <p>Use natural lighting and show your product clearly. Good photos increase sales by 40%!</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <i class="fas fa-pencil-alt text-success"></i>
                            <div>
                                <strong>Detailed Descriptions</strong>
                                <p>Mention growing methods, taste, freshness, and any certifications.</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <i class="fas fa-rupee-sign text-warning"></i>
                            <div>
                                <strong>Competitive Pricing</strong>
                                <p>Research local market prices to set competitive rates for your produce.</p>
                            </div>
                        </div>
                        
                        <div class="tip-item">
                            <i class="fas fa-calendar text-info"></i>
                            <div>
                                <strong>Seasonal Information</strong>
                                <p>Let customers know when your products are in peak season.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.page-header h1 {
    color: var(--primary-green);
    margin-bottom: 0.5rem;
}

.form-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
    display: flex;
    gap: 1rem;
}

.tip-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.tip-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.tip-item i {
    font-size: 1.2rem;
    margin-right: 1rem;
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.tip-item strong {
    color: var(--dark-green);
    display: block;
    margin-bottom: 0.25rem;
}

.tip-item p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--gray-medium);
    line-height: 1.4;
}

.input-group-text {
    background: var(--light-green);
    border-color: var(--primary-green);
    color: var(--dark-green);
    font-weight: 600;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .page-header .col-md-4 {
        margin-top: 1rem;
        text-align: center;
    }
}
</style>

<?php include '../../components/footer.php'; ?>
