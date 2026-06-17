<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$pdo = $db->getConnection();

// Load current settings before handling POST actions
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'update_site_settings':
            $site_name = $_POST['site_name'] ?? '';
            $site_email = $_POST['site_email'] ?? '';
            $currency = $_POST['currency'] ?? '';
            $approval_required = isset($_POST['approval_required']) ? 'true' : 'false';
            $min_rental_age = $_POST['min_rental_age'] ?? '';
            
            // Update settings
            $settings = [
                'site_name' => $site_name,
                'site_email' => $site_email,
                'currency' => $currency,
                'approval_required' => $approval_required,
                'min_rental_age' => $min_rental_age
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO settings (setting_key, setting_value, updated_at) 
                    VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $success_message = "Site settings updated successfully!";
            break;
            
        case 'update_email_settings':
            $smtp_host = $_POST['smtp_host'] ?? '';
            $smtp_port = $_POST['smtp_port'] ?? '';
            $smtp_username = $_POST['smtp_username'] ?? '';
            $smtp_password = $_POST['smtp_password'] ?? '';
            $smtp_encryption = $_POST['smtp_encryption'] ?? '';
            
            $email_settings = [
                'smtp_host' => $smtp_host,
                'smtp_port' => $smtp_port,
                'smtp_username' => $smtp_username,
                'smtp_password' => $smtp_password,
                'smtp_encryption' => $smtp_encryption
            ];
            
            foreach ($email_settings as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO settings (setting_key, setting_value, updated_at) 
                    VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
                ");
                $stmt->execute([$key, $value, $value]);
            }
            
            $success_message = "Email settings updated successfully!";
            break;
        case 'test_smtp_settings':
            $smtp_host = $_POST['smtp_host'] ?? '';
            $smtp_port = $_POST['smtp_port'] ?? '';
            $smtp_username = $_POST['smtp_username'] ?? '';
            $smtp_password = $_POST['smtp_password'] ?? '';
            $smtp_encryption = $_POST['smtp_encryption'] ?? '';
            $test_email = $_POST['test_email'] ?? '';

            if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Please provide a valid test email address.';
            } else {
                $smtpConfig = [
                    'host' => $smtp_host,
                    'port' => $smtp_port,
                    'username' => $smtp_username,
                    'password' => $smtp_password,
                    'encryption' => $smtp_encryption
                ];

                $site_name = $settings['site_name'] ?? 'AutoMarket';
                $fromEmail = $settings['site_email'] ?? 'noreply@automarketpro.com';
                $fromName = $site_name;
                $subject = 'SMTP Configuration Test';
                $body = '<p>This is a test email to verify SMTP settings for AutoMarket.</p>';

                if (smtpSendEmail($test_email, $subject, $body, $fromName, $fromEmail, $smtpConfig)) {
                    $success_message = 'SMTP test email sent successfully to ' . htmlspecialchars($test_email) . '.';
                } else {
                    $error_message = 'SMTP test failed. Please verify your SMTP configuration.';
                }
            }
            break;
    }
}

// Get current settings
$settings_stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $settings_stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Default values
$site_name = $settings['site_name'] ?? 'AutoMarket';
$site_email = $settings['site_email'] ?? 'info@automarket.com';
$currency = $settings['currency'] ?? 'USD';
$approval_required = $settings['approval_required'] ?? 'true';
$min_rental_age = $settings['min_rental_age'] ?? '21';

