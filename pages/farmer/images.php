<?php
$pageTitle = "Image Management";
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

// Handle image uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verifyCSRFToken($_POST['csrf_token'])) {
        
        if ($_POST['action'] === 'upload_profile_image') {
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $uploadedImage = uploadFile($_FILES['profile_image'], 'farmers');
                if ($uploadedImage) {
                    global $pdo;
                    $stmt = $pdo->prepare("UPDATE farmers SET profile_image = ? WHERE id = ?");
                    if ($stmt->execute([$uploadedImage, $farmer['id']])) {
                        $message = "Profile image updated successfully!";
                        $messageType = "success";
                        $farmer['profile_image'] = $uploadedImage;
                    } else {
                        $message = "Failed to update profile image in database.";
                        $messageType = "danger";
                    }
                } else {
                    $message = "Failed to upload image. Please try again.";
                    $messageType = "danger";
                }
            }
        }
        
        if ($_POST['action'] === 'upload_product_image') {
            $productId = intval($_POST['product_id']);
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                $uploadedImage = uploadFile($_FILES['product_image'], 'products');
                if ($uploadedImage) {
                    global $pdo;
                    $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ? AND farmer_id = ?");
                    if ($stmt->execute([$uploadedImage, $productId, $farmer['id']])) {
                        $message = "Product image updated successfully!";
                        $messageType = "success";
                    } else {
                        $message = "Failed to update product image.";
                        $messageType = "danger";
                    }
                } else {
                    $message = "Failed to upload image. Please try again.";
                    $messageType = "danger";
                }
            }
        }
    }
}

// Get farmer's products
$products = getFarmerProducts($farmer['id']);

include '../../components/header.php';
?>

<main class="main-content">
    <div class="container">
        <div class="dashboard-header">
            <h1><i class="fas fa-images"></i> Image Management</h1>
            <p>Upload and manage high-quality images for your farm and products</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Image Guidelines -->
        <div class="image-guidelines card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-lightbulb"></i> Image Guidelines</h3>
            </div>
            <div class="card-body">
                <div class="guidelines-grid">
                    <div class="guideline-item">
                        <i class="fas fa-camera text-primary"></i>
                        <h4>High Resolution</h4>
                        <p>Use images at least 800x600 pixels for best quality</p>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-file-image text-success"></i>
                        <h4>File Formats</h4>
                        <p>JPEG, PNG, or WebP. Maximum file size: 5MB</p>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-sun text-warning"></i>
                        <h4>Good Lighting</h4>
                        <p>Use natural light and avoid shadows or blur</p>
                    </div>
                    <div class="guideline-item">
                        <i class="fas fa-crop text-info"></i>
                        <h4>Proper Framing</h4>
                        <p>Show the full product with minimal background</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Farm Profile Image -->
        <div class="profile-image-section card mb-4">
            <div class="card-header">
                <h3><i class="fas fa-user-circle"></i> Farm Profile Image</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="current-image">
                            <div class="farmer-avatar-container">
                                <img src="<?php echo $farmer['profile_image'] ? UPLOAD_URL . $farmer['profile_image'] : SITE_URL . '/assets/images/default-farmer.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars(html_entity_decode($farmer['farm_name'], ENT_QUOTES)); ?>" 
                                     class="farmer-avatar"
                                     id="current-profile-image">
                                <div class="image-quality-badge quality-optimized">HD</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                            <?php echo getCSRFInput(); ?>
                            <input type="hidden" name="action" value="upload_profile_image">
                            
                            <div class="form-group">
                                <label for="profile_image" class="form-label">
                                    <i class="fas fa-cloud-upload-alt"></i> Upload New Profile Image
                                </label>
                                <input type="file" 
                                       id="profile_image" 
                                       name="profile_image" 
                                       class="form-control"
                                       accept="image/*"
                                       required>
                                <small class="form-text text-muted">
                                    Choose a clear photo of yourself or your farm. Square images work best.
                                </small>
                            </div>
                            
                            <div class="image-preview-container" id="profile-preview-container" style="display: none;">
                                <img id="profile-preview" class="image-preview" alt="Preview">
                                <div class="image-upload-overlay">
                                    <i class="fas fa-eye"></i> Preview
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile Image
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Images -->
        <div class="product-images-section card">
            <div class="card-header">
                <h3><i class="fas fa-shopping-basket"></i> Product Images</h3>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="empty-state text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4>No Products Yet</h4>
                        <p class="text-muted">Add products to your farm to manage their images</p>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Your First Product
                        </a>
                    </div>
                <?php else: ?>
                    <div class="products-image-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-image-card">
                                <div class="product-image-display">
                                    <div class="image-container">
                                        <img src="<?php echo $product['image'] ? UPLOAD_URL . $product['image'] : SITE_URL . '/assets/images/default-product.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-image">
                                        <?php if ($product['image']): ?>
                                            <div class="image-quality-badge quality-hd">Custom</div>
                                        <?php else: ?>
                                            <div class="image-quality-badge">Default</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="product-image-info">
                                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="text-muted"><?php echo htmlspecialchars($product['category']); ?></p>
                                    
                                    <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                                        <?php echo getCSRFInput(); ?>
                                        <input type="hidden" name="action" value="upload_product_image">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        
                                        <div class="form-group">
                                            <label for="product_image_<?php echo $product['id']; ?>" class="form-label">
                                                <i class="fas fa-image"></i> Update Image
                                            </label>
                                            <input type="file" 
                                                   id="product_image_<?php echo $product['id']; ?>" 
                                                   name="product_image" 
                                                   class="form-control form-control-sm"
                                                   accept="image/*">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-sm btn-success w-100">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Image Optimization Tips -->
        <div class="optimization-tips card mt-4">
            <div class="card-header">
                <h3><i class="fas fa-rocket"></i> Image Optimization Tips</h3>
            </div>
            <div class="card-body">
                <div class="tips-grid">
                    <div class="tip-item">
                        <i class="fas fa-compress-arrows-alt text-primary"></i>
                        <h4>Auto-Optimization</h4>
                        <p>Images are automatically resized and compressed for fast loading</p>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-mobile-alt text-success"></i>
                        <h4>Mobile-Friendly</h4>
                        <p>All images are optimized for mobile devices and slow connections</p>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-search text-warning"></i>
                        <h4>SEO Benefits</h4>
                        <p>Good images improve your products' visibility in search results</p>
                    </div>
                    <div class="tip-item">
                        <i class="fas fa-chart-line text-info"></i>
                        <h4>Better Sales</h4>
                        <p>High-quality images increase customer trust and purchase rates</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.dashboard-header {
    margin-bottom: 2rem;
    text-align: center;
}

