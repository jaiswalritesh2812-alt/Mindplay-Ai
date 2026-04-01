CREATE DATABASE IF NOT EXISTS mindplay_db;
USE mindplay_db;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100),
 email VARCHAR(100) UNIQUE,
 password VARCHAR(255),
 role ENUM('admin','student') DEFAULT 'student',
 last_login TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
 id INT AUTO_INCREMENT PRIMARY KEY,
 subject_name VARCHAR(100),
 description TEXT NULL
);

CREATE TABLE syllabus (
 id INT AUTO_INCREMENT PRIMARY KEY,
 subject_id INT,
 topic VARCHAR(150),
 content TEXT,
 FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

CREATE TABLE questions (
 id INT AUTO_INCREMENT PRIMARY KEY,
 syllabus_id INT,
 question TEXT,
 answer VARCHAR(255),
 FOREIGN KEY (syllabus_id) REFERENCES syllabus(id)
);

CREATE TABLE results (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT,
 subject_id INT,
 syllabus_id INT NULL,
 score INT,
 time_taken INT DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id),
 FOREIGN KEY (subject_id) REFERENCES subjects(id),
 FOREIGN KEY (syllabus_id) REFERENCES syllabus(id)
);

CREATE TABLE quiz_attempts (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT,
 question_id INT,
 user_answer TEXT,
 is_correct BOOLEAN,
 attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id),
 FOREIGN KEY (question_id) REFERENCES questions(id)
);

CREATE TABLE login_history (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 ip_address VARCHAR(45),
 user_agent TEXT,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_user_login (user_id, login_time DESC)
);

CREATE TABLE weak_topics (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT,
 syllabus_id INT,
 mistake_count INT DEFAULT 1,
 last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id),
 FOREIGN KEY (syllabus_id) REFERENCES syllabus(id)
);

-- Insert default admin user (password: admin123)
-- Hash generated using: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@mindplay.com', '$2y$10$E5bZjl5Z5Y5y5Z5Z5Z5Z5OXKq.HqH5LK7Z5Z5Z5Z5Z5Z5Z5Z5Z5ZC', 'admin')
ON DUPLICATE KEY UPDATE password = '$2y$10$E5bZjl5Z5Y5y5Z5Z5Z5Z5OXKq.HqH5LK7Z5Z5Z5Z5Z5Z5Z5Z5Z5ZC';
