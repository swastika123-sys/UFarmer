<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/functions.php';

try {
    $currentUser = getCurrentUser();
} catch (Exception $e) {
    $currentUser = null;
    echo "<!-- Error getting current user: " . $e->getMessage() . " -->";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - UFarmer' : 'UFarmer - Fresh from Local Farmers'; ?></title>
    
    <!-- Preload critical images -->
    <link rel="preload" href="https://i.pinimg.com/originals/4f/ab/20/4fab2009bfd2a3f7c820da8384acb7c1.jpg" as="image">
    <link rel="preload" href="<?php echo SITE_URL; ?>/assets/images/default-product.jpg" as="image">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/image-enhancements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Performance optimizations -->
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="//cdnjs.cloudflare.com" crossorigin>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="<?php echo SITE_URL; ?>" class="logo">
                <span class="logo-icon">🌱</span>
                UFarmer
            </a>
            
            <ul class="nav-links">
                <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>/pages/farmers.php">Farmers</a></li>
                <li><a href="<?php echo SITE_URL; ?>/pages/shop.php">Shop</a></li>
                <?php if (isLoggedIn() && $_SESSION['user_type'] === 'farmer'): ?>
                    <li><a href="<?php echo SITE_URL; ?>/pages/farmer/dashboard.php">My Farm</a></li>
                <?php endif; ?>
                <li><a href="<?php echo SITE_URL; ?>/pages/about.php">About</a></li>
                <li><a href="<?php echo SITE_URL; ?>/pages/contact.php">Contact</a></li>
            </ul>
            
            <div class="auth-section">
                <?php if (isLoggedIn()): ?>
                    <div class="user-info">
                        <span class="user-greeting">Hello, <?php echo htmlspecialchars($currentUser['name']); ?>!</span>
                        <span class="wallet-balance">
                            <i class="fas fa-wallet"></i>
                            ₹<?php 
                            try {
                                echo number_format(getUserWalletBalance($currentUser['id']) * 83, 0);
                            } catch (Exception $e) {
                                echo "0";
                            }
                            ?>
                        </span>
                    </div>
                    <div class="user-actions">
                        <?php if ($currentUser['user_type'] === 'customer'): ?>
                            <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-shopping-cart"></i>
                                Cart <span class="cart-counter" style="display:none;">0</span>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/pages/wallet.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-credit-card"></i>
                            Wallet
                        </a>
                        <a href="<?php echo SITE_URL; ?>/pages/auth/logout.php" class="btn btn-secondary btn-sm">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="<?php echo SITE_URL; ?>/pages/auth/login.php" class="btn btn-secondary">Login</a>
                        <a href="<?php echo SITE_URL; ?>/pages/auth/register.php" class="btn btn-primary">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    
    <main>
        <!-- Error Notification System -->
    <div id="notification-container" class="notification-container"></div>

    <!-- Include notification CSS and JS -->
    <style>
        .notification-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            width: 300px;
            pointer-events: none;
        }

        .notification {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
            transform: translateX(350px);
            opacity: 0;
            transition: all 0.4s ease;
            position: relative;
            word-wrap: break-word;
            pointer-events: auto;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification.hide {
            transform: translateX(350px);
            opacity: 0;
        }

        .notification .close-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
        }

        .notification-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .notification-message {
            font-size: 14px;
            line-height: 1.4;
        }
    </style>

    <script>
        class NotificationSystem {
            constructor() {
                this.container = document.getElementById('notification-container');
                this.notifications = [];
            }

            show(message, title = 'Error', duration = 10000, type = 'error') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <button class="close-btn" onclick="this.closest('.notification').remove()">&times;</button>
                    <div class="notification-title">${title}</div>
                    <div class="notification-message">${message}</div>
                `;

                this.container.appendChild(notification);
                this.notifications.push(notification);

                // Trigger animation
                setTimeout(() => {
                    notification.classList.add('show');
                }, 10);

                // Auto remove after duration
                setTimeout(() => {
                    this.hide(notification);
                }, duration);
            }

            hide(notification) {
                notification.classList.add('hide');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                    const index = this.notifications.indexOf(notification);
                    if (index > -1) {
                        this.notifications.splice(index, 1);
                    }
                }, 400);
            }

            showPHPError(error) {
                this.show(error, 'PHP Error', 15000, 'error');
            }

            showSuccess(message, title = 'Success') {
                this.show(message, title, 3000, 'success');
            }
        }

        // Initialize notification system
        const notificationSystem = new NotificationSystem();

        // Capture PHP errors and show as notifications
        window.addEventListener('DOMContentLoaded', function() {
            // Check for PHP errors in the page
            const errorElements = document.querySelectorAll('.php-error, .error-message');
            errorElements.forEach(function(element) {
                if (element.textContent.trim()) {
                    notificationSystem.showPHPError(element.textContent.trim());
                    element.style.display = 'none'; // Hide the original error
                }
            });
        });
    </script>
