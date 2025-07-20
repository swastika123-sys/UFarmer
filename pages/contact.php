<?php
$pageTitle = 'Contact Us';
include '../components/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real application, you would send an email here
        // For now, we'll just show a success message
        $success = 'Thank you for your message! We\'ll get back to you within 24 hours.';
    }
}
?>

<section class="contact-hero">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4">Get in Touch</h1>
            <p class="lead">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </div>
</section>

<!-- Contact Information Cards Section -->
<section class="contact-info-cards py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h5>Email Us</h5>
                        <p>hello@ufarmer.in<br>support@ufarmer.in</p>
                        <small class="text-muted">24hr response time</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h5>Call Us</h5>
                        <p>+91 98765 43210</p>
                        <small class="text-muted">Mon-Sat 9AM-6PM</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h5>Visit Us</h5>
                        <p>Newtown, Kolkata<br>West Bengal 700156</p>
                        <small class="text-muted">Office hours apply</small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-details">
                        <h5>Support Hours</h5>
                        <p>Mon-Sat: 9AM-6PM<br>24/7 Emergency</p>
                        <small class="text-muted">Sunday closed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Contact Section -->
<section class="contact-main py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-8 col-md-12">
                <div class="contact-form-wrapper">
                    <h2 class="mb-4">Send us a Message</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="contact-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       class="form-control" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select id="subject" name="subject" class="form-control" required>
                                <option value="">Choose a subject...</option>
                                <option value="general" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'general') ? 'selected' : ''; ?>>General Question</option>
                                <option value="farmer" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'farmer') ? 'selected' : ''; ?>>Farmer Support</option>
                                <option value="customer" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'customer') ? 'selected' : ''; ?>>Customer Support</option>
                                <option value="technical" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'technical') ? 'selected' : ''; ?>>Technical Issue</option>
                                <option value="partnership" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'partnership') ? 'selected' : ''; ?>>Partnership Inquiry</option>
                                <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Message *</label>
                            <textarea id="message" 
                                      name="message" 
                                      class="form-control" 
                                      rows="6"
                                      placeholder="Tell us how we can help you..."
                                      required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Map and FAQ -->
            <div class="col-lg-4 col-md-12">
                <!-- Google Maps -->
                <div class="map-section mb-4">
                    <h3 class="mb-3"><i class="fas fa-map-marked-alt text-success"></i> Find Us</h3>
                    <div class="map-embed">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3684.0933150684436!2d88.47450817536877!3d22.58243807948!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a0275b020b5ea35%3A0x3d94e9c32b5e5e35!2sNewtown%2C%20Kolkata%2C%20West%20Bengal!5e0!3m2!1sen!2sin!4v1704721200000!5m2!1sen!2sin"
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            title="UFarmer Location - Newtown, Kolkata">
                        </iframe>
                    </div>
                    <div class="map-info mt-3">
                        <p class="text-muted small text-center">
                            <i class="fas fa-info-circle"></i>
                            Located in Newtown's tech hub, easily accessible by metro and public transport.
                        </p>
                    </div>
                </div>
                
                <!-- FAQ Section -->
                <div class="faq-section">
                    <h3 class="mb-3"><i class="fas fa-question-circle text-success"></i> Quick Answers</h3>
                    
                    <div class="faq-item">
                        <h6 class="fw-bold">How do I become a farmer on UFarmer?</h6>
                        <p class="text-muted small">Register with a farmer account and complete your farm profile. We'll review and verify your information.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h6 class="fw-bold">What are your delivery options?</h6>
                        <p class="text-muted small">Delivery options vary by farmer. Some offer home delivery, others have pickup locations.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h6 class="fw-bold">How do I track my order?</h6>
                        <p class="text-muted small">Check your order status in your account dashboard and receive updates from farmers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Response Time Guarantee Section -->
<section class="response-guarantee py-5" style="background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%);">
    <div class="container">
        <div class="row align-items-center text-white">
            <div class="col-lg-8">
                <div class="guarantee-content">
                    <h2 class="mb-3"><i class="fas fa-clock"></i> Our Response Guarantee</h2>
                    <p class="lead mb-4">We value your time and are committed to providing exceptional customer service.</p>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="guarantee-item">
                                <div class="guarantee-time">15 mins</div>
                                <div class="guarantee-desc">Live Chat Response</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="guarantee-item">
                                <div class="guarantee-time">2 hours</div>
                                <div class="guarantee-desc">Email Support</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="guarantee-item">
                                <div class="guarantee-time">24/7</div>
                                <div class="guarantee-desc">Emergency Line</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="guarantee-badge">
                    <i class="fas fa-award fa-4x mb-3"></i>
                    <h4>100% Satisfaction</h4>
                    <p>We stand behind our service</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Chat Widget -->
