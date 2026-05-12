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
    
    // Basic validation
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
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            } else {
                // Generate unique username
                $username = explode('@', $email)[0] . '_' . time();
                
                // Create new user
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Simple SQL without complex columns
                $sql = "INSERT INTO users (first_name, last_name, email, phone, password_hash, role, username) 
                        VALUES ('$first_name', '$last_name', '$email', '$phone', '$password_hash', '$role', '$username')";
                
                $result = $pdo->exec($sql);
                
                if ($result) {
                    // Registration successful - redirect to login
                    header('Location: login.html');
                    exit();
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        } catch(PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
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
                <a href="register-simple.php" class="glass-button primary">Register</a>
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
                    
                    <!-- Error display -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="register-form" class="auth-form" method="post" action="register-simple.php">
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
                                I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="form-submit">Create Account</button>
                    </form>
                    
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
