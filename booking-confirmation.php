<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$booking_id = $_GET['id'] ?? '';
$db = new Database();
$pdo = $db->getConnection();

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, rc.title as car_title, rc.images, rc.daily_rate, rc.weekly_rate, rc.monthly_rate, 
           rc.security_deposit, rc.location, u.first_name, u.last_name, u.email, u.phone,
           b.name as brand_name, m.name as model_name
    FROM bookings b
    JOIN rental_cars rc ON b.rental_car_id = rc.id
    JOIN users u ON b.customer_id = u.id
    JOIN car_brands b ON rc.brand_id = b.id
    JOIN car_models m ON rc.model_id = m.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: rentals.php');
    exit();
}

// Send confirmation email
if ($booking['status'] === 'pending') {
    $subject = "Booking Confirmation - AutoMarket Pro";
    $message = "
        <h2>Booking Confirmed!</h2>
        <p>Thank you for booking with AutoMarket Pro. Here are your booking details:</p>
        
        <h3>Vehicle Details</h3>
        <p><strong>Car:</strong> {$booking['car_title']}</p>
        <p><strong>Pickup:</strong> {$booking['pickup_date']} at {$booking['pickup_time']}</p>
        <p><strong>Return:</strong> {$booking['return_date']} at {$booking['return_time']}</p>
        <p><strong>Location:</strong> {$booking['location']}</p>
        
        <h3>Payment Details</h3>
        <p><strong>Total Amount:</strong> $" . number_format($booking['total_amount'], 2) . "</p>
        <p><strong>Payment Method:</strong> " . ucfirst($booking['payment_method']) . "</p>
        <p><strong>Security Deposit:</strong> $" . number_format($booking['security_deposit'], 2) . "</p>
        
        <p>Please arrive 15 minutes before your pickup time with a valid driver's license and the payment method you selected.</p>
        
        <p>For any changes or cancellations, please contact us at least 24 hours in advance.</p>
    ";
    
    sendEmail($booking['email'], $subject, $message);
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - AutoMarket Pro</title>
    
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
            <h1>Booking Confirmation</h1>
            <p>Your rental has been successfully booked!</p>
        </div>
    </section>

    <!-- Confirmation Content -->
    <section class="section">
        <div class="container">
            <div class="confirmation-container">
                <!-- Success Message -->
                <div class="success-message glass-card animate-fadeInUp">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2>Booking Confirmed!</h2>
                    <p>Your rental booking has been successfully processed. A confirmation email has been sent to your registered email address.</p>
                </div>

                <!-- Booking Details -->
                <div class="booking-details glass-card animate-fadeInUp">
                    <h3>Booking Details</h3>
                    
                    <div class="booking-summary">
                        <div class="booking-summary-image">
                            <img src="<?php echo $booking['images'] ? json_decode($booking['images'])[0] : 'assets/images/default-car.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($booking['car_title']); ?>">
                        </div>
                        
                        <div class="booking-summary-info">
                            <h4><?php echo htmlspecialchars($booking['car_title']); ?></h4>
                            <p class="car-brand"><?php echo htmlspecialchars($booking['brand_name'] . ' ' . $booking['model_name']); ?></p>
                            
                            <div class="booking-info-grid">
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <div>
                                        <strong>Pickup:</strong><br>
                                        <?php echo formatDate($booking['pickup_date']); ?> at <?php echo $booking['pickup_time']; ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <div>
                                        <strong>Return:</strong><br>
                                        <?php echo formatDate($booking['return_date']); ?> at <?php echo $booking['return_time']; ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div>
                                        <strong>Location:</strong><br>
                                        <?php echo htmlspecialchars($booking['location']); ?>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <i class="fas fa-user"></i>
                                    <div>
                                        <strong>Customer:</strong><br>
                                        <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-payment-details">
                        <h4>Payment Summary</h4>
                        <div class="payment-breakdown">
                            <div class="payment-row">
                                <span>Daily Rate:</span>
                                <span>$<?php echo number_format($booking['daily_rate'], 2); ?></span>
                            </div>
                            <div class="payment-row">
                                <span>Rental Days:</span>
                                <span><?php echo ceil((strtotime($booking['return_date']) - strtotime($booking['pickup_date'])) / (60 * 60 * 24)); ?></span>
                            </div>
                            <div class="payment-row subtotal">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($booking['total_amount'] - ($booking['security_deposit'] ?? 0), 2); ?></span>
                            </div>
                            <?php if ($booking['security_deposit']): ?>
                                <div class="payment-row">
                                    <span>Security Deposit:</span>
                                    <span>$<?php echo number_format($booking['security_deposit'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="payment-row total">
                                <span>Total Amount:</span>
                                <span>$<?php echo number_format($booking['total_amount'], 2); ?></span>
                            </div>
                            <div class="payment-row">
                                <span>Payment Method:</span>
                                <span><?php echo ucfirst($booking['payment_method']); ?></span>
                            </div>
                            <div class="payment-row">
                                <span>Booking ID:</span>
                                <span>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Information -->
                <div class="important-info glass-card animate-fadeInUp">
                    <h3>Important Information</h3>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <i class="fas fa-id-card"></i>
                            <div>
                                <strong>Required Documents:</strong>
                                <p>Please bring a valid driver's license and a government-issued ID at pickup.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Pickup Time:</strong>
                                <p>Please arrive 15 minutes before your scheduled pickup time.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-dollar-sign"></i>
                            <div>
                                <strong>Payment:</strong>
                                <p>Complete payment using your selected method before pickup.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <strong>Contact:</strong>
                                <p>For any questions or changes, contact us at least 24 hours in advance.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="confirmation-actions glass-card animate-fadeInUp">
                    <h3>Next Steps</h3>
                    
                    <div class="action-buttons">
                        <a href="dashboard.php" class="glass-button">
                            <i class="fas fa-tachometer-alt"></i> View Dashboard
                        </a>
                        <a href="my-bookings.php" class="glass-button primary">
                            <i class="fas fa-calendar"></i> My Bookings
                        </a>
                        <button class="glass-button" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Confirmation
                        </button>
                        <a href="rentals.php" class="glass-button">
                            <i class="fas fa-search"></i> Browse More Rentals
                        </a>
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
        // Auto-refresh booking status
        function checkBookingStatus() {
            fetch('api/booking-status.php?id=<?php echo $booking_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status !== 'pending') {
                        // Update UI based on new status
                        updateBookingStatus(data.status);
                    }
                })
                .catch(error => console.error('Error checking booking status:', error));
        }
        
        function updateBookingStatus(status) {
            const statusElement = document.querySelector('.booking-status');
            if (statusElement) {
                statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                statusElement.className = 'booking-status ' + status;
            }
        }
        
        // Check status every 30 seconds
        setInterval(checkBookingStatus, 30000);
    </script>
    
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .success-message {
            text-align: center;
            padding: 3rem;
            margin-bottom: 2rem;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        
        .success-message h2 {
            color: #28a745;
            margin-bottom: 1rem;
        }
        
        .booking-summary {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .booking-summary-image {
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .booking-summary-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .booking-summary-info h4 {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .car-brand {
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        .booking-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: 8px;
        }
        
        .info-item i {
            color: var(--secondary-color);
            margin-top: 0.25rem;
        }
        
        .booking-payment-details {
            padding: 1.5rem;
            background: var(--bg-tertiary);
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .booking-payment-details h4 {
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }
        
        .payment-breakdown {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .payment-row.subtotal {
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            font-weight: 600;
        }
        
        .payment-row.total {
            padding-top: 0.75rem;
            border-top: 1px solid var(--border-color);
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .important-info {
            margin-bottom: 2rem;
        }
        
        .important-info h3 {
            color: var(--accent-color);
            margin-bottom: 1.5rem;
        }
        
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .important-info .info-item {
            padding: 1.5rem;
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid rgba(255, 107, 53, 0.3);
        }
        
        .important-info .info-item i {
            color: var(--accent-color);
            font-size: 1.25rem;
        }
        
        .important-info .info-item strong {
            color: var(--accent-color);
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .confirmation-actions {
            text-align: center;
        }
        
        .confirmation-actions h3 {
            margin-bottom: 1.5rem;
            color: var(--secondary-color);
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        
        .action-buttons .glass-button {
            flex: 1;
            min-width: 200px;
        }
        
        @media (max-width: 768px) {
            .booking-summary {
                grid-template-columns: 1fr;
            }
            
            .booking-info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .glass-button {
                min-width: auto;
            }
        }
        
        @media print {
            .navbar, .footer, .confirmation-actions {
                display: none;
            }
            
            .page-header {
                padding: 2rem 0 1rem;
            }
            
            .glass-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</body>
</html>