.dashboard-header h1 {
    color: var(--primary-green);
    margin-bottom: 0.5rem;
}

.image-guidelines {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.guidelines-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 1rem;
}

.guideline-item {
    text-align: center;
    padding: 1rem;
}

.guideline-item i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.guideline-item h4 {
    color: var(--dark-green);
    margin-bottom: 0.5rem;
}

.farmer-avatar-container {
    position: relative;
    margin: 0 auto;
}

.current-image {
    text-align: center;
}

.image-upload-form {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 2px dashed #dee2e6;
    transition: border-color 0.3s ease;
}

.image-upload-form:hover {
    border-color: var(--primary-green);
}

.products-image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.product-image-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-image-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-image-display {
    height: 200px;
    position: relative;
    overflow: hidden;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image-info {
    padding: 1.5rem;
}

.product-image-info h4 {
    color: var(--dark-green);
    margin-bottom: 0.5rem;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 1rem;
}

.tip-item {
    text-align: center;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.tip-item i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.tip-item h4 {
    color: var(--dark-green);
    margin-bottom: 0.5rem;
}

.empty-state {
    color: var(--gray-medium);
}

@media (max-width: 768px) {
    .guidelines-grid,
    .tips-grid {
        grid-template-columns: 1fr;
    }
    
    .products-image-grid {
        grid-template-columns: 1fr;
    }
    
    .row {
        flex-direction: column;
    }
    
    .col-md-4,
    .col-md-8 {
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle profile image preview
    const profileInput = document.getElementById('profile_image');
    const profilePreview = document.getElementById('profile-preview');
    const profilePreviewContainer = document.getElementById('profile-preview-container');
    const currentProfileImage = document.getElementById('current-profile-image');

    if (profileInput) {
        profileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePreview.src = e.target.result;
                    profilePreviewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle product image previews
    document.querySelectorAll('input[type="file"][name="product_image"]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const card = input.closest('.product-image-card');
                const currentImage = card.querySelector('.product-image');
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create preview overlay
                    let overlay = card.querySelector('.preview-overlay');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.className = 'preview-overlay';
                        overlay.style.cssText = `
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background: url('${e.target.result}') center/cover;
                            opacity: 0.8;
                            z-index: 2;
                        `;
                        card.querySelector('.product-image-display').appendChild(overlay);
                    } else {
                        overlay.style.backgroundImage = `url('${e.target.result}')`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Add drag and drop functionality
    document.querySelectorAll('.image-upload-form').forEach(function(form) {
        const fileInput = form.querySelector('input[type="file"]');
        
        form.addEventListener('dragover', function(e) {
            e.preventDefault();
            form.style.borderColor = 'var(--primary-green)';
            form.style.backgroundColor = '#e8f5e8';
        });
        
        form.addEventListener('dragleave', function(e) {
            e.preventDefault();
            form.style.borderColor = '#dee2e6';
            form.style.backgroundColor = '#f8f9fa';
        });
        
        form.addEventListener('drop', function(e) {
            e.preventDefault();
            form.style.borderColor = '#dee2e6';
            form.style.backgroundColor = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });
    });
});
</script>

<?php include '../../components/footer.php'; ?>
