<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = cleanInput($_POST['role'] ?? 'buyer');
    $terms = isset($_POST['terms']);
    
    $errors = [];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $errors[] = 'All required fields must be filled';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to terms and conditions';
    }
    
    if (empty($errors)) {
        $db = new Database();
        $pdo = $db->getConnection();
        
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            } else {
                // Create new user
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, phone, password_hash, role, is_verified) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([$first_name, $last_name, $email, $phone, $password_hash, $role, false]);
                
                // Get user ID for verification
                $user_id = $pdo->lastInsertId();
                
                // Create verification token
                $verification_token = generateToken();
                $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Insert verification record
                $stmt = $pdo->prepare("
                    INSERT INTO user_verification (user_id, email, verification_token, token_type, expires_at) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([$user_id, $email, $verification_token, 'registration', $expires_at]);
                
                // Send verification email
                $verification_link = "verify.php?email=" . urlencode($email) . "&token=" . $verification_token;
                $subject = "Verify your AutoMarket account";
                $message = "
                    <h2>Account Verification</h2>
                    <p>Hello $first_name,</p>
                    <p>Thank you for registering with AutoMarket. Please click the link below to verify your email address:</p>
                    <p><a href='$verification_link' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Email</a></p>
                    <p>This link will expire in 24 hours.</p>
                    <p>If you didn't create this account, please ignore this email.</p>
                ";
                
                // Send email (you'll need to configure email sending)
                // mail($email, $subject, $message);
                
                // Redirect to verification page
                header('Location: verification-pending.php?email=' . urlencode($email));
                exit();
            }
        } catch(PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AutoMarket</title>
    
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
                <a href="login.html" class="glass-button">Login</a>
                <a href="register-with-verification.php" class="glass-button primary">Register</a>
                <a href="dashboard.php" class="glass-button">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Registration Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card glass-card animate-fadeInUp">
                    <div class="auth-header">
                        <h2>Create Account</h2>
                        <p>Join AutoMarket and start your journey</p>
                    </div>
                    
                    <!-- Alert container for dynamic messages -->
                    <div id="alert-container"></div>
                    
                    <form id="register-form" class="auth-form" method="post" action="register-with-verification.php">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="first_name" name="first_name" class="form-input" required 
                                           placeholder="First name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="last_name" name="last_name" class="form-input" required 
                                           placeholder="Last name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-input" required 
                                       placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" class="form-input" 
                                       placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="role" class="form-label">Account Type</label>
                            <div class="input-group">
                                <i class="fas fa-user-tag"></i>
                                <select id="role" name="role" class="form-select" required>
                                    <option value="">Select account type</option>
                                    <option value="buyer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                                    <option value="seller" <?php echo (isset($_POST['role']) && $_POST['role'] === 'seller') ? 'selected' : ''; ?>>Seller</option>
                                    <option value="rental_customer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'rental_customer') ? 'selected' : ''; ?>>Rental Customer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" class="form-input" required 
                                           placeholder="Create a password" minlength="8">
                                    <button type="button" class="password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-hint">Password must be at least 8 characters long</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required 
                                           placeholder="Confirm your password">
                                    <button type="button" class="password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" id="terms" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-error">
                                <?php foreach ($errors as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="form-submit">Create Account</button>
                    </form>
                    
                    <div class="auth-divider">
                        <span>OR</span>
                    </div>
                    
                    <div class="social-login">
                        <button class="social-button google">
                            <i class="fab fa-google"></i>
                            <span>Sign up with Google</span>
                        </button>
                        <button class="social-button facebook">
                            <i class="fab fa-facebook-f"></i>
                            <span>Sign up with Facebook</span>
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.html">Login here</a></p>
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
        // Registration functionality
        class RegistrationHandler {
            constructor() {
                this.form = document.getElementById('register-form');
                this.submitButton = this.form.querySelector('.form-submit');
                this.alertContainer = document.getElementById('alert-container');
                
                this.init();
            }
            
            init() {
                // Form submission
                this.form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handleRegistration();
                });
                
                // Password toggles
                const passwordToggles = this.form.querySelectorAll('.password-toggle');
                passwordToggles.forEach(toggle => {
                    toggle.addEventListener('click', () => {
                        this.togglePassword(toggle);
                    });
                });
            }
            
            togglePassword(button) {
                const input = button.previousElementSibling;
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                
                const icon = button.querySelector('i');
                icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
            
            handleRegistration() {
                this.setLoading(true);
                
                // Submit form normally
                this.form.submit();
            }
            
            setLoading(loading) {
                if (loading) {
                    this.submitButton.disabled = true;
                    this.submitButton.textContent = 'Creating Account...';
                } else {
                    this.submitButton.disabled = false;
                    this.submitButton.textContent = 'Create Account';
                }
            }
        }
        
        // Initialize registration handler when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            new RegistrationHandler();
        });
    </script>
</body>
</html>
