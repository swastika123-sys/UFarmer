<?php
$pageTitle = 'Complete Your Farmer Profile';
require_once '../../includes/functions.php';

// Ensure user is logged in and is a farmer
if (!isLoggedIn() || $_SESSION['user_type'] !== 'farmer') {
    header('Location: ' . SITE_URL);
    exit();
}

// Check if farmer profile already exists
$existingFarmer = getFarmerByUserId($_SESSION['user_id']);
if ($existingFarmer) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmName = sanitizeInput($_POST['farm_name']);
    $description = sanitizeInput($_POST['description']);
    $location = sanitizeInput($_POST['location']);
    $phone = sanitizeInput($_POST['phone']);
    
    if (empty($farmName) || empty($description) || empty($location) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle profile image upload
        $profileImage = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $profileImage = uploadFile($_FILES['profile_image'], 'farmers');
            if (!$profileImage) {
                $error = 'Failed to upload profile image. Please ensure the file is less than 5MB and is a valid image format (JPG, PNG, GIF, WebP).';
            }
        } elseif (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle other upload errors
            switch ($_FILES['profile_image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error = 'Image file is too large. Please choose a file smaller than 5MB.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error = 'Image upload was interrupted. Please try again.';
                    break;
                default:
                    $error = 'An error occurred during image upload. Please try again.';
            }
        }
        
        if (!$error) {
            if (createFarmerProfile($_SESSION['user_id'], $farmName, $description, $location, $phone)) {
                // Update profile image if uploaded
                if ($profileImage) {
                    global $pdo;
                    $stmt = $pdo->prepare("UPDATE farmers SET profile_image = ? WHERE user_id = ?");
                    $stmt->execute([$profileImage, $_SESSION['user_id']]);
                }
                
                header('Location: dashboard.php?welcome=1');
                exit();
            } else {
                $error = 'Failed to create farmer profile. Please try again.';
            }
        }
    }
}

include '../../components/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body p-4">
                    <h1 class="text-center mb-4">Complete Your Farmer Profile</h1>
                    <p class="text-center text-muted mb-4">Tell us about your farm so customers can discover your amazing produce!</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <?php echo getCSRFInput(); ?>
                        
                        <div class="form-group">
                            <label for="farm_name" class="form-label">Farm Name *</label>
                            <input type="text" 
                                   id="farm_name" 
                                   name="farm_name" 
                                   class="form-control" 
                                   value="<?php echo isset($_POST['farm_name']) ? htmlspecialchars($_POST['farm_name']) : ''; ?>"
                                   placeholder="e.g., Green Valley Organic Farm"
                                   required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Farm Description *</label>
                            <textarea id="description" 
                                      name="description" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Tell customers about your farming practices, what makes your produce special, your story..."
                                      required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" 
                                   id="location" 
                                   name="location" 
                                   class="form-control" 
                                   value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                                   placeholder="e.g., Springfield County, Oregon"
                                   required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control" 
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                   placeholder="(555) 123-4567"
                                   required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="profile_image" class="form-label">Profile Picture</label>
                            <input type="file" 
                                   id="profile_image" 
                                   name="profile_image" 
                                   class="form-control" 
                                   accept="image/*">
                            <small class="form-text text-muted">Upload a clear photo of yourself. This helps build trust with customers.</small>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> What happens next?</h6>
                            <ul class="mb-0">
                                <li>Your profile will be created and visible to customers</li>
                                <li>You can start adding products to sell</li>
                                <li>New farmers are prioritized in our listings</li>
                                <li>You can manage orders and communicate with customers</li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-check"></i> Complete Profile Setup
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.row {
    display: flex;
    justify-content: center;
    margin: 0 -15px;
}

.col-md-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
    padding: 0 15px;
}

@media (max-width: 768px) {
    .col-md-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

.justify-content-center {
    justify-content: center;
}

.py-5 {
    padding-top: 3rem;
    padding-bottom: 3rem;
}

.p-4 {
    padding: 1.5rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.btn-lg {
    padding: 0.875rem 1.25rem;
    font-size: 1.125rem;
}

.w-100 {
    width: 100%;
}
</style>

<?php include '../../components/footer.php'; ?>
