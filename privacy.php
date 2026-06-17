<?php
$site_name = 'AutoMarket';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/enhanced-styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="logo"><i class="fas fa-car"></i> <?php echo htmlspecialchars($site_name); ?></a>
            <div class="nav-actions">
                <a href="login.php" class="glass-button">Login</a>
                <a href="register-simple.php" class="glass-button primary">Register</a>
            </div>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <div class="content-card glass-card" style="padding: 3rem;">
                <h1>Privacy Policy</h1>
                <p><?php echo htmlspecialchars($site_name); ?> is committed to protecting your personal information. This Privacy Policy explains how we collect, use, and secure your data.</p>
                <h2>1. Information We Collect</h2>
                <p>We collect information you provide during registration, such as name, email, phone number, and account role. We may also collect usage data and analytics to improve our services.</p>
                <h2>2. How We Use Your Data</h2>
                <p>We use your data to provide our platform, send account notifications, verify your identity, and improve our product. We never sell your personal information to third parties.</p>
                <h2>3. Security</h2>
                <p>We use industry-standard security practices to protect your data. Passwords are hashed before storage, and sensitive actions are protected by secure server-side validation.</p>
                <h2>4. Cookies</h2>
                <p>We may use cookies for session management and to remember preferences, such as the 'remember me' login option.</p>
                <h2>5. Your Rights</h2>
                <p>You can request access, correction, or deletion of your personal data by contacting our support team.</p>
                <div class="auth-footer" style="margin-top: 2rem;">
                    <a href="index.html" class="glass-button">Home</a>
                    <a href="terms.php" class="glass-button">Terms</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>