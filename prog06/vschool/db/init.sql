CREATE DATABASE IF NOT EXISTS school;
CREATE USER IF NOT EXISTS 'winky'@'%' IDENTIFIED BY 'testpassword';
GRANT ALL PRIVILEGES ON school.* TO 'winky'@'%';
FLUSH PRIVILEGES;
USE school;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    avatar VARCHAR(255) DEFAULT '/static/images/default.jpg',
    role ENUM('admin','student','teacher') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    class_code VARCHAR(50) UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE class_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (class_id) REFERENCES classes(id)
        ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    UNIQUE (class_id, user_id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (sender_id) REFERENCES users(id)
        ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (assignment_id) REFERENCES assignments(id)
        ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE TABLE challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hint_text TEXT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE
);

INSERT INTO classes (class_name, class_code, password) VALUES
('Class A', 'CLASSA', 'classA123'),
('Class B', 'CLASSB', 'classB123');

INSERT INTO users (username, password, full_name, email, role, avatar) VALUES
('admin', '123456a@A', 'Admin', 'admin@school.com', 'admin', '/static/images/default.jpg'),
('teacher1', '123456a@A', 'Mr. Smith', 'smith@school.com', 'teacher', '/static/images/default.jpg'),
('teacher2', '123456a@A', 'Mrs. Johnson', 'johnson@school.com', 'teacher', '/static/images/default.jpg'),
('student1', '123456a@A', 'John', 'john@example.com', 'student', '/static/images/default.jpg'),
('student2', '123456a@A', 'Jane', 'jane@example.com', 'student', '/static/images/default.jpg'),
('student3', '123456a@A', 'Alice', 'alice@example.com', 'student', '/static/images/default.jpg'),
('student4', '123456a@A', 'Bob', 'bob@example.com', 'student', '/static/images/default.jpg');

INSERT INTO class_user (class_id, user_id) VALUES
(1, 2),
(2, 3),
(1, 4),
(2, 4),
(1, 5),
(2, 6),
(2, 7);