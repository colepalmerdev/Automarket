<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'update_status':
            $booking_id = $_POST['booking_id'];
            $new_status = $_POST['new_status'];
            
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $booking_id]);
            
            header('Location: bookings.php');
            exit();
            break;
            
        case 'update_payment':
            $booking_id = $_POST['booking_id'];
            $payment_status = $_POST['payment_status'];
            
            $stmt = $pdo->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
            $stmt->execute([$payment_status, $booking_id]);
            
            header('Location: bookings.php');
            exit();
            break;
            
        case 'delete_booking':
            $booking_id = $_POST['booking_id'];
            
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            
            header('Location: bookings.php');
            exit();
            break;
    }
}

// Get bookings with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "(rc.title LIKE ? OR customer.first_name LIKE ? OR customer.last_name LIKE ? OR owner.first_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

if (!empty($status_filter)) {
    $where_clause .= ($where_clause ? ' AND ' : 'WHERE ') . "b.status = ?";
    $params[] = $status_filter;
}

// Get total bookings count
$count_sql = "SELECT COUNT(*) as total 
             FROM bookings b 
             JOIN rental_cars rc ON b.rental_car_id = rc.id 
             JOIN users customer ON b.customer_id = customer.id 
             JOIN users owner ON rc.owner_id = owner.id 
             $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_bookings = $stmt->fetch()['total'];
$total_pages = ceil($total_bookings / $per_page);

// Get bookings
$sql = "SELECT b.*, rc.title, rc.daily_rate, 
               customer.first_name as customer_first, customer.last_name as customer_last, customer.email as customer_email,
               owner.first_name as owner_first, owner.last_name as owner_last
        FROM bookings b 
        JOIN rental_cars rc ON b.rental_car_id = rc.id 
        JOIN users customer ON b.customer_id = customer.id 
        JOIN users owner ON rc.owner_id = owner.id 
        $where_clause 
        ORDER BY b.created_at DESC 
        LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - Admin Dashboard</title>
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
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="bookings.php" class="nav-link active">Bookings</a></li>
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
                <h1>Bookings Management</h1>
                <p>Manage all rental bookings and their status</p>
            </div>
        </div>
    </section>

    <!-- Bookings Section -->
    <section class="section">
        <div class="container">
            <!-- Search and Filters -->
            <div class="search-filters glass-card">
                <form method="GET" class="search-form">
                    <div class="search-row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Search bookings by car, customer, or owner..." 
                                   value="<?php echo htmlspecialchars($search); ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="glass-button primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="bookings.php" class="glass-button">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bookings Table -->
            <div class="table-container glass-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Car</th>
                            <th>Customer</th>
                            <th>Owner</th>
                            <th>Dates</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td>
                                    <div class="car-info">
                                        <strong><?php echo htmlspecialchars($booking['title']); ?></strong>
                                        <small>$<?php echo number_format($booking['daily_rate'], 2); ?>/day</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <?php echo htmlspecialchars($booking['customer_first'] . ' ' . $booking['customer_last']); ?>
                                        <small><?php echo htmlspecialchars($booking['customer_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="owner-info">
                                        <?php echo htmlspecialchars($booking['owner_first'] . ' ' . $booking['owner_last']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="dates-info">
                                        <?php echo date('M j', strtotime($booking['pickup_date'])); ?> - 
                                        <?php echo date('M j', strtotime($booking['return_date'])); ?>
                                        <small><?php echo date('Y', strtotime($booking['pickup_date'])); ?></small>
                                    </div>
                                </td>
                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="new_status" class="status-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="active" <?php echo $booking['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_payment">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="payment_status" class="status-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $booking['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $booking['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="refunded" <?php echo $booking['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="glass-button small" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                            <input type="hidden" name="action" value="delete_booking">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
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
                        (<?php echo $total_bookings; ?> total bookings)
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
        function viewBooking(bookingId) {
            // Placeholder for booking detail view
            alert('Booking detail view coming soon for booking ID: ' + bookingId);
        }
    </script>
</body>
</html>
