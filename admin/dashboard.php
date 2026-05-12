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
                
                <a href="../index.html" class="glass-button">
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
            <h1>Admin Dashboard</h1>
            <p>Manage your automotive marketplace platform</p>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="section">
        <div class="container">
            <!-- Stats Grid -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="stat-icon cars">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_cars']); ?></div>
                        <div class="stat-label">Cars for Sale</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="stat-icon rentals">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_rentals']); ?></div>
                        <div class="stat-label">Rental Cars</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="stat-icon bookings">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['total_bookings']); ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($stats['pending_cars'] + $stats['pending_rentals']); ?></div>
                        <div class="stat-label">Pending Approvals</div>
                    </div>
                </div>
                
                <div class="admin-stat-card glass-card animate-fadeInUp">
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
                <p>&copy; 2024 AutoMarket Pro. All rights reserved.</p>
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
    </script>
    
    <style>
        .admin-header {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            padding: 6rem 0 3rem;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .admin-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .admin-stat-card {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.users { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.cars { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.rentals { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.bookings { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stat-icon.pending { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .stat-icon.revenue { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .admin-dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        .admin-section-card {
            padding: 2rem;
        }
        
        .activities-list,
        .approvals-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .activity-item,
        .approval-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover,
        .approval-item:hover {
            background: var(--bg-secondary);
        }
        
        .activity-icon,
        .approval-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .approval-icon {
            background: var(--glass-bg);
            color: var(--text-primary);
        }
        
        .activity-content,
        .approval-content {
            flex: 1;
        }
        
        .activity-title,
        .approval-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .activity-meta,
        .approval-meta {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .activity-action,
        .approval-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .approval-actions .glass-button {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        .approval-actions .approve {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-color: #28a745;
        }
        
        .approval-actions .approve:hover {
            background: #28a745;
            color: white;
        }
        
        .approval-actions .reject {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-color: #dc3545;
        }
        
        .approval-actions .reject:hover {
            background: #dc3545;
            color: white;
        }
        
        .no-activities,
        .no-approvals {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        
        .no-activities i,
        .no-approvals i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .quick-action-btn:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .quick-action-btn i {
            font-size: 1.25rem;
        }
        
        .quick-action-btn .badge {
            position: absolute;
            top: -0.5rem;
            right: -0.5rem;
            background: var(--accent-color);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .system-status {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--bg-tertiary);
            border-radius: 8px;
        }
        
        .status-label {
            font-weight: 500;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        
        .status-indicator.online {
            color: #28a745;
        }
        
        .status-indicator.offline {
            color: #dc3545;
        }
        
        .status-indicator i {
            font-size: 0.5rem;
        }
        
        @media (max-width: 1024px) {
            .admin-dashboard-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .admin-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
