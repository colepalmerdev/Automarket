<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Rental Cars Check</h2>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Check if rental_cars table exists
try {
    $stmt = $pdo->query("DESCRIBE rental_cars");
    echo "<p style='color: green;'>✓ Rental cars table exists</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Rental cars table not found: " . $e->getMessage() . "</p>";
    echo "<p>Creating rental_cars table...</p>";
    
    // Create rental_cars table
    $sql = "
        CREATE TABLE rental_cars (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            brand_id INT NOT NULL,
            model_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            year INT NOT NULL,
            fuel_type VARCHAR(50) NOT NULL,
            transmission VARCHAR(50) NOT NULL,
            mileage INT,
            location VARCHAR(255) NOT NULL,
            daily_rate DECIMAL(10,2) NOT NULL,
            weekly_rate DECIMAL(10,2),
            monthly_rate DECIMAL(10,2),
            security_deposit DECIMAL(10,2),
            min_rental_days INT DEFAULT 1,
            is_available BOOLEAN DEFAULT TRUE,
            is_approved BOOLEAN DEFAULT FALSE,
            images JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id),
            FOREIGN KEY (brand_id) REFERENCES car_brands(id),
            FOREIGN KEY (model_id) REFERENCES car_models(id)
        )
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Rental cars table created successfully</p>";
}

// Check existing rental cars
echo "<h3>Existing Rental Cars:</h3>";
$stmt = $pdo->query("
    SELECT rc.*, u.username as owner_name, b.name as name, m.name as model_name 
    FROM rental_cars rc 
    LEFT JOIN users u ON rc.owner_id = u.id 
    LEFT JOIN car_brands b ON rc.brand_id = b.id 
    LEFT JOIN car_models m ON rc.model_id = m.id 
    ORDER BY rc.id
");
$rental_cars = $stmt->fetchAll();

if (empty($rental_cars)) {
    echo "<p>No rental cars found. Creating sample rental cars...</p>";
    
    // Get first user as owner
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $owner = $stmt->fetch();
    $owner_id = $owner ? $owner['id'] : 1;
    
    // Get brands and models
    $stmt = $pdo->query("SELECT id, name FROM car_brands LIMIT 3");
    $brands = $stmt->fetchAll();
    
    $sample_cars = [
        ['brand' => 'Toyota', 'model' => 'Camry', 'year' => 2023, 'daily_rate' => 45.00, 'weekly_rate' => 280.00, 'monthly_rate' => 980.00, 'deposit' => 500.00],
        ['brand' => 'Honda', 'model' => 'Civic', 'year' => 2022, 'daily_rate' => 35.00, 'weekly_rate' => 220.00, 'monthly_rate' => 770.00, 'deposit' => 400.00],
        ['brand' => 'Nissan', 'model' => 'Altima', 'year' => 2023, 'daily_rate' => 40.00, 'weekly_rate' => 250.00, 'monthly_rate' => 875.00, 'deposit' => 450.00]
    ];
    
    foreach ($sample_cars as $car_data) {
        // Get brand ID
        $stmt = $pdo->prepare("SELECT id FROM car_brands WHERE name = ?");
        $stmt->execute([$car_data['brand']]);
        $brand = $stmt->fetch();
        
        if ($brand) {
            // Get first model for this brand
            $stmt = $pdo->prepare("SELECT id FROM car_models WHERE brand_id = ? LIMIT 1");
            $stmt->execute([$brand['id']]);
            $model = $stmt->fetch();
            
            if ($model) {
                $stmt = $pdo->prepare("
                    INSERT INTO rental_cars (
                        owner_id, brand_id, model_id, title, description, year, fuel_type, 
                        transmission, mileage, location, daily_rate, weekly_rate, monthly_rate, 
                        security_deposit, min_rental_days, is_available, is_approved, images
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $result = $stmt->execute([
                    $owner_id,
                    $brand['id'],
                    $model['id'],
                    $car_data['brand'] . ' ' . $car_data['model'] . ' ' . $car_data['year'],
                    'Well-maintained ' . $car_data['brand'] . ' ' . $car_data['model'] . ' available for rental. Perfect for daily commuting or weekend trips.',
                    $car_data['year'],
                    'petrol',
                    'automatic',
                    15000,
                    'Nairobi, Kenya',
                    $car_data['daily_rate'],
                    $car_data['weekly_rate'],
                    $car_data['monthly_rate'],
                    $car_data['deposit'],
                    1,
                    true,
                    true,
                    json_encode(['assets/images/rental-' . strtolower($car_data['brand']) . '.jpg'])
                ]);
                
                if ($result) {
                    echo "<p style='color: green;'>✓ Created rental: {$car_data['brand']} {$car_data['model']}</p>";
                }
            }
        }
    }
    
    // Check again
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM rental_cars");
    $count = $stmt->fetch()['count'];
    echo "<p style='color: green;'>✓ Total rental cars created: {$count}</p>";
} else {
    foreach ($rental_cars as $car) {
        $status = $car['is_available'] ? 'Available' : 'Not Available';
        $approved = $car['is_approved'] ? 'Approved' : 'Pending';
        echo "<p>ID: {$car['id']} | {$car['title']} | ${$car['daily_rate']}/day | {$car['location']} | Status: {$status} | {$approved}</p>";
    }
}

// Check bookings table
echo "<h3>Existing Bookings:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE bookings");
    echo "<p style='color: green;'>✓ Bookings table exists</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Bookings table not found. Creating...</p>";
    
    $sql = "
        CREATE TABLE bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rental_car_id INT NOT NULL,
            customer_id INT NOT NULL,
            pickup_date DATE NOT NULL,
            return_date DATE NOT NULL,
            pickup_time TIME NOT NULL,
            return_time TIME NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            security_deposit DECIMAL(10,2),
            payment_method VARCHAR(50) NOT NULL,
            special_requests TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (rental_car_id) REFERENCES rental_cars(id),
            FOREIGN KEY (customer_id) REFERENCES users(id)
        )
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Bookings table created successfully</p>";
}

$stmt = $pdo->query("
    SELECT b.*, rc.title as car_title, u.username as customer_name 
    FROM bookings b 
    LEFT JOIN rental_cars rc ON b.rental_car_id = rc.id 
    LEFT JOIN users u ON b.customer_id = u.id 
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();

if (empty($bookings)) {
    echo "<p>No bookings found yet.</p>";
} else {
    foreach ($bookings as $booking) {
        echo "<p>Booking ID: {$booking['id']} | Car: {$booking['car_title']} | Customer: {$booking['customer_name']} | Amount: ${$booking['total_amount']} | Status: {$booking['status']}</p>";
    }
}

echo "<h3>Next Steps:</h3>";
echo "<p>1. <a href='login.php'>Login to your account</a></p>";
echo "<p>2. <a href='rentals.php'>Browse rental cars</a></p>";
echo "<p>3. Select a car and click 'Book Now'</p>";
echo "<p>4. Fill in booking details and submit</p>";
echo "<p>5. Check <a href='admin/dashboard.php'>Admin Dashboard</a> to see the booking</p>";
?>
