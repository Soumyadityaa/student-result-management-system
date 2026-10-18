-- Create the database
CREATE DATABASE IF NOT EXISTS school_results CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_results;

-- 1. Create the admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin account
-- Username: admin
-- Password: password (hashed using PHP password_hash with PASSWORD_DEFAULT / bcrypt)
INSERT INTO admin (username, password) VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
) ON DUPLICATE KEY UPDATE username = username;

-- 2. Create the students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(50) NOT NULL UNIQUE,
    student_name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    maths INT NOT NULL CHECK (maths BETWEEN 0 AND 100),
    science INT NOT NULL CHECK (science BETWEEN 0 AND 100),
    english INT NOT NULL CHECK (english BETWEEN 0 AND 100),
    computer INT NOT NULL CHECK (computer BETWEEN 0 AND 100),
    total_marks INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    grade VARCHAR(5) NOT NULL,
    exam_term VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample student records for immediate testing
INSERT INTO students (roll_number, student_name, class, maths, science, english, computer, total_marks, percentage, grade, exam_term) VALUES
('STU-2026-101', 'Aarav Sharma', 'Grade 10 - Section A', 94, 89, 86, 95, 364, 91.00, 'A', 'Final Term 2026'),
('STU-2026-102', 'Meera Nair', 'Grade 10 - Section A', 78, 82, 74, 80, 314, 78.50, 'B', 'Final Term 2026'),
('STU-2026-103', 'Rohan Verma', 'Grade 10 - Section B', 62, 58, 65, 71, 256, 64.00, 'C', 'Mid Term 2026')
ON DUPLICATE KEY UPDATE roll_number = roll_number;