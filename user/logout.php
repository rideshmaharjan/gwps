<?php
session_start();

// Store logout message before destroying session
$logout_message = "You have been successfully logged out. See you again!";

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Start a new session to store the logout message
session_start();
$_SESSION['logout_message'] = $logout_message;

// Redirect to home page
header('Location: ../index.php');
exit();
?>