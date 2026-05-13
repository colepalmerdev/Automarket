<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Inserting All Rental Cars from rentals.html</h2>";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Get first user as owner (or create if needed)
$stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$owner = $stmt->fetch();
$owner_id = $owner ? $owner['id'] : 1;

if (!$owner) {
    echo "<p>No admin user found. Creating admin user...</p>";
    $password = 'admin123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_verified, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute(['admin', 'admin@automarket.com', $password_hash, 'Admin', 'User', 'admin', 1, 1]);
    $owner_id = $pdo->lastInsertId();
    echo "<p style='color: green;'>✓ Admin user created with ID: {$owner_id}</p>";
}

// All rental cars from rentals.html
$rental_cars = [
    [
        'title' => '2023 Toyota Camry',
        'brand' => 'Toyota',
        'model' => 'Camry',
        'year' => 2023,
        'fuel_type' => 'Hybrid',
        'transmission' => 'Automatic',
        'daily_rate' => 120.00,
        'weekly_rate' => 720.00,
        'monthly_rate' => 2880.00,
        'security_deposit' => 600.00,
        'location' => 'Nairobi, Kenya',
        'image' => 'images/2023 Toyota Camry.png'
    ],
    [
        'title' => '2022 Honda CR-V',
        'brand' => 'Honda',
        'model' => 'CR-V',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 150.00,
        'weekly_rate' => 900.00,
        'monthly_rate' => 3600.00,
        'security_deposit' => 750.00,
        'location' => 'Nairobi, Kenya',
        'image' => 'images/2022 Honda cr-v.png'
    ],
    [
        'title' => '2023 Nissan Sentra',
        'brand' => 'Nissan',
        'model' => 'Sentra',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 80.00,
        'weekly_rate' => 480.00,
        'monthly_rate' => 1920.00,
        'security_deposit' => 400.00,
        'location' => 'Mombasa, Kenya',
        'image' => 'images/2023 Nissan Sentra.png'
    ],
    [
        'title' => '2022 BMW 5 Series',
        'brand' => 'BMW',
        'model' => '5 Series',
        'year' => 2022,
        'fuel_type' => 'Diesel',
        'transmission' => 'Automatic',
        'daily_rate' => 200.00,
        'weekly_rate' => 1200.00,
        'monthly_rate' => 4800.00,
        'security_deposit' => 1000.00,
        'location' => 'Nairobi, Kenya',
        
    ],
    [
        'title' => '2023 Toyota RAV4',
        'brand' => 'Toyota',
        'model' => 'RAV4',
        'year' => 2023,
        'fuel_type' => 'Hybrid',
        'transmission' => 'CVT',
        'daily_rate' => 180.00,
        'weekly_rate' => 1080.00,
        'monthly_rate' => 4320.00,
        'security_deposit' => 900.00,
        'location' => 'Kisumu, Kenya',
        
    ],
    [
        'title' => '2022 Ford Focus',
        'brand' => 'Ford',
        'model' => 'Focus',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'Manual',
        'daily_rate' => 95.00,
        'weekly_rate' => 570.00,
        'monthly_rate' => 2280.00,
        'security_deposit' => 475.00,
        'location' => 'Nakuru, Kenya',
        
    ],
    [
        'title' => '2023 Mercedes-Benz C-Class',
        'brand' => 'Mercedes-Benz',
        'model' => 'C-Class',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 250.00,
        'weekly_rate' => 1500.00,
        'monthly_rate' => 6000.00,
        'security_deposit' => 1250.00,
        'location' => 'Nairobi, Kenya',
        
    ],
    [
        'title' => '2022 Mazda CX-5',
        'brand' => 'Mazda',
        'model' => 'CX-5',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 160.00,
        'weekly_rate' => 960.00,
        'monthly_rate' => 3840.00,
        'security_deposit' => 800.00,
        'location' => 'Mombasa, Kenya',
        
    ],
    [
        'title' => '2023 Volkswagen Jetta',
        'brand' => 'Volkswagen',
        'model' => 'Jetta',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 85.00,
        'weekly_rate' => 510.00,
        'monthly_rate' => 2040.00,
        'security_deposit' => 425.00,
        'location' => 'Eldoret, Kenya',
        
    ],
    [
        'title' => '2022 Audi A4',
        'brand' => 'Audi',
        'model' => 'A4',
        'year' => 2022,
        'fuel_type' => 'Diesel',
        'transmission' => 'Automatic',
        'daily_rate' => 220.00,
        'weekly_rate' => 1320.00,
        'monthly_rate' => 5280.00,
        'security_deposit' => 1100.00,
        'location' => 'Nairobi, Kenya',
        
    ],
    [
        'title' => '2023 Subaru Forester',
        'brand' => 'Subaru',
        'model' => 'Forester',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'CVT',
        'daily_rate' => 175.00,
        'weekly_rate' => 1050.00,
        'monthly_rate' => 4200.00,
        'security_deposit' => 875.00,
        'location' => 'Kisumu, Kenya',
        
    ],
    [
        'title' => '2022 Hyundai Elantra',
        'brand' => 'Hyundai',
        'model' => 'Elantra',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 75.00,
        'weekly_rate' => 450.00,
        'monthly_rate' => 1800.00,
        'security_deposit' => 375.00,
        'location' => 'Nakuru, Kenya',
        
    ],
    [
        'title' => '2023 Lexus ES',
        'brand' => 'Lexus',
        'model' => 'ES',
        'year' => 2023,
        'fuel_type' => 'Hybrid',
        'transmission' => 'Automatic',
        'daily_rate' => 280.00,
        'weekly_rate' => 1680.00,
        'monthly_rate' => 6720.00,
        'security_deposit' => 1400.00,
        'location' => 'Nairobi, Kenya',
        
    ],
    [
        'title' => '2022 Kia Sportage',
        'brand' => 'Kia',
        'model' => 'Sportage',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 145.00,
        'weekly_rate' => 870.00,
        'monthly_rate' => 3480.00,
        'security_deposit' => 725.00,
        'location' => 'Mombasa, Kenya',
        
    ],
    [
        'title' => '2023 Nissan Altima',
        'brand' => 'Nissan',
        'model' => 'Altima',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'CVT',
        'daily_rate' => 110.00,
        'weekly_rate' => 660.00,
        'monthly_rate' => 2640.00,
        'security_deposit' => 550.00,
        'location' => 'Eldoret, Kenya',
        
    ],
    [
        'title' => '2022 Toyota Corolla',
        'brand' => 'Toyota',
        'model' => 'Corolla',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'Manual',
        'daily_rate' => 70.00,
        'weekly_rate' => 420.00,
        'monthly_rate' => 1680.00,
        'security_deposit' => 350.00,
        'location' => 'Nakuru, Kenya',
        
    ],
    [
        'title' => '2023 BMW X3',
        'brand' => 'BMW',
        'model' => 'X3',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 320.00,
        'weekly_rate' => 1920.00,
        'monthly_rate' => 7680.00,
        'security_deposit' => 1600.00,
        'location' => 'Nairobi, Kenya',
        
    ],
    [
        'title' => '2022 Honda Civic',
        'brand' => 'Honda',
        'model' => 'Civic',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'CVT',
        'daily_rate' => 105.00,
        'weekly_rate' => 630.00,
        'monthly_rate' => 2520.00,
        'security_deposit' => 525.00,
        'location' => 'Mombasa, Kenya',
        
    ],
    [
        'title' => '2023 Tesla Model 3',
        'brand' => 'Tesla',
        'model' => 'Model 3',
        'year' => 2023,
        'fuel_type' => 'Electric',
        'transmission' => 'Automatic',
        'daily_rate' => 350.00,
        'weekly_rate' => 2100.00,
        'monthly_rate' => 8400.00,
        'security_deposit' => 1750.00,
        'location' => 'Nairobi, Kenya',
        
    ],
    [
        'title' => '2022 Chevrolet Malibu',
        'brand' => 'Chevrolet',
        'model' => 'Malibu',
        'year' => 2022,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 90.00,
        'weekly_rate' => 540.00,
        'monthly_rate' => 2160.00,
        'security_deposit' => 450.00,
        'location' => 'Kisumu, Kenya',
        
    ],
    [
        'title' => '2023 Mitsubishi Outlander',
        'brand' => 'Mitsubishi',
        'model' => 'Outlander',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'CVT',
        'daily_rate' => 165.00,
        'weekly_rate' => 990.00,
        'monthly_rate' => 3960.00,
        'security_deposit' => 825.00,
        'location' => 'Eldoret, Kenya',
        
    ],
    [
        'title' => '2022 Toyota Hilux',
        'brand' => 'Toyota',
        'model' => 'Hilux',
        'year' => 2022,
        'fuel_type' => 'Diesel',
        'transmission' => 'Manual',
        'daily_rate' => 190.00,
        'weekly_rate' => 1140.00,
        'monthly_rate' => 4560.00,
        'security_deposit' => 950.00,
        'location' => 'Nakuru, Kenya',
        
    ],
    [
        'title' => '2023 Volvo XC60',
        'brand' => 'Volvo',
        'model' => 'XC60',
        'year' => 2023,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 240.00,
        'weekly_rate' => 1440.00,
        'monthly_rate' => 5760.00,
        'security_deposit' => 1200.00,
        'location' => 'Nairobi, Kenya',
        
    ],
    [
        'title' => '2022 Peugeot 308',
        'brand' => 'Peugeot',
        'model' => '308',
        'year' => 2022,
        'fuel_type' => 'Diesel',
        'transmission' => 'Manual',
        'daily_rate' => 78.00,
        'weekly_rate' => 468.00,
        'monthly_rate' => 1872.00,
        'security_deposit' => 390.00,
        'location' => 'Mombasa, Kenya',
        
    ]
];

