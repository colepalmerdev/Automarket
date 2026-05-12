<?php
/*
 * login-final.php - DISABLED
 * This login system has been commented out.
 * Please use login-direct.php instead.
 */

// DISABLED - Use login-direct.php
/*
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    $errors = [];
    
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
            
            // Find user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Debug: Show password verification
                error_log("Login attempt - Email: " . $email);
                error_log("User found: YES");
                error_log("Stored hash: " . substr($user['password_hash'], 0, 30) . "...");
                error_log("Password verify result: " . (password_verify($password, $user['password_hash']) ? 'SUCCESS' : 'FAILED'));
                
                if (password_verify($password, $user['password_hash'])) {
                    // Login successful
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Set remember me cookie
                    if ($remember) {
                        $token = generateToken();
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                        
                        $update_stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                        $update_stmt->execute([$token, $user['id']]);
                    }
                    
                    // Redirect based on role
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
                error_log("Login attempt - Email: " . $email);
                error_log("User found: NO");
                $errors[] = 'Invalid email or password';
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = 'Login failed. Please try again.';
        }
    }
    
    // Return JSON error for AJAX requests
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $errors[0]]);
        exit();
    }
}
?>

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
                <a href="dashboard.php" class="glass-button">
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
                    
                    <!-- Alert container for dynamic messages -->
                    <div id="alert-container"></div>
                    
                    <form id="login-form" class="auth-form" method="post" action="login-final.php">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-input" required 
                                       placeholder="Enter your email">
                            </div>
                            <span class="form-error" id="email-error"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" class="form-input" required 
                                       placeholder="Enter your password">
                                <button type="button" class="password-toggle" id="password-toggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <span class="form-error" id="password-error"></span>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="forgot-password.html" class="forgot-link">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="form-submit" id="login-button">
                            <span class="button-text">Login</span>
                            <div class="spinner"></div>
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register-simple.php">Register here</a></p>
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
        // Login functionality
        class LoginHandler {
            constructor() {
                this.form = document.getElementById('login-form');
                this.loginButton = document.getElementById('login-button');
                this.alertContainer = document.getElementById('alert-container');
                this.passwordToggle = document.getElementById('password-toggle');
                this.passwordInput = document.getElementById('password');
                
                this.init();
            }
            
            init() {
                // Form submission
                this.form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handleLogin();
                });
                
                // Password toggle
                this.passwordToggle.addEventListener('click', () => {
                    this.togglePassword();
                });
                
                // Clear errors on input
                document.getElementById('email').addEventListener('input', () => {
                    this.clearFieldError('email');
                });
                
                document.getElementById('password').addEventListener('input', () => {
                    this.clearFieldError('password');
                });
            }
            
            togglePassword() {
                const type = this.passwordInput.type === 'password' ? 'text' : 'password';
                this.passwordInput.type = type;
                
                const icon = this.passwordToggle.querySelector('i');
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
            
            validateForm() {
                let isValid = true;
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;
                
                // Clear previous errors
                this.clearAllErrors();
                
                // Email validation
                if (!email) {
                    this.showFieldError('email', 'Email is required');
                    isValid = false;
                } else if (!this.isValidEmail(email)) {
                    this.showFieldError('email', 'Please enter a valid email address');
                    isValid = false;
                }
                
                // Password validation
                if (!password) {
                    this.showFieldError('password', 'Password is required');
                    isValid = false;
                }
                
                return isValid;
            }
            
            isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }
            
            showFieldError(field, message) {
                const errorElement = document.getElementById(`${field}-error`);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }
            }
            
            clearFieldError(field) {
                const errorElement = document.getElementById(`${field}-error`);
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }
            }
            
            clearAllErrors() {
                this.clearFieldError('email');
                this.clearFieldError('password');
                this.clearAlerts();
            }
            
            showAlert(message, type = 'error') {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                
                const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
                alertDiv.innerHTML = `
                    <i class="fas ${icon}"></i>
                    <span>${message}</span>
                `;
                
                this.alertContainer.innerHTML = '';
                this.alertContainer.appendChild(alertDiv);
                
                // Auto-hide success messages
                if (type === 'success') {
                    setTimeout(() => {
                        this.clearAlerts();
                    }, 5000);
                }
            }
            
            clearAlerts() {
                this.alertContainer.innerHTML = '';
            }
            
            setLoading(loading) {
                if (loading) {
                    this.loginButton.classList.add('loading');
                    this.loginButton.disabled = true;
                } else {
                    this.loginButton.classList.remove('loading');
                    this.loginButton.disabled = false;
                }
            }
            
            handleLogin() {
                if (!this.validateForm()) {
                    return;
                }
                
                // Submit form normally (no AJAX)
                this.setLoading(true);
                this.form.submit();
            }
        }
        
        // Initialize login handler when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            new LoginHandler();
        });
    </script>
</body>
</html>
*/
