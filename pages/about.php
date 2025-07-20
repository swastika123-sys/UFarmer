<?php
$pageTitle = 'About UFarmer';
include '../components/header.php';
?>

<section class="about-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="hero-content">
                    <h1>About UFarmer</h1>
                    <p class="lead">Connecting local farmers with conscious consumers for a more sustainable food system.</p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Local Farmers</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">10k+</span>
                            <span class="stat-label">Happy Customers</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">50+</span>
                            <span class="stat-label">Cities Served</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="hero-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/about-hero.jpg" alt="Farmer in field" class="img-fluid">
                    <div class="image-overlay">
                        <div class="play-button">
                            <i class="fas fa-play"></i>
                        </div>
                        <p>Watch Our Story</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mission py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <h2 class="mb-4">Our Mission</h2>
                <p class="lead">UFarmer was born from a simple belief: everyone deserves access to fresh, locally-grown food, and farmers deserve fair compensation for their hard work.</p>
                <p>We eliminate the middleman, creating direct relationships between farmers and consumers. This means fresher food on your table, better prices for farmers, and a stronger local economy.</p>
            </div>
        </div>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>How UFarmer Works</h2>
            <p class="section-subtitle">Simple steps to connect farmers and customers</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <h4>Farmers Join</h4>
                    <p>Local farmers create profiles and list their fresh produce, setting their own prices and availability.</p>
                    <div class="step-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4>Customers Shop</h4>
                    <p>Customers browse farmer profiles and products, ordering directly from local growers in their area.</p>
                    <div class="step-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h4>Fresh Delivery</h4>
                    <p>Farmers coordinate delivery or pickup, ensuring the freshest possible produce reaches customers quickly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="values">
    <div class="container">
        <div class="section-header">
            <h2>Our Values</h2>
            <p class="section-subtitle">The principles that guide everything we do</p>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="value-content">
                        <h4>Sustainability</h4>
                        <p>We promote environmentally friendly farming practices and reduce food miles by connecting consumers with local producers.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="value-content">
                        <h4>Fair Trade</h4>
                        <p>Farmers receive fair compensation for their work, while customers get competitive prices on high-quality produce.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="value-content">
                        <h4>Community</h4>
                        <p>We strengthen local food systems and build connections between farmers and the communities they serve.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="value-content">
                        <h4>Quality</h4>
                        <p>All farmers on our platform are committed to producing high-quality, fresh produce using responsible methods.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats">
    <div class="container">
        <div class="section-header white-text">
            <h2>UFarmer by the Numbers</h2>
            <p class="section-subtitle">Making a real impact in our community</p>
        </div>
        
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Local Farmers</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-number">10k+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="stat-number">2k+</div>
                    <div class="stat-label">Fresh Products</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-number">₹50L+</div>
                    <div class="stat-label">Paid to Farmers</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2>What People Say</h2>
            <p class="section-subtitle">Stories from our farming community</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"UFarmer has completely transformed how I sell my produce. I now have direct contact with customers who truly appreciate fresh, local vegetables."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h5>Ramesh Kumar</h5>
                            <span>Organic Farmer, West Bengal</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"The freshness and quality of produce from UFarmer is unmatched. My family loves knowing exactly where our food comes from."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h5>Priya Sharma</h5>
                            <span>Customer, Kolkata</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Supporting local farmers through UFarmer makes me feel good about my purchasing decisions. It's sustainable and beneficial for everyone."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h5>Arjun Mehta</h5>
                            <span>Customer, Mumbai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="story-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="story-content">
                    <h2>Our Story</h2>
                    <div class="story-timeline">
                        <div class="timeline-item">
                            <div class="timeline-year">2023</div>
                            <div class="timeline-content">
                                <h4>The Beginning</h4>
                                <p>UFarmer started when a group of food enthusiasts and tech professionals noticed a gap in the local food system.</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-year">2024</div>
                            <div class="timeline-content">
                                <h4>Platform Launch</h4>
                                <p>We launched our platform connecting farmers directly with customers, eliminating the middleman.</p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-year">2025</div>
                            <div class="timeline-content">
                                <h4>Growing Impact</h4>
                                <p>Today, we proudly support 500+ local farmers and 10,000+ families who believe in fresh, local food.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="story-visual">
                    <div class="impact-cards">
                        <div class="impact-card">
                            <i class="fas fa-seedling"></i>
                            <h4>Farm to Table</h4>
                            <p>Direct connection between farmers and consumers</p>
                        </div>
                        <div class="impact-card">
                            <i class="fas fa-globe-asia"></i>
                            <h4>Local Impact</h4>
                            <p>Supporting sustainable agriculture in India</p>
                        </div>
                        <div class="impact-card">
                            <i class="fas fa-mobile-alt"></i>
                            <h4>Technology</h4>
                            <p>Modern platform for traditional farming</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cta-section">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h3>Ready to Join Our Community?</h3>
                    <p class="lead">Whether you're a farmer or a customer, UFarmer has something for you.</p>
                    <div class="cta-buttons">
                        <a href="<?php echo SITE_URL; ?>/pages/farmers.php" class="btn-modern btn-primary">
                            <i class="fas fa-users"></i>
                            <span>Meet Our Farmers</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn-modern btn-secondary">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Start Shopping</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ===============================
   ABOUT PAGE MODERN STYLES
   =============================== */