<div class="live-chat-widget" id="liveChatWidget">
    <div class="chat-header">
        <i class="fas fa-comments"></i>
        <span>Live Support</span>
        <button class="chat-close" onclick="toggleChat()">×</button>
    </div>
    <div class="chat-body">
        <div class="chat-message bot">
            <div class="message-avatar">🌱</div>
            <div class="message-content">
                <p>Hi! I'm here to help. What can I assist you with today?</p>
                <div class="quick-actions">
                    <button class="quick-btn" onclick="selectQuickAction('farmer')">Farmer Support</button>
                    <button class="quick-btn" onclick="selectQuickAction('order')">Order Help</button>
                    <button class="quick-btn" onclick="selectQuickAction('technical')">Technical Issue</button>
                </div>
            </div>
        </div>
    </div>
    <div class="chat-input">
        <input type="text" placeholder="Type your message..." id="chatInput">
        <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<button class="chat-toggle" onclick="toggleChat()">
    <i class="fas fa-comment-dots"></i>
    <span class="chat-notification">1</span>
</button>

<!-- Team Section -->
<section class="team-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5">Meet Our Support Team</h2>
            <p class="lead text-muted">Dedicated professionals ready to help you succeed</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-card">
                    <div class="team-avatar">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face" alt="Rajesh Kumar">
                        <div class="status-indicator online"></div>
                    </div>
                    <h5>Rajesh Kumar</h5>
                    <p class="team-role">Customer Success Manager</p>
                    <p class="team-specialty">Farmer Onboarding & Support</p>
                    <div class="team-contact">
                        <a href="mailto:rajesh@ufarmer.in"><i class="fas fa-envelope"></i></a>
                        <a href="tel:+919876543210"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-card">
                    <div class="team-avatar">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face" alt="Priya Sharma">
                        <div class="status-indicator online"></div>
                    </div>
                    <h5>Priya Sharma</h5>
                    <p class="team-role">Technical Support Lead</p>
                    <p class="team-specialty">Platform & Technical Issues</p>
                    <div class="team-contact">
                        <a href="mailto:priya@ufarmer.in"><i class="fas fa-envelope"></i></a>
                        <a href="tel:+919876543211"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-card">
                    <div class="team-avatar">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face" alt="Amit Patel">
                        <div class="status-indicator away"></div>
                    </div>
                    <h5>Amit Patel</h5>
                    <p class="team-role">Business Development</p>
                    <p class="team-specialty">Partnerships & Growth</p>
                    <div class="team-contact">
                        <a href="mailto:amit@ufarmer.in"><i class="fas fa-envelope"></i></a>
                        <a href="tel:+919876543212"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-card">
                    <div class="team-avatar">
                        <img src="https://images.unsplash.com/photo-1559899122-89f5f0b095fe?w=150&h=150&fit=crop&crop=face" alt="Sneha Reddy">
                        <div class="status-indicator online"></div>
                    </div>
                    <h5>Sneha Reddy</h5>
                    <p class="team-role">Customer Experience</p>
                    <p class="team-specialty">Order Support & Returns</p>
                    <div class="team-contact">
                        <a href="mailto:sneha@ufarmer.in"><i class="fas fa-envelope"></i></a>
                        <a href="tel:+919876543213"><i class="fas fa-phone"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interactive Support Categories -->
