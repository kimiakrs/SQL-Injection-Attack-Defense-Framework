<?php
// Start the session to track logged-in users
session_start();

// Include the database connection
require 'db.php';

// Initialize error message variable
$error = '';

// Check if the form was submitted using the POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // WARNING: This SQL is vulnerable to SQL Injection and uses plaintext passwords
    // A secure version would use prepared statements and hashed passwords
    $sql = "SELECT id FROM portal_users 
            WHERE email = '" . $_POST['email'] . "' 
            AND password = '" . $_POST['password'] . "'";

    // Execute the query and fetch the result
    $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

    // If a user with matching credentials is found
    if ($row) {
        // Store user ID in the session to track login state
        $_SESSION['user_id'] = $row['id'];

        // Redirect to the home page
        header('Location: home.php');
        exit;
    }

    // Set error message if login fails
    $error = 'Invalid credentials';
}
?>
<!doctype html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <title>Login</title>
    <!-- Bootstrap CSS for styling -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='d-flex justify-content-center align-items-center vh-100 bg-light'>

<!-- Login Form Container -->
<div class='card p-4 shadow rounded-4' style='min-width:22rem'>
    <h3 class='text-center mb-3'>Student Portal</h3>

    <!-- Show error message if login fails -->
    <?php if ($error): ?>
        <div class='alert alert-danger'><?= $error ?></div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method='post'>
        <div class='mb-3'>
            <label class='form-label'>Email</label>
            <input name='email' class='form-control'>
        </div>
        <div class='mb-3'>
            <label class='form-label'>Password</label>
            <input name='password' class='form-control'>
        </div>
        <button class='btn btn-primary w-100'>Log in</button>
    </form>
</div>

</body>
</html>
