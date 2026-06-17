<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

// Get dashboard statistics
$stats = [];

// Total users
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->fetch()['total'];

// Total cars for sale
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cars");
$stmt->execute();
$stats['total_cars'] = $stmt->fetch()['total'];

// Total rental cars
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM rental_cars");
$stmt->execute();
$stats['total_rentals'] = $stmt->fetch()['total'];

// Total bookings
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings");
$stmt->execute();
$stats['total_bookings'] = $stmt->fetch()['total'];

// Pending car approvals
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cars WHERE is_approved = 0");
$stmt->execute();
$stats['pending_cars'] = $stmt->fetch()['total'];

// Pending rental approvals
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM rental_cars WHERE is_approved = 0");
$stmt->execute();
$stats['pending_rentals'] = $stmt->fetch()['total'];

// Active bookings
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE status IN ('confirmed', 'active')");
$stmt->execute();
$stats['active_bookings'] = $stmt->fetch()['total'];

// Today's revenue
$stmt = $pdo->prepare("
    SELECT SUM(amount) as total 
    FROM payments 
    WHERE status = 'completed' 
    AND DATE(created_at) = CURDATE()
");
$stmt->execute();
$stats['today_revenue'] = $stmt->fetch()['total'] ?? 0;

// Recent activities
$recent_activities = [];

// Recent car listings
$stmt = $pdo->prepare("
    SELECT c.*, u.username, 'car_listing' as type
    FROM cars c
    JOIN users u ON c.seller_id = u.id
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->execute();
$car_listings = $stmt->fetchAll();

// Recent bookings
$stmt = $pdo->prepare("
    SELECT b.*, u.username, rc.title as car_title, 'booking' as type
    FROM bookings b
    JOIN users u ON b.customer_id = u.id
    JOIN rental_cars rc ON b.rental_car_id = rc.id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$stmt->execute();
$bookings = $stmt->fetchAll();

// Recent user registrations
$stmt = $pdo->prepare("
    SELECT username, email, role, created_at, 'user_registration' as type
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute();
$user_registrations = $stmt->fetchAll();

// Merge and sort activities
$recent_activities = array_merge($car_listings, $bookings, $user_registrations);
usort($recent_activities, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$recent_activities = array_slice($recent_activities, 0, 10);

// Pending approvals
$stmt = $pdo->prepare("
    SELECT c.*, u.username, 'car' as type
    FROM cars c
    JOIN users u ON c.seller_id = u.id
    WHERE c.is_approved = 0
    ORDER BY c.created_at DESC
    LIMIT 5
");
$stmt->execute();
$pending_cars = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT rc.*, u.username, 'rental' as type
    FROM rental_cars rc
    JOIN users u ON rc.owner_id = u.id
    WHERE rc.is_approved = 0
    ORDER BY rc.created_at DESC
    LIMIT 5
");
$stmt->execute();
$pending_rentals = $stmt->fetchAll();

$pending_approvals = array_merge($pending_cars, $pending_rentals);
usort($pending_approvals, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AutoMarket</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/additional.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-car"></i>
                <span>AutoMarket Admin</span>
            </a>
            
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li><a href="users.php" class="nav-link">Users</a></li>
                <li><a href="cars.php" class="nav-link">Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="bookings.php" class="nav-link">Bookings</a></li>
                <li><a href="analytics.php" class="nav-link">Analytics</a></li>
                <li><a href="settings.php" class="nav-link">Settings</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                
                <a href="index.html" class="glass-button">
                    <i class="fas fa-home"></i> View Site
                </a>
                <a href="../logout.php" class="glass-button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Admin Header -->
    <section class="admin-header">
        <div class="container">
            <div class="admin-breadcrumb" style="margin-bottom: 20px;">
                <a href="dashboard.php" class="breadcrumb-item active">Dashboard</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Overview</span>
            </div>
            <div class="admin-header-content">
                <h1>Admin Dashboard</h1>
                <p>Manage your automotive marketplace platform</p>
                <div class="admin-header-actions">
                    <button class="glass-button" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                    <button  class="glass-button primary" onclick="exportData()" style="margin-left: 10px; background: linear-gradient(135deg, #4CAF50, #81C784); color: white;">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="section">
        <div class="container">
            <!-- Stats Grid -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="skeleton-loader" id="stats-loader-1" style="display: none;">
                        <div class="skeleton-header">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-content">
                                <div class="skeleton-value"></div>
                                <div class="skeleton-label"></div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="skeleton-loader" id="stats-loader-2" style="display: none;">
                        <div class="skeleton-header">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-content">
                                <div class="skeleton-value"></div>
                                <div class="skeleton-label"></div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-icon cars">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_cars']); ?></div>
                        <div class="stat-label">Cars for Sale</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="skeleton-loader" id="stats-loader-3" style="display: none;">
                        <div class="skeleton-header">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-content">
                                <div class="skeleton-value"></div>
                                <div class="skeleton-label"></div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-icon rentals">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_rentals']); ?></div>
                        <div class="stat-label">Rental Cars</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="skeleton-loader" id="stats-loader-4" style="display: none;">
                        <div class="skeleton-header">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-content">
                                <div class="skeleton-value"></div>
                                <div class="skeleton-label"></div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-icon bookings">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_bookings']); ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="skeleton-loader" id="stats-loader-5" style="display: none;">
                        <div class="skeleton-header">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-content">
                                <div class="skeleton-value"></div>
                                <div class="skeleton-label"></div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['pending_cars'] + $stats['pending_rentals']); ?></div>
                        <div class="stat-label">Pending Approvals</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="skeleton-loader" id="stats-loader-6" style="display: none;">
                        <div class="skeleton-header">
                            <div class="skeleton-icon"></div>
                            <div class="skeleton-content">
                                <div class="skeleton-value"></div>
                                <div class="skeleton-label"></div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo formatPrice($stats['today_revenue']); ?></div>
                        <div class="stat-label">Today's Revenue</div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="admin-dashboard-grid">
                <!-- Recent Activities -->
                <div class="admin-section-card glass-card animate-fadeInUp">
                    <div class="card-header">
                        <h3>Recent Activities</h3>
                        <a href="activities.php" class="view-all">View All</a>
                    </div>
                    
                    <div class="activities-list">
                        <?php if (empty($recent_activities)): ?>
                            <div class="no-activities">
                                <i class="fas fa-inbox"></i>
                                <p>No recent activities</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <?php if ($activity['type'] === 'car_listing'): ?>
                                            <i class="fas fa-car"></i>
                                        <?php elseif ($activity['type'] === 'booking'): ?>
                                            <i class="fas fa-calendar-check"></i>
                                        <?php elseif ($activity['type'] === 'user_registration'): ?>
                                            <i class="fas fa-user-plus"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">
                                            <?php if ($activity['type'] === 'car_listing'): ?>
                                                New car listing: <?php echo htmlspecialchars($activity['title']); ?>
                                            <?php elseif ($activity['type'] === 'booking'): ?>
                                                New booking: <?php echo htmlspecialchars($activity['car_title']); ?>
                                            <?php elseif ($activity['type'] === 'user_registration'): ?>
                                                New user registration: <?php echo htmlspecialchars($activity['username']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-meta">
                                            By <?php echo htmlspecialchars($activity['username']); ?> • 
                                            <?php echo timeAgo($activity['created_at']); ?>
                                        </div>
                                    </div>
                                    <div class="activity-action">
                                        <?php if ($activity['type'] === 'car_listing'): ?>
                                            <a href="../car-details.php?id=<?php echo $activity['id']; ?>" class="glass-button">View</a>
                                        <?php elseif ($activity['type'] === 'booking'): ?>
                                            <a href="booking-details.php?id=<?php echo $activity['id']; ?>" class="glass-button">View</a>
                                        <?php elseif ($activity['type'] === 'user_registration'): ?>
                                            <a href="user-details.php?id=<?php echo $activity['id']; ?>" class="glass-button">View</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="admin-section-card glass-card animate-fadeInUp">
                    <div class="card-header">
                        <h3>Pending Approvals</h3>
                        <a href="approvals.php" class="view-all">View All</a>
                    </div>
                    
                    <div class="approvals-list">
                        <?php if (empty($pending_approvals)): ?>
                            <div class="no-approvals">
                                <i class="fas fa-check-circle"></i>
                                <p>No pending approvals</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pending_approvals as $item): ?>
                                <div class="approval-item">
                                    <div class="approval-icon">
                                        <?php if ($item['type'] === 'car'): ?>
                                            <i class="fas fa-car"></i>
                                        <?php else: ?>
                                            <i class="fas fa-calendar"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="approval-content">
                                        <div class="approval-title">
                                            <?php if ($item['type'] === 'car'): ?>
                                                Car: <?php echo htmlspecialchars($item['title']); ?>
                                            <?php else: ?>
                                                Rental: <?php echo htmlspecialchars($item['title']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="approval-meta">
                                            By <?php echo htmlspecialchars($item['username']); ?> • 
                                            <?php echo timeAgo($item['created_at']); ?>
                                        </div>
                                    </div>
                                    <div class="approval-actions">
                                        <button class="glass-button approve" onclick="approveItem('<?php echo $item['type']; ?>', <?php echo $item['id']; ?>)">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="glass-button reject" onclick="rejectItem('<?php echo $item['type']; ?>', <?php echo $item['id']; ?>)">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="admin-section-card glass-card animate-fadeInUp">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    
                    <div class="quick-actions">
                        <a href="users.php" class="quick-action-btn">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="cars.php" class="quick-action-btn">
                            <i class="fas fa-car"></i>
                            <span>Manage Cars</span>
                        </a>
                        <a href="rentals.php" class="quick-action-btn">
                            <i class="fas fa-calendar"></i>
                            <span>Manage Rentals</span>
                        </a>
                        <a href="bookings.php" class="quick-action-btn">
                            <i class="fas fa-clipboard-check"></i>
                            <span>View Bookings</span>
                        </a>
                        <a href="approvals.php" class="quick-action-btn">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Pending Approvals</span>
                            <?php if ($stats['pending_cars'] + $stats['pending_rentals'] > 0): ?>
                                <span class="badge"><?php echo $stats['pending_cars'] + $stats['pending_rentals']; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="analytics.php" class="quick-action-btn">
                            <i class="fas fa-chart-line"></i>
                            <span>Analytics</span>
                        </a>
                        <a href="reports.php" class="quick-action-btn">
                            <i class="fas fa-file-alt"></i>
                            <span>Reports</span>
                        </a>
                        <a href="settings.php" class="quick-action-btn">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>

                <!-- System Status -->
                <div class="admin-section-card glass-card animate-fadeInUp">
                    <div class="card-header">
                        <h3>System Status</h3>
                    </div>
                    
                    <div class="system-status">
                        <div class="status-item">
                            <div class="status-label">Database Connection</div>
                            <div class="status-indicator online">
                                <i class="fas fa-circle"></i> Online
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">File Upload System</div>
                            <div class="status-indicator online">
                                <i class="fas fa-circle"></i> Online
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">Email Service</div>
                            <div class="status-indicator online">
                                <i class="fas fa-circle"></i> Online
                            </div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">Payment Processing</div>
                            <div class="status-indicator online">
                                <i class="fas fa-circle"></i> Online
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
                <p>&copy; 2026 AutoMarket. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="../assets/js/script.js"></script>
    
    <script>
        // Approve item
        function approveItem(type, id) {
            if (confirm('Are you sure you want to approve this ' + type + '?')) {
                fetch('api/approve.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        type: type,
                        id: id,
                        action: 'approve'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Item approved successfully!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Approval failed', 'error');
                    }
                })
                .catch(error => {
                    showToast('Network error. Please try again.', 'error');
                });
            }
        }
        
        // Reject item
        function rejectItem(type, id) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                fetch('api/approve.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        type: type,
                        id: id,
                        action: 'reject',
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Item rejected successfully!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(data.message || 'Rejection failed', 'error');
                    }
                })
                .catch(error => {
                    showToast('Network error. Please try again.', 'error');
                });
            }
        }
        
        // Refresh dashboard data
        function refreshDashboard() {
            showToast('Refreshing dashboard data...', 'info');
            showSkeletonLoaders();
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
        
        // Show skeleton loaders when refreshing
        function showSkeletonLoaders() {
            const loaders = document.querySelectorAll('.skeleton-loader');
            loaders.forEach(loader => {
                loader.style.display = 'flex';
            });
        }
        
        // Hide skeleton loaders after page loads
        document.addEventListener('DOMContentLoaded', function() {
            const brandSelect = document.querySelector('select[name="brand"]');
            if (brandSelect && brandSelect.value) {
                updateModels(brandSelect.value);
            }
            
            // Hide skeleton loaders after page loads
            setTimeout(() => {
                const loaders = document.querySelectorAll('.skeleton-loader');
                loaders.forEach(loader => {
                    loader.style.display = 'none';
                });
            }, 1500);
        });
        
        // Export dashboard data
        function exportData() {
            showToast('Preparing export...', 'info');
            
            // Create CSV data for statistics
            const csvContent = [
                ['Metric', 'Value'],
                ['Total Users', '<?php echo $stats['total_users']; ?>'],
                ['Cars for Sale', '<?php echo $stats['total_cars']; ?>'],
                ['Rental Cars', '<?php echo $stats['total_rentals']; ?>'],
                ['Total Bookings', '<?php echo $stats['total_bookings']; ?>'],
                ['Pending Approvals', '<?php echo $stats['pending_cars'] + $stats['pending_rentals']; ?>'],
                ['Today\'s Revenue', '<?php echo formatPrice($stats['today_revenue']); ?>']
            ].map(row => row.join(',')).join('\n');
            
            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'dashboard-export-' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showToast('Dashboard data exported successfully!', 'success');
        }
    </script>
</body>
</html>
