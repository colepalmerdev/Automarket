<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>🧪 Simple Login Test</h2>";

// Create test user first
try {
    $db = new Database();
    $pdo = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connected</p>";
    
    // Create a simple test user
    $email = 'simpletest@example.com';
    $password = 'test12345';
    $first_name = 'Simple';
    $last_name = 'Test';
    $role = 'buyer';
    $username = 'simpletest_' . time();
    
    // Delete if exists
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    // Create user
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password_hash, role, username) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $first_name,
        $last_name,
        $email,
        '1234567890',
        $password_hash,
        $role,
        $username
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Test user created!</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        
        // Now test login
        echo "<h3>🔑 Testing Login</h3>";
        
        // Simulate login
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color: green;'>✅ User found</p>";
            
            if (password_verify($password, $user['password_hash'])) {
                echo "<p style='color: green;'>✅ Password verification SUCCESS</p>";
                echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 LOGIN WORKS!</p>";
                
                // Create session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                echo "<p style='color: green;'>✅ Session created</p>";
                echo "<p><strong>Session Data:</strong></p>";
                echo "<ul>";
                echo "<li>User ID: " . $_SESSION['user_id'] . "</li>";
                echo "<li>Email: " . $_SESSION['user_email'] . "</li>";
                echo "<li>Name: " . $_SESSION['user_name'] . "</li>";
                echo "<li>Role: " . $_SESSION['user_role'] . "</li>";
                echo "</ul>";
                
                echo "<p><a href='index.html' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Main Site</a></p>";
                
            } else {
                echo "<p style='color: red;'>❌ Password verification FAILED</p>";
                
                // Debug password hash
                echo "<p><strong>Stored Hash:</strong> " . $user['password_hash'] . "</p>";
                echo "<p><strong>Test Password:</strong> $password</p>";
                echo "<p><strong>Test Hash:</strong> " . password_hash($password, PASSWORD_DEFAULT) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User not found</p>";
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
    <title>Simple Login Test</title>
</head>
<body>
    <div style="margin: 20px 0;">
        <h3>📋 Test Results Summary</h3>
        <p>If you see "LOGIN WORKS!" above, then the authentication system is working correctly.</p>
        <p>The issue might be in the form submission or JavaScript handling.</p>
        
        <div style="margin: 20px 0;">
            <a href="login.html">Try Login Form</a> | 
            <a href="create-test-user.php">Create Test Users</a> | 
            <a href="debug-login-step.php">Debug Login</a>
        </div>
    </div>
</body>
</html>
