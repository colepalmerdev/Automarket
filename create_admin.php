<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$db = new Database();
$pdo = $db->getConnection();

// Create admin user
$password = 'admin123';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@automarket.com']);
    
    if (!$stmt->fetch()) {
        // Insert admin user
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
            true,
            true
        ]);
        
        if ($result) {
            echo "Admin user created successfully!<br>";
            echo "Email: admin@automarket.com<br>";
            echo "Password: admin123<br>";
            echo "<a href='login.php'>Login Now</a>";
        } else {
            echo "Error creating admin user";
        }
    } else {
        echo "Admin user already exists";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
