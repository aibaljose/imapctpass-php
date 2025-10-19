<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Store user info temporarily to show logout message
$was_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';

// Clear all session variables
$_SESSION = array();

// If it's desired to kill the session cookie, do this
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set cache control headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Past date

// Create a success message
if ($was_logged_in) {
    // Start a new session for the success message
    session_start();
    $_SESSION['success_message'] = "You have been logged out successfully.";
}

// Add a timestamp parameter to prevent browser caching
header("Location: index.php?logout_success=true&nocache=" . time());
exit();
?>