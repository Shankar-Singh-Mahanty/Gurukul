-- Table Creation

CREATE TABLE test_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sheet_name VARCHAR(255) NOT NULL UNIQUE,
    time_limit INT NOT NULL,  -- Time limit in minutes
    start_time DATETIME DEFAULT NULL,  -- Scheduled start time (NULL for immediate publishing)
    status ENUM('scheduled', 'published') NOT NULL DEFAULT 'scheduled',  -- Status of the test
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Record creation time
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- Last update time
);

-- View all the records of the table
SELECT * FROM test_schedule;