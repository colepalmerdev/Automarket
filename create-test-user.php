<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>👤 Create Test User</h2>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    // Create test user with known credentials
    $email = 'testuser@automarket.com';
    $password = 'test12345';
    $first_name = 'Test';
    $last_name = 'User';
    $role = 'buyer';
    $username = 'testuser_' . time();
    
    // Delete existing test user if exists
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    // Create new test user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password_hash, role, username) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $first_name,
        $last_name,
        $email,
        '0712345678',
        $password_hash,
        $role,
        $username
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Test user created successfully!</p>";
        echo "<h3>Test User Credentials:</h3>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> $email</li>";
        echo "<li><strong>Password:</strong> $password</li>";
        echo "<li><strong>Name:</strong> $first_name $last_name</li>";
        echo "<li><strong>Role:</strong> $role</li>";
        echo "<li><strong>Username:</strong> $username</li>";
        echo "</ul>";
        
        // Test password verification
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $verify_result = password_verify($password, $user['password_hash']);
            echo "<h3>Password Verification Test:</h3>";
            echo "<p><strong>Verification Result:</strong> " . ($verify_result ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
            
            if ($verify_result) {
                echo "<p style='color: green;'>✅ Login should work!</p>";
                echo "<p><a href='login.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login</a></p>";
            }
        }
        
        // Create admin test user
        $admin_email = 'admin@automarket.com';
        $admin_password = 'admin123';
        $admin_username = 'admin_' . time();
        
        // Delete existing admin if exists
        $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$admin_email]);
        
        $admin_password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone, password_hash, role, username) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $admin_result = $stmt->execute([
            'Admin',
            'User',
            $admin_email,
            '0712345678',
            $admin_password_hash,
            'admin',
            $admin_username
        ]);
        
        if ($admin_result) {
            echo "<h3>Admin Test User Created:</h3>";
            echo "<ul>";
            echo "<li><strong>Email:</strong> $admin_email</li>";
            echo "<li><strong>Password:</strong> $admin_password</li>";
            echo "<li><strong>Role:</strong> admin</li>";
            echo "</ul>";
            echo "<p><a href='login.html' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Admin Login</a></p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Failed to create test user</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Test User</title>
</head>
<body>
    <div style="margin: 20px 0;">
        <a href="login.html">Login</a> | 
        <a href="register-simple.php">Register</a> | 
        <a href="test-login-direct.php">Test Login</a>
    </div>
</body>
</html>