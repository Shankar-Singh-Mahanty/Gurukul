<?php
session_start();
include 'db_connect.php'; // Include your database connection script

header("Content-Type: application/json"); // Ensure JSON response

// Check if request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginEmail'], $_POST['loginPassword'])) {
    
    $user_email = trim($_POST['loginEmail']);
    $user_password = trim($_POST['loginPassword']);

    // Prepare SQL statement to fetch user data by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify the password using password_verify()
        if (password_verify($user_password, $user['password'])) {
            // Set session variables
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Determine redirection URL based on user role
            if ($user['role'] == 'admin') {
                $redirect_url = "admin-page.php";
            } elseif ($user['role'] == 'student') {
                $redirect_url = "student-page.php";
            } elseif ($user['role'] == 'guest') {
                $redirect_url = "guest-page.php";
            } else {
                echo json_encode(["status" => "error", "message" => "Unexpected role."]);
                exit();
            }

            // Send success response with redirection URL
            echo json_encode(["status" => "success", "redirect" => $redirect_url]);
        } else {
            // Incorrect password
            echo json_encode(["status" => "error", "message" => "Invalid email or password! Please try again."]);
        }
    } else {
        // Email not found
        echo json_encode(["status" => "error", "message" => "Invalid email or password! Please try again."]);
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
} else {
    // Handle invalid request
    echo json_encode(["status" => "error", "message" => "Invalid request!"]);
}
?>
