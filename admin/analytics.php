<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

// Get analytics data
$period = $_GET['period'] ?? '30'; // days

// Revenue analytics
$revenue_sql = "
    SELECT DATE(created_at) as date, SUM(amount) as revenue 
    FROM payments 
    WHERE status = 'completed' 
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
";
$stmt = $pdo->prepare($revenue_sql);
$stmt->execute();
$revenue_data = $stmt->fetchAll();

// User registration analytics
$users_sql = "
    SELECT DATE(created_at) as date, COUNT(*) as users 
    FROM users 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
";
$stmt = $pdo->prepare($users_sql);
$stmt->execute();
$users_data = $stmt->fetchAll();

// Car listings analytics
$cars_sql = "
    SELECT DATE(created_at) as date, COUNT(*) as cars 
    FROM cars 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
";
$stmt = $pdo->prepare($cars_sql);
$stmt->execute();
$cars_data = $stmt->fetchAll();

// Booking analytics
$bookings_sql = "
    SELECT DATE(created_at) as date, COUNT(*) as bookings 
    FROM bookings 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL $period DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
";
$stmt = $pdo->prepare($bookings_sql);
$stmt->execute();
$bookings_data = $stmt->fetchAll();

// Summary statistics
$total_revenue = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch()['total'] ?? 0;
$total_users = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'];
$total_cars = $pdo->query("SELECT COUNT(*) as total FROM cars")->fetch()['total'];
$total_bookings = $pdo->query("SELECT COUNT(*) as total FROM bookings")->fetch()['total'];

// Top performing cars
$top_cars_sql = "
    SELECT c.title, COUNT(b.id) as bookings, SUM(b.total_amount) as revenue
    FROM cars c
    LEFT JOIN bookings b ON c.id = b.rental_car_id
    GROUP BY c.id
    ORDER BY bookings DESC
    LIMIT 10
";
$stmt = $pdo->prepare($top_cars_sql);
$stmt->execute();
$top_cars = $stmt->fetchAll();

// User distribution by role
$role_distribution = $pdo->query("
    SELECT role, COUNT(*) as count 
    FROM users 
    GROUP BY role
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket Admin
            </a>
            
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="users.php" class="nav-link">Users</a></li>
                <li><a href="cars.php" class="nav-link">Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="bookings.php" class="nav-link">Bookings</a></li>
                <li><a href="analytics.php" class="nav-link active">Analytics</a></li>
                <li><a href="settings.php" class="nav-link">Settings</a></li>
            </ul>
            
            <div class="nav-actions">
                <a href="../index.html" class="glass-button">
                    <i class="fas fa-home"></i> View Site
                </a>
                <a href="../logout.php" class="glass-button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Analytics Dashboard</h1>
                <p>View detailed analytics and performance metrics</p>
            </div>
        </div>
    </section>

    <!-- Analytics Section -->
    <section class="section">
        <div class="container">
            <!-- Period Selector -->
            <div class="period-selector glass-card">
                <form method="GET">
                    <div class="form-group">
                        <label for="period">Time Period:</label>
                        <select name="period" id="period" class="form-select" onchange="this.form.submit()">
                            <option value="7" <?php echo $period == '7' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30" <?php echo $period == '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90" <?php echo $period == '90' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="365" <?php echo $period == '365' ? 'selected' : ''; ?>>Last Year</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card glass-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                
                <div class="stat-card glass-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card glass-card">
                    <div class="stat-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_cars); ?></div>
                    <div class="stat-label">Car Listings</div>
                </div>
                
                <div class="stat-card glass-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_bookings); ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- Revenue Chart -->
                <div class="chart-card glass-card">
                    <h3>Revenue Trend</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                
                <!-- User Registrations Chart -->
                <div class="chart-card glass-card">
                    <h3>User Registrations</h3>
                    <canvas id="usersChart"></canvas>
                </div>
                
                <!-- Car Listings Chart -->
                <div class="chart-card glass-card">
                    <h3>Car Listings</h3>
                    <canvas id="carsChart"></canvas>
                </div>
                
                <!-- Bookings Chart -->
                <div class="chart-card glass-card">
                    <h3>Bookings</h3>
                    <canvas id="bookingsChart"></canvas>
                </div>
            </div>

            <!-- Additional Analytics -->
            <div class="analytics-grid">
                <!-- Top Cars -->
                <div class="analytics-card glass-card">
                    <h3>Top Performing Cars</h3>
                    <div class="top-cars-list">
                        <?php foreach ($top_cars as $car): ?>
                            <div class="top-car-item">
                                <div class="car-info">
                                    <strong><?php echo htmlspecialchars($car['title']); ?></strong>
                                    <small><?php echo $car['bookings']; ?> bookings</small>
                                </div>
                                <div class="car-revenue">
                                    $<?php echo number_format($car['revenue'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- User Distribution -->
                <div class="analytics-card glass-card">
                    <h3>User Distribution by Role</h3>
                    <canvas id="roleChart"></canvas>
                </div>
                
                <!-- Recent Activity -->
                <div class="analytics-card glass-card">
                    <h3>Recent Activity</h3>
                    <div class="activity-list">
                        <?php
                        // Get recent activities
                        $activities = $pdo->query("
                            SELECT 'User Registration' as type, created_at, CONCAT(first_name, ' ', last_name) as details 
                            FROM users 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ")->fetchAll();
                        
                        foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-type">
                                    <i class="fas fa-user-plus"></i>
                                    <?php echo $activity['type']; ?>
                                </div>
                                <div class="activity-details">
                                    <?php echo htmlspecialchars($activity['details']); ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 AutoMarket Admin Panel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Prepare data for charts
        const revenueData = <?php echo json_encode(array_column($revenue_data, 'revenue')); ?>;
        const usersData = <?php echo json_encode(array_column($users_data, 'users')); ?>;
        const carsData = <?php echo json_encode(array_column($cars_data, 'cars')); ?>;
        const bookingsData = <?php echo json_encode(array_column($bookings_data, 'bookings')); ?>;
        const dates = <?php echo json_encode(array_column($revenue_data, 'date')); ?>;
        
        const roleData = <?php echo json_encode($role_distribution); ?>;
        
        // Revenue Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
        // Users Chart
        new Chart(document.getElementById('usersChart'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'New Users',
                    data: usersData,
                    backgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
        // Cars Chart
        new Chart(document.getElementById('carsChart'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Car Listings',
                    data: carsData,
                    backgroundColor: '#f59e0b'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
        // Bookings Chart
        new Chart(document.getElementById('bookingsChart'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Bookings',
                    data: bookingsData,
                    backgroundColor: '#8b5cf6'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
        
        // Role Distribution Chart
        new Chart(document.getElementById('roleChart'), {
            type: 'doughnut',
            data: {
                labels: roleData.map(r => r.role),
                datasets: [{
                    data: roleData.map(r => r.count),
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
