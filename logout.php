<?php
require_once 'includes/functions.php';

// Destroy session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home
header('Location: index.php');
exit();
?>
