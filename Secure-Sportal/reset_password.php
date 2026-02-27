<?php
// --- Advanced session security settings for best practices ---
ini_set('session.cookie_httponly', 1);   // Prevent JavaScript access to session cookie
ini_set('session.cookie_secure', 1);     // Only send session cookie over HTTPS
ini_set('session.use_strict_mode', 1);   // Enforce strict session handling to prevent fixation

session_start();
require 'db.php';

$error = '';

// --- CSRF Token generation (if it doesn't already exist) ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- (Optional) Simple rate limiting to slow down brute-force attacks ---
// if (!isset($_SESSION['reset_attempts'])) $_SESSION['reset_attempts'] = 0;
// if ($_SESSION['reset_attempts'] > 10) $error = "Too many requests. Please try again later.";

// --- Handle form submission securely ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    //Validate CSRF token to prevent CSRF attacks
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $email = trim($_POST['email'] ?? '');

    //validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // If rate limiting is enabled, uncomment below
        // $_SESSION['reset_attempts']++;

        //Use prepared statement to prevent SQL injection ---
        try {
            $stmt = $pdo->prepare("SELECT id FROM portal_users WHERE email=? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            // Log the database error and show only a generic message to the user
            error_log("DB error in reset_password: " . $e->getMessage());
            $error = "Internal Server Error. Please try again later.";
        }

        // If user exists, proceed to next step in reset flow
        if (isset($user) && $user) {
            $_SESSION['reset_email'] = $email;
            header('Location: set_new_password.php');
            exit;
        } elseif (!isset($error)) {
            // If user does not exist, show a general error message
            $error = "The email you entered is not registered in the system.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
    <!-- Load Bootstrap 5 for clean and professional UI -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f7f7fa !important; }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
<div class="card shadow p-4 rounded-4" style="min-width: 350px;">
    <h3 class="mb-3 text-center">Reset Password</h3>
    <!-- Show error messages using Bootstrap alert component -->
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <!-- Email entry form for password reset -->
    <form method="post" autocomplete="off" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="mb-3">
            <label for="email" class="form-label">Your Email</label>
            <input name="email" id="email" class="form-control" type="email" required autofocus>
        </div>
        <button class="btn btn-primary w-100" type="submit">Continue</button>
    </form>
    <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">Back to Login</a>
    </div>
</div>
<!-- Bootstrap script for interactive components -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
