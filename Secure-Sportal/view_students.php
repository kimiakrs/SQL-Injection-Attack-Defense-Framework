<?php
session_start();
require 'db.php';

//Check if user is logged in. If not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

//Get the current user's ID from session.
$id = $_SESSION['user_id'];
$student = null;
$result = [];
$error = null;

try {
    //Calling the stored procedure to fetch profile & class info for the student.
    $stmt = $pdo->prepare("CALL StudentProfiles(?)");
    $stmt->execute([$id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); //Fetch all rows as associative arrays
    $student = $result[0] ?? null;               //Use the first row as the main student profile

    //Release the connection
    $stmt->closeCursor();

} catch (PDOException $e) {
    //If a database error occurs, show a generic message
    $student = null;
    $error = "Database error. Please try again later.";
  
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>My Profile & Classes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f7f7fa; }
    .card { border-radius: 1.25rem; }
    .table th, .table td { vertical-align: middle; }
  </style>
</head>
<body class="bg-body-tertiary p-4">
<div class="container" style="max-width: 750px;">
  <?php include 'nav.inc.php'; ?>
  <h2 class="mb-4 fw-bold text-center">My Profile & Classes</h2>

  <?php if ($error): ?>
    <!-- Show error alert if something went wrong -->
    <div class="alert alert-danger shadow-sm"><?= htmlspecialchars($error) ?></div>
  <?php elseif (!$student): ?>
    <!--  Show warning if no student profile was found -->
    <div class="alert alert-warning shadow-sm">No profile found for your account.</div>
  <?php else: ?>
    <!--  Display user profile information -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white fw-bold">Profile</div>
      <div class="card-body">
        <div><strong>User ID:</strong> <?= htmlspecialchars($student['user_id']) ?></div>
        <div><strong>Name:</strong> <?= htmlspecialchars($student['first_name']) ?> <?= htmlspecialchars($student['family_name']) ?></div>
        <div><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></div>
        <div><strong>Matriculation Number:</strong> <?= htmlspecialchars($student['matriculation_number']) ?></div>
        <div><strong>Program:</strong> <?= htmlspecialchars($student['program_name']) ?> (<?= htmlspecialchars($student['program_code']) ?>)</div>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-secondary text-white fw-bold">Class Schedule</div>
      <div class="card-body p-0">
        <?php
        // Check if student has any class schedule (course_name is not null)
        $hasSchedule = false;
        foreach ($result as $row) {
            if ($row['course_name']) { $hasSchedule = true; break; }
        }
        ?>
        <?php if ($hasSchedule): ?>
          <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Weekday</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Room</th>
                <th>Professor</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($result as $row): ?>
                <?php if ($row['course_name']): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td><?= htmlspecialchars($row['weekday']) ?></td>
                    <td><?= htmlspecialchars($row['start_time']) ?></td>
                    <td><?= htmlspecialchars($row['end_time']) ?></td>
                    <td><?= htmlspecialchars($row['room_number']) ?></td>
                    <td><?= htmlspecialchars($row['professor_name']) ?></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        <?php else: ?>
          <!-- If student has no classes, show info message -->
          <div class="alert alert-info m-3">No class schedule found for you.</div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
