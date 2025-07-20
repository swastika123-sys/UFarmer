<?php
$pageTitle = 'Register';
require_once '../../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit();
}

$error = '';
$success = '';
$userType = isset($_GET['type']) && $_GET['type'] === 'farmer' ? 'farmer' : 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $userType = $_POST['user_type'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Try to register
        if (register($name, $email, $password, $userType)) {
            // Auto-login after registration
            login($email, $password);
            
            if ($userType === 'farmer') {
                header('Location: ../farmer/setup.php');
            } else {
                header('Location: ' . SITE_URL);
            }
            exit();
        } else {
            $error = 'Email already exists. Please choose a different email.';
        }
    }
}

include '../../components/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Join UFarmer</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo getCSRFInput(); ?>
            
            <div class="form-group">
                <label for="user_type" class="form-label">I want to:</label>
                <select id="user_type" name="user_type" class="form-control" required>
                    <option value="customer" <?php echo $userType === 'customer' ? 'selected' : ''; ?>>Buy fresh produce</option>
                    <option value="farmer" <?php echo $userType === 'farmer' ? 'selected' : ''; ?>>Sell my farm products</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                       required>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       required>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control" 
                       required>
                <small class="form-text text-muted">Must be at least 6 characters long</small>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       class="form-control" 
                       required>
                <div class="invalid-feedback"></div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>
        
        <div class="auth-links text-center mt-3">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    background: linear-gradient(135deg, var(--light-green), var(--accent-green));
    min-height: calc(100vh - 160px);
}

.auth-links a {
    color: var(--primary-green);
    text-decoration: none;
    font-weight: 500;
}

.auth-links a:hover {
    text-decoration: underline;
}

.w-100 {
    width: 100%;
}

.form-text {
    font-size: 0.875rem;
    color: var(--gray-medium);
}
</style>

<script>
// Show different instructions based on user type selection
document.getElementById('user_type').addEventListener('change', function() {
    const userType = this.value;
    const title = document.querySelector('.auth-title');
    
    if (userType === 'farmer') {
        title.textContent = 'Join as a Farmer';
    } else {
        title.textContent = 'Join UFarmer';
    }
});
</script>

<?php include '../../components/footer.php'; ?>
