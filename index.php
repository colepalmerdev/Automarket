<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$db = new Database();
$pdo = $db->getConnection();

// Get featured cars for homepage
$featured_cars = [];
try {
    $stmt = $pdo->prepare("
        SELECT c.*, b.name as brand_name, m.name as model_name, u.username as seller_name
        FROM cars c
        JOIN car_brands b ON c.brand_id = b.id
        JOIN car_models m ON c.model_id = m.id
        JOIN users u ON c.seller_id = u.id
        WHERE c.is_approved = 1 AND c.is_sold = 0 AND c.is_featured = 1
        ORDER BY c.created_at DESC
        LIMIT 6
    ");
    $stmt->execute();
    $featured_cars = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently for homepage
}

// Get recent rental cars
$rental_cars = [];
try {
    $stmt = $pdo->prepare("
        SELECT rc.*, b.name as brand_name, m.name as model_name, u.username as owner_name
        FROM rental_cars rc
        JOIN car_brands b ON rc.brand_id = b.id
        JOIN car_models m ON rc.model_id = m.id
        JOIN users u ON rc.owner_id = u.id
        WHERE rc.is_approved = 1 AND rc.is_available = 1
        ORDER BY rc.created_at DESC
        LIMIT 6
    ");
    $stmt->execute();
    $rental_cars = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error silently for homepage
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoMarket - Premium Car Marketplace & Rentals</title>
    <meta name="description" content="Buy, sell, and rent premium cars with confidence. Browse our extensive collection of quality vehicles.">
    
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
                AutoMarket
            </a>
            
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="browse-cars.php" class="nav-link">Buy Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="sell-car.php" class="nav-link">Sell Car</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                   <!-- <a href="admin/dashboard.php" class="glass-button">
                        <i class="fas fa-user"></i> Dashboard
                    </a> -->
                    <a href="logout.php" class="glass-button">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="glass-button">Login</a>
                    <a href="register-simple.php" class="glass-button primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text animate-fadeInUp">
                <h1>Drive Your Dream Car</h1>
                <p>Discover premium vehicles for sale and rent. Experience luxury, performance, and reliability with AutoMarket.</p>
                <div class="hero-buttons">
                    <a href="browse-cars.php" class="glass-button primary">
                        <i class="fas fa-search"></i> Browse Cars
                    </a>
                    <a href="sell-car.php" class="glass-button">
                        <i class="fas fa-plus"></i> Sell Your Car
                    </a>
                    <a href="rentals.php" class="glass-button">
                        <i class="fas fa-calendar"></i> Rent a Car
                    </a>
                </div>
            </div>
            
            <div class="hero-image animate-fadeIn">
                <img src="images/2022 BMW 5 Series.png" alt="Premium Luxury Car">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose AutoMarket?</h2>
                <p>Your trusted partner for all automotive needs</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card glass-card animate-fadeInUp">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Verified Listings</h3>
                    <p>All vehicles are thoroughly inspected and verified by our expert team.</p>
                </div>
                
                <div class="feature-card glass-card animate-fadeInUp">
                    <div class="feature-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Secure Transactions</h3>
                    <p>Safe and secure payment processing with multiple payment options.</p>
                </div>
                
                <div class="feature-card glass-card animate-fadeInUp">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock customer support for all your queries and concerns.</p>
                </div>
                
                <div class="feature-card glass-card animate-fadeInUp">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Quick and reliable delivery options for purchased vehicles.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Cars Section -->
    <?php if (!empty($featured_cars)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Featured Cars for Sale</h2>
                <p>Premium vehicles handpicked by our experts</p>
            </div>
            
            <div class="car-grid">
                <?php foreach ($featured_cars as $car): ?>
                    <div class="car-card animate-fadeInUp">
                        <div class="car-image">
                            <img src="<?php echo $car['images'] ? json_decode($car['images'])[0] : 'images/2022 BMW 5 Series.png'; ?>" alt="<?php echo htmlspecialchars($car['title']); ?>">
                            <span class="car-badge">Featured</span>
                            <span class="car-price"><?php echo formatPrice($car['price']); ?></span>
                        </div>
                        <div class="car-details">
                            <h3 class="car-title"><?php echo htmlspecialchars($car['title']); ?></h3>
                            <div class="car-specs">
                                <span class="spec-item">
                                    <i class="fas fa-calendar"></i> <?php echo $car['year']; ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-gas-pump"></i> <?php echo ucfirst($car['fuel_type']); ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-cog"></i> <?php echo ucfirst($car['transmission']); ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-tachometer-alt"></i> <?php echo number_format($car['mileage']); ?> km
                                </span>
                            </div>
                            <div class="car-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?>
                            </div>
                            <div class="car-actions">
                                <button class="glass-button" onclick="viewCar(<?php echo $car['id']; ?>)">View Details</button>
                                <button class="glass-button primary" onclick="contactSeller(<?php echo $car['id']; ?>)">Contact</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="browse-cars.php" class="glass-button primary">View All Cars</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Rental Cars Section -->
    <?php if (!empty($rental_cars)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Available for Rental</h2>
                <p>Premium vehicles for your temporary needs</p>
            </div>
            
            <div class="car-grid">
                <?php foreach ($rental_cars as $car): ?>
                    <div class="car-card animate-fadeInUp">
                        <div class="car-image">
                            <img src="<?php echo $car['images'] ? json_decode($car['images'])[0] : 'images/2022 kia sportage.png'; ?>" alt="<?php echo htmlspecialchars($car['title']); ?>">
                            <span class="car-badge">Available</span>
                            <span class="car-price">$<?php echo number_format($car['daily_rate'], 2); ?>/day</span>
                        </div>
                        <div class="car-details">
                            <h3 class="car-title"><?php echo htmlspecialchars($car['title']); ?></h3>
                            <div class="car-specs">
                                <span class="spec-item">
                                    <i class="fas fa-calendar"></i> <?php echo $car['year']; ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-gas-pump"></i> <?php echo ucfirst($car['fuel_type']); ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-cog"></i> <?php echo ucfirst($car['transmission']); ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-dollar-sign"></i> $<?php echo number_format($car['daily_rate'], 2); ?>/day
                                </span>
                            </div>
                            <div class="car-location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?>
                            </div>
                            <div class="car-actions">
                                <button class="glass-button" onclick="viewRentalCar(<?php echo $car['id']; ?>)">View Details</button>
                                <button class="glass-button primary" onclick="bookRental(<?php echo $car['id']; ?>)">Book Now</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="rentals.php" class="glass-button primary">View All Rentals</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Statistics Section -->
    <section class="section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card glass-card animate-fadeInUp">
                    <div class="stat-value">500+</div>
                    <div class="stat-label">Cars Available</div>
                </div>
                <div class="stat-card glass-card animate-fadeInUp">
                    <div class="stat-value">1000+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-card glass-card animate-fadeInUp">
                    <div class="stat-value">50+</div>
                    <div class="stat-label">Car Brands</div>
                </div>
                <div class="stat-card glass-card animate-fadeInUp">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>AutoMarket</h3>
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
                    <p><i class="fas fa-phone"></i> +254 745 554 951</p>
                    <p><i class="fas fa-envelope"></i> info@automarket.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Auto Street, Nairobi, Kenya</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 AutoMarket. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Additional homepage-specific functions
        function viewRentalCar(carId) {
            window.location.href = `rental-details.php?id=${carId}`;
        }
        
        function bookRental(carId) {
            window.location.href = `booking.php?car_id=${carId}`;
        }
    </script>
</body>
</html>
