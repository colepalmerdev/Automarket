<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Fix Admin Login Issue</h2>";

$db = new Database();
$pdo = $db->getConnection();

// Delete existing admin user and create new one
try {
    echo "<p>Removing existing admin user...</p>";
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute(['admin@automarket.com']);
    
    echo "<p>Creating new admin user...</p>";
    
    // Create admin with simple password
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
        echo "<p style='color: green; font-size: 18px;'>✓ Admin user created successfully!</p>";
        echo "<div style='background: #f0f0f0; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Login Credentials:</h3>";
        echo "<p><strong>Email:</strong> admin@automarket.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Link:</strong> <a href='login.php' style='color: #007bff;'>Go to Login</a></p>";
        echo "</div>";
        
        // Test the login immediately
        echo "<h3>Testing Login...</h3>";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute(['admin@automarket.com']);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p>✓ User found in database</p>";
            if (password_verify('admin123', $user['password_hash'])) {
                echo "<p style='color: green;'>✓ Password verification SUCCESS!</p>";
                echo "<p style='color: green; font-size: 16px;'><strong>Admin login should work now!</strong></p>";
                echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Login Now</a></p>";
            } else {
                echo "<p style='color: red;'>✗ Password verification FAILED!</p>";
                echo "<p>This shouldn't happen. There might be a database issue.</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ User not found after creation!</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Failed to create admin user</p>";
        echo "<p>Error: " . print_r($stmt->errorInfo(), true) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
