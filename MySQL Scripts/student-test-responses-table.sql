-- Table Creation

CREATE TABLE test_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    sheet_name VARCHAR(255) NOT NULL,
    question_id INT NOT NULL,
    selected_answer VARCHAR(10),
    correct_answer VARCHAR(10),
    is_correct BOOLEAN,
    UNIQUE KEY unique_response (email, sheet_name, question_id)
);

-- View all the records of the table
SELECT * FROM test_responses;