<?php
$site_name = 'AutoMarket';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - <?php echo htmlspecialchars($site_name); ?></title>
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
                <h1>Terms and Conditions</h1>
                <p>Welcome to <?php echo htmlspecialchars($site_name); ?>. These Terms and Conditions govern your use of our website and services. By accessing or using the site, you agree to be bound by these terms.</p>
                <h2>1. Acceptance of Terms</h2>
                <p>By registering an account or using our services, you agree to comply with these Terms. If you do not agree, please do not use our website.</p>
                <h2>2. User Accounts</h2>
                <p>You are responsible for maintaining the confidentiality of your account credentials and for all activity under your account. You agree to provide accurate information when registering.</p>
                <h2>3. Listings and Transactions</h2>
                <p>All vehicle listings, rental bookings, and transactions are between the parties involved. <?php echo htmlspecialchars($site_name); ?> acts as a marketplace platform and is not responsible for the performance of any third party.</p>
                <h2>4. Prohibited Conduct</h2>
                <p>Users must not engage in fraudulent activity, harassment, or any actions that violate applicable law or third-party rights.</p>
                <h2>5. Account Termination</h2>
                <p>We reserve the right to suspend or terminate accounts that violate these Terms or pose a risk to the platform.</p>
                <h2>6. Changes to Terms</h2>
                <p>We may update these Terms at any time. Continued use of the site constitutes acceptance of the revised Terms.</p>
                <div class="auth-footer" style="margin-top: 2rem;">
                    <a href="index.html" class="glass-button">Home</a>
                    <a href="privacy.php" class="glass-button">Privacy Policy</a>
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