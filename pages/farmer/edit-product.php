<?php
$pageTitle = 'Edit Product';
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

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: dashboard.php');
    exit();
}

// Get product details
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND farmer_id = ?");
$stmt->execute([$product_id, $farmer['id']]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    showNotification('Product not found or you do not have permission to edit it.', 'error');
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $unit = trim($_POST['unit'] ?? '');
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name)) {
        $errors[] = 'Product name is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Product description is required';
    }
    
    if (empty($category)) {
        $errors[] = 'Category is required';
    }
    
    if (empty($unit)) {
        $errors[] = 'Unit is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than 0';
    }
    
    if ($stock_quantity < 0) {
        $errors[] = 'Stock quantity cannot be negative';
    }
    
    // Handle image upload (optional)
    $image_path = $product['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image'], 'products');
        if ($upload_result['success']) {
            $image_path = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['error'];
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET 
                name = ?, 
                description = ?, 
                category = ?, 
                price = ?, 
                unit = ?, 
                stock_quantity = ?, 
                image = ?, 
                is_active = ?,
                updated_at = NOW() 
                WHERE id = ? AND farmer_id = ?");
            
            $stmt->execute([
                $name,
                $description,
                $category,
                $price,
                $unit,
                $stock_quantity,
                $image_path,
                $is_active,
                $product_id,
                $farmer['id']
            ]);
            
            $_SESSION['success_message'] = 'Product updated successfully!';
            header('Location: dashboard.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            error_log("Edit product error: " . $e->getMessage());
        } catch (Exception $e) {
            $errors[] = 'Unexpected error: ' . $e->getMessage();
            error_log("Edit product unexpected error: " . $e->getMessage());
        }
    }
    
    // Update product array with form values for display
    $product = array_merge($product, [
        'name' => $name,
        'description' => $description,
        'category' => $category,
        'price' => $price,
        'unit' => $unit,
        'stock_quantity' => $stock_quantity,
        'is_active' => $is_active
    ]);
}

