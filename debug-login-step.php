<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>🔍 Debug Login Step by Step</h2>";

// Test database connection
try {
    $db = new Database();
    $pdo = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connected</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>📝 Form Submitted</h3>";
    
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Password:</strong> " . (empty($password) ? '[EMPTY]' : '[FILLED]') . "</p>";
    
    // Validation
    if (empty($email) || empty($password)) {
        echo "<p style='color: red;'>❌ Email and password are required</p>";
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p style='color: red;'>❌ Invalid email format</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Basic validation passed</p>";
    
    try {
        // Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color: green;'>✅ User found in database</p>";
            echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
            echo "<p><strong>User Name:</strong> " . $user['first_name'] . " " . $user['last_name'] . "</p>";
            echo "<p><strong>User Role:</strong> " . $user['role'] . "</p>";
            echo "<p><strong>Password Hash:</strong> " . substr($user['password_hash'], 0, 30) . "...</p>";
            
            // Test password verification
            $verify_result = password_verify($password, $user['password_hash']);
            echo "<p><strong>Password Verify Result:</strong> " . ($verify_result ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
            
            if ($verify_result) {
                echo "<p style='color: green;'>✅ Login should work!</p>";
                
                // Test session creation
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                echo "<p style='color: green;'>✅ Session created!</p>";
                echo "<p><strong>Session Data:</strong></p>";
                echo "<ul>";
                echo "<li>User ID: " . $_SESSION['user_id'] . "</li>";
                echo "<li>Email: " . $_SESSION['user_email'] . "</li>";
                echo "<li>Name: " . $_SESSION['user_name'] . "</li>";
                echo "<li>Role: " . $_SESSION['user_role'] . "</li>";
                echo "</ul>";
                
                // Test redirect
                $redirect_url = ($user['role'] === 'admin') ? 'admin/dashboard.php' : 'index.html';
                echo "<p><strong>Redirect URL:</strong> $redirect_url</p>";
                echo "<p><a href='$redirect_url' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Redirect</a></p>";
                
            } else {
                echo "<p style='color: red;'>❌ Password verification failed</p>";
                
                // Try with test password
                $test_password = 'test12345';
                $test_verify = password_verify($test_password, $user['password_hash']);
                echo "<p><strong>Test with 'test12345':</strong> " . ($test_verify ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
            }
            
        } else {
            echo "<p style='color: red;'>❌ User not found in database</p>";
            
            // Show all users for debugging
            echo "<h3>👥 All Users in Database:</h3>";
            $stmt = $pdo->query("SELECT id, email, first_name, last_name, role FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
            
            if (count($users) > 0) {
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Role</th></tr>";
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . $user['id'] . "</td>";
                    echo "<td>" . $user['email'] . "</td>";
                    echo "<td>" . $user['first_name'] . " " . $user['last_name'] . "</td>";
                    echo "<td>" . $user['role'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No users found. Please create a test user first.</p>";
                echo "<p><a href='create-test-user.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Create Test User</a></p>";
            }
        }
        
    } catch(PDOException $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>Form not submitted. Please submit the form to debug login.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Debug Login</title>
</head>
<body>
    <div style="margin: 20px 0;">
        <h3>🧪 Test Login Form</h3>
        <form method="post" action="debug-login-step.php">
            <div style="margin: 10px 0;">
                <label>Email:</label><br>
                <input type="email" name="email" required style="padding: 5px; margin: 5px 0;">
            </div>
            <div style="margin: 10px 0;">
                <label>Password:</label><br>
                <input type="password" name="password" required style="padding: 5px; margin: 5px 0;">
            </div>
            <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Debug Login</button>
        </form>
        
        <div style="margin: 20px 0;">
            <a href="login.html">Login</a> | 
            <a href="create-test-user.php">Create Test User</a> | 
            <a href="test-login-direct.php">Test Direct Login</a>
        </div>
    </div>
</body>
</html>
