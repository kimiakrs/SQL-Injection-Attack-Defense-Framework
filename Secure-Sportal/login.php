<?php
// Start a secure session with recommended settings before session_start()
ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access to session cookie
ini_set('session.use_strict_mode', 1);  // Prevent session fixation
ini_set('session.cookie_secure', 1);      // Only send session cookie over HTTPS

session_start();

require 'db.php'; // Securely connect to the database using PDO

// Show logout success message if available, then remove it (flash message)
$message = '';
if (isset($_SESSION['logout_message'])) {
    $message = $_SESSION['logout_message']; // Store message
    unset($_SESSION['logout_message']);     // Remove it after showing once
}

// Generate a CSRF token if it does not exist yet for the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Prevent CSRF attacks
}

// Redirect logged-in users directly to home page
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

// ---------- LOGIN PROCESS ----------
$error = '';

// Initialize login attempts counter if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token to prevent Cross-Site Request Forgery attacks
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token'); // Stop if CSRF token doesn't match
    }

    // Get and sanitize user inputs
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation: check if email is valid and password is not empty
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        $error = "Please enter a valid email and password.";
    } else {
        // --- Wrap DB query in try-catch for secure error handling ---
        try {
            // Prepare a secure query to prevent SQL injection
            $stmt = $pdo->prepare("SELECT id, email, password FROM portal_users WHERE role='student' AND email=? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify password hash and check login attempts
            if ($user && password_verify($password, $user['password'])) {
                // Reset login attempts on successful login
                $_SESSION['login_attempts'] = 0;

                session_regenerate_id(true); // Prevent session fixation attacks
                $_SESSION['user_id']    = $user['id'];    // Store user ID in session
                $_SESSION['user_email'] = $user['email']; // Store user email in session

                header('Location: home.php'); // Redirect on success
                exit;
            } else {
                $_SESSION['login_attempts']++; // Increase failed attempt count

                // Lockout after 5 failed attempts - simple example
                if ($_SESSION['login_attempts'] >= 5) {
                    $error = "Too many login attempts. Please try again later.";
                } else {
                    $error = "Invalid credentials"; // Show login error
                }
            }
        } catch (PDOException $e) {
            // Secure error: never show raw DB errors to the user!
            $error = "Database error. Please try again later.";
            // error_log($e->getMessage()); // Optional: log for admin/debugging
        }
    }
}
?>
<!doctype html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <title>Login</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href="/assets/css/style.css" rel="stylesheet" />
</head>
<body class='d-flex justify-content-center align-items-center vh-100 bg-light'>
<div class='card p-4 shadow rounded-4' style='min-width:22rem'>
    <h3 class='text-center mb-3'>Student Portal</h3>

    <!-- Display logout success message -->
    <?php if ($message): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Display login error messages -->
    <?php if ($error): ?>
        <div class='alert alert-danger' role='alert'><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <!-- Login form -->
    <form method='post' autocomplete="off">
        <!-- CSRF token for security -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <!-- Email field -->
        <div class='mb-3'>
            <label class='form-label'>Email</label>
            <input name='email' class='form-control' type='text' autofocus>
        </div>
        <!-- Password field -->
        <div class='mb-3'>
            <label class='form-label'>Password</label>
            <input name='password' class='form-control' type='password' autocomplete="new-password">
        </div>
        <!-- Submit button -->
        <button class='btn btn-primary w-100'>Log in</button>
        <!-- Forgot password link -->
        <div class="text-center mt-3">
            <a href="reset_password.php" class="small text-decoration-none">Forgot your password?</a>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
