<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'toggle_status':
            $user_id = $_POST['user_id'];
            $is_active = $_POST['is_active'] === 'true' ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$is_active, $user_id]);
            
            header('Location: users.php');
            exit();
            break;
            
        case 'toggle_verification':
            $user_id = $_POST['user_id'];
            $is_verified = $_POST['is_verified'] === 'true' ? 0 : 1;
            
            $stmt = $pdo->prepare("UPDATE users SET is_verified = ? WHERE id = ?");
            $stmt->execute([$is_verified, $user_id]);
            
            header('Location: users.php');
            exit();
            break;
            
        case 'delete_user':
            $user_id = $_POST['user_id'];
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            header('Location: users.php');
            exit();
            break;
    }
}

// Get users with pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

// Get total users count
$count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_users = $stmt->fetch()['total'];
$total_pages = ceil($total_users / $per_page);

// Get users
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin Dashboard</title>
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
                <li><a href="users.php" class="nav-link active">Users</a></li>
                <li><a href="cars.php" class="nav-link">Cars</a></li>
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
                <h1>Users Management</h1>
                <p>Manage all registered users and their accounts</p>
            </div>
        </div>
    </section>

    <!-- Users Section -->
    <section class="section">
        <div class="container">
            <!-- Search and Filters -->
            <div class="search-filters glass-card">
                <form method="GET" class="search-form">
                    <div class="search-row">
                        <div class="form-group">
                            <input type="text" name="search" placeholder="Search users by name or email..." 
                                   value="<?php echo htmlspecialchars($search); ?>" class="form-input">
                        </div>
                        <button type="submit" class="glass-button primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="users.php" class="glass-button">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="table-container glass-card">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Verified</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="is_active" value="<?php echo $user['is_active']; ?>">
                                        <button type="submit" class="status-toggle <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                            <i class="fas fa-<?php echo $user['is_active'] ? 'check' : 'times'; ?>"></i>
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_verification">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="is_verified" value="<?php echo $user['is_verified']; ?>">
                                        <button type="submit" class="status-toggle <?php echo $user['is_verified'] ? 'verified' : 'unverified'; ?>">
                                            <i class="fas fa-<?php echo $user['is_verified'] ? 'check' : 'times'; ?>"></i>
                                            <?php echo $user['is_verified'] ? 'Verified' : 'Not Verified'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="glass-button small" onclick="viewUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
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
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="glass-button">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <span class="page-info">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?> 
                        (<?php echo $total_users; ?> total users)
                    </span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="glass-button">
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
        function viewUser(userId) {
            // Placeholder for user detail view
            alert('User detail view coming soon for user ID: ' + userId);
        }
    </script>
</body>
</html>
