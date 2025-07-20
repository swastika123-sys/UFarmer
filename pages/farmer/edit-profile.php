<?php
$pageTitle = 'Edit Profile';
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate inputs
    $farm_name = trim($_POST['farm_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialty = trim($_POST['specialty'] ?? '');
    $certification = $_POST['certification'] ?? '';
    
    if (empty($farm_name)) {
        $errors[] = 'Farm name is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Farm description is required';
    }
    
    if (empty($location)) {
        $errors[] = 'Location is required';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    // Handle image upload
    $image_path = $farmer['profile_image']; // Keep existing image by default
    if (!empty($_POST['image_url'])) {
       $image_path = sanitizeInput($_POST['image_url']);
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['image'], 'farmers');
        if ($upload_result['success']) {
            $image_path = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['error'];
        }
    }
    
    if (empty($errors)) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("UPDATE farmers SET 
                farm_name = ?, 
                description = ?, 
                location = ?, 
                phone = ?, 
                specialty = ?, 
                certification = ?, 
                profile_image = ?,
                updated_at = NOW() 
                WHERE user_id = ?");
            
            $stmt->execute([
                $farm_name,
                $description,
                $location,
                $phone,
                $specialty,
                $certification,
                $image_path,
                $_SESSION['user_id']
            ]);
            
            showNotification('Profile updated successfully!', 'success');
            header('Location: dashboard.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include '../../components/header.php';
?>

<?php
// Prepare image URL for display
$displayImage = !empty($farmer['profile_image'])
    ? (filter_var($farmer['profile_image'], FILTER_VALIDATE_URL) ? $farmer['profile_image'] : UPLOAD_URL . $farmer['profile_image'])
    : SITE_URL . '/assets/images/default-farmer.jpg';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex align-items-center mb-4">
                <a href="dashboard.php" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <div>
                    <h2 class="mb-0">Edit Farm Profile</h2>
                    <p class="text-muted mb-0">Update your farm information and details</p>
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

            <!-- Edit Profile Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Farm Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Current Profile Image -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Current Profile Image</label>
                                <div class="text-center">
                                    <img src="<?= htmlspecialchars($displayImage) ?>" 
                                         alt="Profile Image" 
                                         class="profile-preview img-thumbnail" 
                                         style="width: 200px; height: 200px; object-fit: cover;">
                                </div>
                            </div>
                            
                            <!-- Form Fields -->
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="farm_name" class="form-label">Farm Name *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="farm_name" 
                                           name="farm_name" 
                                           value="<?php echo htmlspecialchars($farmer['farm_name']); ?>"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="location" class="form-label">Location *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="location" 
                                           name="location" 
                                           value="<?php echo htmlspecialchars($farmer['location']); ?>"
                                           placeholder="City, State"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo htmlspecialchars($farmer['phone']); ?>"
                                           placeholder="+91 98765 43210"
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label for="specialty" class="form-label">Farm Specialty</label>
                                    <select class="form-select" id="specialty" name="specialty">
                                        <option value="">Select a specialty</option>
                                        <option value="Organic Vegetables" <?php echo $farmer['specialty'] === 'Organic Vegetables' ? 'selected' : ''; ?>>Organic Vegetables</option>
                                        <option value="Fruits" <?php echo $farmer['specialty'] === 'Fruits' ? 'selected' : ''; ?>>Fruits</option>
                                        <option value="Herbs & Spices" <?php echo $farmer['specialty'] === 'Herbs & Spices' ? 'selected' : ''; ?>>Herbs & Spices</option>
                                        <option value="Grains & Cereals" <?php echo $farmer['specialty'] === 'Grains & Cereals' ? 'selected' : ''; ?>>Grains & Cereals</option>
                                        <option value="Dairy Products" <?php echo $farmer['specialty'] === 'Dairy Products' ? 'selected' : ''; ?>>Dairy Products</option>
                                        <option value="Mixed Farming" <?php echo $farmer['specialty'] === 'Mixed Farming' ? 'selected' : ''; ?>>Mixed Farming</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="certification" class="form-label">Certification</label>
                                    <select class="form-select" id="certification" name="certification">
                                        <option value="">No Certification</option>
                                        <option value="Organic" <?php echo $farmer['certification'] === 'Organic' ? 'selected' : ''; ?>>Organic Certified</option>
                                        <option value="Natural" <?php echo $farmer['certification'] === 'Natural' ? 'selected' : ''; ?>>Natural Farming</option>
                                        <option value="Fair Trade" <?php echo $farmer['certification'] === 'Fair Trade' ? 'selected' : ''; ?>>Fair Trade</option>
                                        <option value="Sustainable" <?php echo $farmer['certification'] === 'Sustainable' ? 'selected' : ''; ?>>Sustainable Agriculture</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Farm Description *</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Tell customers about your farm, farming methods, and what makes your products special..."
                                      required><?php echo htmlspecialchars($farmer['description']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Update Profile Image</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*">
                            <div class="form-text">Leave empty to keep current image. Recommended size: 400x400px (max 5MB)</div>
                        </div>
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Or Provide Image URL</label>
                            <input type="url" 
                                   class="form-control" 
                                   id="image_url" 
                                   name="image_url" 
                                   placeholder="https://example.com/image.jpg">
                            <div class="form-text">Enter a direct link to an image instead of uploading. Leave empty to keep current image.</div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Profile Tips</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-camera text-primary"></i> Profile Image</h6>
                            <ul class="small text-muted">
                                <li>Use a clear, professional photo of your farm</li>
                                <li>Square images (400x400px) work best</li>
                                <li>Show your crops, farm landscape, or yourself</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-edit text-success"></i> Description</h6>
                            <ul class="small text-muted">
                                <li>Highlight your farming methods</li>
                                <li>Mention any special practices</li>
                                <li>Tell your farm's story</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-preview {
    border-radius: 12px !important;
    border: 3px solid var(--light-green) !important;
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

.alert-danger {
    border-left: 4px solid #dc3545;
}

@media (max-width: 768px) {
    .profile-preview {
        width: 150px !important;
        height: 150px !important;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<?php include '../../components/footer.php'; ?>
