USE studentportal_ajaska;
-- Create academic programs table
CREATE TABLE academic_programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    degree_level ENUM('BSc', 'MSc', 'PhD') NOT NULL,
    total_semesters TINYINT UNSIGNED NOT NULL,
    department VARCHAR(100)
);

-- Create course catalog table
CREATE TABLE course_catalog (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(150) NOT NULL,
    module_name VARCHAR(150), 
    credits TINYINT UNSIGNED
);

-- Create portal users table
CREATE TABLE portal_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'professor', 'admin') NOT NULL
);

-- Create student profiles table
CREATE TABLE student_profiles (
    user_id INT UNSIGNED PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    family_name VARCHAR(100) NOT NULL,
    matriculation_number VARCHAR(50),
    program_id INT UNSIGNED,
    date_of_birth DATE,
    FOREIGN KEY (user_id) REFERENCES portal_users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES academic_programs(id) ON DELETE SET NULL
);

-- Create professor profiles table
CREATE TABLE professor_profiles (
    user_id INT UNSIGNED PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    family_name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    date_of_birth DATE,
    FOREIGN KEY (user_id) REFERENCES portal_users(id) ON DELETE CASCADE
);

-- Create professor courses table
CREATE TABLE professor_courses (
    professor_user_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (professor_user_id, course_id),
    FOREIGN KEY (professor_user_id) REFERENCES portal_users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES course_catalog(id) ON DELETE CASCADE
);

-- Create class offerings table
CREATE TABLE class_offerings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    professor_user_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED,
    semester_no TINYINT UNSIGNED,
    room_number VARCHAR(50),
    weekday ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun'),
    start_time TIME,
    end_time TIME,
    duration_hours INT UNSIGNED,
    FOREIGN KEY (course_id) REFERENCES course_catalog(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_user_id) REFERENCES portal_users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES academic_programs(id) ON DELETE SET NULL
);

-- Create student enrolments table
CREATE TABLE student_enrolments (
    student_user_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (student_user_id, class_id),
    FOREIGN KEY (student_user_id) REFERENCES portal_users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES class_offerings(id) ON DELETE CASCADE
);

-- Create student exams table
CREATE TABLE student_exams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_user_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    exam_date DATE NOT NULL,
    exam_time TIME,
    exam_type ENUM('written', 'project'),
    FOREIGN KEY (student_user_id) REFERENCES portal_users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES class_offerings(id) ON DELETE CASCADE
);
