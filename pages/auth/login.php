<?php
$pageTitle = 'Login';
require_once '../../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        if (login($email, $password)) {
            $redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL;
            header('Location: ' . $redirectUrl);
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include '../../components/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Welcome Back</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo getCSRFInput(); ?>
            
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
                <div class="invalid-feedback"></div>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" id="showPassword" onclick="togglePasswordVisibility()">
                    <label class="form-check-label" for="showPassword">Show Password</label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        
        <div class="auth-links text-center mt-3">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="forgot-password.php">Forgot your password?</a></p>
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
</style>

<script>
function togglePasswordVisibility() {
    var pw = document.getElementById('password');
    pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>

<?php include '../../components/footer.php'; ?>
