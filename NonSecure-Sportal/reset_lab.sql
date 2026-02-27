-- WARNING: This will delete all existing data and recreate the schema
-- Use with caution during development or schema reset

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS student_exams;
DROP TABLE IF EXISTS student_enrolments;
DROP TABLE IF EXISTS class_offerings;
DROP TABLE IF EXISTS professor_courses;
DROP TABLE IF EXISTS professor_profiles;
DROP TABLE IF EXISTS student_profiles;
DROP TABLE IF EXISTS portal_users;
DROP TABLE IF EXISTS course_catalog;
DROP TABLE IF EXISTS academic_programs;

-- Create table for academic programs (e.g., BSc, MSc)
CREATE TABLE academic_programs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(20)  UNIQUE NOT NULL,
    name            VARCHAR(150) NOT NULL,
    degree_level    ENUM('BSc','MSc','PhD') NOT NULL,
    total_semesters TINYINT UNSIGNED NOT NULL,
    department      VARCHAR(100)
);

-- Create catalog of available courses
CREATE TABLE course_catalog (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_code  VARCHAR(20)  UNIQUE NOT NULL,
    course_name  VARCHAR(150) NOT NULL,
    module_name  VARCHAR(150),
    credits      TINYINT UNSIGNED
);

-- Create central user table (students, professors, admins)
CREATE TABLE portal_users (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email    VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role     ENUM('student','professor','admin') NOT NULL
);

-- Create profile table for students, linked to portal_users
CREATE TABLE student_profiles (
    user_id             INT UNSIGNED PRIMARY KEY,
    first_name          VARCHAR(100) NOT NULL,
    family_name         VARCHAR(100) NOT NULL,
    matriculation_number VARCHAR(50),
    program_id          INT UNSIGNED,
    date_of_birth       DATE,
    FOREIGN KEY (user_id)    REFERENCES portal_users(id)    ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES academic_programs(id) ON DELETE SET NULL
);

-- Create profile table for professors
CREATE TABLE professor_profiles (
    user_id       INT UNSIGNED PRIMARY KEY,
    first_name    VARCHAR(100) NOT NULL,
    family_name   VARCHAR(100) NOT NULL,
    department    VARCHAR(100),
    date_of_birth DATE,
    FOREIGN KEY (user_id) REFERENCES portal_users(id) ON DELETE CASCADE
);

-- Create many-to-many link between professors and courses
CREATE TABLE professor_courses (
    professor_user_id INT UNSIGNED NOT NULL,
    course_id         INT UNSIGNED NOT NULL,
    PRIMARY KEY (professor_user_id, course_id),
    FOREIGN KEY (professor_user_id) REFERENCES portal_users(id)  ON DELETE CASCADE,
    FOREIGN KEY (course_id)         REFERENCES course_catalog(id) ON DELETE CASCADE
);

-- Create table for scheduled class offerings
CREATE TABLE class_offerings (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id          INT UNSIGNED NOT NULL,
    professor_user_id  INT UNSIGNED NOT NULL,
    program_id         INT UNSIGNED,
    semester_no        TINYINT UNSIGNED,
    room_number        VARCHAR(50),
    weekday            ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun'),
    start_time         TIME,
    end_time           TIME,
    duration_hours     INT UNSIGNED,
    FOREIGN KEY (course_id)         REFERENCES course_catalog(id)   ON DELETE CASCADE,
    FOREIGN KEY (professor_user_id) REFERENCES portal_users(id)     ON DELETE CASCADE,
    FOREIGN KEY (program_id)        REFERENCES academic_programs(id) ON DELETE SET NULL
);

-- Create many-to-many link between students and classes
CREATE TABLE student_enrolments (
    student_user_id INT UNSIGNED NOT NULL,
    class_id        INT UNSIGNED NOT NULL,
    PRIMARY KEY (student_user_id, class_id),
    FOREIGN KEY (student_user_id) REFERENCES portal_users(id)    ON DELETE CASCADE,
    FOREIGN KEY (class_id)        REFERENCES class_offerings(id) ON DELETE CASCADE
);