<section class="support-categories py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5">How Can We Help You?</h2>
            <p class="lead text-muted">Choose your category for faster assistance</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="support-category-card" onclick="expandCategory('farmer')">
                    <div class="category-icon">
                        <i class="fas fa-tractor"></i>
                    </div>
                    <h4>Farmer Support</h4>
                    <p>Get help with farm setup, product listings, and sales optimization</p>
                    <div class="category-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="category-details" id="farmer-details">
                        <ul>
                            <li><i class="fas fa-check"></i> Farm Profile Setup</li>
                            <li><i class="fas fa-check"></i> Product Photography Tips</li>
                            <li><i class="fas fa-check"></i> Pricing Strategies</li>
                            <li><i class="fas fa-check"></i> Order Management</li>
                        </ul>
                        <a href="#contact-form" class="btn btn-success btn-sm">Get Farmer Support</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="support-category-card" onclick="expandCategory('customer')">
                    <div class="category-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h4>Customer Support</h4>
                    <p>Assistance with orders, deliveries, payments, and returns</p>
                    <div class="category-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="category-details" id="customer-details">
                        <ul>
                            <li><i class="fas fa-check"></i> Order Tracking</li>
                            <li><i class="fas fa-check"></i> Payment Issues</li>
                            <li><i class="fas fa-check"></i> Delivery Problems</li>
                            <li><i class="fas fa-check"></i> Returns & Refunds</li>
                        </ul>
                        <a href="#contact-form" class="btn btn-success btn-sm">Get Customer Support</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="support-category-card" onclick="expandCategory('technical')">
                    <div class="category-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h4>Technical Support</h4>
                    <p>Help with platform issues, bugs, and technical difficulties</p>
                    <div class="category-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <div class="category-details" id="technical-details">
                        <ul>
                            <li><i class="fas fa-check"></i> Login Issues</li>
                            <li><i class="fas fa-check"></i> App Problems</li>
                            <li><i class="fas fa-check"></i> Website Bugs</li>
                            <li><i class="fas fa-check"></i> Account Recovery</li>
                        </ul>
                        <a href="#contact-form" class="btn btn-success btn-sm">Get Technical Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office Hours & Availability -->
<section class="office-hours py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="hours-card">
                    <h3><i class="fas fa-clock text-success"></i> Office Hours</h3>
                    <div class="hours-schedule">
                        <div class="schedule-item">
                            <span class="day">Monday - Friday</span>
                            <span class="time">9:00 AM - 6:00 PM IST</span>
                            <span class="status open">Open</span>
                        </div>
                        <div class="schedule-item">
                            <span class="day">Saturday</span>
                            <span class="time">10:00 AM - 4:00 PM IST</span>
                            <span class="status open">Open</span>
                        </div>
                        <div class="schedule-item">
                            <span class="day">Sunday</span>
                            <span class="time">Closed</span>
                            <span class="status closed">Closed</span>
                        </div>
                        <div class="schedule-item emergency">
                            <span class="day">Emergency Support</span>
                            <span class="time">24/7 Available</span>
                            <span class="status emergency">Emergency</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="availability-card">
                    <h3><i class="fas fa-users text-success"></i> Current Availability</h3>
                    <div class="availability-status">
                        <div class="status-item">
                            <div class="status-indicator online"></div>
                            <span>3 Customer Support agents online</span>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator online"></div>
                            <span>2 Technical Support specialists available</span>
                        </div>
                        <div class="status-item">
                            <div class="status-indicator away"></div>
                            <span>1 Business Development manager in meeting</span>
                        </div>
                    </div>
                    <div class="response-time">
                        <h5>Current Response Time</h5>
                        <div class="response-metric">
                            <span class="metric-value">< 5 minutes</span>
                            <span class="metric-label">Average response time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media & Community -->
<section class="social-contact py-5 bg-light">
    <div class="container text-center">
        <h3 class="mb-4">Connect With Our Community</h3>
        <p class="lead mb-4">Join thousands of farmers and customers in our growing community</p>
        
        <div class="social-stats mb-4">
            <div class="row g-3">
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="social-stat">
                        <i class="fab fa-facebook-f"></i>
                        <div class="stat-number">12.5K</div>
                        <div class="stat-label">Followers</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="social-stat">
                        <i class="fab fa-instagram"></i>
                        <div class="stat-number">8.2K</div>
                        <div class="stat-label">Followers</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="social-stat">
                        <i class="fab fa-youtube"></i>
                        <div class="stat-number">15.7K</div>
                        <div class="stat-label">Subscribers</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <div class="social-stat">
                        <i class="fab fa-whatsapp"></i>
                        <div class="stat-number">5.1K</div>
                        <div class="stat-label">Community Members</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="social-links-large">
            <a href="https://facebook.com/ufarmer" class="social-link" target="_blank">
                <i class="fab fa-facebook-f"></i>
                <span>Facebook</span>
                <small>Daily farm updates</small>
            </a>
            <a href="https://instagram.com/ufarmer" class="social-link" target="_blank">
                <i class="fab fa-instagram"></i>
                <span>Instagram</span>
                <small>Fresh produce photos</small>
            </a>
            <a href="https://youtube.com/ufarmer" class="social-link" target="_blank">
                <i class="fab fa-youtube"></i>
                <span>YouTube</span>
                <small>Farming tutorials</small>
            </a>
            <a href="https://wa.me/919876543210" class="social-link" target="_blank">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp</span>
                <small>Quick support</small>
            </a>
            <a href="https://twitter.com/ufarmer" class="social-link" target="_blank">
                <i class="fab fa-twitter"></i>
                <span>Twitter</span>
                <small>News & updates</small>
            </a>
            <a href="https://linkedin.com/company/ufarmer" class="social-link" target="_blank">
                <i class="fab fa-linkedin-in"></i>
                <span>LinkedIn</span>
                <small>Professional network</small>
            </a>
        </div>
    </div>
