<?php
/**
 * Session Checker Script
 * 
 * This file can be included at the top of each page to ensure session consistency
 * across the entire application. It forces a session to be consistent and correctly
 * reloaded when there are discrepancies.
 */

// Don't initialize a new session here as this file is included in pages that already started sessions
// We only want the JavaScript part to run

// Check if this is an AJAX request checking for session status
if (isset($_GET['check_session'])) {
    // Ensure session is available for this AJAX request
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'logged_in' => isset($_SESSION['user_id']),
        'user_name' => $_SESSION['user_name'] ?? null,
        'time' => time()
    ]);
    exit;
}
?>

<script>
// Function to check session status consistency across tabs/windows
function checkSessionConsistency() {
    // Add a random parameter to avoid caching
    fetch('session-checker.php?check_session=1&nocache=' + Math.random())
        .then(response => response.json())
        .then(data => {
            const hasSessionElements = document.getElementById('profileDropdownBtn') !== null;
            
            // If server says logged in but page doesn't show it (or vice versa)
            if ((data.logged_in && !hasSessionElements) || (!data.logged_in && hasSessionElements)) {
                console.log('Session state mismatch detected. Reloading page...');
                window.location.reload(true);
            }
        })
        .catch(error => console.error('Error checking session:', error));
}

// Run the check when page loads and periodically
document.addEventListener('DOMContentLoaded', function() {
    // Initial check after page has fully loaded
    setTimeout(checkSessionConsistency, 1000);
    
    // Periodic check every 30 seconds
    setInterval(checkSessionConsistency, 30000);
});
</script>