<?php
include 'db_connect.php';

// Set a valid timezone (change according to your region)
date_default_timezone_set('Asia/Kolkata');

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sheet_name = $_POST['sheet_name'];
    $time_limit = intval($_POST['time_limit']);
    $start_time = $_POST['start_time'];

    // Get current time and ensure start time is at least 5 minutes ahead
    $current_time = date("Y-m-d H:i:s");
    $minTime = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    if (strtotime($start_time) < strtotime($minTime)) {
        echo "Error: Start time must be at least 5 minutes from now.";
        exit;
    }

    // Determine status based on start_time
    $status = (strtotime($start_time) > strtotime($current_time)) ? 'scheduled' : 'published';

    // Insert or update schedule in database
    $sql = "INSERT INTO test_schedule (sheet_name, time_limit, start_time, status) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE time_limit = VALUES(time_limit), start_time = VALUES(start_time), status = VALUES(status)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $sheet_name, $time_limit, $start_time, $status);

    if ($stmt->execute()) {
        // Update the status of question_bank table
        $update_question_status_sql = "UPDATE question_bank SET status = 'Published for Student' WHERE sheet_name = ?";
        $stmt_update = $conn->prepare($update_question_status_sql);
        $stmt_update->bind_param("s", $sheet_name);
        $stmt_update->execute();
        $stmt_update->close();
        
        echo "Test scheduled successfully and questions published for students!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