/* Section Headers */
.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 1rem;
    position: relative;
}

.section-header h2::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    border-radius: 2px;
}

.section-header.white-text h2,
.section-header.white-text .section-subtitle {
    color: white;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #666;
    margin: 0;
}

/* Hero Section */
.hero-content h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 2;
}

.hero-stats {
    display: flex;
    gap: 2rem;
    margin-top: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #4CAF50;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
}

.hero-image {
    position: relative;
}

.hero-image img {
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.image-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
}

.play-button {
    width: 80px;
    height: 80px;
    background: rgba(76, 175, 80, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.play-button:hover {
    background: rgba(76, 175, 80, 1);
    transform: scale(1.1);
}

.play-button i {
    font-size: 2rem;
    margin-left: 5px;
}

/* Step Cards */
.step-card {
    background: white;
    padding: 2.5rem 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    text-align: center;
    position: relative;
    transition: transform 0.3s ease;
    height: 100%;
}

.step-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.step-number {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.step-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 1.5rem auto;
    color: #4CAF50;
    font-size: 2rem;
}

.step-card h4 {
    color: #333;
    font-weight: 600;
    margin-bottom: 1rem;
}

.step-card p {
    color: #666;
    line-height: 1.6;
}

.step-arrow {
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    color: #4CAF50;
    font-size: 1.5rem;
}

/* Value Cards */
.value-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    height: 100%;
    transition: transform 0.3s ease;
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.value-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.value-content h4 {
    color: #333;
    font-weight: 600;
    margin-bottom: 1rem;
}

.value-content p {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

/* Stats Section */
.stats {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    padding: 4rem 0;
    position: relative;
    overflow: hidden;
}

.stats::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="30" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="70" r="2.5" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
    animation: float 20s infinite linear;
}

.stat-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 2.5rem 1.5rem;
    text-align: center;
    transition: transform 0.3s ease;
    margin: 0 10px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.stat-card:hover {
    transform: translateY(-8px);
    background: rgba(255, 255, 255, 0.2);
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
}

.stat-icon {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 1.8rem;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.4);
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    display: block;
    margin-bottom: 0.8rem;
    color: white;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.95;
    color: white;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* Testimonials */
.testimonials {
    padding: 4rem 0;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.testimonial-card {
    background: white;
    padding: 2rem;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    height: 100%;
    transition: transform 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.testimonial-content {
    margin-bottom: 2rem;
}

.testimonial-content p {
    font-style: italic;
    color: #555;
    line-height: 1.8;
    font-size: 1.1rem;
    margin: 0;
    position: relative;
}

.testimonial-content p::before {
    content: '"';
    font-size: 4rem;
    color: #4CAF50;
    position: absolute;
    top: -20px;
    left: -20px;
    opacity: 0.3;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.author-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.author-info h5 {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.author-info span {
    color: #666;
    font-size: 0.9rem;
}

/* Story Section */
.story-section {
    padding: 4rem 0;
    background: white;
}

.story-timeline {
    margin-top: 2rem;
}

.timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 2rem;
    margin-bottom: 2rem;
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 50px;
    top: 60px;
    bottom: -20px;
    width: 2px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-year {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.timeline-content h4 {
    color: #333;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.timeline-content p {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.impact-cards {
    display: grid;
    gap: 1.5rem;
}

.impact-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.3s ease;
}

.impact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.impact-card i {
    font-size: 2.5rem;
    color: #4CAF50;
    margin-bottom: 1rem;
}

.impact-card h4 {
    color: #333;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.impact-card p {
    color: #666;
    margin: 0;
}

/* CTA Section */
.cta-section {
    margin-top: 4rem;
    padding: 3rem 0;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 20px;
    text-align: center;
}

.cta-section h3 {
    color: #333;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cta-section .lead {
    color: #666;
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    border: none;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 180px;
    justify-content: center;
}

.btn-modern.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.btn-modern.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
    text-decoration: none;
    color: white;
}

.btn-modern.btn-secondary {
    background: white;
    color: #333;
    border: 2px solid #4CAF50;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.btn-modern.btn-secondary:hover {
    background: #4CAF50;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(76, 175, 80, 0.3);
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2.5rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .step-arrow {
        display: none;
    }
    
    .value-card {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .timeline-item {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .timeline-item::before {
        display: none;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .section-header h2 {
        font-size: 2rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .impact-cards {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .step-card,
    .value-card,
    .testimonial-card {
        padding: 1.5rem;
    }
    
    .story-section,
    .testimonials,
    .stats {
        padding: 2rem 0;
    }
}
</style>

<?php include '../components/footer.php'; ?>
