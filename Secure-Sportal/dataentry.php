<?php
require 'db.php'; # This loads the database connection

function hashPassword($password) {    # This function turns plain text passwords into hashed 
    return password_hash($password, PASSWORD_DEFAULT);
}

try {
    $pdo->beginTransaction(); # This makes sure that either everything works, or nothing is saved, ensuring database consistency.
        

    // Programs
    $programs = [
        ['BCS-CS', 'B.Sc. Computer Science', 'BSc', 6, 'CS'],
        ['MCS-IT', 'M.Sc. Information Technology', 'MSc', 4, 'IT'],
        ['PHD-CS', 'Ph.D. Computer Science', 'PhD', 8, 'CS'],
        ['BBA-MKT', 'BBA Marketing', 'BSc', 6, 'Business'],
        ['MSC-FIN', 'M.Sc. Finance', 'MSc', 4, 'Business'],
    ];

    #This inserts each program into the database.
    $stmt = $pdo->prepare("INSERT INTO academic_programs (code, name, degree_level, total_semesters, department) VALUES (?, ?, ?, ?, ?)");
    foreach ($programs as $p) {
        $stmt->execute($p);
    } 

    // Program IDs 
    #After inserting, you get the generated IDs for each program and store them for later use
    $programIds = [];
    $res = $pdo->query("SELECT id, code FROM academic_programs");
    foreach ($res as $row) {
        $programIds[$row['code']] = $row['id'];
    }

    // Courses
    $courses = [
        ['CS101', 'Algorithms I', 'Algorithms', 5],
        ['CS102', 'Algorithms II', 'Algorithms', 8],
        ['CS103', 'Math I', 'Math', 8],
        ['CS104', 'Math II', 'Math', 4],
        ['CS105', 'Physics I', 'Physics', 8],
        ['IT201', 'Network Security', 'Security', 6],
        ['BUS101', 'Introduction to Marketing', 'Marketing', 4],
        ['FIN201', 'Financial Management', 'Finance', 5],
    ];
    # Inserting them into the database.
    $stmt = $pdo->prepare("INSERT INTO course_catalog (course_code, course_name, module_name, credits) VALUES (?, ?, ?, ?)");
    foreach ($courses as $c) {
        $stmt->execute($c);
    }

    // Professors (6 different ones)
    $professors = [
        ['t.smith@ue-germany.de', 'P@ssw0rd!2025', 'Tom', 'Smith', 'CS'],
        ['kokosmit@ue-germany.de', 'Secur3Key#987', 'Koko', 'Smith', 'CS'],
        ['linda.bauer@ue-germany.de', 'Linda#2025', 'Linda', 'Bauer', 'IT'],
        ['marco.schmidt@ue-germany.de', 'Marco$2025', 'Marco', 'Schmidt', 'Math'],
        ['elena.rossi@ue-germany.de', 'Elena@2025', 'Elena', 'Rossi', 'Business'],
        ['daniel.meyer@ue-germany.de', 'Daniel!2025', 'Daniel', 'Meyer', 'Physics'],
    ];

    // Students with unique strong passwords
    $students = [
        ['Arsilda.qato@ue-germany.de',     'Arsilda$Super2025',   'Arsilda',   'Qato',     null, 'A614580', $programIds['BCS-CS']],
        ['andres.sabillon@ue-germany.de',  'Andres!321ue',        'Andres',    'Sabillon', null, 'A614581', $programIds['BCS-CS']],
        ['sorasith.chormalee@ue-germany.de','Sor@ch2025#X',       'Sorasith',  'Chormalee',null, 'A614582', $programIds['MCS-IT']],
        ['kimia.karbasi@ue-germany.de',    'Kimia*Secret888',     'Kimia',     'Karbasi',  null, 'A614583', $programIds['MCS-IT']],
        ['senem.turkaydin@ue-germany.de',  'STur_2025pass!',      'Senem',     'Turkaydin',null, 'A614584', $programIds['MSC-FIN']],
        ['civan.ozel@ue-germany.de',       'CivanOzel@144',       'Civan',     'Ozel',     null, 'A614585', $programIds['BBA-MKT']],
    ];

    // Combine all users
    # Create one unified list of all users (professors and students) to insert into the portal_users table.
    $users = [];
    foreach ($professors as $p) {
        $users[] = [$p[0], $p[1], 'professor', $p[2], $p[3], $p[4], null, null];
    }
    foreach ($students as $s) {
        $users[] = [$s[0], $s[1], 'student', $s[2], $s[3], $s[4], $s[5], $s[6]];
    }

    #Insert additional profile data depending on whether the user is a professor or student.
    $stmtUser = $pdo->prepare("INSERT INTO portal_users (email, password, role) VALUES (?, ?, ?)");
    $stmtProf = $pdo->prepare("INSERT INTO professor_profiles (user_id, first_name, family_name, department) VALUES (?, ?, ?, ?)");
    $stmtStud = $pdo->prepare("INSERT INTO student_profiles (user_id, first_name, family_name, matriculation_number, program_id) VALUES (?, ?, ?, ?, ?)");

    foreach ($users as $user) {
        [$email, $pass, $role, $first, $last, $dept, $mat_no, $prog_id] = $user;
        $hashed = hashPassword($pass); // Hash the password for secure storage
        $stmtUser->execute([$email, $hashed, $role]); // Insert the user into the 'portal_users' table
        $userId = $pdo->lastInsertId();
        if ($role === 'professor') {
            $stmtProf->execute([$userId, $first, $last, $dept]); // professors table expects
        } elseif ($role === 'student') {
            $stmtStud->execute([$userId, $first, $last, $mat_no, $prog_id]); // students table expects
        }
    }

    // Map users and courses for assignments
    $userIds = [];
    $res = $pdo->query("SELECT id, email FROM portal_users");
    foreach ($res as $row) {
        $userIds[$row['email']] = $row['id'];
    }
    $courseIds = [];
    $res = $pdo->query("SELECT id, course_code FROM course_catalog");
    foreach ($res as $row) {
        $courseIds[$row['course_code']] = $row['id'];
    }

    // Professors assigned to multiple courses
    $profCourses = [
        ['t.smith@ue-germany.de', 'CS101'],
        ['t.smith@ue-germany.de', 'CS104'],
        ['kokosmit@ue-germany.de', 'CS102'],
        ['kokosmit@ue-germany.de', 'CS103'],
        ['linda.bauer@ue-germany.de', 'IT201'],
        ['linda.bauer@ue-germany.de', 'CS105'],
        ['marco.schmidt@ue-germany.de', 'CS103'],
        ['marco.schmidt@ue-germany.de', 'CS101'],
        ['elena.rossi@ue-germany.de', 'BUS101'],
        ['elena.rossi@ue-germany.de', 'FIN201'],
        ['daniel.meyer@ue-germany.de', 'CS105'],
        ['daniel.meyer@ue-germany.de', 'CS104'],
    ];

    # It inserts those relationships into the database
    $stmt = $pdo->prepare("INSERT INTO professor_courses (professor_user_id, course_id) VALUES (?, ?)");
    foreach ($profCourses as $pc) {
        [$email, $course_code] = $pc;
        $stmt->execute([$userIds[$email], $courseIds[$course_code]]);
    }

    // Class offerings (for simplicity, one offering per course by one of the professors)
    $classOfferings = [
        ['CS101', 't.smith@ue-germany.de', 'BCS-CS', 1, 'A101', 'Mon', '09:00', '12:00', 3],
        ['CS102', 'kokosmit@ue-germany.de', 'BCS-CS', 1, 'A102', 'Tue', '10:00', '13:00', 3],
        ['CS103', 'marco.schmidt@ue-germany.de', 'MCS-IT', 1, 'A103', 'Wed', '11:00', '14:00', 3],
        ['CS104', 't.smith@ue-germany.de', 'BCS-CS', 1, 'A104', 'Thu', '12:00', '15:00', 3],
        ['CS105', 'linda.bauer@ue-germany.de', 'MCS-IT', 1, 'A105', 'Fri', '09:00', '12:00', 3],
        ['IT201', 'linda.bauer@ue-germany.de', 'MCS-IT', 1, 'B201', 'Fri', '13:00', '16:00', 3],
        ['BUS101', 'elena.rossi@ue-germany.de', 'BBA-MKT', 1, 'B101', 'Mon', '14:00', '17:00', 3],
        ['FIN201', 'elena.rossi@ue-germany.de', 'MSC-FIN', 1, 'B102', 'Tue', '15:00', '18:00', 3],
    ];
    # Each course offering gets inserted.
    $stmt = $pdo->prepare("INSERT INTO class_offerings (course_id, professor_user_id, program_id, semester_no, room_number, weekday, start_time, end_time, duration_hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($classOfferings as $co) {
        [$course_code, $prof_email, $program_code, $semester, $room, $weekday, $start, $end, $duration] = $co;
        $stmt->execute([
            $courseIds[$course_code],
            $userIds[$prof_email],
            $programIds[$program_code],
            $semester,
            $room,
            $weekday,
            $start,
            $end,
            $duration
        ]);
    }

    // Student enrolments (assign each student to 2 classes)
    $studentEnrolments = [
        ['Arsilda.qato@ue-germany.de', 'CS101'],
        ['Arsilda.qato@ue-germany.de', 'CS102'],
        ['andres.sabillon@ue-germany.de', 'CS103'],
        ['andres.sabillon@ue-germany.de', 'CS104'],
        ['sorasith.chormalee@ue-germany.de', 'CS105'],
        ['sorasith.chormalee@ue-germany.de', 'IT201'],
        ['kimia.karbasi@ue-germany.de', 'BUS101'],
        ['kimia.karbasi@ue-germany.de', 'CS101'],
        ['senem.turkaydin@ue-germany.de', 'FIN201'],
        ['senem.turkaydin@ue-germany.de', 'BUS101'],
        ['civan.ozel@ue-germany.de', 'CS103'],
        ['civan.ozel@ue-germany.de', 'CS105'],
    ];

    $stmt = $pdo->prepare("INSERT INTO student_enrolments (student_user_id, class_id) VALUES (?, ?)"); # Insert the enrolments.
    $res = $pdo->query("SELECT id, email FROM portal_users WHERE role = 'student'"); # You fetch all student IDs for matching.
    $studentIds = [];
    foreach ($res as $row) {
        $studentIds[$row['email']] = $row['id'];
    }
    $res = $pdo->query("SELECT id, course_id FROM class_offerings"); # Get class offerings so you can link students correctly
    $classIds = [];
    foreach ($res as $row) {
        $classIds[$row['course_id']][] = $row['id'];
    }
    foreach ($studentEnrolments as $enrol) {
        [$student_email, $course_code] = $enrol;
        $student_id = $studentIds[$student_email];
        $class_id = $classIds[$courseIds[$course_code]][0];
        $stmt->execute([$student_id, $class_id]);
    }

    // Sample student exams (each student 1 exam for 1 class)
    $studentExams = [
        ['Arsilda.qato@ue-germany.de', 'CS101', '2025-01-15', '10:00', 'written'],
        ['andres.sabillon@ue-germany.de', 'CS104', '2025-01-20', '11:00', 'project'],
        ['sorasith.chormalee@ue-germany.de', 'IT201', '2025-02-01', '09:00', 'written'],
        ['kimia.karbasi@ue-germany.de', 'BUS101', '2025-02-05', '10:30', 'written'],
        ['senem.turkaydin@ue-germany.de', 'FIN201', '2025-01-25', '13:00', 'project'],
        ['civan.ozel@ue-germany.de', 'CS105', '2025-01-28', '14:00', 'written'],
    ];
    
    #This logs the exam date, time, and type written or project
    $stmt = $pdo->prepare("INSERT INTO student_exams (student_user_id, class_id, exam_date, exam_time, exam_type) VALUES (?, ?, ?, ?, ?)");
    foreach ($studentExams as $exam) {
        [$student_email, $course_code, $date, $time, $type] = $exam;
        $student_id = $studentIds[$student_email];
        $class_id = $classIds[$courseIds[$course_code]][0];
        $stmt->execute([$student_id, $class_id, $date, $time, $type]);
    }

    $pdo->commit(); 
    echo "Sample data inserted successfully."; #If everything worked, save all changes.
    
#If anything goes wrong during the process, undo all changes and show the error message

} catch (Exception $e) { 
    $pdo->rollBack();
    echo "Failed to insert data: " . $e->getMessage();
}
?>
