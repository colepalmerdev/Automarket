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

function getSetting($key, $default = null) {
    try {
        $db = new Database();
        $pdo = $db->getConnection();
        if ($pdo === null) {
            return $default;
        }

        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $setting = $stmt->fetch();

        if ($setting && isset($setting['setting_value'])) {
            return $setting['setting_value'];
        }
    } catch (Exception $e) {
        // Ignore and return default
    }

    return $default;
}

function smtpSendEmail($to, $subject, $message, $fromName, $fromEmail, $smtpConfig = []) {
    $smtp_host = $smtpConfig['host'] ?? getSetting('smtp_host', '');
    $smtp_port = $smtpConfig['port'] ?? getSetting('smtp_port', '587');
    $smtp_username = $smtpConfig['username'] ?? getSetting('smtp_username', '');
    $smtp_password = $smtpConfig['password'] ?? getSetting('smtp_password', '');
    $smtp_encryption = $smtpConfig['encryption'] ?? getSetting('smtp_encryption', 'tls');
    $timeout = 30;

    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
        return false;
    }

    $remote = ($smtp_encryption === 'ssl' ? 'ssl://' : '') . $smtp_host . ':' . $smtp_port;
    $socket = stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

    if (!$socket) {
        return false;
    }

    $getResponse = function() use ($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    };

    $sendCommand = function($command) use ($socket) {
        fwrite($socket, $command . "\r\n");
    };

    $response = $getResponse();
    if (substr($response, 0, 3) !== '220') {
        fclose($socket);
        return false;
    }

    $hostname = gethostname() ?: 'localhost';
    $sendCommand("EHLO " . $hostname);
    $response = $getResponse();
    if ($smtp_encryption === 'tls') {
        $sendCommand('STARTTLS');
        $response = $getResponse();
        if (substr($response, 0, 3) !== '220') {
            fclose($socket);
            return false;
        }
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $sendCommand("EHLO " . $hostname);
        $response = $getResponse();
    }

    if (!empty($smtp_username) && !empty($smtp_password)) {
        $sendCommand('AUTH LOGIN');
        $response = $getResponse();
        if (substr($response, 0, 3) !== '334') {
            fclose($socket);
            return false;
        }

        $sendCommand(base64_encode($smtp_username));
        $response = $getResponse();
        if (substr($response, 0, 3) !== '334') {
            fclose($socket);
            return false;
        }

        $sendCommand(base64_encode($smtp_password));
        $response = $getResponse();
        if (substr($response, 0, 3) !== '235') {
            fclose($socket);
            return false;
        }
    }

    $sendCommand('MAIL FROM: <' . $fromEmail . '>');
    $response = $getResponse();
    if (substr($response, 0, 3) !== '250') {
        fclose($socket);
        return false;
    }

    $sendCommand('RCPT TO: <' . $to . '>');
    $response = $getResponse();
    if (!in_array(substr($response, 0, 3), ['250', '251'])) {
        fclose($socket);
        return false;
    }

    $sendCommand('DATA');
    $response = $getResponse();
    if (substr($response, 0, 3) !== '354') {
        fclose($socket);
        return false;
    }

    $headers = [];
    $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
    $headers[] = 'Reply-To: ' . $fromEmail;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'To: ' . $to;

    $body = implode("\r\n", $headers) . "\r\n\r\n";
    $body .= str_replace('\n', "\r\n", $message);
    $body .= "\r\n.\r\n";

    fwrite($socket, $body);
    $response = $getResponse();
    if (substr($response, 0, 3) !== '250') {
        fclose($socket);
        return false;
    }

    $sendCommand('QUIT');
    fclose($socket);
    return true;
}

function sendEmail($to, $subject, $message) {
    $site_name = getSetting('site_name', 'AutoMarket');
    $fromEmail = getSetting('site_email', 'noreply@automarketpro.com');
    $fromName = $site_name;

    $smtp_host = getSetting('smtp_host', '');
    $smtp_username = getSetting('smtp_username', '');
    $smtp_password = getSetting('smtp_password', '');

    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
        return false;
    }

    return smtpSendEmail($to, $subject, $message, $fromName, $fromEmail);
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
