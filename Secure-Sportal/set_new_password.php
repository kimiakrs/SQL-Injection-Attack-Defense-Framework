<?php
// --- Secure session settings (before session_start) ---
ini_set('session.cookie_httponly', 1);   // Prevent JavaScript from accessing session cookie
ini_set('session.use_strict_mode', 1);   // Prevent session fixation
ini_set('session.cookie_secure', 1);     // Only send session cookie over HTTPS (set to 1 if using HTTPS)

session_start();
require 'db.php';

$error = '';
$success = '';

// 1. Only allow access if reset_email is set in session (prevents direct access)
if (!isset($_SESSION['reset_email'])) {
    header('Location: reset_password.php');
    exit;
}

// 2. CSRF token setup
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. On form submit, validate CSRF, input, and update password in DB
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3a. Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Strong password policy
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)
        || !preg_match('/[a-z]/', $password)
        || !preg_match('/[0-9]/', $password)
        || !preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $error = "Password must be at least 8 characters, and include uppercase, lowercase, digit, and symbol.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // 3c. Try/catch for DB update
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $email = $_SESSION['reset_email'];
            $stmt = $pdo->prepare("UPDATE portal_users SET password=? WHERE email=?");
            $stmt->execute([$hash, $email]);

            // (Optional) Destroy all other sessions for this user, if possible
            // (Not implemented unless session DB table is used)

            unset($_SESSION['reset_email']);
            $success = "Your password has been reset successfully. <a href='login.php'>Log in now</a>";
        } catch (PDOException $e) {
            error_log("Reset password error: " . $e->getMessage());
            $error = "Database error. Please try again later.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Set New Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
<div class="card p-4 shadow rounded-4" style="min-width:22rem">
    <h3 class="text-center mb-3">Set New Password</h3>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (!$success): ?>
    <form method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input name="password" type="password" class="form-control" required autofocus>
            <div class="form-text">Minimum 8 chars, must include uppercase, lowercase, number, and symbol.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input name="confirm_password" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Reset Password</button>
    </form>
    <?php endif; ?>
    <div class="mt-3 text-center">
        <a href="login.php">Back to Login</a>
    </div>
</div>
</body>
</html>
