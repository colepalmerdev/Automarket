<?php
echo "<h2>Database Connection Check</h2>";

// Test database connection
try {
    require_once 'config/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check if users table exists
try {
    $stmt = $pdo->query("DESCRIBE users");
    echo "<p style='color: green;'>✓ Users table exists</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Users table not found: " . $e->getMessage() . "</p>";
    exit;
}

// Check all users
echo "<h3>All Users in Database:</h3>";
$stmt = $pdo->query("SELECT id, username, email, role, is_active FROM users ORDER BY id");
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "<p>No users found in database. Creating admin user...</p>";
    
    $password = 'admin123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_verified, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        'admin',
        'admin@automarket.com',
        $password_hash,
        'Admin',
        'User',
        'admin',
        1,
        1
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Admin user created successfully!</p>";
        
        // Verify creation
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['admin@automarket.com']);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p>Verification - User ID: {$user['id']}</p>";
            echo "<p>Verification - Email: {$user['email']}</p>";
            echo "<p>Verification - Role: {$user['role']}</p>";
            echo "<p>Verification - Active: " . ($user['is_active'] ? 'Yes' : 'No') . "</p>";
            echo "<p>Verification - Password hash: " . substr($user['password_hash'], 0, 20) . "...</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin user</p>";
    }
} else {
    foreach ($users as $user) {
        $status = $user['is_active'] ? 'Active' : 'Inactive';
        echo "<p>ID: {$user['id']} | Username: {$user['username']} | Email: {$user['email']} | Role: {$user['role']} | Status: {$status}</p>";
    }
}

// Test password verification
echo "<h3>Password Verification Test:</h3>";
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['admin@automarket.com']);
$user = $stmt->fetch();

if ($user) {
    $password = 'admin123';
    $verify_result = password_verify($password, $user['password_hash']);
    echo "<p>Password 'admin123' verification: " . ($verify_result ? '✓ Success' : '✗ Failed') . "</p>";
    
    if (!$verify_result) {
        echo "<p>Testing with different passwords...</p>";
        $test_passwords = ['password', 'admin', '123456', 'test'];
        foreach ($test_passwords as $test_pass) {
            if (password_verify($test_pass, $user['password_hash'])) {
                echo "<p style='color: green;'>✓ Password is: '{$test_pass}'</p>";
                break;
            }
        }
    }
} else {
    echo "<p style='color: red;'>No admin user found to test password</p>";
}

// Test login function
echo "<h3>Login Function Test:</h3>";
if (isset($_POST['test_login'])) {
    $email = $_POST['email'] ?? 'admin@automarket.com';
    $password = $_POST['password'] ?? 'admin123';
    
    echo "<p>Testing login with Email: {$email}, Password: {$password}</p>";
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✓ User found in database</p>";
        
        if (password_verify($password, $user['password_hash'])) {
            echo "<p style='color: green;'>✓ Password verification successful!</p>";
            echo "<p>User Role: {$user['role']}</p>";
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            
            echo "<p style='color: green;'>✓ Session set successfully!</p>";
            echo "<p><a href='admin/dashboard.php'>Try Admin Dashboard</a></p>";
        } else {
            echo "<p style='color: red;'>✗ Password verification failed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ User not found or inactive</p>";
    }
} else {
    echo "<form method='post'>";
    echo "<input type='hidden' name='test_login' value='1'>";
    echo "<button type='submit'>Test Admin Login</button>";
    echo "</form>";
}
?>
