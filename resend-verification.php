<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$errors = [];
$success_message = '';
$email = cleanInput($_GET['email'] ?? $_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($email)) {
    if (empty($email)) {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        try {
            $db = new Database();
            $pdo = $db->getConnection();

            if ($pdo === null) {
                $errors[] = 'Unable to connect to the database. Please try again later.';
            } else {
                $stmt = $pdo->prepare('SELECT id, first_name, is_verified FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && !$user['is_verified']) {
                    $verification_token = generateToken();
                    $stmt = $pdo->prepare('UPDATE users SET verification_token = ? WHERE id = ?');
                    $stmt->execute([$verification_token, $user['id']]);

                    $verification_link = "http://localhost/cars/verify.php?token=" . $verification_token;
                    $subject = 'Verify your AutoMarket account';
                    $body = "<h2>Verify your AutoMarket account</h2>" .
                        "<p>Hi " . htmlspecialchars($user['first_name']) . ",</p>" .
                        "<p>Please verify your email address by clicking the button below:</p>" .
                        "<p><a href='" . $verification_link . "' style='display:inline-block;padding:12px 18px;background:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;'>Verify Email</a></p>" .
                        "<p>If you did not request this, you can ignore this email.</p>";

                    sendEmail($email, $subject, $body);
                    $success_message = 'A verification email has been sent. Please check your inbox.';
                } elseif ($user && $user['is_verified']) {
                    $success_message = 'This account is already verified. You can login now.';
                } else {
                    $success_message = 'If an account exists for that email, a verification link has been sent.';
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'An error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - AutoMarket</title>
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
                        <h2>Resend Verification</h2>
                        <p>Enter your email to resend your activation link</p>
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

                    <form method="post" action="resend-verification.php">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-input" required placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                        </div>

                        <button type="submit" class="form-submit">Resend Email</button>
                    </form>

                    <div class="auth-footer">
                        <p>Back to <a href="login.php">Login</a></p>
                    </div>
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