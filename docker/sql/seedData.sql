-- ===========================
-- USERS
-- ===========================
INSERT INTO users (first_name, last_name, email, password_hash, role, created_at) VALUES
    ('Admin', 'User', 'admin@example.com', crypt('admin123', gen_salt('bf')), 'admin', NOW()),
    ('John', 'Doe', 'john.doe@example.com', crypt('password123', gen_salt('bf')), 'student', NOW()),
    ('Jane', 'Smith', 'jane.smith@example.com', crypt('password123', gen_salt('bf')), 'student', NOW()),
    ('Peter', 'Novak', 'peter.novak@example.com', crypt('password123', gen_salt('bf')), 'teacher', NOW()),
    ('Lucia', 'Hruba', 'lucia.hruba@example.com', crypt('password123', gen_salt('bf')), 'teacher', NOW());

-- ===========================
-- STUDENTS
-- ===========================
INSERT INTO students (user_id, student_number, year)
VALUES
    (2, 'S1001', 1),
    (3, 'S1002', 2);

-- ===========================
-- TEACHERS
-- ===========================
INSERT INTO teachers (user_id, department)
VALUES
    (4, 'Mathematics'),
    (5, 'Computer Science');

-- ===========================
-- COURSES
-- ===========================
INSERT INTO courses (teacher_id, name, description, credits)
VALUES
    (1, 'Mathematics 101', 'Introduction to basic algebra and geometry', 5),
    (2, 'Programming 1', 'Introduction to programming in Python', 6),
    (2, 'Databases', 'Introduction to SQL and relational design', 6);

-- ===========================
-- ENROLLMENTS
-- ===========================
INSERT INTO enrollments (student_id, course_id, grade, status)
VALUES
    (1, 1, 'A', 'approved'),
    (1, 2, NULL, 'approved'),
    (2, 2, NULL, 'not approved'),
    (2, 3, 'C', 'approved');
