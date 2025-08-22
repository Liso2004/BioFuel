<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Save cart to database before logging out
if (isset($_SESSION['user_id'])) {
    include 'includes/config.php';
    if (isset($_SESSION['cart'])) {
        saveUserCart($_SESSION['user_id'], $_SESSION['cart'], $conn);
    }
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>