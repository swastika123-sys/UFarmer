</main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>UFarmer</h3>
                    <p>Connecting local farmers with conscious consumers. Fresh, sustainable, and direct from the source.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/farmers.php">Our Farmers</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/shop.php">Shop</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/about.php">About</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>For Farmers</h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/pages/auth/register.php?type=farmer">Join as Farmer</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/farmer/dashboard.php">Farmer Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/support.php">Support</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-envelope"></i> info@ufarmer.com</p>
                    <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> UFarmer. All rights reserved. | Made with 💚 for sustainable farming</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/image-manager.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Image optimization initialization -->
    <script>
    // Initialize image management when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Convert existing images to use lazy loading
        document.querySelectorAll('img').forEach(function(img) {
            if (!img.dataset.src && img.src) {
                // Add loading attribute for performance
                img.loading = 'lazy';
                
                // Add error handling
                img.onerror = function() {
                    if (window.ufarmerImages) {
                        window.ufarmerImages.handleImageError(this);
                    }
                };
            }
        });
        
        // Initialize image containers
        document.querySelectorAll('.card-img, .farmer-avatar, .product-image').forEach(function(img) {
            const container = img.parentElement;
            if (container && !container.classList.contains('image-container')) {
                container.classList.add('image-container');
            }
        });
    });
    </script>
</body>
</html>
