<?php
echo "<h2>🔍 Debug Form Submission</h2>";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>📝 Form Submitted Successfully!</h3>";
    echo "<p><strong>POST Data Received:</strong></p>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Check each field
    $email = $_POST['email'] ?? 'NOT SET';
    $password = $_POST['password'] ?? 'NOT SET';
    $remember = $_POST['remember'] ?? 'NOT SET';
    
    echo "<h3>📋 Field Values:</h3>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($email) . "</li>";
    echo "<li><strong>Password:</strong> " . (empty($password) ? '[EMPTY]' : '[FILLED]') . "</li>";
    echo "<li><strong>Remember:</strong> " . ($remember ? 'YES' : 'NO') . "</li>";
    echo "</ul>";
    
    // Test authentication if email and password are provided
    if ($email !== 'NOT SET' && $password !== 'NOT SET') {
        echo "<h3>🔑 Testing Authentication</h3>";
        
        try {
            require_once 'config/database.php';
            require_once 'includes/functions.php';
            
            $db = new Database();
            $pdo = $db->getConnection();
            
            // Find user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo "<p style='color: green;'>✅ User found in database</p>";
                
                if (password_verify($password, $user['password_hash'])) {
                    echo "<p style='color: green;'>✅ Password verification SUCCESS</p>";
                    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 LOGIN WORKS!</p>";
                } else {
                    echo "<p style='color: red;'>❌ Password verification FAILED</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ User not found in database</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<h3>⚠️ Form Not Submitted</h3>";
    echo "<p>Please submit the form first.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Debug Form Submission</title>
</head>
<body>
    <div style="margin: 20px 0;">
        <h3>🧪 Test Form</h3>
        <form method="post" action="debug-form-submission.php">
            <div style="margin: 10px 0;">
                <label>Email:</label><br>
                <input type="email" name="email" required style="padding: 5px; margin: 5px 0;">
            </div>
            <div style="margin: 10px 0;">
                <label>Password:</label><br>
                <input type="password" name="password" required style="padding: 5px; margin: 5px 0;">
            </div>
            <div style="margin: 10px 0;">
                <label>Remember:</label><br>
                <input type="checkbox" name="remember" style="margin: 5px 0;">
            </div>
            <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px;">Submit Form</button>
        </form>
        
        <div style="margin: 20px 0;">
            <a href="login-direct.php">Try Login Direct</a> | 
            <a href="login.html">Try Login Form</a> | 
            <a href="create-test-user.php">Create Test User</a>
        </div>
    </div>
</body>
</html>
