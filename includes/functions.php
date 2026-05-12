<?php
// Helper Functions
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? 'guest';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    if (getCurrentUserRole() !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

// Clean input data
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format price
function formatPrice($price, $currency = 'USD') {
    return '$' . number_format($price, 2);
}

// Format date
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

// Generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Send email (placeholder function)
function sendEmail($to, $subject, $message) {
    // In production, use actual email service
    $headers = "From: noreply@automarketpro.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

// Upload image
function uploadImage($file, $target_dir = "uploads/") {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = time() . '_' . basename($file["name"]);
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    return false;
}

// Get car brands
function getCarBrands($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM car_brands ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get car models by brand
function getCarModels($pdo, $brand_id) {
    $stmt = $pdo->prepare("SELECT * FROM car_models WHERE brand_id = ? ORDER BY name");
    $stmt->execute([$brand_id]);
    return $stmt->fetchAll();
}

// Calculate rental price
function calculateRentalPrice($daily_rate, $pickup_date, $return_date) {
    $days = ceil((strtotime($return_date) - strtotime($pickup_date)) / (60 * 60 *24));
    return $daily_rate * $days;
}

// Check car availability
function isCarAvailable($pdo, $car_id, $pickup_date, $return_date) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE rental_car_id = ? 
        AND status IN ('pending', 'confirmed', 'active')
        AND (
            (pickup_date <= ? AND return_date >= ?) OR
            (pickup_date <= ? AND return_date >= ?) OR
            (pickup_date >= ? AND return_date <= ?)
        )
    ");
    $stmt->execute([$car_id, $pickup_date, $pickup_date, $return_date, $return_date, $pickup_date, $return_date]);
    $result = $stmt->fetch();
    return $result['count'] == 0;
}

// Get user by ID
function getUserById($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Get car details
function getCarDetails($pdo, $car_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, b.name as brand_name, m.name as model_name, u.username as seller_name, u.email as seller_email
        FROM cars c
        JOIN car_brands b ON c.brand_id = b.id
        JOIN car_models m ON c.model_id = m.id
        JOIN users u ON c.seller_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$car_id]);
    return $stmt->fetch();
}

// Get rental car details
function getRentalCarDetails($pdo, $car_id) {
    $stmt = $pdo->prepare("
        SELECT rc.*, b.name as brand_name, m.name as model_name, u.username as owner_name, u.email as owner_email
        FROM rental_cars rc
        JOIN car_brands b ON rc.brand_id = b.id
        JOIN car_models m ON rc.model_id = m.id
        JOIN users u ON rc.owner_id = u.id
        WHERE rc.id = ?
    ");
    $stmt->execute([$car_id]);
    return $stmt->fetch();
}

// Search cars
function searchCars($pdo, $filters = []) {
    $query = "
        SELECT c.*, b.name as brand_name, m.name as model_name, u.username as seller_name
        FROM cars c
        JOIN car_brands b ON c.brand_id = b.id
        JOIN car_models m ON c.model_id = m.id
        JOIN users u ON c.seller_id = u.id
        WHERE c.is_approved = 1 AND c.is_sold = 0
    ";
    $params = [];
    
    if (!empty($filters['brand'])) {
        $query .= " AND c.brand_id = ?";
        $params[] = $filters['brand'];
    }
    
    if (!empty($filters['model'])) {
        $query .= " AND c.model_id = ?";
        $params[] = $filters['model'];
    }
    
    if (!empty($filters['min_price'])) {
        $query .= " AND c.price >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $query .= " AND c.price <= ?";
        $params[] = $filters['max_price'];
    }
    
    if (!empty($filters['fuel_type'])) {
        $query .= " AND c.fuel_type = ?";
        $params[] = $filters['fuel_type'];
    }
    
    if (!empty($filters['transmission'])) {
        $query .= " AND c.transmission = ?";
        $params[] = $filters['transmission'];
    }
    
    if (!empty($filters['location'])) {
        $query .= " AND c.location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    $query .= " ORDER BY c.is_featured DESC, c.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Search rental cars
function searchRentalCars($pdo, $filters = []) {
    $query = "
        SELECT rc.*, b.name as brand_name, m.name as model_name, u.username as owner_name
        FROM rental_cars rc
        JOIN car_brands b ON rc.brand_id = b.id
        JOIN car_models m ON rc.model_id = m.id
        JOIN users u ON rc.owner_id = u.id
        WHERE rc.is_approved = 1 AND rc.is_available = 1
    ";
    $params = [];
    
    if (!empty($filters['brand'])) {
        $query .= " AND rc.brand_id = ?";
        $params[] = $filters['brand'];
    }
    
    if (!empty($filters['location'])) {
        $query .= " AND rc.location LIKE ?";
        $params[] = '%' . $filters['location'] . '%';
    }
    
    if (!empty($filters['pickup_date']) && !empty($filters['return_date'])) {
        $query .= " AND rc.id NOT IN (
            SELECT DISTINCT rental_car_id 
            FROM bookings 
            WHERE status IN ('pending', 'confirmed', 'active')
            AND (
                (pickup_date <= ? AND return_date >= ?) OR
                (pickup_date <= ? AND return_date >= ?) OR
                (pickup_date >= ? AND return_date <= ?)
            )
        )";
        $params = array_merge($params, [
            $filters['pickup_date'], $filters['pickup_date'],
            $filters['return_date'], $filters['return_date'],
            $filters['pickup_date'], $filters['return_date']
        ]);
    }
    
    $query .= " ORDER BY rc.daily_rate ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Time ago function
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>
