<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

// Handle rental actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'toggle_approval':
            $rental_id = $_POST['rental_id'];
            $is_approved = $_POST['is_approved'] === 'true' ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE rental_cars SET is_approved = ? WHERE id = ?");
            $stmt->execute([$is_approved, $rental_id]);
            
            header('Location: rentals.php');
            exit();
            break;
            
        case 'toggle_availability':
            $rental_id = $_POST['rental_id'];
            $is_available = $_POST['is_available'] === 'true' ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE rental_cars SET is_available = ? WHERE id = ?");
            $stmt->execute([$is_available, $rental_id]);
            
            header('Location: rentals.php');
            exit();
            break;
            
        case 'delete_rental':
            $rental_id = $_POST['rental_id'];
            
            $stmt = $pdo->prepare("DELETE FROM rental_cars WHERE id = ?");
            $stmt->execute([$rental_id]);
            
            header('Location: rentals.php');
            exit();
            break;
    }
}

// Get rental cars with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "(rc.title LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if (!empty($status_filter)) {
    if ($status_filter === 'approved') {
        $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "rc.is_approved = 1";
    } elseif ($status_filter === 'pending') {
        $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "rc.is_approved = 0";
    } elseif ($status_filter === 'available') {
        $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "rc.is_available = 1";
    } elseif ($status_filter === 'unavailable') {
        $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "rc.is_available = 0";
    }
}

// Get total rentals count
$count_sql = "SELECT COUNT(*) as total FROM rental_cars rc JOIN users u ON rc.owner_id = u.id $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_rentals = $stmt->fetch()['total'];
$total_pages = ceil($total_rentals / $per_page);

// Get rental cars
$sql = "SELECT rc.*, u.first_name, u.last_name, u.email 
        FROM rental_cars rc 
        JOIN users u ON rc.owner_id = u.id 
        $where_clause 
        ORDER BY rc.created_at DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rentals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentals Management - Admin Dashboard</title>
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
                <li><a href="cars.php" class="nav-link">Cars</a></li>
                <li><a href="rentals.php" class="nav-link active">Rentals</a></li>
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
                <h1>Rentals Management</h1>
                <p>Manage all rental car listings and their availability</p>
            </div>
        </div>
    </section>

    <!-- Rentals Section -->
    <section class="section">
        <div class="container">
            <!-- Search and Filters -->
            <div class="search-filters glass-card">
                <form method="GET" class="search-form">
                    <div class="search-row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Search rentals by title or owner..." 
                                   value="<?php echo htmlspecialchars($search); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="unavailable" <?php echo $status_filter === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                        </div>
                        <button type="submit" class="glass-button primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="rentals.php" class="glass-button">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Rentals Table -->
            <div class="table-container glass-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Owner</th>
                            <th>Daily Rate</th>
                            <th>Weekly Rate</th>
                            <th>Monthly Rate</th>
                            <th>Location</th>
                            <th>Approval</th>
                            <th>Available</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $rental): ?>
                            <tr>
                                <td><?php echo $rental['id']; ?></td>
                                <td>
                                    <div class="rental-title-cell">
                                        <strong><?php echo htmlspecialchars($rental['title']); ?></strong>
                                        <small>Year: <?php echo $rental['year']; ?> | <?php echo ucfirst($rental['fuel_type']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="owner-info">
                                        <?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?>
                                        <small><?php echo htmlspecialchars($rental['email']); ?></small>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($rental['daily_rate'], 2); ?></td>
                                <td><?php echo $rental['weekly_rate'] ? '$' . number_format($rental['weekly_rate'], 2) : 'N/A'; ?></td>
                                <td><?php echo $rental['monthly_rate'] ? '$' . number_format($rental['monthly_rate'], 2) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($rental['location']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_approval">
                                        <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                                        <input type="hidden" name="is_approved" value="<?php echo $rental['is_approved']; ?>">
                                        <button type="submit" class="status-toggle <?php echo $rental['is_approved'] ? 'approved' : 'pending'; ?>">
                                            <i class="fas fa-<?php echo $rental['is_approved'] ? 'check' : 'clock'; ?>"></i>
                                            <?php echo $rental['is_approved'] ? 'Approved' : 'Pending'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_availability">
                                        <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
                                        <input type="hidden" name="is_available" value="<?php echo $rental['is_available']; ?>">
                                        <button type="submit" class="status-toggle <?php echo $rental['is_available'] ? 'available' : 'unavailable'; ?>">
                                            <i class="fas fa-<?php echo $rental['is_available'] ? 'check' : 'times'; ?>"></i>
                                            <?php echo $rental['is_available'] ? 'Available' : 'Unavailable'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($rental['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="glass-button small" onclick="viewRental(<?php echo $rental['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this rental listing?');">
                                            <input type="hidden" name="action" value="delete_rental">
                                            <input type="hidden" name="rental_id" value="<?php echo $rental['id']; ?>">
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
                        (<?php echo $total_rentals; ?> total rentals)
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
        function viewRental(rentalId) {
            // Placeholder for rental detail view
            alert('Rental detail view coming soon for rental ID: ' + rentalId);
        }
    </script>
</body>
</html>