</section>

<!-- Newsletter Subscription -->
<section class="newsletter-section py-5" style="background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 100%);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-white">
                <h3 class="mb-3">Stay Updated with UFarmer</h3>
                <p class="mb-4">Get the latest updates on seasonal produce, farmer spotlights, cooking tips, and exclusive offers delivered to your inbox.</p>
                <div class="newsletter-benefits">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Weekly seasonal produce guide</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Exclusive farmer stories and tips</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Early access to special offers</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="newsletter-form">
                    <h4 class="text-center mb-4 text-dark">Join 25,000+ Subscribers</h4>
                    <form class="subscription-form">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Enter your email address" required>
                            <button class="btn btn-warning" type="submit">
                                <i class="fas fa-paper-plane"></i> Subscribe
                            </button>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="newsletter-consent" required>
                            <label class="form-check-label small text-dark" for="newsletter-consent">
                                I agree to receive marketing emails from UFarmer. You can unsubscribe at any time.
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Contact Information Cards */
.contact-info-cards {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.contact-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    height: 100%;
    transition: all 0.3s ease;
    border: 1px solid rgba(76, 175, 80, 0.1);
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.contact-card .contact-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
}

.contact-card h5 {
    color: var(--dark-green);
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.contact-card p {
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

/* Main Contact Section */
.contact-main {
    background: white;
}

.contact-form-wrapper {
    background: #f8f9fa;
    padding: 2.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    width: 100%;
    margin-bottom: 2rem;
}

.contact-form-wrapper h2 {
    color: var(--dark-green);
    font-weight: 600;
    position: relative;
    padding-bottom: 1rem;
}

.contact-form-wrapper h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    border-radius: 2px;
}

.form-label {
    font-weight: 500;
    color: var(--dark-green);
    margin-bottom: 0.5rem;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

/* Map Section */
.map-section {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    width: 100%;
    margin-bottom: 2rem;
}

.map-section h3 {
    color: var(--dark-green);
    font-weight: 600;
    font-size: 1.25rem;
}

.map-embed {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.map-embed iframe {
    width: 100%;
    height: 250px;
    border: none;
}

.map-info {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid var(--primary-green);
}

/* FAQ Section */
.faq-section {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    width: 100%;
}

.faq-section h3 {
    color: var(--dark-green);
    font-weight: 600;
    font-size: 1.25rem;
}

.faq-item {
    padding: 1rem 0;
    border-bottom: 1px solid #e9ecef;
}

.faq-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.faq-item h6 {
    color: var(--dark-green);
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.faq-item p {
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Response Guarantee Section */
.response-guarantee {
    position: relative;
    overflow: hidden;
}

.response-guarantee::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    opacity: 0.3;
}

.guarantee-item {
    text-align: center;
    padding: 1rem;
}

.guarantee-time {
    font-size: 2rem;
    font-weight: bold;
    color: #FFD700;
}

.guarantee-desc {
    font-size: 0.9rem;
    opacity: 0.9;
}

.guarantee-badge {
    background: rgba(255,255,255,0.1);
    padding: 2rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

/* Live Chat Widget */
.live-chat-widget {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 350px;
    height: 400px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 1000;
    display: none;
    flex-direction: column;
    animation: slideUp 0.3s ease;
}

.chat-header {
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    color: white;
    padding: 1rem;
    border-radius: 15px 15px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
}

.chat-body {
    flex: 1;
    padding: 1rem;
    overflow-y: auto;
}

.chat-message {
    display: flex;
    margin-bottom: 1rem;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: var(--primary-green);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    font-size: 1.2rem;
}

.message-content {
    background: #f1f3f4;
    padding: 0.75rem;
    border-radius: 10px;
    max-width: 70%;
}

.quick-actions {
    margin-top: 0.5rem;
}

.quick-btn {
    background: var(--primary-green);
    color: white;
    border: none;
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    font-size: 0.8rem;
    margin: 0.2rem;
    cursor: pointer;
}

.chat-input {
    display: flex;
    padding: 1rem;
    border-top: 1px solid #eee;
}

.chat-input input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 0.5rem 1rem;
    margin-right: 0.5rem;
}

.chat-input button {
    background: var(--primary-green);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
}

.chat-toggle {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.5rem;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    z-index: 999;
    animation: pulse 2s infinite;
}

.chat-notification {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Team Section */
.team-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
    width: 100%;
    margin-bottom: 2rem;
}

.team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.team-avatar {
    position: relative;
    display: inline-block;
    margin-bottom: 1rem;
}

.team-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-green);
    display: block;
}

.fallback-text {
    display: none !important;
}

.status-indicator {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-indicator.online {
    background: #4CAF50;
}

.status-indicator.away {
    background: #FF9800;
}

.status-indicator.offline {
    background: #757575;
}

.team-role {
    color: var(--primary-green);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.team-specialty {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.team-contact a {
    display: inline-block;
    width: 35px;
    height: 35px;
    background: var(--primary-green);
    color: white;
    border-radius: 50%;
    text-decoration: none;
    margin: 0 0.25rem;
    line-height: 35px;
    transition: all 0.3s ease;
}

.team-contact a:hover {
    background: var(--dark-green);
    transform: scale(1.1);
}

/* Support Categories */
.support-category-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.support-category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.category-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin: 0 auto 1.5rem;
}

.category-arrow {
    position: absolute;
    top: 1rem;
    right: 1rem;
    color: var(--primary-green);
    transition: transform 0.3s ease;
}

.support-category-card.expanded .category-arrow {
    transform: rotate(90deg);
}

.category-details {
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
    margin-top: 1rem;
}

.category-details.expanded {
    max-height: 200px;
}

.category-details ul {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.category-details li {
    padding: 0.25rem 0;
    color: #666;
}

.category-details li i {
    color: var(--primary-green);
    margin-right: 0.5rem;
}

/* Office Hours */
.office-hours {
    background: #f8f9fa;
}

.hours-card, .availability-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    height: 100%;
    width: 100%;
}

.schedule-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.schedule-item:last-child {
    border-bottom: none;
}

.schedule-item.emergency {
    background: #fff3cd;
    padding: 0.75rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.status {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status.open {
    background: #d4edda;
    color: #155724;
}

.status.closed {
    background: #f8d7da;
    color: #721c24;
}

.status.emergency {
    background: #fff3cd;
    color: #856404;
}

.availability-status {
    margin-bottom: 2rem;
}

.status-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}

.status-item .status-indicator {
    margin-right: 0.75rem;
    position: static;
    border: none;
}

.response-metric {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.metric-value {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary-green);
}

.metric-label {
    font-size: 0.9rem;
    color: #666;
}

/* Social Stats */
.social-stats {
    margin-bottom: 2rem;
}

.social-stats .row {
    margin: 0;
}

.social-stats .row > div {
    padding: 0.5rem;
}

.social-stat {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
    width: 100%;
}

.social-stat:hover {
    transform: translateY(-5px);
}

.social-stat i {
    font-size: 2rem;
    color: var(--primary-green);
    margin-bottom: 0.5rem;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--dark-green);
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

/* Enhanced Social Links */
.social-links-large {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    max-width: 800px;
    margin: 0 auto;
}

.social-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background: var(--white);
    border-radius: 15px;
    text-decoration: none;
    color: var(--gray-dark);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
}

.social-link:hover {
    transform: translateY(-5px);
    color: var(--primary-green);
    text-decoration: none;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.social-link i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--primary-green);
}

.social-link span {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.social-link small {
    color: #666;
    font-size: 0.8rem;
}

/* Newsletter Section */
.newsletter-form {
    background: rgba(255,255,255,0.95);
    padding: 2rem;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
}

.newsletter-form h4 {
    color: #2E7D32 !important;
    font-weight: 600;
}

.newsletter-form label {
    color: #2E7D32 !important;
    font-weight: 500;
}

.newsletter-benefits {
    margin-top: 1rem;
}

.benefit-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
}

.benefit-item i {
    margin-right: 0.75rem;
    color: #4CAF50;
    background: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}

.subscription-form .form-control {
    border: 2px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.9);
    border-radius: 25px 0 0 25px;
}

.subscription-form .btn {
    border-radius: 0 25px 25px 0;
    font-weight: 600;
}

/* Animations */
@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
    }
}

