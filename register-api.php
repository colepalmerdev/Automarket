<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';
require_once 'includes/functions.php';

$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $response['message'] = 'Invalid request data';
        echo json_encode($response);
        exit;
    }
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response['message'] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            echo json_encode($response);
            exit;
        }
    }
    
    // Extract and clean data
    $first_name = cleanInput($data['first_name']);
    $last_name = cleanInput($data['last_name']);
    $email = cleanInput($data['email']);
    $phone = cleanInput($data['phone'] ?? '');
    $password = $data['password'];
    $confirm_password = $data['confirm_password'];
    $role = cleanInput($data['role'] ?? 'buyer');
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
        echo json_encode($response);
        exit;
    }
    
    // Validate password match
    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match';
        echo json_encode($response);
        exit;
    }
    
    // Validate password strength
    if (strlen($password) < 6) {
        $response['message'] = 'Password must be at least 6 characters long';
        echo json_encode($response);
        exit;
    }
    
    // Connect to database
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $response['message'] = 'Email already exists';
        echo json_encode($response);
        exit;
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate verification token
    $verification_token = generateToken();
    
    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password_hash, role, is_verified, verification_token, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $first_name,
        $last_name,
        $email,
        $phone,
        $password_hash,
        $role,
        false,
        $verification_token
    ]);
    
    if ($result) {
        $user_id = $pdo->lastInsertId();
        
        // Try to send verification email (optional - won't fail if email doesn't work)
        try {
            $verification_link = "http://localhost/cars/verify.php?token=" . $verification_token;
            $email_subject = "Verify Your AutoMarket Account";
            $email_body = "
                <h2>Welcome to AutoMarket!</h2>
                <p>Hi $first_name $last_name,</p>
                <p>Thank you for registering with AutoMarket. Please click the link below to verify your email address:</p>
                <p><a href='$verification_link'>Verify Email</a></p>
                <p>If you didn't create this account, please ignore this email.</p>
                <p>Best regards,<br>AutoMarket Team</p>
            ";
            
            sendEmail($email, $email_subject, $email_body);
        } catch (Exception $e) {
            // Email sending failed but user is still registered
            error_log("Email sending failed: " . $e->getMessage());
        }
        
        $response['success'] = true;
        $response['message'] = 'Registration successful! Please check your email for verification.';
        $response['redirect'] = 'login.html';
        
    } else {
        $response['message'] = 'Registration failed. Please try again.';
    }
    
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    error_log("Registration error: " . $e->getMessage());
}

echo json_encode($response);
?>
