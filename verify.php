<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';
$verified = false;
$message = '';

if (!empty($token)) {
    try {
        $db = new Database();
        $pdo = $db->getConnection();
        
        // Find user with verification token
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE verification_token = ? AND is_verified = 0");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Mark user as verified
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $result = $stmt->execute([$user['id']]);
            
            if ($result) {
                $verified = true;
                $message = "Your email has been successfully verified! You can now login to your account.";
            } else {
                $message = "Verification failed. Please try again.";
            }
        } else {
            $message = "Invalid or expired verification link.";
        }
    } catch (Exception $e) {
        $message = "An error occurred during verification. Please try again.";
        error_log("Verification error: " . $e->getMessage());
    }
} else {
    $message = "No verification token provided.";
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - AutoMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/enhanced-styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket
            </a>
            
            <div class="nav-actions">
                <a href="login.php" class="glass-button">Login</a>
                <a href="register-simple.php" class="glass-button primary">Register</a>
            </div>
        </div>
    </nav>

    <!-- Verification Section -->
    <section class="section" style="min-height: 80vh; display: flex; align-items: center;">
        <div class="container">
            <div class="verification-container" style="max-width: 500px; margin: 0 auto; text-align: center;">
                <div class="glass-card" style="padding: 3rem;">
                    <div class="verification-icon" style="font-size: 4rem; margin-bottom: 2rem;">
                        <?php if ($verified): ?>
                            <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle" style="color: #ef4444;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <h2 style="margin-bottom: 1rem; font-size: 2rem;">
                        <?php if ($verified): ?>
                            Email Verified!
                        <?php else: ?>
                            Verification Failed
                        <?php endif; ?>
                    </h2>
                    
                    <p style="margin-bottom: 2rem; color: var(--text-secondary); font-size: 1.1rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                    
                    <div class="verification-actions">
                        <?php if ($verified): ?>
                            <a href="login.php" class="glass-button primary" style="padding: 1rem 2rem;">
                                <i class="fas fa-sign-in-alt"></i> Login Now
                            </a>
                        <?php else: ?>
                            <a href="register-simple.php" class="glass-button" style="padding: 1rem 2rem; margin-right: 1rem;">
                                <i class="fas fa-user-plus"></i> Register Again
                            </a>
                            <a href="login.php" class="glass-button primary" style="padding: 1rem 2rem;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 AutoMarket. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
