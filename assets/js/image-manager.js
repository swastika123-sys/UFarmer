/**
 * UFarmer Image Enhancement Module
 * Handles lazy loading, error fallbacks, and optimization
 */

class UFarmerImageManager {
    constructor() {
        this.lazyImages = [];
        this.observer = null;
        this.fallbackImages = this.createFallbackImages();
        this.init();
    }

    init() {
        this.setupLazyLoading();
        this.setupImageErrorHandling();
        this.setupImageOptimization();
        this.preloadCriticalImages();
    }

    /**
     * Create fallback image mappings
     */
    createFallbackImages() {
        return {
            categories: {
                'vegetables': { icon: '🥬', color: '#4CAF50' },
                'fruits': { icon: '🍎', color: '#FF6B6B' },
                'herbs': { icon: '🌿', color: '#66BB6A' },
                'dairy': { icon: '🥛', color: '#FFF3E0' },
                'specialty': { icon: '🍯', color: '#8D6E63' },
                'nuts': { icon: '🥜', color: '#795548' },
                'seeds': { icon: '🌱', color: '#8BC34A' }
            },
            farmer: { icon: '👨‍🌾', color: '#2E7D32' },
            product: { icon: '🛒', color: '#4CAF50' }
        };
    }

    /**
     * Setup intersection observer for lazy loading
     */
    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.observer.unobserve(entry.target);
                    }
                });
            }, {
                root: null,
                rootMargin: '50px',
                threshold: 0.1
            });

            // Observe all lazy images
            document.querySelectorAll('img[data-src]').forEach(img => {
                this.observer.observe(img);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            this.loadAllImages();
        }
    }

    /**
     * Load an individual image
     */
    loadImage(img) {
        const src = img.dataset.src;
        if (!src) return;

        // Show loading state
        img.classList.add('image-loading');

        // Create a new image to test loading
        const imageLoader = new Image();
        
        imageLoader.onload = () => {
            img.src = src;
            img.classList.remove('image-loading');
            img.classList.add('lazy-image', 'loaded');
            img.removeAttribute('data-src');
        };

        imageLoader.onerror = () => {
            this.handleImageError(img);
        };

        imageLoader.src = src;
    }

    /**
     * Handle image loading errors
     */
    handleImageError(img) {
        img.classList.remove('image-loading');
        img.classList.add('image-error');
        
        // Check if fallback should be skipped
        if (img.hasAttribute('data-skip-fallback')) {
            return; // Skip fallback overlays for images that explicitly opt out
        }
        
        return; // Skip fallback overlays entirely for now
    }

    /**
     * Get appropriate fallback for an image
     */
    getFallbackForImage(img) {
        const classes = img.className;
        const alt = img.alt.toLowerCase();
        
        // Check for category-specific fallbacks
        for (const [category, fallback] of Object.entries(this.fallbackImages.categories)) {
            if (classes.includes(category) || alt.includes(category)) {
                return fallback;
            }
        }

        // Check for farmer images
        if (classes.includes('farmer') || alt.includes('farmer')) {
            return this.fallbackImages.farmer;
        }

        // Default to product fallback
        return this.fallbackImages.product;
    }

    /**
     * Create fallback content for failed images
     */
    createFallbackContent(img, fallback) {
        const container = img.parentElement;
        
        // Create fallback div
        const fallbackDiv = document.createElement('div');
        fallbackDiv.className = 'image-fallback';
        fallbackDiv.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, ${fallback.color}, ${this.lightenColor(fallback.color, 20)});
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            text-align: center;
        `;
        
        fallbackDiv.innerHTML = `
            <div>${fallback.icon}</div>
            <div class="fallback-text">${this.getFallbackText(img)}</div>
        `;

        // Hide the broken image and show fallback
        img.style.display = 'none';
        container.style.position = 'relative';
        container.appendChild(fallbackDiv);
    }

    /**
     * Get appropriate fallback text
     */
    getFallbackText(img) {
        const alt = img.alt;
        if (alt) {
            return alt.length > 20 ? alt.substring(0, 20) + '...' : alt;
        }
        return 'Image';
    }

    /**
     * Lighten a color by a percentage
     */
    lightenColor(color, percent) {
        // Simple color lightening - could be improved with proper color parsing
        return color.replace(/[0-9A-F]{2}/gi, (match) => {
            const num = parseInt(match, 16);
            const lightened = Math.min(255, Math.floor(num + (255 - num) * percent / 100));
            return lightened.toString(16).padStart(2, '0');
        });
    }

    /**
     * Setup general image error handling
     */
    setupImageErrorHandling() {
        document.addEventListener('error', (e) => {
            if (e.target.tagName === 'IMG') {
                this.handleImageError(e.target);
            }
        }, true);
    }

    /**
     * Setup image optimization features
     */
    setupImageOptimization() {
        // Add loading attributes to all images
        document.querySelectorAll('img:not([loading])').forEach(img => {
            img.loading = 'lazy';
        });

        // Setup image compression for uploads
        this.setupImageUploadOptimization();
    }

    /**
     * Setup image upload optimization
     */
    setupImageUploadOptimization() {
        document.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    this.optimizeUploadedImage(file, input);
                }
            });
        });
    }

    /**
     * Optimize uploaded images
     */
    optimizeUploadedImage(file, input) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = () => {
            // Calculate optimal dimensions
            const maxWidth = 800;
            const maxHeight = 600;
            let { width, height } = img;

            if (width > height) {
                if (width > maxWidth) {
                    height = (height * maxWidth) / width;
                    width = maxWidth;
                }
            } else {
                if (height > maxHeight) {
                    width = (width * maxHeight) / height;
                    height = maxHeight;
                }
            }

            // Resize canvas and draw image
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);

            // Convert to blob with compression
            canvas.toBlob((blob) => {
                // Show preview
                this.showImagePreview(blob, input);
                
                // Show optimization info
                this.showOptimizationInfo(file.size, blob.size, input);
            }, 'image/jpeg', 0.85);
        };

        img.src = URL.createObjectURL(file);
    }

    /**
     * Show image preview
     */
    showImagePreview(blob, input) {
        const preview = input.parentNode.querySelector('.image-preview') || 
                       this.createImagePreview(input);
        
        preview.src = URL.createObjectURL(blob);
        preview.style.display = 'block';
    }

    /**
     * Create image preview element
     */
    createImagePreview(input) {
        const preview = document.createElement('img');
        preview.className = 'image-preview';
        preview.style.cssText = `
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: none;
        `;
        
        input.parentNode.appendChild(preview);
        return preview;
    }

    /**
     * Show optimization information
     */
    showOptimizationInfo(originalSize, optimizedSize, input) {
        const savings = ((originalSize - optimizedSize) / originalSize * 100).toFixed(1);
        
        let infoDiv = input.parentNode.querySelector('.optimization-info');
        if (!infoDiv) {
            infoDiv = document.createElement('div');
            infoDiv.className = 'optimization-info';
            infoDiv.style.cssText = `
                margin-top: 5px;
                font-size: 0.9rem;
                color: #666;
            `;
            input.parentNode.appendChild(infoDiv);
        }

        infoDiv.innerHTML = `
            <small>
                📊 Optimized: ${this.formatFileSize(originalSize)} → ${this.formatFileSize(optimizedSize)} 
                (${savings}% smaller)
            </small>
        `;
    }

    /**
     * Format file size for display
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Preload critical images
     */
    preloadCriticalImages() {
        const criticalImages = [
            'https://i.pinimg.com/originals/4f/ab/20/4fab2009bfd2a3f7c820da8384acb7c1.jpg',
            '/assets/images/default-product.jpg'
        ];

        criticalImages.forEach(src => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = src;
            document.head.appendChild(link);
        });
    }

    /**
     * Load all images (fallback for older browsers)
     */
    loadAllImages() {
        document.querySelectorAll('img[data-src]').forEach(img => {
            this.loadImage(img);
        });
    }

    /**
     * Create responsive image with multiple sources
     */
    createResponsiveImage(baseSrc, alt = '', className = '') {
        const picture = document.createElement('picture');
        
        // WebP source for modern browsers
        const webpSource = document.createElement('source');
        webpSource.srcset = baseSrc.replace(/\.(jpg|jpeg|png)$/i, '.webp');
        webpSource.type = 'image/webp';
        
        // Fallback image
        const img = document.createElement('img');
        img.src = baseSrc;
        img.alt = alt;
        img.className = className;
        img.loading = 'lazy';
        
        picture.appendChild(webpSource);
        picture.appendChild(img);
        
        return picture;
    }
}

// Initialize the image manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.ufarmerImages = new UFarmerImageManager();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UFarmerImageManager;
}
