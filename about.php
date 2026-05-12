<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AutoMarket Pro</title>
    <meta name="description" content="Learn about AutoMarket Pro - your trusted partner for buying, selling, and renting quality vehicles.">
    
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
                <li><a href="about.php" class="nav-link active">About</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
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
                <h1>About AutoMarket Pro</h1>
                <p>Your trusted partner in the automotive marketplace since 2020</p>
            </div>
        </div>
    </section>

    <!-- About Content -->
    <section class="section">
        <div class="container">
            <div class="about-content">
                <div class="about-text animate-fadeInUp">
                    <h2>Our Story</h2>
                    <p>Founded in 2020, AutoMarket Pro has quickly become the leading online marketplace for buying, selling, and renting quality vehicles. We started with a simple mission: to make the car buying and selling process transparent, secure, and convenient for everyone.</p>
                    
                    <p>Our platform connects thousands of buyers and sellers across the country, offering a wide selection of vehicles from trusted dealerships and private sellers. Whether you're looking for your dream car, selling your current vehicle, or need a rental for a special occasion, AutoMarket Pro is here to help.</p>
                    
                    <div class="stats-row">
                        <div class="stat-item">
                            <div class="stat-number">500+</div>
                            <div class="stat-text">Cars Available</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">1000+</div>
                            <div class="stat-text">Happy Customers</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">50+</div>
                            <div class="stat-text">Car Brands</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">4.8/5</div>
                            <div class="stat-text">Customer Rating</div>
                        </div>
                    </div>
                </div>
                
                <div class="about-image animate-fadeIn">
                    <img src="assets/images/about-showroom.jpg" alt="AutoMarket Pro Showroom">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="section bg-light">
        <div class="container">
            <div class="mission-vision-grid">
                <div class="mission-card glass-card animate-fadeInUp">
                    <div class="card-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>To provide a trusted, transparent, and efficient platform for automotive transactions, making quality vehicles accessible to everyone while ensuring the best experience for both buyers and sellers.</p>
                </div>
                
                <div class="vision-card glass-card animate-fadeInUp">
                    <div class="card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>To become the world's most trusted automotive marketplace, revolutionizing how people buy, sell, and rent vehicles through innovation, technology, and exceptional customer service.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Our Core Values</h2>
                <p>The principles that guide everything we do</p>
            </div>
            
            <div class="values-grid">
                <div class="value-card glass-card animate-fadeInUp">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Trust & Security</h3>
                    <p>We prioritize the safety and security of every transaction, implementing robust verification processes and secure payment systems.</p>
                </div>
                
                <div class="value-card glass-card animate-fadeInUp">
                    <div class="value-icon">
                        <i class="fas fa-transparency"></i>
                    </div>
                    <h3>Transparency</h3>
                    <p>We believe in complete transparency in all our dealings, providing accurate information and fair pricing for all vehicles.</p>
                </div>
                
                <div class="value-card glass-card animate-fadeInUp">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Customer First</h3>
                    <p>Our customers are at the heart of everything we do. We strive to exceed expectations and provide exceptional service.</p>
                </div>
                
                <div class="value-card glass-card animate-fadeInUp">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Innovation</h3>
                    <p>We continuously innovate and improve our platform to provide the best possible experience for our users.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-header">
                <h2>Meet Our Team</h2>
                <p>The passionate people behind AutoMarket Pro</p>
            </div>
            
            <div class="team-grid">
                <div class="team-member animate-fadeInUp">
                    <div class="member-image">
                        <img src="assets/images/team-ceo.jpg" alt="CEO">
                    </div>
                    <h3>John Anderson</h3>
                    <p class="member-role">CEO & Founder</p>
                    <p class="member-bio">With over 15 years in the automotive industry, John founded AutoMarket Pro with a vision to revolutionize car buying and selling.</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="team-member animate-fadeInUp">
                    <div class="member-image">
                        <img src="assets/images/team-cto.jpg" alt="CTO">
                    </div>
                    <h3>Sarah Chen</h3>
                    <p class="member-role">CTO</p>
                    <p class="member-bio">Sarah leads our technical team, ensuring our platform is secure, scalable, and user-friendly.</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                
                <div class="team-member animate-fadeInUp">
                    <div class="member-image">
                        <img src="assets/images/team-coo.jpg" alt="COO">
                    </div>
                    <h3>Michael Roberts</h3>
                    <p class="member-role">COO</p>
                    <p class="member-bio">Michael oversees our daily operations, ensuring smooth experiences for all our customers and partners.</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                
                <div class="team-member animate-fadeInUp">
                    <div class="member-image">
                        <img src="assets/images/team-cmo.jpg" alt="CMO">
                    </div>
                    <h3>Emily Davis</h3>
                    <p class="member-role">CMO</p>
                    <p class="member-bio">Emily drives our marketing efforts, building brand awareness and connecting with our community.</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Our Achievements</h2>
                <p>Milestones that mark our journey of excellence</p>
            </div>
            
            <div class="achievements-timeline">
                <div class="timeline-item animate-fadeInUp">
                    <div class="timeline-year">2020</div>
                    <div class="timeline-content">
                        <h3>Company Founded</h3>
                        <p>AutoMarket Pro was established with a mission to transform the automotive marketplace.</p>
                    </div>
                </div>
                
                <div class="timeline-item animate-fadeInUp">
                    <div class="timeline-year">2021</div>
                    <div class="timeline-content">
                        <h3>1000+ Cars Listed</h3>
                        <p>Reached our first major milestone with over 1000 vehicles listed on the platform.</p>
                    </div>
                </div>
                
                <div class="timeline-item animate-fadeInUp">
                    <div class="timeline-year">2022</div>
                    <div class="timeline-content">
                        <h3>Expanded Services</h3>
                        <p>Launched our car rental service and financing options for customers.</p>
                    </div>
                </div>
                
                <div class="timeline-item animate-fadeInUp">
                    <div class="timeline-year">2023</div>
                    <div class="timeline-content">
                        <h3>National Recognition</h3>
                        <p>Awarded "Best Automotive Marketplace" by the Automotive Industry Association.</p>
                    </div>
                </div>
                
                <div class="timeline-item animate-fadeInUp">
                    <div class="timeline-year">2024</div>
                    <div class="timeline-content">
                        <h3>5000+ Happy Customers</h3>
                        <p>Served over 5000 satisfied customers and expanded to new markets.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section bg-primary">
        <div class="container">
            <div class="cta-content text-center">
                <h2>Ready to Join the AutoMarket Pro Family?</h2>
                <p>Experience the difference of working with a trusted automotive marketplace</p>
                <div class="cta-buttons">
                    <a href="register.php" class="glass-button primary">Get Started</a>
                    <a href="contact.php" class="glass-button">Contact Us</a>
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
</body>
</html>