include '../../components/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="dashboard.php" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <div>
                    <h2 class="mb-0">Edit Product</h2>
                    <p class="text-muted mb-0">Update your product information</p>
                </div>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Main Form -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-edit"></i> Product Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <!-- Product Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="name" 
                                           name="name" 
                                           value="<?php echo htmlspecialchars($product['name']); ?>"
                                           placeholder="e.g., Fresh Organic Tomatoes"
                                           required>
                                </div>

                                <!-- Category and Price Row -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category *</label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Select a category</option>
                                                <option value="Vegetables" <?php echo $product['category'] === 'Vegetables' ? 'selected' : ''; ?>>🥬 Vegetables</option>
                                                <option value="Fruits" <?php echo $product['category'] === 'Fruits' ? 'selected' : ''; ?>>🍎 Fruits</option>
                                                <option value="Herbs" <?php echo $product['category'] === 'Herbs' ? 'selected' : ''; ?>>🌿 Herbs</option>
                                                <option value="Grains" <?php echo $product['category'] === 'Grains' ? 'selected' : ''; ?>>🌾 Grains</option>
                                                <option value="Dairy" <?php echo $product['category'] === 'Dairy' ? 'selected' : ''; ?>>🥛 Dairy</option>
                                                <option value="Other" <?php echo $product['category'] === 'Other' ? 'selected' : ''; ?>>📦 Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price (₹) *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="price" 
                                                       name="price" 
                                                       value="<?php echo $product['price']; ?>"
                                                       step="0.01" 
                                                       min="0.01"
                                                       placeholder="0.00"
                                                       required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="unit" class="form-label">Unit *</label>
                                            <select class="form-select" id="unit" name="unit" required>
                                                <option value="">Select Unit</option>
                                                <option value="kg" <?php echo $product['unit'] === 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                                <option value="g" <?php echo $product['unit'] === 'g' ? 'selected' : ''; ?>>Gram (g)</option>
                                                <option value="piece" <?php echo $product['unit'] === 'piece' ? 'selected' : ''; ?>>Piece</option>
                                                <option value="dozen" <?php echo $product['unit'] === 'dozen' ? 'selected' : ''; ?>>Dozen</option>
                                                <option value="liter" <?php echo $product['unit'] === 'liter' ? 'selected' : ''; ?>>Liter (L)</option>
                                                <option value="bunch" <?php echo $product['unit'] === 'bunch' ? 'selected' : ''; ?>>Bunch</option>
                                                <option value="pack" <?php echo $product['unit'] === 'pack' ? 'selected' : ''; ?>>Pack</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="stock_quantity" 
                                                   name="stock_quantity" 
                                                   value="<?php echo $product['stock_quantity']; ?>"
                                                   min="0"
                                                   placeholder="Available quantity"
                                                   required>
                                            <div class="form-text">Quantity in selected unit</div>
                                        </div>
                                    </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Product Description *</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              placeholder="Describe your product - quality, freshness, growing methods, etc."
                                              required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>

                                <!-- Product Image -->
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*">
                                    <div class="form-text">Leave empty to keep current image. Recommended size: 800x600px (max 5MB)</div>
                                </div>

                                <!-- Active Status -->
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active"
                                               <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active Product</strong>
                                            <small class="d-block text-muted">Customers can see and order this product</small>
                                        </label>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Update Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Current Product Image -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-image"></i> Current Image</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="<?php echo $product['image'] ? UPLOAD_URL . $product['image'] : SITE_URL . '/assets/images/default-product.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-preview img-fluid">
                            <div class="mt-2">
                                <small class="text-muted">Current product image</small>
                            </div>
                        </div>
                    </div>

                    <!-- Product Stats -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Product Stats</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get product stats
                            $statsStmt = $pdo->prepare("
                                SELECT 
                                    COUNT(DISTINCT oi.order_id) as total_orders,
                                    SUM(oi.quantity) as total_sold,
                                    SUM(oi.price * oi.quantity) as total_revenue
                                FROM order_items oi
                                JOIN orders o ON oi.order_id = o.id
                                WHERE oi.product_id = ? AND o.farmer_id = ?
                            ");
                            $statsStmt->execute([$product_id, $farmer['id']]);
                            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo $stats['total_orders'] ?: 0; ?></span>
                                    <span class="stat-label">Total Orders</span>
                                </div>
                            </div>

                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-weight"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo $stats['total_sold'] ?: 0; ?> kg</span>
                                    <span class="stat-label">Total Sold</span>
                                </div>
                            </div>

                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo formatPrice($stats['total_revenue'] ?: 0); ?></span>
                                    <span class="stat-label">Total Revenue</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Tips for Success</h5>
                        </div>
                        <div class="card-body">
                            <div class="tip-item">
                                <i class="fas fa-camera text-primary"></i>
                                <div>
                                    <strong>High-Quality Photos</strong>
                                    <p>Use clear, well-lit photos of your products</p>
                                </div>
                            </div>

                            <div class="tip-item">
                                <i class="fas fa-edit text-success"></i>
                                <div>
                                    <strong>Detailed Descriptions</strong>
                                    <p>Include freshness, taste, and growing methods</p>
                                </div>
                            </div>

                            <div class="tip-item">
                                <i class="fas fa-rupee-sign text-warning"></i>
                                <div>
                                    <strong>Competitive Pricing</strong>
                                    <p>Research market prices for fair pricing</p>
                                </div>
                            </div>

                            <div class="tip-item">
                                <i class="fas fa-boxes text-info"></i>
                                <div>
                                    <strong>Stock Management</strong>
                                    <p>Keep stock quantities updated regularly</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-preview {
    border-radius: 12px;
    max-height: 200px;
    object-fit: cover;
    border: 3px solid var(--light-green);
}

.form-label {
    font-weight: 600;
    color: var(--dark-green);
}

.card-header {
    background: linear-gradient(135deg, var(--light-green), var(--primary-green));
    color: var(--dark-green);
    border-bottom: none;
}

.card-header h5 {
    color: var(--dark-green);
}

.btn-success {
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    border: none;
    padding: 0.5rem 1.5rem;
}

.btn-success:hover {
    background: linear-gradient(135deg, var(--secondary-green), var(--primary-green));
    transform: translateY(-1px);
}

.form-check-input:checked {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
}

.stat-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: var(--light-green);
    color: var(--primary-green);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.stat-info {
    flex: 1;
}

.stat-value {
    display: block;
    font-weight: 700;
    color: var(--dark-green);
    font-size: 1.1rem;
}

.stat-label {
    display: block;
    color: var(--gray-medium);
    font-size: 0.9rem;
}

.tip-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.tip-item:last-child {
    margin-bottom: 0;
}

.tip-item i {
    width: 30px;
    margin-right: 0.75rem;
    margin-top: 0.25rem;
}

.tip-item strong {
    display: block;
    color: var(--dark-green);
    margin-bottom: 0.25rem;
}

.tip-item p {
    margin: 0;
    color: var(--gray-medium);
    font-size: 0.9rem;
    line-height: 1.4;
}

.alert-danger {
    border-left: 4px solid #dc3545;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        width: 100%;
    }
    
    .product-preview {
        max-height: 150px;
    }
}
</style>

<?php include '../../components/footer.php'; ?>
