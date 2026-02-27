<?php
// Include database connection
require 'db.php';

// Initialize a message variable to store status or error
$message = '';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // WARNING: This is an insecure SQL query
    // It directly injects user input into the SQL without escaping
    // This makes it vulnerable to SQL injection attacks
    $sql = "
        UPDATE portal_users 
        SET password = '" . $_POST['new_password'] . "' 
        WHERE email = '" . $_POST['email'] . "'
    ";

    try {
        // Execute the update query
        $affected = $pdo->exec($sql);

        // If at least one row was affected, the password was updated
        if ($affected) {
            $message = 'Password updated successfully.';
        } else {
            $message = 'No user found with that email.';
        }
    } catch (PDOException $e) {
        // Catch and show raw SQL error (NOT recommended in production)
        $message = 'SQL Error: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password</title>
  <!-- Bootstrap styling for layout -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-body-tertiary p-4">
  <div class="container">
    <!-- Navigation bar (external file) -->
    <?php include 'nav.inc.php'; ?> 

    <!-- Reset Password Card UI -->
    <div class="d-flex justify-content-center align-items-center vh-100">
      <div class="card p-4 shadow rounded-4" style="min-width:22rem">
        <h3 class="text-center mb-3">Reset Password</h3>

        <!-- Display message if set -->
        <?php if ($message): ?>
          <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <!-- Reset Form -->
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" class="form-control" placeholder="Enter your email">
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input name="new_password" class="form-control" placeholder="New password">
          </div>
          <button class="btn btn-danger w-100">Reset Password</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
