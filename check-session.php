<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check session status
    session_start();
    
    $session_data = [
        'session_id' => session_id(),
        'session_status' => session_status(),
        'session_variables' => $_SESSION,
        'cookie_data' => $_COOKIE
    ];
    
    // Check if user is logged in
    $is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    
    if ($is_logged_in) {
        $session_data['user_info'] = [
            'user_id' => $_SESSION['user_id'],
            'user_email' => $_SESSION['user_email'] ?? 'Not set',
            'user_name' => $_SESSION['user_name'] ?? 'Not set',
            'user_role' => $_SESSION['user_role'] ?? 'Not set'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'is_logged_in' => $is_logged_in,
        'session_data' => $session_data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Session check error: ' . $e->getMessage(),
        'session_data' => []
    ]);
}
?>
