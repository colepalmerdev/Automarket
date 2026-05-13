<?php
// Database Setup Script
require_once 'includes/connect.php';

echo "<h1>AutoMarket - Database Setup</h1>";

try {
    // Create database and tables
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read and execute schema
    $sql = file_get_contents('database/schema.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "<h2>Creating database tables...</h2>";
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $conn->exec($statement);
                echo "<p style='color: green;'>✓ Table created successfully</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>⚠ " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p style='color: green;'>✓ Database setup completed successfully</p>";
    echo "<p><a href='index.php' style='background: #d4af37; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Setup Failed!</h2>";
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in includes/connect.php</p>";
}
?>
