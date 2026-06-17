<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AutoMarket</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/enhanced-styles.css">
    <link rel="stylesheet" href="assets/css/form-fix.css">
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
                <a href="register-simple.php" class="glass-button primary">Register</a>
                <a href="admin/dashboard.php" class="glass-button">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card glass-card animate-fadeInUp">
                    <div class="auth-header">
                        <h2>Welcome Back</h2>
                        <p>Login to your AutoMarket account</p>
                    </div>
                    
                    <!-- PHP Login Processing -->
                    <?php
                    require_once 'config/database.php';
                    require_once 'includes/functions.php';
                    
                    $errors = [];

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $email = cleanInput($_POST['email']);
                        $password = $_POST['password'];
                        $remember = isset($_POST['remember']);

                        // Basic validation
                        if (empty($email) || empty($password)) {
                            $errors[] = 'Email and password are required';
                        }

                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = 'Invalid email format';
                        }

                        if (empty($errors)) {
                            try {
                                $db = new Database();
                                $pdo = $db->getConnection();

                                if ($pdo === null) {
                                    $errors[] = 'Database connection failed. Please try again later.';
                                } else {
                                    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                                    $stmt->execute([$email]);
                                    $user = $stmt->fetch();

                                    if ($user) {
                                        if (!$user['is_verified']) {
                                            $errors[] = 'Please verify your email before logging in. Check your inbox or <a href="resend-verification.php?email=' . urlencode($email) . '">resend verification</a>.';
                                        } elseif (password_verify($password, $user['password_hash'])) {
                                            $_SESSION['user_id'] = $user['id'];
                                            $_SESSION['user_email'] = $user['email'];
                                            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                                            $_SESSION['user_role'] = $user['role'];

                                            if ($remember) {
                                                $token = generateToken();
                                                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');

                                                $update_stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                                                $update_stmt->execute([$token, $user['id']]);
                                            }

                                            if ($user['role'] === 'admin') {
                                                header('Location: admin/dashboard.php');
                                            } else {
                                                header('Location: index.html');
                                            }
                                            exit();
                                        } else {
                                            $errors[] = 'Invalid email or password';
                                        }
                                    } else {
                                        $errors[] = 'Invalid email or password';
                                    }
                                }
                            } catch (PDOException $e) {
                                $errors[] = 'Login failed. Please try again.';
                            }
                        }
                    }

                    // Display errors
                    if (!empty($errors)) {
                        echo "<div class='alert alert-error'>";
                        foreach ($errors as $error) {
                            echo "<i class='fas fa-exclamation-circle'></i>";
                            echo "<span>$error</span>";
                        }
                        echo "</div>";
                    }

                    // Display success message if redirected
                    if (isset($_GET['success']) && $_GET['success'] === '1') {
                        echo "<div class='alert alert-success'>";
                        echo "<i class='fas fa-check-circle'></i>";
                        echo "<span>Registration successful! Please login.</span>";
                        echo "</div>";
                    }
                    ?>
                    
                    <!-- Login Form (Direct PHP Submission) -->
                    <form method="post" action="login.php">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-input" required 
                                       placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" class="form-input" required 
                                       placeholder="Enter your password">
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="form-submit">
                            <span class="button-text">Login</span>
                            <div class="spinner"></div>
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register-simple.php">Register here</a></p>
                        <!--<p><a href="create-test-user.php">Create test user</a> | <a href="test-login-simple.php">Test authentication</a></p>-->
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

    <!-- JavaScript -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
        
        // Clear errors on input
        document.getElementById('email').addEventListener('input', function() {
            const errors = document.querySelectorAll('.alert-error');
            errors.forEach(error => error.remove());
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const errors = document.querySelectorAll('.alert-error');
            errors.forEach(error => error.remove());
        });
    </script>
</body>
</html>
