-- Table Creation

CREATE TABLE test_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    sheet_name VARCHAR(255) NOT NULL,
    score INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (email, sheet_name) -- Prevent multiple attempts
);

-- View all the records of the table
SELECT * FROM test_attempts;