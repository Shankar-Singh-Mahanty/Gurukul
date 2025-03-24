-- Table Creation

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(10) NOT NULL CHECK (LENGTH(contact) = 10 AND contact REGEXP '^[0-9]{10}$'),
    address VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM('admin', 'student', 'guest') NOT NULL
);

-- Describe table
DESC users;

-- View all the records of the table
SELECT * FROM users;
