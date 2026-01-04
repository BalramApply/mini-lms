-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    duration VARCHAR(50) NOT NULL,
    course_material VARCHAR(255),
    video_link VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Enrollments Table
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_status ENUM('enrolled', 'in_progress', 'completed') NOT NULL DEFAULT 'enrolled',
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Insert Default Admin
INSERT INTO users (name, email, password, role, status) 
VALUES ('Admin', 'admin@lms.com', '$2y$10$Pe3./Auf6/y7CsjrqB.xgeSpjY8t7VZHhXPgsPmMirk5fJ/nP0l/a', 'admin', 'active');
-- Default password: password123

-- Insert Sample Courses (optional)
INSERT INTO courses (title, description, duration, status, created_by) VALUES
('Introduction to PHP', 'Learn the basics of PHP programming language', '4 weeks', 'active', 1),
('MySQL Database Design', 'Master database design and SQL queries', '3 weeks', 'active', 1),
('Web Development Fundamentals', 'HTML, CSS, and JavaScript basics', '6 weeks', 'active', 1);