-- Create table to schedule student exams per class
CREATE TABLE student_exams (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_user_id INT UNSIGNED NOT NULL,
    class_id        INT UNSIGNED NOT NULL,
    exam_date       DATE NOT NULL,
    exam_time       TIME,
    exam_type       ENUM('written','project'),
    FOREIGN KEY (student_user_id) REFERENCES portal_users(id)    ON DELETE CASCADE,
    FOREIGN KEY (class_id)        REFERENCES class_offerings(id) ON DELETE CASCADE
);

-- ==========================================
-- Sample Data Insertion
-- ==========================================

-- Insert an academic program
INSERT INTO academic_programs
(code,name,degree_level,total_semesters,department)
VALUES
('BCS-CS','B.Sc. Computer Science','BSc',6,'CS'); 

-- Insert course offerings
INSERT INTO course_catalog
(course_code,course_name,module_name,credits)
VALUES
('CS101','Algorithms I','Algorithms',5),
('CS102','Algorithms II','Algorithms',8),
('CS103','Math I','Math',8),
('CS104','Math II','Math',4),
('CS105','Physic I','Physic',8);

-- Add two professors to portal_users
INSERT INTO portal_users(email,password,role)
VALUES ('t.smith@ue-germany.de','demo123','professor');
SET @prof := LAST_INSERT_ID();

INSERT INTO portal_users(email,password,role)
VALUES ('kokosmit@ue-germany.de','demo123','professor');
SET @prof1 := LAST_INSERT_ID();

-- Add profiles for professors
INSERT INTO professor_profiles(user_id,first_name,family_name,department)
VALUES 
(@prof,'Tom','Smith','CS'),
(@prof1,'Koko','Smith','CS');

-- Add a student user
INSERT INTO portal_users(email,password,role)
VALUES ('kimia.karbasi@ue-germany.de','1234','student');
SET @stu := LAST_INSERT_ID();

-- Add student profile
INSERT INTO student_profiles
(user_id,first_name,family_name,matriculation_number,program_id)
VALUES
(@stu,'Kimia','Karbasi','A614580',1);

-- Add another student (for login test)
INSERT INTO portal_users(email,password,role)
VALUES ('andres.sabillon@ue-germany.de','Andres!321ue','student');
SET @andres := LAST_INSERT_ID();

INSERT INTO student_profiles
(user_id,first_name,family_name,matriculation_number,program_id)
VALUES
(@andres,'Andres','Sabillon','A000000',1);

-- Link professors to courses
INSERT INTO professor_courses(professor_user_id,course_id)
VALUES 
(@prof,1),
(@prof1,2),
(@prof1,3);

-- Create class offerings for semester 1
INSERT INTO class_offerings
(course_id,professor_user_id,program_id,semester_no,room_number,weekday,start_time,end_time,duration_hours)
VALUES
(1,@prof,1,1,'A101','Mon','09:00','12:00',3),
(2,@prof1,1,1,'A104','Tue','11:00','15:00',5),
(3,@prof1,1,1,'A105','Wed','11:00','15:00',5),
(3,@prof,1,1,'A106','Wed','11:00','15:00',5),
(3,@prof,1,1,'A101','Wed','12:00','16:00',5);

-- Get IDs of the inserted classes for use in enrollments
SELECT id INTO @c1 FROM class_offerings ORDER BY id ASC LIMIT 1 OFFSET 0;
SELECT id INTO @c2 FROM class_offerings ORDER BY id ASC LIMIT 1 OFFSET 1;
SELECT id INTO @c3 FROM class_offerings ORDER BY id ASC LIMIT 1 OFFSET 2;
SELECT id INTO @c4 FROM class_offerings ORDER BY id ASC LIMIT 1 OFFSET 3;
SELECT id INTO @c5 FROM class_offerings ORDER BY id ASC LIMIT 1 OFFSET 4;

-- Enroll both students in all classes
INSERT INTO student_enrolments 
VALUES 
(@stu,@c1),(@stu,@c2),(@stu,@c3),(@stu,@c4),(@stu,@c5),
(@andres,@c1),(@andres,@c2),(@andres,@c3),(@andres,@c4),(@andres,@c5);

-- Add exam entries for both students
INSERT INTO student_exams
(student_user_id,class_id,exam_date,exam_time,exam_type)
VALUES
(@stu,@c1,'2025-01-15','10:00','written'),
(@stu,@c2,'2025-01-21','11:00','project'),
(@andres,@c1,'2025-01-15','10:00','written'),
(@andres,@c2,'2025-01-21','11:00','project');
