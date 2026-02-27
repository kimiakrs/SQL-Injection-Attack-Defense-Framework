<?php
// Start the session to maintain user login state
session_start();

// If the user is not logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Include database connection
require 'db.php';

// Get current logged-in user's ID from the session
$uid = $_SESSION['user_id'];

/* ===============================
   Fetch Student Profile Information
   =============================== */

// Get student's full profile and their academic program info
$profileSql = "SELECT sp.*, ap.name AS program_name, ap.total_semesters
               FROM student_profiles sp
               LEFT JOIN academic_programs ap ON ap.id = sp.program_id
               WHERE sp.user_id = $uid";

// Run the profile query and store the result
$student = $pdo->query($profileSql)->fetch(PDO::FETCH_ASSOC);

/* ===============================
   Fetch Enrolled Courses
   =============================== */

// Get list of courses the student is enrolled in,
// along with course info, professor name, room, and duration
$cSql = "SELECT cc.course_code, cc.course_name, co.room_number, co.duration_hours,
                pp.first_name AS prof_fn, pp.family_name AS prof_ln
         FROM   student_enrolments se
         JOIN   class_offerings   co ON co.id = se.class_id
         JOIN   course_catalog    cc ON cc.id = co.course_id
         JOIN   portal_users      pu ON pu.id = co.professor_user_id
         JOIN   professor_profiles pp ON pp.user_id = pu.id
         WHERE  se.student_user_id = $uid";

// Run the courses query and store all rows in an array
$courses = $pdo->query($cSql)->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   Fetch Upcoming Exams
   =============================== */

// Get list of exams scheduled for the student,
// including date, time (formatted), type, and course name
$eSql = "SELECT e.exam_date,
                TIME_FORMAT(e.exam_time,'%H:%i') AS time,
                e.exam_type,
                cc.course_name
         FROM   student_exams e
         JOIN   class_offerings co ON co.id = e.class_id
         JOIN   course_catalog  cc ON cc.id = co.course_id
         WHERE  e.student_user_id = $uid";

// Run the exams query and store all rows in an array
$exams = $pdo->query($eSql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <title>Home</title>
    <!-- Bootstrap CSS for styling -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-body-tertiary p-4'>
<div class='container'>

<!-- Navigation Bar -->
<?php include 'nav.inc.php'; ?>

<!-- ===================== Profile Card ===================== -->
<div class='card shadow-sm mb-4'>
    <div class='card-header bg-primary text-white'>Profile</div>
    <div class='card-body'>
        <div><strong>Name:</strong> <?= $student['first_name'] . ' ' . $student['family_name'] ?></div>
        <div><strong>ID:</strong> <?= $student['matriculation_number'] ?></div>
        <div><strong>Program:</strong> <?= $student['program_name'] ?></div>
        <div><strong>Length:</strong> <?= $student['total_semesters'] ?> semesters</div>
    </div>
</div>

<!-- ===================== Courses Table ===================== -->
<div class='card shadow-sm mb-4'>
    <div class='card-header bg-primary text-white'>Courses</div>
    <div class='card-body p-0'>
        <table class='table table-striped mb-0'>
            <thead class='table-light'>
                <tr>
                    <th>#</th><th>Code</th><th>Name</th><th>Professor</th><th>Room</th><th>Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $i => $c): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= $c['course_code'] ?></td>
                        <td><?= htmlspecialchars($c['course_name']) ?></td>
                        <td><?= htmlspecialchars($c['prof_fn'] . ' ' . $c['prof_ln']) ?></td>
                        <td><?= $c['room_number'] ?></td>
                        <td><?= $c['duration_hours'] ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Show if no course records found -->
                <?php if (!$courses): ?>
                    <tr><td colspan='6' class='text-center text-muted'>No courses</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== Exams Table ===================== -->
<div class='card shadow-sm'>
    <div class='card-header bg-primary text-white'>My Exams</div>
    <div class='card-body p-0'>
        <table class='table table-striped mb-0'>
            <thead class='table-light'>
                <tr><th>Date</th><th>Time</th><th>Course</th><th>Type</th></tr>
            </thead>
            <tbody>
                <?php foreach ($exams as $e): ?>
                    <tr>
                        <td><?= $e['exam_date'] ?></td>
                        <td><?= $e['time'] ?></td>
                        <td><?= htmlspecialchars($e['course_name']) ?></td>
                        <td><?= $e['exam_type'] ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Show if no exam records found -->
                <?php if (!$exams): ?>
                    <tr><td colspan='4' class='text-center text-muted'>No exams</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
</body>
</html>
