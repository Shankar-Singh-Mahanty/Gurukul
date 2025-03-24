-- Table Creation

CREATE TABLE question_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sheet_name VARCHAR(255) NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    answer CHAR(1) NOT NULL CHECK (answer IN ('A', 'B', 'C', 'D')),
    explanation TEXT NOT NULL,
    status ENUM('Not Published', 'Published for Student', 'Published for Both Guest & Student') DEFAULT 'Not Published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- View all the records of the table
SELECT * FROM question_bank;