$inserted_count = 0;
$skipped_count = 0;

echo "<h3>Inserting Rental Cars...</h3>";

foreach ($rental_cars as $car_data) {
    // Get brand ID
    $stmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
    $stmt->execute([$car_data['brand']]);
    $brand = $stmt->fetch();
    
    if (!$brand) {
        echo "<p style='color: orange;'>⚠ Brand '{$car_data['brand']}' not found. Skipping...</p>";
        $skipped_count++;
        continue;
    }
    
    // Get or create model
    $stmt = $pdo->prepare("SELECT id FROM models WHERE brand_id = ? AND name = ?");
    $stmt->execute([$brand['id'], $car_data['model']]);
    $model = $stmt->fetch();
    
    if (!$model) {
        // Create model if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO models (brand_id, name) VALUES (?, ?)");
        $stmt->execute([$brand['id'], $car_data['model']]);
        $model_id = $pdo->lastInsertId();
        echo "<p style='color: blue;'>ℹ Created model: {$car_data['model']}</p>";
    } else {
        $model_id = $model['id'];
    }
    
    // Check if car already exists
    $stmt = $pdo->prepare("SELECT id FROM rental_cars WHERE title = ? AND year = ?");
    $stmt->execute([$car_data['title'], $car_data['year']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "<p style='color: orange;'>⚠ Car '{$car_data['title']}' already exists. Skipping...</p>";
        $skipped_count++;
        continue;
    }
    
    // Insert rental car
    $stmt = $pdo->prepare("
        INSERT INTO rental_cars (
            owner_id, brand_id, model_id, title, description, year, fuel_type, 
            transmission, mileage, location, daily_rate, weekly_rate, monthly_rate, 
            security_deposit, min_rental_days, is_available, is_approved, images
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $description = "Well-maintained {$car_data['brand']} {$car_data['model']} ({$car_data['year']}) available for rental. Perfect for daily commuting or weekend trips. Features include modern amenities and reliable performance.";
    
    $result = $stmt->execute([
        $owner_id,
        $brand['id'],
        $model_id,
        $car_data['title'],
        $description,
        $car_data['year'],
        $car_data['fuel_type'],
        $car_data['transmission'],
        15000, // Default mileage
        $car_data['location'],
        $car_data['daily_rate'],
        $car_data['weekly_rate'],
        $car_data['monthly_rate'],
        $car_data['security_deposit'],
        1, // Minimum rental days
        true,
        true,
        json_encode([$car_data['image']])
    ]);
    
    if ($result) {
        $inserted_count++;
        echo "<p style='color: green;'>✓ Inserted: {$car_data['title']} - ${$car_data['daily_rate']}/day</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to insert: {$car_data['title']}</p>";
        $skipped_count++;
    }
}

echo "<h3>Summary:</h3>";
echo "<p style='color: green;'>✓ Successfully inserted: {$inserted_count} rental cars</p>";
echo "<p style='color: orange;'>⚠ Skipped: {$skipped_count} cars</p>";

// Verify total count
$stmt = $pdo->query("SELECT COUNT(*) as total FROM rental_cars");
$total = $stmt->fetch()['total'];
echo "<p style='color: blue;'>ℹ Total rental cars in database: {$total}</p>";

echo "<h3>Next Steps:</h3>";
echo "<p>1. <a href='rentals.php'>View Rental Cars Page</a></p>";
echo "<p>2. <a href='login.php'>Login to make a booking</a></p>";
echo "<p>3. <a href='admin/dashboard.php'>Check Admin Dashboard</a></p>";
echo "<p>4. Make a booking to test the system</p>";
?>
