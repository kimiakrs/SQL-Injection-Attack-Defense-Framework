<?php
//Start session and redirect to login page if not authenticated
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Include the database connection using PDO
require 'db.php';

// Get the logged-in user's ID from session ID=3
$uid = $_SESSION['user_id'];

try {
    // Use stored procedure to prevent SQL injection for profile query
    $stmt = $pdo->prepare("CALL StudentInformation(?)");
    $stmt->execute([$uid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // If no student found, redirect to login (session might be invalid)
    if (!$student) {
        header('Location: login.php');
        exit;
    }
    $stmt = $pdo->prepare("CALL StundetCourses(?)"); 
    $stmt->execute([$uid]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Call stored procedure for exams
    $stmt = $pdo->prepare("CALL StudentExams(?)");
    $stmt->execute([$uid]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

} catch (PDOException $e) {
    // Handle and log any unexpected DB errors
    error_log("Database error in home.php: " . $e->getMessage());
    // For debugging (remove/comment in production): 
    // die($e->getMessage());
    die("Internal Server Error. Please try again later.");
}

?>
<!doctype html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/style.css" rel="stylesheet" />
</head>
<body class='bg-body-tertiary p-4'>
<div class='container'>
    <?php include 'nav.inc.php'; ?>

    <div class='card shadow-sm mb-4'>
        <div class='card-header bg-primary text-white'>Profile</div>
        <div class='card-body'>
            <div><strong>Name:</strong> <?= htmlspecialchars($student['first_name'].' '.$student['family_name']) ?></div>
            <div><strong>ID:</strong> <?= htmlspecialchars($student['matriculation_number']) ?></div>
            <div><strong>Program:</strong> <?= htmlspecialchars($student['program_name']) ?></div>
            <div><strong>Length:</strong> <?= htmlspecialchars($student['total_semesters']) ?> semesters</div>
        </div>
    </div>

    <div class='card shadow-sm mb-4'>
        <div class='card-header bg-primary text-white'>Courses</div>
        <div class='card-body p-0'>
            <table class='table table-striped mb-0'>
                <thead class='table-light'>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Professor</th>
                        <th>Room</th>
                        <th>Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $i => $c): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($c['course_code']) ?></td>
                            <td><?= htmlspecialchars($c['course_name']) ?></td>
                            <td><?= htmlspecialchars($c['prof_fn'].' '.$c['prof_ln']) ?></td>
                            <td><?= htmlspecialchars($c['room_number']) ?></td>
                            <td><?= htmlspecialchars($c['duration_hours']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$courses): ?>
                        <tr><td colspan='6' class='text-center text-muted'>No courses</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class='card shadow-sm'>
        <div class='card-header bg-primary text-white'>My Exams</div>
        <div class='card-body p-0'>
            <table class='table table-striped mb-0'>
                <thead class='table-light'>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Course</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($exams as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['exam_date']) ?></td>
                            <td><?= htmlspecialchars($e['time']) ?></td>
                            <td><?= htmlspecialchars($e['course_name']) ?></td>
                            <td><?= htmlspecialchars($e['exam_type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$exams): ?>
                        <tr><td colspan='4' class='text-center text-muted'>No exams</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