/* Mobile Responsive Updates */
@media (max-width: 768px) {
    .live-chat-widget {
        width: 300px;
        height: 350px;
        right: 15px;
        bottom: 85px;
    }
    
    .chat-toggle {
        right: 15px;
        bottom: 15px;
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .team-card {
        margin-bottom: 2rem;
    }
    
    .guarantee-item {
        padding: 0.5rem;
    }
    
    .guarantee-time {
        font-size: 1.5rem;
    }
    
    .newsletter-form {
        padding: 1rem;
    }
    
    .social-links-large {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .social-links-large {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Live Chat Functionality
function toggleChat() {
    const chatWidget = document.getElementById('liveChatWidget');
    const chatToggle = document.querySelector('.chat-toggle');
    
    if (chatWidget.style.display === 'none' || chatWidget.style.display === '') {
        chatWidget.style.display = 'flex';
        chatToggle.style.display = 'none';
    } else {
        chatWidget.style.display = 'none';
        chatToggle.style.display = 'block';
    }
}

function selectQuickAction(action) {
    const chatInput = document.getElementById('chatInput');
    const subjects = {
        'farmer': 'I need help with farmer support',
        'order': 'I need help with my order',
        'technical': 'I have a technical issue'
    };
    
    chatInput.value = subjects[action] || '';
    
    // Simulate response
    setTimeout(() => {
        addBotMessage("I'll connect you with our " + action + " specialist right away. They'll be with you in just a moment!");
    }, 1000);
}

function sendMessage() {
    const chatInput = document.getElementById('chatInput');
    const message = chatInput.value.trim();
    
    if (message) {
        addUserMessage(message);
        chatInput.value = '';
        
        // Simulate bot response
        setTimeout(() => {
            addBotMessage("Thank you for your message! I'm connecting you with the right team member. You can also fill out our contact form for detailed assistance.");
        }, 1500);
    }
}

function addUserMessage(message) {
    const chatBody = document.querySelector('.chat-body');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message user';
    messageDiv.innerHTML = `
        <div class="message-content" style="background: var(--primary-green); color: white; margin-left: auto;">
            <p>${message}</p>
        </div>
    `;
    chatBody.appendChild(messageDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function addBotMessage(message) {
    const chatBody = document.querySelector('.chat-body');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message bot';
    messageDiv.innerHTML = `
        <div class="message-avatar">🌱</div>
        <div class="message-content">
            <p>${message}</p>
        </div>
    `;
    chatBody.appendChild(messageDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// Support Category Expansion
function expandCategory(category) {
    const categoryCard = event.currentTarget;
    const categoryDetails = document.getElementById(category + '-details');
    
    // Close all other categories
    document.querySelectorAll('.category-details').forEach(detail => {
        if (detail !== categoryDetails) {
            detail.classList.remove('expanded');
            detail.closest('.support-category-card').classList.remove('expanded');
        }
    });
    
    // Toggle current category
    categoryDetails.classList.toggle('expanded');
    categoryCard.classList.toggle('expanded');
}

// Auto-scroll to contact form
document.querySelectorAll('a[href="#contact-form"]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('.contact-form-wrapper').scrollIntoView({ 
            behavior: 'smooth' 
        });
    });
});

// Newsletter form submission
document.querySelector('.subscription-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    // Simulate subscription
    alert('Thank you for subscribing! You\'ll receive our weekly newsletter at ' + email);
    this.reset();
});

// Initialize chat notification pulse
setInterval(() => {
    const notification = document.querySelector('.chat-notification');
    if (notification) {
        notification.style.animation = 'none';
        setTimeout(() => {
            notification.style.animation = 'pulse 1s ease';
        }, 100);
    }
}, 5000);

// Enhanced form interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add focus effects to form inputs
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Add typing indicator to chat
    document.getElementById('chatInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});
</script>

<?php include '../components/footer.php'; ?>
