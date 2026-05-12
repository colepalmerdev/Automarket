<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get rental car ID from URL
$car_id = $_GET['id'] ?? null;

if (!$car_id) {
    header('Location: rentals.php');
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

// Get rental car details
$car = null;
try {
    $stmt = $pdo->prepare("
        SELECT rc.*, b.name as brand_name, m.name as model_name, u.username as owner_name, u.email as owner_email, u.phone as owner_phone
        FROM rental_cars rc
        JOIN car_brands b ON rc.brand_id = b.id
        JOIN car_models m ON rc.model_id = m.id
        JOIN users u ON rc.owner_id = u.id
        WHERE rc.id = ? AND rc.is_approved = 1
    ");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
} catch(PDOException $e) {
    error_log("Error fetching rental car: " . $e->getMessage());
}

if (!$car) {
    header('Location: rentals.php');
    exit;
}

// Get similar rental cars
$similar_cars = [];
try {
    $stmt = $pdo->prepare("
        SELECT rc.*, b.name as brand_name, m.name as model_name
        FROM rental_cars rc
        JOIN car_brands b ON rc.brand_id = b.id
        JOIN car_models m ON rc.model_id = m.id
        WHERE rc.id != ? AND rc.is_approved = 1 AND rc.is_available = 1
        AND (rc.brand_id = ? OR rc.fuel_type = ?)
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt->execute([$car_id, $car['brand_id'], $car['fuel_type']]);
    $similar_cars = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error fetching similar cars: " . $e->getMessage());
}

// Handle booking form submission
$booking_errors = [];
$booking_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $pickup_location = trim($_POST['pickup_location'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($start_date)) {
        $booking_errors[] = 'Start date is required';
    }
    
    if (empty($end_date)) {
        $booking_errors[] = 'End date is required';
    }
    
    if (empty($pickup_location)) {
        $booking_errors[] = 'Pickup location is required';
    }
    
    if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        $booking_errors[] = 'Start date cannot be in the past';
    }
    
    if (strtotime($end_date) <= strtotime($start_date)) {
        $booking_errors[] = 'End date must be after start date';
    }
    
    if (empty($booking_errors)) {
        // Calculate total price
        $days = ceil((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24)) + 1;
        $total_price = $days * $car['daily_rate'];
        
        // Here you would typically save the booking to database
        $booking_success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['title']); ?> - Rental Details | AutoMarket Pro</title>
    <meta name="description" content="Rent <?php echo htmlspecialchars($car['title']); ?> at $<?php echo number_format($car['daily_rate'], 2); ?>/day. View details and book online.">
    
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

    <!-- Breadcrumb -->
    <section class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <a href="rentals.php">Rentals</a>
                <span><?php echo htmlspecialchars($car['title']); ?></span>
            </nav>
        </div>
    </section>

    <!-- Car Details -->
    <section class="section">
        <div class="container">
            <div class="car-details-grid">
                <!-- Car Images -->
                <div class="car-images-section animate-fadeIn">
                    <div class="main-image">
                        <?php 
                        $images = json_decode($car['images'] ?? '[]');
                        $main_image = !empty($images) ? $images[0] : 'assets/images/default-car.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($main_image); ?>" alt="<?php echo htmlspecialchars($car['title']); ?>">
                    </div>
                    
                    <?php if (!empty($images) && count($images) > 1): ?>
                    <div class="image-thumbnails">
                        <?php foreach ($images as $index => $image): ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($car['title']); ?> - Image <?php echo $index + 1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Car Information -->
                <div class="car-info-section animate-fadeInUp">
                    <div class="car-header">
                        <h1><?php echo htmlspecialchars($car['title']); ?></h1>
                        <div class="car-price">
                            <span class="price-amount">$<?php echo number_format($car['daily_rate'], 2); ?></span>
                            <span class="price-unit">per day</span>
                        </div>
                    </div>
                    
                    <div class="car-specs">
                        <div class="spec-item">
                            <i class="fas fa-car"></i>
                            <span><?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model_name']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo $car['year']; ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-gas-pump"></i>
                            <span><?php echo ucfirst($car['fuel_type']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-cog"></i>
                            <span><?php echo ucfirst($car['transmission']); ?></span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo $car['seats']; ?> Seats</span>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <span><?php echo number_format($car['mileage']); ?> km</span>
                        </div>
                    </div>
                    
                    <div class="car-features">
                        <h3>Features & Amenities</h3>
                        <div class="features-list">
                            <?php 
                            $features = json_decode($car['features'] ?? '[]');
                            $all_features = [
                                'Air Conditioning', 'GPS Navigation', 'Bluetooth', 'USB Ports',
                                'Cruise Control', 'Parking Sensors', 'Backup Camera', 'Leather Seats',
                                'Sunroof', 'Premium Sound System', 'Heated Seats', 'Keyless Entry'
                            ];
                            
                            foreach ($all_features as $feature) {
                                $has_feature = in_array($feature, $features);
                                echo '<div class="feature-item ' . ($has_feature ? 'available' : 'unavailable') . '">';
                                echo '<i class="fas fa-' . ($has_feature ? 'check' : 'times') . '"></i>';
                                echo '<span>' . $feature . '</span>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="car-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($car['description'] ?? 'Experience luxury and comfort with this premium rental vehicle. Perfect for both business and leisure travel.')); ?></p>
                    </div>
                    
                    <div class="car-location">
                        <h3>Pickup Location</h3>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?></p>
                    </div>
                    
                    <div class="car-owner">
                        <h3>Vehicle Owner</h3>
                        <div class="owner-info">
                            <div class="owner-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="owner-details">
                                <p class="owner-name"><?php echo htmlspecialchars($car['owner_name']); ?></p>
                                <p class="owner-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= 4 ? 'rated' : ''; ?>"></i>
                                    <?php endfor; ?>
                                    <span>(4.5)</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Section -->
    <section class="section bg-light">
        <div class="container">
            <div class="booking-section">
                <div class="booking-form-section animate-fadeInUp">
                    <h2>Book This Vehicle</h2>
                    
                    <?php if ($booking_success): ?>
                        <div class="alert alert-success">
                            <h3>Booking Request Submitted!</h3>
                            <p>Thank you for your booking request. The owner will contact you within 24 hours to confirm your reservation.</p>
                            <p><strong>Total Cost:</strong> $<?php echo number_format($total_price, 2); ?> for <?php echo $days; ?> day(s)</p>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($booking_errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($booking_errors as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="booking-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="start_date">Start Date *</label>
                                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>" required onchange="updateEndDate()">
                                </div>
                                
                                <div class="form-group">
                                    <label for="end_date">End Date *</label>
                                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="pickup_location">Pickup Location *</label>
                                <input type="text" id="pickup_location" name="pickup_location" value="<?php echo htmlspecialchars($_POST['pickup_location'] ?? $car['location']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Additional Message (Optional)</label>
                                <textarea id="message" name="message" rows="4"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="price-summary">
                                <div class="price-row">
                                    <span>Daily Rate:</span>
                                    <span>$<?php echo number_format($car['daily_rate'], 2); ?></span>
                                </div>
                                <div class="price-row">
                                    <span>Number of Days:</span>
                                    <span id="days-count">-</span>
                                </div>
                                <div class="price-row total">
                                    <span>Total Cost:</span>
                                    <span id="total-cost">$0.00</span>
                                </div>
                            </div>
                            
                            <button type="submit" name="book_now" class="glass-button primary">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <div class="booking-info-section animate-fadeInUp">
                    <h3>Rental Terms & Conditions</h3>
                    <div class="terms-list">
                        <div class="term-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Valid driver's license required</span>
                        </div>
                        <div class="term-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Minimum age: 21 years</span>
                        </div>
                        <div class="term-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Security deposit required</span>
                        </div>
                        <div class="term-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Insurance included in daily rate</span>
                        </div>
                        <div class="term-item">
                            <i class="fas fa-check-circle"></i>
                            <span>24/7 roadside assistance</span>
                        </div>
                        <div class="term-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Free cancellation up to 24 hours before pickup</span>
                        </div>
                    </div>
                    
                    <div class="contact-support">
                        <h4>Need Help?</h4>
                        <p>Our support team is available 24/7 to assist you with your booking.</p>
                        <a href="tel:+12345678900" class="glass-button">
                            <i class="fas fa-phone"></i> Call Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Cars -->
    <?php if (!empty($similar_cars)): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2>Similar Vehicles</h2>
                <p>You might also like these rental cars</p>
            </div>
            
            <div class="car-grid">
                <?php foreach ($similar_cars as $similar_car): ?>
                    <div class="car-card animate-fadeInUp">
                        <div class="car-image">
                            <?php 
                            $similar_images = json_decode($similar_car['images'] ?? '[]');
                            $similar_main_image = !empty($similar_images) ? $similar_images[0] : 'assets/images/default-car.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($similar_main_image); ?>" alt="<?php echo htmlspecialchars($similar_car['title']); ?>">
                            <span class="car-badge">Available</span>
                            <span class="car-price">$<?php echo number_format($similar_car['daily_rate'], 2); ?>/day</span>
                        </div>
                        <div class="car-details">
                            <h3 class="car-title"><?php echo htmlspecialchars($similar_car['title']); ?></h3>
                            <div class="car-specs">
                                <span class="spec-item">
                                    <i class="fas fa-calendar"></i> <?php echo $similar_car['year']; ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-gas-pump"></i> <?php echo ucfirst($similar_car['fuel_type']); ?>
                                </span>
                                <span class="spec-item">
                                    <i class="fas fa-cog"></i> <?php echo ucfirst($similar_car['transmission']); ?>
                                </span>
                            </div>
                            <div class="car-actions">
                                <button class="glass-button" onclick="window.location.href='rental-details.php?id=<?php echo $similar_car['id']; ?>'">View Details</button>
                                <button class="glass-button primary" onclick="window.location.href='booking.php?car_id=<?php echo $similar_car['id']; ?>'">Book Now</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>AutoMarket </h3>
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
                    <p><i class="fas fa-phone"></i> +254 745 445 951</p>
                    <p><i class="fas fa-envelope"></i> info@automarket.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Auto Street, Nairobi, Kenya</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 AutoMarket . All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Image gallery functionality
        function changeMainImage(imageSrc, thumbnail) {
            document.querySelector('.main-image img').src = imageSrc;
            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
        }
        
        // Date functionality
        function updateEndDate() {
            const startDate = document.getElementById('start_date').value;
            const endDateInput = document.getElementById('end_date');
            
            if (startDate) {
                endDateInput.min = startDate;
                if (endDateInput.value && endDateInput.value < startDate) {
                    endDateInput.value = startDate;
                }
            }
            updatePriceCalculation();
        }
        
        function updatePriceCalculation() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const dailyRate = <?php echo $car['daily_rate']; ?>;
            
            if (startDate && endDate) {
                const days = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24)) + 1;
                const totalCost = days * dailyRate;
                
                document.getElementById('days-count').textContent = days;
                document.getElementById('total-cost').textContent = '$' + totalCost.toFixed(2);
            } else {
                document.getElementById('days-count').textContent = '-';
                document.getElementById('total-cost').textContent = '$0.00';
            }
        }
        
        // Event listeners
        document.getElementById('start_date').addEventListener('change', updateEndDate);
        document.getElementById('end_date').addEventListener('change', updatePriceCalculation);
        
        // Initialize
        updateEndDate();
        updatePriceCalculation();
    </script>
</body>
</html>
