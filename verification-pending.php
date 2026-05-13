<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Pending - AutoMarket</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/enhanced-styles.css">
</head>
<body class="auth-page">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket
            </a>
            
            <div class="nav-actions">
                <a href="login.php" class="glass-button">Login</a>
                <a href="register-with-verification.php" class="glass-button primary">Register</a>
            </div>
        </div>
    </nav>

    <!-- Verification Pending Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card glass-card animate-fadeInUp">
                    <div class="auth-header">
                        <h2>Check Your Email</h2>
                        <p>Verification email has been sent</p>
                    </div>
                    
                    <div class="verification-info">
                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h3>Email Sent</h3>
                                <p>We've sent a verification link to your email address.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Check Your Inbox</h3>
                                <p>Please check your email and click the verification link.</p>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <i class="fas fa-hourglass-half"></i>
                            <div>
                                <h3>24 Hours</h3>
                                <p>The verification link will expire in 24 hours.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Didn't receive email? <a href="register-with-verification.php">Try registering again</a></p>
                        <p>Already verified? <a href="login.php">Login here</a></p>
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
