<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow rounded-4 mb-4">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="home.php">Student Portal</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto flex-row-reverse align-items-center">
                    <!-- Logout -->
                    <li class="nav-item ms-3">
                        <a class="nav-link text-danger" href="logout.php"><b>Logout</b></a>
                    </li>
                    <!-- Home -->
                    <li class="nav-item ms-3">
                        <a class="nav-link" href="home.php"><b>Home</b></a>
                    </li>
                    <!-- Reset Password -->
                    <li class="nav-item ms-3">
                        <a class="nav-link" href="set_new_password.php"><b>Reset Password</b></a>
                    </li>
                    <!-- Students (or profile or your student list page) -->
                    <li class="nav-item ms-3">
                        <a class="nav-link" href="view_students.php"><b>StudentProfile</b></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
<?php
}
?>
