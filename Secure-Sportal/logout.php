<?php
// --- Advanced session security settings (optional but best practice) ---
ini_set('session.cookie_httponly', 1);   // Prevent JavaScript from accessing session cookie
ini_set('session.cookie_secure', 1);     // Only send session cookie over HTTPS
ini_set('session.use_strict_mode', 1);   // Prevent session fixation attacks

session_start();

// Set a flash message that can be displayed on the next page load
$_SESSION['logout_message'] = "You have been logged out successfully.";

// Clear all session variables (removes all data from the session)
$_SESSION = [];

// If the session uses cookies, then delete the session cookie as well
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();  // Get current cookie parameters

    // Set the session cookie to expire in the past (effectively deletes it)
    setcookie(
        session_name(),     // Name of the session cookie
        '',                 // Set its value to empty
        time() - 42000,     // Set expiration time in the past
        $params["path"],    // Maintain the same path
        $params["domain"],  // Maintain the same domain
        $params["secure"],  // Maintain same security (HTTPS only)
        $params["httponly"] // Maintain HTTPOnly flag
    );
}

// Destroy session data on the server
session_destroy();

// Redirect to login.php
header('Location: login.php');
// Ensure no further code is executed after redirect
exit;
?>
