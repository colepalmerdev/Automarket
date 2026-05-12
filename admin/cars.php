<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

// Handle car actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'toggle_approval':
            $car_id = $_POST['car_id'];
            $is_approved = $_POST['is_approved'] === 'true' ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE cars SET is_approved = ? WHERE id = ?");
            $stmt->execute([$is_approved, $car_id]);
            
            header('Location: cars.php');
            exit();
            break;
            
        case 'toggle_featured':
            $car_id = $_POST['car_id'];
            $is_featured = $_POST['is_featured'] === 'true' ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE cars SET is_featured = ? WHERE id = ?");
            $stmt->execute([$is_featured, $car_id]);
            
            header('Location: cars.php');
            exit();
            break;
            
        case 'toggle_sold':
            $car_id = $_POST['car_id'];
            $is_sold = $_POST['is_sold'] === 'true' ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE cars SET is_sold = ? WHERE id = ?");
            $stmt->execute([$is_sold, $car_id]);
            
            header('Location: cars.php');
            exit();
            break;
            
        case 'delete_car':
            $car_id = $_POST['car_id'];
            
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$car_id]);
            
            header('Location: cars.php');
            exit();
            break;
    }
}

// Get cars with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "(c.title LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if (!empty($status_filter)) {
    $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "c.is_approved = ?";
    $params[] = $status_filter === 'approved' ? 1 : 0;
}

// Get total cars count
$count_sql = "SELECT COUNT(*) as total FROM cars c JOIN users u ON c.seller_id = u.id $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_cars = $stmt->fetch()['total'];
$total_pages = ceil($total_cars / $per_page);

// Get cars
$sql = "SELECT c.*, u.first_name, u.last_name, u.email 
        FROM cars c 
        JOIN users u ON c.seller_id = u.id 
        $where_clause 
        ORDER BY c.created_at DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars Management - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <li><a href="cars.php" class="nav-link active">Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="bookings.php" class="nav-link">Bookings</a></li>
                <li><a href="analytics.php" class="nav-link">Analytics</a></li>
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
                <h1>Cars Management</h1>
                <p>Manage all car listings and their status</p>
            </div>
        </div>
    </section>

    <!-- Cars Section -->
    <section class="section">
        <div class="container">
            <!-- Search and Filters -->
            <div class="search-filters glass-card">
                <form method="GET" class="search-form">
                    <div class="search-row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Search cars by title or seller..." 
                                   value="<?php echo htmlspecialchars($search); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                        <button type="submit" class="glass-button primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="cars.php" class="glass-button">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Cars Table -->
            <div class="table-container glass-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Sold</th>
                            <th>Views</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?php echo $car['id']; ?></td>
                                <td>
                                    <div class="car-title-cell">
                                        <strong><?php echo htmlspecialchars($car['title']); ?></strong>
                                        <small><?php echo htmlspecialchars($car['location']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="seller-info">
                                        <?php echo htmlspecialchars($car['first_name'] . ' ' . $car['last_name']); ?>
                                        <small><?php echo htmlspecialchars($car['email']); ?></small>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($car['price'], 2); ?></td>
                                <td><?php echo $car['year']; ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_approval">
                                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                        <input type="hidden" name="is_approved" value="<?php echo $car['is_approved']; ?>">
                                        <button type="submit" class="status-toggle <?php echo $car['is_approved'] ? 'approved' : 'pending'; ?>">
                                            <i class="fas fa-<?php echo $car['is_approved'] ? 'check' : 'clock'; ?>"></i>
                                            <?php echo $car['is_approved'] ? 'Approved' : 'Pending'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                        <input type="hidden" name="is_featured" value="<?php echo $car['is_featured']; ?>">
                                        <button type="submit" class="status-toggle <?php echo $car['is_featured'] ? 'featured' : 'normal'; ?>">
                                            <i class="fas fa-<?php echo $car['is_featured'] ? 'star' : 'star'; ?>"></i>
                                            <?php echo $car['is_featured'] ? 'Featured' : 'Normal'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_sold">
                                        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                        <input type="hidden" name="is_sold" value="<?php echo $car['is_sold']; ?>">
                                        <button type="submit" class="status-toggle <?php echo $car['is_sold'] ? 'sold' : 'available'; ?>">
                                            <i class="fas fa-<?php echo $car['is_sold'] ? 'check' : 'times'; ?>"></i>
                                            <?php echo $car['is_sold'] ? 'Sold' : 'Available'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo number_format($car['views_count']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($car['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="glass-button small" onclick="viewCar(<?php echo $car['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this car listing?');">
                                            <input type="hidden" name="action" value="delete_car">
                                            <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                            <button type="submit" class="glass-button small danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="glass-button">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <span class="page-info">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?> 
                        (<?php echo $total_cars; ?> total cars)
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="glass-button">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
        function viewCar(carId) {
            // Placeholder for car detail view
            alert('Car detail view coming soon for car ID: ' + carId);
        }
    </script>
</body>
</html>
