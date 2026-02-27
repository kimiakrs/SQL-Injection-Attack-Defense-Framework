<?php
// Include database connection
require 'db.php';

// Get student ID from query string or default to '3'
$id = $_GET['id'] ?? '3';

try {
    // Intentionally vulnerable raw SQL query
    // WARNING: This is subject to SQL injection if $id is manipulated via URL
    $sql = "
        SELECT 
            sp.user_id,
            sp.first_name,
            sp.family_name,
            sp.matriculation_number,
            pu.email,
            ap.name AS program_name,
            ap.code AS program_code,
            co.weekday,
            co.start_time,
            co.end_time,
            co.room_number,
            cc.course_name,
            cc.course_code,
            CONCAT(pp.first_name, ' ', pp.family_name) AS professor_name
        FROM student_profiles sp
        LEFT JOIN portal_users pu ON sp.user_id = pu.id
        LEFT JOIN academic_programs ap ON sp.program_id = ap.id
        LEFT JOIN student_enrolments se ON sp.user_id = se.student_user_id
        LEFT JOIN class_offerings co ON se.class_id = co.id
        LEFT JOIN course_catalog cc ON co.course_id = cc.id
        LEFT JOIN professor_profiles pp ON co.professor_user_id = pp.user_id
        WHERE sp.user_id = $id
    ";

    // Execute the query and fetch all matching rows
    $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Extract the first row for student profile display
    $student = $result[0] ?? null;

} catch (PDOException $e) {
    // Handle query error
    $student = null;
    $error = $e->getMessage();
}
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">

  <!-- Duplicate head tag and meta, retained as-is per request -->
  <!doctype html><html lang='en'><head>
  <meta charset='utf-8'><title>Home</title>
  <!-- Bootstrap CDN for styling -->
  <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head>

<body class='bg-body-tertiary p-4'>
<div class='container'>

<!-- Navigation bar include -->
<?php include 'nav.inc.php'; ?>

  <title>View Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
<div class="container">
  <h2>Student Viewer </h2>

  <!-- Display SQL error if one occurred -->
  <?php if (isset($error)): ?>
    <div class="alert alert-danger">SQL Error: <?=htmlspecialchars($error)?></div>

  <!-- If no student found -->
  <?php elseif (!$student): ?>
    <div class="alert alert-warning">No student found with ID <?=htmlspecialchars($id)?></div>

  <!-- Display student profile if found -->
  <?php else: ?>
    <ul class="list-group mb-4">
      <li class="list-group-item"><strong>User ID:</strong> <?=$student['user_id']?></li>
      <li class="list-group-item"><strong>Name:</strong> <?=$student['first_name']?> <?=$student['family_name']?></li>
      <li class="list-group-item"><strong>Email:</strong> <?=$student['email']?></li>
      <li class="list-group-item"><strong>Matriculation Number:</strong> <?=$student['matriculation_number']?></li>
      <li class="list-group-item"><strong>Program:</strong> <?=$student['program_name']?> (<?=$student['program_code']?>)</li>
    </ul>

    <h4>Class Schedule</h4>

    <!-- If class results are available -->
    <?php if (count($result) > 0): ?>
      <table class="table table-bordered">
        <thead>
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
            <?php if ($row['course_name']): // Only show if course info exists ?>
              <tr>
                <td><?=htmlspecialchars($row['course_code'])?></td>
                <td><?=htmlspecialchars($row['course_name'])?></td>
                <td><?=htmlspecialchars($row['weekday'])?></td>
                <td><?=htmlspecialchars($row['start_time'])?></td>
                <td><?=htmlspecialchars($row['end_time'])?></td>
                <td><?=htmlspecialchars($row['room_number'])?></td>
                <td><?=htmlspecialchars($row['professor_name'])?></td>
              </tr>
            <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>

    <!-- No classes found -->
    <?php else: ?>
      <div class="alert alert-info">No class schedule found for this student.</div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