$smtp_host = $settings['smtp_host'] ?? '';
$smtp_port = $settings['smtp_port'] ?? '587';
$smtp_username = $settings['smtp_username'] ?? '';
$smtp_password = $settings['smtp_password'] ?? '';
$smtp_encryption = $settings['smtp_encryption'] ?? 'tls';
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket Admin
            </a>
            
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="users.php" class="nav-link">Users</a></li>
                <li><a href="cars.php" class="nav-link">Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="bookings.php" class="nav-link">Bookings</a></li>
                <li><a href="analytics.php" class="nav-link">Analytics</a></li>
                <li><a href="settings.php" class="nav-link active">Settings</a></li>
            </ul>
            
            <div class="nav-actions">
                <a href="../index.html" class="glass-button">
                    <i class="fas fa-home"></i> View Site
                </a>
                <a href="../logout.php" class="glass-button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Settings</h1>
                <p>Configure site settings and preferences</p>
            </div>
        </div>
    </section>

    <!-- Settings Section -->
    <section class="section">
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Site Settings -->
            <div class="settings-card glass-card">
                <div class="settings-header">
                    <h3>Site Settings</h3>
                    <p>Basic site configuration</p>
                </div>
                
                <form method="POST" class="settings-form">
                    <input type="hidden" name="action" value="update_site_settings">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" id="site_name" name="site_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($site_name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_email" class="form-label">Site Email</label>
                            <input type="email" id="site_email" name="site_email" class="form-input" 
                                   value="<?php echo htmlspecialchars($site_email); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currency" class="form-label">Default Currency</label>
                            <select id="currency" name="currency" class="form-select">
                                <option value="USD" <?php echo $currency === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                <option value="EUR" <?php echo $currency === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                <option value="GBP" <?php echo $currency === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                <option value="KES" <?php echo $currency === 'KES' ? 'selected' : ''; ?>>KES (KSh)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="min_rental_age" class="form-label">Minimum Rental Age</label>
                            <input type="number" id="min_rental_age" name="min_rental_age" class="form-input" 
                                   value="<?php echo htmlspecialchars($min_rental_age); ?>" min="18" max="99" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="approval_required" 
                                   <?php echo $approval_required === 'true' ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Require admin approval for listings
                        </label>
                    </div>
                    
                    <button type="submit" class="glass-button primary">
                        <i class="fas fa-save"></i> Save Site Settings
                    </button>
                </form>
            </div>

            <!-- Email Settings -->
            <div class="settings-card glass-card">
                <div class="settings-header">
                    <h3>Email Settings</h3>
                    <p>SMTP configuration for sending emails</p>
                </div>
                
                <form method="POST" class="settings-form">
                    <input type="hidden" name="action" value="update_email_settings">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" class="form-input" 
                                   value="<?php echo htmlspecialchars($smtp_host); ?>" placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_port" class="form-label">SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" class="form-input" 
                                   value="<?php echo htmlspecialchars($smtp_port); ?>" placeholder="587">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" id="smtp_username" name="smtp_username" class="form-input" 
                                   value="<?php echo htmlspecialchars($smtp_username); ?>" placeholder="your@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" class="form-input" 
                                   value="<?php echo htmlspecialchars($smtp_password); ?>" placeholder="Your app password">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="smtp_encryption" class="form-label">Encryption</label>
                        <select id="smtp_encryption" name="smtp_encryption" class="form-select">
                            <option value="none" <?php echo $smtp_encryption === 'none' ? 'selected' : ''; ?>>None</option>
                            <option value="tls" <?php echo $smtp_encryption === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo $smtp_encryption === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="glass-button primary">
                        <i class="fas fa-save"></i> Save Email Settings
                    </button>
                </form>

                <form method="POST" class="settings-form" style="margin-top: 1rem;">
                    <input type="hidden" name="action" value="test_smtp_settings">
                    <input type="hidden" name="smtp_host" value="<?php echo htmlspecialchars($smtp_host); ?>">
                    <input type="hidden" name="smtp_port" value="<?php echo htmlspecialchars($smtp_port); ?>">
                    <input type="hidden" name="smtp_username" value="<?php echo htmlspecialchars($smtp_username); ?>">
                    <input type="hidden" name="smtp_password" value="<?php echo htmlspecialchars($smtp_password); ?>">
                    <input type="hidden" name="smtp_encryption" value="<?php echo htmlspecialchars($smtp_encryption); ?>">

                    <div class="form-group">
                        <label for="test_email" class="form-label">Test Email Address</label>
                        <input type="email" id="test_email" name="test_email" class="form-input" placeholder="Enter email to test SMTP">
                    </div>

                    <button type="submit" class="glass-button secondary">
                        <i class="fas fa-paper-plane"></i> Send SMTP Test Email
                    </button>
                </form>
            </div>

            <!-- System Information -->
            <div class="settings-card glass-card">
                <div class="settings-header">
                    <h3>System Information</h3>
                    <p>Current system status and information</p>
                </div>
                
                <div class="system-info">
                    <div class="info-row">
                        <div class="info-label">PHP Version:</div>
                        <div class="info-value"><?php echo PHP_VERSION; ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">MySQL Version:</div>
                        <div class="info-value"><?php echo $pdo->query("SELECT VERSION()")->fetch()[0]; ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Server Time:</div>
                        <div class="info-value"><?php echo date('Y-m-d H:i:s'); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Upload Max Size:</div>
                        <div class="info-value"><?php echo ini_get('upload_max_filesize'); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Memory Limit:</div>
                        <div class="info-value"><?php echo ini_get('memory_limit'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 AutoMarket Admin Panel. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
