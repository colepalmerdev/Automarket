<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set remember me cookie
            if ($remember) {
                $token = generateToken();
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                
                // Store token in database
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);
            }
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: admin/dashboard.php');
            }
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    } catch(PDOException $e) {
        $error = 'Login failed. Please try again.';
    }
}

// Check for remember me cookie
if (isset($_COOKIE['remember_token']) && !isLoggedIn()) {
    $token = $_COOKIE['remember_token'];
    $db = new Database();
    $pdo = $db->getConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND is_active = 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role'];
            
            header('Location: admin/dashboard.php');
            exit();
        }
    } catch(PDOException $e) {
        // Handle error silently
    }
}

// Handle error display
if (isset($error)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $error]);
    exit();
}
?>
