<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$car_id = $_GET['car_id'] ?? '';
$db = new Database();
$pdo = $db->getConnection();

// Get rental car details
$car = getRentalCarDetails($pdo, $car_id);

if (!$car || !$car['is_available'] || !$car['is_approved']) {
    header('Location: rentals.php');
    exit();
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_time = $_POST['pickup_time'];
    $return_time = $_POST['return_time'];
    $payment_method = $_POST['payment_method'];
    $special_requests = $_POST['special_requests'] ?? '';
    
    $errors = [];
    
    // Validate dates
    if (empty($pickup_date) || empty($return_date)) {
        $errors[] = 'Pickup and return dates are required';
    }
    
    if (strtotime($return_date) <= strtotime($pickup_date)) {
        $errors[] = 'Return date must be after pickup date';
    }
    
    if (strtotime($pickup_date) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Pickup date cannot be in the past';
    }
    
    // Check minimum rental days
    $days = ceil((strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 * 24));
    if ($days < $car['min_rental_days']) {
        $errors[] = "Minimum rental period is {$car['min_rental_days']} day(s)";
    }
    
    // Check availability
    if (!isCarAvailable($pdo, $car_id, $pickup_date, $return_date)) {
        $errors[] = 'Car is not available for the selected dates';
    }
    
    if (empty($errors)) {
        try {
            // Calculate total amount
            $daily_rate = $car['daily_rate'];
            $weekly_rate = $car['weekly_rate'] ?? ($daily_rate * 7);
            $monthly_rate = $car['monthly_rate'] ?? ($daily_rate * 30);
            
            if ($days >= 30) {
                $months = floor($days / 30);
                $remaining_days = $days % 30;
                $total_amount = ($months * $monthly_rate) + ($remaining_days * $daily_rate);
            } elseif ($days >= 7) {
                $weeks = floor($days / 7);
                $remaining_days = $days % 7;
                $total_amount = ($weeks * $weekly_rate) + ($remaining_days * $daily_rate);
            } else {
                $total_amount = $days * $daily_rate;
            }
            
            $security_deposit = $car['security_deposit'] ?? 0;
            
            // Create booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (
                    rental_car_id, customer_id, pickup_date, return_date, 
                    pickup_time, return_time, total_amount, security_deposit,
                    payment_method, special_requests, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->execute([
                $car_id,
                getCurrentUserId(),
                $pickup_date,
                $return_date,
                $pickup_time,
                $return_time,
                $total_amount,
                $security_deposit,
                $payment_method,
                $special_requests
            ]);
            
            $booking_id = $pdo->lastInsertId();
            
            // Redirect to confirmation page
            header("Location: booking-confirmation.php?id=$booking_id");
            exit();
            
        } catch(PDOException $e) {
            $errors[] = 'Booking failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Rental - AutoMarket Pro</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/additional.css">
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
                
                <a href="dashboard.php" class="glass-button">
                    <i class="fas fa-user"></i> Dashboard
                </a>
                <a href="logout.php" class="glass-button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Book Your Rental</h1>
            <p>Complete your booking for <?php echo htmlspecialchars($car['title']); ?></p>
        </div>
    </section>

    <!-- Booking Content -->
    <section class="section">
        <div class="container">
            <div class="booking-layout">
                <!-- Car Summary -->
                <div class="booking-summary">
                    <div class="glass-card">
                        <h2>Vehicle Details</h2>
                        
                        <div class="car-summary-image">
                            <img src="<?php echo $car['images'] ? json_decode($car['images'])[0] : 'assets/images/default-car.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($car['title']); ?>">
                        </div>
                        
                        <div class="car-summary-details">
                            <h3><?php echo htmlspecialchars($car['title']); ?></h3>
                            
                            <div class="car-summary-specs">
                                <div class="spec-row">
                                    <span><i class="fas fa-car"></i> <?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model_name']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo $car['year']; ?></span>
                                </div>
                                <div class="spec-row">
                                    <span><i class="fas fa-gas-pump"></i> <?php echo ucfirst($car['fuel_type']); ?></span>
                                    <span><i class="fas fa-cog"></i> <?php echo ucfirst($car['transmission']); ?></span>
                                </div>
                                <div class="spec-row">
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?></span>
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($car['owner_name']); ?></span>
                                </div>
                            </div>
                            
                            <div class="pricing-info">
                                <div class="rate-info">
                                    <div class="rate-item">
                                        <i class="fas fa-calendar-day"></i>
                                        <span>Daily Rate:</span>
                                        <strong>$<?php echo number_format($car['daily_rate'], 2); ?></strong>
                                    </div>
                                    <?php if ($car['weekly_rate']): ?>
                                        <div class="rate-item">
                                            <i class="fas fa-calendar-week"></i>
                                            <span>Weekly Rate:</span>
                                            <strong>$<?php echo number_format($car['weekly_rate'], 2); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($car['monthly_rate']): ?>
                                        <div class="rate-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Monthly Rate:</span>
                                            <strong>$<?php echo number_format($car['monthly_rate'], 2); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($car['security_deposit']): ?>
                                    <div class="deposit-info">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Security Deposit:</span>
                                        <strong>$<?php echo number_format($car['security_deposit'], 2); ?></strong>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="min-rental-info">
                                    <i class="fas fa-info-circle"></i>
                                    Minimum rental: <?php echo $car['min_rental_days']; ?> day(s)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Form -->
                <div class="booking-form">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="glass-card">
                        <h2>Booking Details</h2>
                        
                        <form method="POST" id="booking-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Pickup Date *</label>
                                    <input type="date" name="pickup_date" class="form-input" required 
                                           min="<?php echo date('Y-m-d'); ?>"
                                           value="<?php echo htmlspecialchars($_POST['pickup_date'] ?? ''); ?>"
                                           onchange="updatePrice()">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Return Date *</label>
                                    <input type="date" name="return_date" class="form-input" required 
                                           min="<?php echo date('Y-m-d'); ?>"
                                           value="<?php echo htmlspecialchars($_POST['return_date'] ?? ''); ?>"
                                           onchange="updatePrice()">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Pickup Time *</label>
                                    <select name="pickup_time" class="form-select" required>
                                        <option value="">Select Time</option>
                                        <?php for ($hour = 8; $hour <= 20; $hour++): ?>
                                            <?php for ($min = 0; $min < 60; $min += 30): ?>
                                                <option value="<?php echo sprintf('%02d:%02d', $hour, $min); ?>">
                                                    <?php echo sprintf('%02d:%02d', $hour, $min); ?>
                                                </option>
                                            <?php endfor; ?>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Return Time *</label>
                                    <select name="return_time" class="form-select" required>
                                        <option value="">Select Time</option>
                                        <?php for ($hour = 8; $hour <= 20; $hour++): ?>
                                            <?php for ($min = 0; $min < 60; $min += 30): ?>
                                                <option value="<?php echo sprintf('%02d:%02d', $hour, $min); ?>">
                                                    <?php echo sprintf('%02d:%02d', $hour, $min); ?>
                                                </option>
                                            <?php endfor; ?>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Payment Method *</label>
                                    <div class="payment-methods">
                                        <label class="payment-method">
                                            <input type="radio" name="payment_method" value="credit_card" required>
                                            <div class="payment-option">
                                                <i class="fas fa-credit-card"></i>
                                                <span>Credit Card</span>
                                            </div>
                                        </label>
                                        
                                        <label class="payment-method">
                                            <input type="radio" name="payment_method" value="mpesa" required>
                                            <div class="payment-option">
                                                <i class="fas fa-mobile-alt"></i>
                                                <span>M-Pesa</span>
                                            </div>
                                        </label>
                                        
                                        <label class="payment-method">
                                            <input type="radio" name="payment_method" value="cash" required>
                                            <div class="payment-option">
                                                <i class="fas fa-money-bill"></i>
                                                <span>Cash</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Special Requests (Optional)</label>
                                    <textarea name="special_requests" class="form-textarea" rows="3"
                                              placeholder="Any special requirements or requests..."><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Price Calculator -->
                            <div class="price-calculator glass-card">
                                <h3>Price Breakdown</h3>
                                <div class="price-breakdown">
                                    <div class="price-row">
                                        <span>Daily Rate:</span>
                                        <span id="daily-rate-display">$<?php echo number_format($car['daily_rate'], 2); ?></span>
                                    </div>
                                    <div class="price-row">
                                        <span>Number of Days:</span>
                                        <span id="days-display">0</span>
                                    </div>
                                    <div class="price-row subtotal">
                                        <span>Subtotal:</span>
                                        <span id="subtotal-display">$0.00</span>
                                    </div>
                                    <?php if ($car['security_deposit']): ?>
                                        <div class="price-row">
                                            <span>Security Deposit:</span>
                                            <span>$<?php echo number_format($car['security_deposit'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="price-row total">
                                        <span>Total Amount:</span>
                                        <span id="total-display">$0.00</span>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="total-price" name="total_amount" value="0">
                            </div>
                            
                            <div class="booking-actions">
                                <a href="rentals.php" class="glass-button">Cancel</a>
                                <button type="submit" class="glass-button primary">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 AutoMarket Pro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <script>
        const dailyRate = <?php echo $car['daily_rate']; ?>;
        const weeklyRate = <?php echo $car['weekly_rate'] ?? ($car['daily_rate'] * 7); ?>;
        const monthlyRate = <?php echo $car['monthly_rate'] ?? ($car['daily_rate'] * 30); ?>;
        const securityDeposit = <?php echo $car['security_deposit'] ?? 0; ?>;
        
        function updatePrice() {
            const pickupDate = document.querySelector('input[name="pickup_date"]').value;
            const returnDate = document.querySelector('input[name="return_date"]').value;
            
            if (!pickupDate || !returnDate) return;
            
            const days = Math.ceil((new Date(returnDate) - new Date(pickupDate)) / (1000 * 60 * 60 * 24));
            
            let subtotal = 0;
            if (days >= 30) {
                const months = Math.floor(days / 30);
                const remainingDays = days % 30;
                subtotal = (months * monthlyRate) + (remainingDays * dailyRate);
            } else if (days >= 7) {
                const weeks = Math.floor(days / 7);
                const remainingDays = days % 7;
                subtotal = (weeks * weeklyRate) + (remainingDays * dailyRate);
            } else {
                subtotal = days * dailyRate;
            }
            
            const total = subtotal + securityDeposit;
            
            // Update display
            document.getElementById('days-display').textContent = days;
            document.getElementById('subtotal-display').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('total-display').textContent = '$' + total.toFixed(2);
            document.getElementById('total-price').value = total.toFixed(2);
        }
        
        // Date validation
        document.addEventListener('DOMContentLoaded', function() {
            const pickupDate = document.querySelector('input[name="pickup_date"]');
            const returnDate = document.querySelector('input[name="return_date"]');
            
            pickupDate.addEventListener('change', function() {
                returnDate.min = this.value;
                if (returnDate.value && returnDate.value < this.value) {
                    returnDate.value = this.value;
                }
                updatePrice();
            });
            
            returnDate.addEventListener('change', function() {
                if (pickupDate.value && this.value < pickupDate.value) {
                    this.value = pickupDate.value;
                }
                updatePrice();
            });
        });
        
        // Form validation
        document.getElementById('booking-form').addEventListener('submit', function(e) {
            const pickupDate = document.querySelector('input[name="pickup_date"]').value;
            const returnDate = document.querySelector('input[name="return_date"]').value;
            const pickupTime = document.querySelector('select[name="pickup_time"]').value;
            const returnTime = document.querySelector('select[name="return_time"]').value;
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!pickupDate || !returnDate || !pickupTime || !returnTime || !paymentMethod) {
                e.preventDefault();
                showToast('Please fill in all required fields', 'error');
            }
        });
    </script>
    
    <style>
        .booking-layout {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
        }
        
        .car-summary-image {
            height: 200px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .car-summary-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .car-summary-details h3 {
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }
        
        .car-summary-specs {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .spec-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            font-size: 0.9rem;
        }
        
        .spec-row span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
        }
        
        .pricing-info {
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: 10px;
        }
        
        .rate-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .rate-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: var(--bg-secondary);
            border-radius: 8px;
        }
        
        .rate-item i {
            color: var(--secondary-color);
        }
        
        .rate-item strong {
            color: var(--secondary-color);
        }
        
        .deposit-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 8px;
            margin-bottom: 0.75rem;
        }
        
        .deposit-info i {
            color: var(--secondary-color);
        }
        
        .deposit-info strong {
            color: var(--secondary-color);
        }
        
        .min-rental-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-style: italic;
        }
        
        .min-rental-info i {
            color: var(--accent-color);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .payment-method input[type="radio"] {
            display: none;
        }
        
        .payment-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-option i {
            font-size: 1.5rem;
            color: var(--text-secondary);
        }
        
        .payment-method input[type="radio"]:checked + .payment-option {
            border-color: var(--secondary-color);
            background: rgba(212, 175, 55, 0.1);
        }
        
        .payment-method input[type="radio"]:checked + .payment-option i {
            color: var(--secondary-color);
        }
        
        .price-calculator {
            margin: 2rem 0;
            padding: 1.5rem;
        }
        
        .price-calculator h3 {
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }
        
        .price-breakdown {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .price-row.subtotal {
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            font-weight: 600;
        }
        
        .price-row.total {
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .booking-actions {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .booking-layout {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
