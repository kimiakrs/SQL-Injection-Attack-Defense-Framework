<?php
// Start the session to access existing session data
session_start();

// Destroy all session data (logs the user out)
session_destroy();

// Redirect the user to the login page
header('Location: index.php');

// Stop script execution after redirection
exit;
?>
