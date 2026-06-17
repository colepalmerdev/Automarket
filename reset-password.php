<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$success_message = '';
$show_form = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $errors[] = 'Please enter and confirm your new password.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if (empty($token)) {
        $errors[] = 'Invalid or missing password reset token.';
    }

    if (empty($errors)) {
        try {
            $db = new Database();
            $pdo = $db->getConnection();

            if ($pdo === null) {
                $errors[] = 'Unable to connect to the database. Please try again later.';
            } else {
                $stmt = $pdo->prepare('SELECT id, password_reset_expires_at FROM users WHERE password_reset_token = ?');
                $stmt->execute([$token]);
                $user = $stmt->fetch();

                if (!$user) {
                    $errors[] = 'Invalid password reset token.';
                } elseif (new DateTime($user['password_reset_expires_at']) < new DateTime()) {
                    $errors[] = 'This password reset link has expired.';
                }

                if (empty($errors)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, password_reset_token = NULL, password_reset_expires_at = NULL WHERE id = ?');
                    $stmt->execute([$password_hash, $user['id']]);

                    $success_message = 'Your password has been reset successfully. You may now login.';
                    $show_form = false;
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'An error occurred. Please try again later.';
        }
    }
} else {
    if (!empty($token)) {
        try {
            $db = new Database();
            $pdo = $db->getConnection();

            if ($pdo !== null) {
                $stmt = $pdo->prepare('SELECT id, password_reset_expires_at FROM users WHERE password_reset_token = ?');
                $stmt->execute([$token]);
                $user = $stmt->fetch();

                if (!$user || new DateTime($user['password_reset_expires_at']) < new DateTime()) {
                    $errors[] = 'This password reset link is invalid or has expired.';
                    $show_form = false;
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'An error occurred. Please try again later.';
            $show_form = false;
        }
    } else {
        $errors[] = 'No password reset token provided.';
        $show_form = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - AutoMarket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/enhanced-styles.css">
</head>
<body class="auth-page">
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="logo"><i class="fas fa-car"></i> AutoMarket</a>
            <div class="nav-actions">
                <a href="login.php" class="glass-button">Login</a>
                <a href="register-simple.php" class="glass-button primary">Register</a>
            </div>
        </div>
    </nav>

    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card glass-card animate-fadeInUp">
                    <div class="auth-header">
                        <h2>Reset Your Password</h2>
                        <p>Choose a new password for your account.</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <p><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_form): ?>
                        <form method="post" action="reset-password.php">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                            <div class="form-group">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" class="form-input" required placeholder="Enter new password" minlength="8">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required placeholder="Confirm new password" minlength="8">
                                </div>
                            </div>

                            <button type="submit" class="form-submit">Reset Password</button>
                        </form>
                    <?php else: ?>
                        <div class="auth-footer">
                            <p><a href="forgot-password.php">Request a new password reset link</a></p>
                            <p><a href="login.php">Back to login</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 AutoMarket. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>