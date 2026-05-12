<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (empty($errors)) {
        // Here you would typically send an email or save to database
        // For now, we'll just show a success message
        $success_message = "Thank you for contacting us! We'll get back to you within 24 hours.";
        
        // Clear form
        $name = $email = $phone = $subject = $message = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - AutoMarket Pro</title>
    <meta name="description" content="Get in touch with AutoMarket Pro. We're here to help with all your automotive needs.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket Pro
            </a>
            
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="browse-cars.php" class="nav-link">Buy Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="sell-car.php" class="nav-link">Sell Car</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="contact.php" class="nav-link active">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="glass-button">
                        <i class="fas fa-user"></i> Dashboard
                    </a>
                    <a href="logout.php" class="glass-button">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="glass-button">Login</a>
                    <a href="register.php" class="glass-button primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Contact Us</h1>
                <p>We're here to help with all your automotive needs</p>
            </div>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="section">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Form -->
                <div class="contact-form-section animate-fadeInUp">
                    <h2>Send us a Message</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <p><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Subject *</label>
                                <select id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="general" <?php echo ($subject ?? '') === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="support" <?php echo ($subject ?? '') === 'support' ? 'selected' : ''; ?>>Technical Support</option>
                                    <option value="billing" <?php echo ($subject ?? '') === 'billing' ? 'selected' : ''; ?>>Billing Question</option>
                                    <option value="partnership" <?php echo ($subject ?? '') === 'partnership' ? 'selected' : ''; ?>>Partnership Opportunity</option>
                                    <option value="complaint" <?php echo ($subject ?? '') === 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                                    <option value="other" <?php echo ($subject ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="glass-button primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div class="contact-info-section animate-fadeInUp">
                    <h2>Get in Touch</h2>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Phone</h3>
                                <p><a href="tel:+12345678900">+1 234 567 8900</a></p>
                                <p>Mon-Fri: 9:00 AM - 6:00 PM EST</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Email</h3>
                                <p><a href="mailto:info@automarketpro.com">info@automarketpro.com</a></p>
                                <p><a href="mailto:support@automarketpro.com">support@automarketpro.com</a></p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Office Location</h3>
                                <p>123 Auto Street<br>
                                City, State 12345<br>
                                United States</p>
                            </div>
                        </div>
                        
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="contact-details">
                                <h3>Live Chat</h3>
                                <p>Available 24/7 for instant support</p>
                                <button class="glass-button primary" onclick="startLiveChat()">
                                    <i class="fas fa-comments"></i> Start Chat
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="social-section">
                        <h3>Follow Us</h3>
                        <div class="social-links">
                            <a href="#" class="social-link">
                                <i class="fab fa-facebook"></i>
                                <span>Facebook</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-twitter"></i>
                                <span>Twitter</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-linkedin"></i>
                                <span>LinkedIn</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-youtube"></i>
                                <span>YouTube</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-header">
                <h2>Frequently Asked Questions</h2>
                <p>Quick answers to common questions</p>
            </div>
            
            <div class="faq-grid">
                <div class="faq-item animate-fadeInUp">
                    <div class="faq-question">
                        <h3>How do I list my car for sale?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>To list your car, simply create an account, click "Sell Your Car" and fill out the required information about your vehicle. Our team will review and approve your listing within 24 hours.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-fadeInUp">
                    <div class="faq-question">
                        <h3>What payment methods do you accept?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We accept various payment methods including credit/debit cards, bank transfers, and financing options. All transactions are secured with industry-standard encryption.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-fadeInUp">
                    <div class="faq-question">
                        <h3>How do I know if a car is trustworthy?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>All vehicles on our platform undergo thorough inspection and verification. We provide detailed reports, vehicle history, and seller ratings to help you make informed decisions.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-fadeInUp">
                    <div class="faq-question">
                        <h3>Can I test drive a car before buying?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we encourage test drives. You can schedule a test drive directly with the seller through our platform. For rental cars, you can book for a day to try it out.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-fadeInUp">
                    <div class="faq-question">
                        <h3>What if I have issues with my purchase?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>We have a dedicated support team to help resolve any issues. We also offer buyer protection programs and dispute resolution services.</p>
                    </div>
                </div>
                
                <div class="faq-item animate-fadeInUp">
                    <div class="faq-question">
                        <h3>Do you offer financing options?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we partner with multiple financial institutions to offer competitive financing rates. You can apply for pre-approval directly through our platform.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Visit Our Showroom</h2>
                <p>Come see our premium collection in person</p>
            </div>
            
            <div class="map-container">
                <div class="map-placeholder">
                    <i class="fas fa-map-marked-alt"></i>
                    <p>Interactive Map</p>
                    <small>123 Auto Street, City, State 12345</small>
                </div>
            </div>
            
            <div class="showroom-info">
                <div class="info-grid">
                    <div class="info-card glass-card">
                        <h3>Showroom Hours</h3>
                        <p><strong>Monday - Friday:</strong> 9:00 AM - 7:00 PM</p>
                        <p><strong>Saturday:</strong> 10:00 AM - 6:00 PM</p>
                        <p><strong>Sunday:</strong> 11:00 AM - 5:00 PM</p>
                    </div>
                    
                    <div class="info-card glass-card">
                        <h3>Services Available</h3>
                        <ul>
                            <li>Vehicle inspection</li>
                            <li>Test drives</li>
                            <li>Paperwork assistance</li>
                            <li>Financing consultation</li>
                            <li>Insurance options</li>
                        </ul>
                    </div>
                    
                    <div class="info-card glass-card">
                        <h3>What to Bring</h3>
                        <ul>
                            <li>Valid driver's license</li>
                            <li>Proof of insurance</li>
                            <li>Pre-approval letter (if applicable)</li>
                            <li>Trade-in documents (if applicable)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>AutoMarket Pro</h3>
                    <p>Your premium destination for buying, selling, and renting quality vehicles.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="browse-cars.php">Browse Cars</a></li>
                        <li><a href="rentals.php">Rentals</a></li>
                        <li><a href="sell-car.php">Sell Your Car</a></li>
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Car Inspection</a></li>
                        <li><a href="#">Financing</a></li>
                        <li><a href="#">Insurance</a></li>
                        <li><a href="#">Maintenance</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope"></i> info@automarketpro.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Auto Street, City, Country</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 AutoMarket Pro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const faqItem = question.parentElement;
                const answer = faqItem.querySelector('.faq-answer');
                const icon = question.querySelector('i');
                
                // Close other FAQs
                document.querySelectorAll('.faq-item').forEach(item => {
                    if (item !== faqItem) {
                        item.querySelector('.faq-answer').style.maxHeight = null;
                        item.querySelector('i').classList.remove('fa-chevron-up');
                        item.querySelector('i').classList.add('fa-chevron-down');
                    }
                });
                
                // Toggle current FAQ
                if (answer.style.maxHeight) {
                    answer.style.maxHeight = null;
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            });
        });
        
        // Live Chat (placeholder)
        function startLiveChat() {
            alert('Live chat feature coming soon! Please call us at +1 234 567 8900 for immediate assistance.');
        }
    </script>
</body>
</html>
