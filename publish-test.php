<?php
include 'db_connect.php';

// Set timezone to Asia/Kolkata
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['sheet_name'], $_POST['publish_type'])) {
        echo "Error: Missing parameters.";
        exit;
    }

    $sheet_name = $_POST['sheet_name'];
    $publish_type = $_POST['publish_type'];

    // Determine the status for question_bank based on publish type
    if ($publish_type === 'student') {
        $qb_status = 'Published for Student';
    } elseif ($publish_type === 'both') {
        $qb_status = 'Published for Both Guest & Student';
    } else {
        echo "Error: Invalid publish type.";
        exit;
    }

    // Update question_bank status
    $query = "UPDATE question_bank SET status = ? WHERE sheet_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $qb_status, $sheet_name);

    if ($stmt->execute()) {
        // Define values for test_schedule
        $start_time = date("Y-m-d H:i:s"); // Current time in Asia/Kolkata
        $time_limit = 20; // Default time limit of 20 minutes
        $schedule_status = 'published';

        // Insert or update test_schedule
        $schedule_query = "INSERT INTO test_schedule (sheet_name, time_limit, start_time, status)
                        VALUES (?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE time_limit = VALUES(time_limit), start_time = VALUES(start_time), status = VALUES(status)";

        $stmt_schedule = $conn->prepare($schedule_query);
        $stmt_schedule->bind_param("siss", $sheet_name, $time_limit, $start_time, $schedule_status);

        if ($stmt_schedule->execute()) {
            echo "Success: Test '$sheet_name' published successfully as '$qb_status'.";
        } else {
            echo "Error: Failed to update test schedule.";
        }

        $stmt_schedule->close();
    } else {
        echo "Error: Failed to update test status.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Error: Invalid request method.";
}
