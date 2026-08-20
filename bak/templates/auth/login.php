<?php
// Start the session to set session variables.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include authentication functions.
include '../../inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (authenticate_user($email, $password)) {
        $_SESSION['email'] = $email;
        // Redirect to the homepage
        header('Location: ../../');
        exit;
    } else {
        // Redirect back with error
        header('Location: ../../?error=login_failed');
        exit;
    }
} else {
    // Direct access not allowed
    header('Location: ../../');
    exit;
}
