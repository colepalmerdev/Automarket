<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AutoMarket</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body> -->
<!--<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$db = new Database();
$pdo = $db->getConnection();
$user_id = getCurrentUserId();
$user_role = getCurrentUserRole();

// Get user data
$user = getUserById($pdo, $user_id);

// Get statistics based on user role
$stats = [];
if ($user_role === 'seller') {
    // Seller stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cars WHERE seller_id = ? AND is_sold = 0");
    $stmt->execute([$user_id]);
    $stats['active_listings'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cars WHERE seller_id = ? AND is_sold = 1");
    $stmt->execute([$user_id]);
    $stats['sold_cars'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cars WHERE seller_id = ? AND is_approved = 0");
    $stmt->execute([$user_id]);
    $stats['pending_approval'] = $stmt->fetch()['total'];
    
    // Get recent listings
    $stmt = $pdo->prepare("
        SELECT c.*, b.name as brand_name, m.name as model_name
        FROM cars c
        JOIN car_brands b ON c.brand_id = b.id
        JOIN car_models m ON c.model_id = m.id
        WHERE c.seller_id = ?
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_listings = $stmt->fetchAll();
    
} elseif ($user_role === 'rental_customer') {
    // Rental customer stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE customer_id = ?");
    $stmt->execute([$user_id]);
    $stats['total_bookings'] = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE customer_id = ? AND status = 'active'");
    $stmt->execute([$user_id]);
    $stats['active_rentals'] = $stmt->fetch()['total'];
    
    // Get recent bookings
    $stmt = $pdo->prepare("
        SELECT b.*, rc.title as car_title, b.name as brand_name, m.name as model_name
        FROM bookings b
        JOIN rental_cars rc ON b.rental_car_id = rc.id
        JOIN car_brands b ON rc.brand_id = b.id
        JOIN car_models m ON rc.model_id = m.id
        WHERE b.customer_id = ?
        ORDER BY b.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_bookings = $stmt->fetchAll();
    
} else {
    // Buyer stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats['wishlist_items'] = $stmt->fetch()['total'];
    
    // Get recently viewed
    $stmt = $pdo->prepare("
        SELECT c.id, c.title, c.price, c.images, b.name as brand_name, m.name as model_name, rv.viewed_at
        FROM recently_viewed rv
        JOIN cars c ON rv.car_id = c.id
        JOIN car_brands b ON c.brand_id = b.id
        JOIN car_models m ON c.model_id = m.id
        WHERE rv.user_id = ?
        ORDER BY rv.viewed_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recently_viewed = $stmt->fetchAll();
}

// Get unread messages count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$stats['unread_messages'] = $stmt->fetch()['total'];
?>
-->
<!--<!DOCTYPE html>
<html lang="en" data-theme="dark">-->
<!--<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AutoMarket Pro</title>
-->
    <!-- Fonts 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    -->
    <!-- Font Awesome 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    -->
    <!-- CSS 
    <link rel="stylesheet" href="assets/css/style.css">
</head>-->
<!--
<body>-->
    <!-- Navigation 
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket Pro
            </a>
            
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="browse-cars.php" class="nav-link">Buy Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>-->
                <?php if ($user_role === 'seller'): ?>
                    <li><a href="sell-car.php" class="nav-link">Sell Car</a></li>
                <?php endif; ?>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            
         <!--   <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                
                <div class="user-menu">
                    <button class="user-avatar">
                        <img src="<?php echo $user['profile_image'] ?: 'assets/images/default-avatar.jpg'; ?>" alt="Profile">
                        <span class="notification-badge" <?php echo $stats['unread_messages'] > 0 ? '' : 'style="display:none;"'; ?>>
                            <?php echo $stats['unread_messages']; ?>
                        </span>
                    </button>
                    
                    <div class="user-dropdown">
                        <div class="user-info">
                            <img src="<?php echo $user['profile_image'] ?: 'assets/images/default-avatar.jpg'; ?>" alt="Profile">
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                <div class="user-role"><?php echo ucfirst($user_role); ?></div>
                            </div>
                        </div>
                        
                        <ul class="user-menu-items">
                            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages <span class="badge"><?php echo $stats['unread_messages']; ?></span></a></li>
                            <?php if ($user_role === 'buyer'): ?>
                                <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                            <?php endif; ?>
                            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                            <li class="divider"></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
-->

    <!-- Dashboard Content 
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
                <p>Here's what's happening with your <?php echo ucfirst($user_role); ?> account</p>
            </div>
-->
            <!-- Stats Grid 
            <div class="stats-grid">
                <?php if ($user_role === 'seller'): ?>
                    <div class="stat-card glass-card animate-fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['active_listings']; ?></div>
                        <div class="stat-label">Active Listings</div>
                    </div>
                    
                    <div class="stat-card glass-card animate-fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['sold_cars']; ?></div>
                        <div class="stat-label">Cars Sold</div>
                    </div>
                    
                    <div class="stat-card glass-card animate-fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['pending_approval']; ?></div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                    
                <?php elseif ($user_role === 'rental_customer'): ?>
                    <div class="stat-card glass-card animate-fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['total_bookings']; ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                    
                    <div class="stat-card glass-card animate-fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-car-side"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['active_rentals']; ?></div>
                        <div class="stat-label">Active Rentals</div>
                    </div>
                    
                <?php else: ?>
                    <div class="stat-card glass-card animate-fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-value"><?php echo $stats['wishlist_items']; ?></div>
                        <div class="stat-label">Wishlist Items</div>
                    </div>
                    
                    <div class="stat-card glass-card animate-fadeInUp">
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-value"><?php echo count($recently_viewed ?? []); ?></div>
                        <div class="stat-label">Recently Viewed</div>
                    </div>
                <?php endif; ?>
                
                <div class="stat-card glass-card animate-fadeInUp">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['unread_messages']; ?></div>
                    <div class="stat-label">Unread Messages</div>
                </div>
            </div>
-->
            <!-- Main Dashboard Grid 
            <div class="dashboard-main-grid">-->
                <!-- Recent Activity 
                <div class="dashboard-section-card glass-card animate-fadeInUp">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                        <a href="#" class="view-all">View All</a>
                    </div>
                    
                    <div class="activity-list">
                        <?php if ($user_role === 'seller' && !empty($recent_listings)): ?>
                            <?php foreach ($recent_listings as $listing): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            Listed: <?php echo htmlspecialchars($listing['title']); ?>
                                        </div>
                                        <div class="activity-meta">
                                            <?php echo htmlspecialchars($listing['brand_name'] . ' ' . $listing['model_name']); ?> • 
                                            <?php echo formatPrice($listing['price']); ?> • 
                                            <?php echo timeAgo($listing['created_at']); ?>
                                        </div>
                                    </div>
                                    <div class="activity-status <?php echo $listing['is_approved'] ? 'approved' : 'pending'; ?>">
                                        <?php echo $listing['is_approved'] ? 'Approved' : 'Pending'; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                        <?php elseif ($user_role === 'rental_customer' && !empty($recent_bookings)): ?>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            Rental: <?php echo htmlspecialchars($booking['car_title']); ?>
                                        </div>
                                        <div class="activity-meta">
                                            <?php echo formatDate($booking['pickup_date']); ?> - <?php echo formatDate($booking['return_date']); ?> • 
                                            <?php echo formatPrice($booking['total_amount']); ?> • 
                                            <?php echo timeAgo($booking['created_at']); ?>
                                        </div>
                                    </div>
                                    <div class="activity-status <?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                        <?php elseif ($user_role === 'buyer' && !empty($recently_viewed)): ?>
                            <?php foreach ($recently_viewed as $car): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            Viewed: <?php echo htmlspecialchars($car['title']); ?>
                                        </div>
                                        <div class="activity-meta">
                                            <?php echo htmlspecialchars($car['brand_name'] . ' ' . $car['model_name']); ?> • 
                                            <?php echo formatPrice($car['price']); ?> • 
                                            <?php echo timeAgo($car['viewed_at']); ?>
                                        </div>
                                    </div>
                                    <div class="activity-action">
                                        <a href="car-details.php?id=<?php echo $car['id']; ?>" class="glass-button">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                        <?php else: ?>
                            <div class="no-activity">
                                <i class="fas fa-inbox"></i>
                                <p>No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
-->
                <!-- Quick Actions -->
                <div class="dashboard-section-card glass-card animate-fadeInUp">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    
                    <div class="quick-actions">
                        <?php if ($user_role === 'seller'): ?>
                            <a href="sell-car.php" class="quick-action-btn">
                                <i class="fas fa-plus"></i>
                                <span>List New Car</span>
                            </a>
                            <a href="my-listings.php" class="quick-action-btn">
                                <i class="fas fa-list"></i>
                                <span>My Listings</span>
                            </a>
                            <a href="analytics.php" class="quick-action-btn">
                                <i class="fas fa-chart-line"></i>
                                <span>Analytics</span>
                            </a>
                            
                        <?php elseif ($user_role === 'rental_customer'): ?>
                            <a href="rentals.php" class="quick-action-btn">
                                <i class="fas fa-search"></i>
                                <span>Browse Rentals</span>
                            </a>
                            <a href="my-bookings.php" class="quick-action-btn">
                                <i class="fas fa-calendar"></i>
                                <span>My Bookings</span>
                            </a>
                            <a href="booking-history.php" class="quick-action-btn">
                                <i class="fas fa-history"></i>
                                <span>Booking History</span>
                            </a>
                            
                        <?php else: ?>
                            <a href="browse-cars.php" class="quick-action-btn">
                                <i class="fas fa-search"></i>
                                <span>Browse Cars</span>
                            </a>
                            <a href="wishlist.php" class="quick-action-btn">
                                <i class="fas fa-heart"></i>
                                <span>My Wishlist</span>
                            </a>
                            <a href="compare.php" class="quick-action-btn">
                                <i class="fas fa-balance-scale"></i>
                                <span>Compare Cars</span>
                            </a>
                        <?php endif; ?>
                        
                        <a href="messages.php" class="quick-action-btn">
                            <i class="fas fa-envelope"></i>
                            <span>Messages</span>
                            <?php if ($stats['unread_messages'] > 0): ?>
                                <span class="badge"><?php echo $stats['unread_messages']; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="profile.php" class="quick-action-btn">
                            <i class="fas fa-user"></i>
                            <span>Edit Profile</span>
                        </a>
                    </div>
                </div>

                <!-- Notifications -->
                <div class="dashboard-section-card glass-card animate-fadeInUp">
                    <div class="card-header">
                        <h3>Notifications</h3>
                        <a href="notifications.php" class="view-all">View All</a>
                    </div>
                    
                    <div class="notifications-list">
                        <div class="notification-item unread">
                            <div class="notification-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Welcome to AutoMarket Pro!</div>
                                <div class="notification-time">2 hours ago</div>
                            </div>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Complete your profile to get started</div>
                                <div class="notification-time">1 day ago</div>
                            </div>
                        </div>
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
        // User dropdown toggle
        document.querySelector('.user-avatar').addEventListener('click', function() {
            document.querySelector('.user-dropdown').classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                document.querySelector('.user-dropdown').classList.remove('active');
            }
        });
    </script>
</body>
</html>
-->
