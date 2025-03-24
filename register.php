<?php
session_start();
include 'db_connect.php';

// Set response type to JSON
header('Content-Type: application/json');

// Function to sanitize user input
function sanitizeInput($data)
{
    global $conn;
    return htmlspecialchars(trim(mysqli_real_escape_string($conn, $data)));
}

$response = ["status" => "error", "message" => "Unknown error occurred."];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitizeInput($_POST["username"]);
    $email = sanitizeInput($_POST["email"]);
    $password = sanitizeInput($_POST["password"]);
    $contact = sanitizeInput($_POST["contact"]);
    $address = sanitizeInput($_POST["address"]);
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $created_at = date("Y-m-d H:i:s");
    $role = "guest";

    $errors = [];

    // Validate inputs
    if (empty($username)) $errors[] = "Name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($contact) || !preg_match('/^\d{10}$/', $contact)) $errors[] = "Valid 10-digit contact number is required.";
    if (empty($address)) $errors[] = "Address is required.";

    // Check for duplicate email
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email already exists.";
        }
        $stmt->close();
    } else {
        $errors[] = "Database error: " . $conn->error;
    }

    if (!empty($errors)) {
        $response["message"] = implode(" ", $errors);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, contact, address, created_at, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssss", $username, $email, $hashed_password, $contact, $address, $created_at, $role);
            if ($stmt->execute()) {
                $response["status"] = "success";
                $response["message"] = "Registration successful. You can now Login";
            } else {
                $response["message"] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $response["message"] = "Database error: " . $conn->error;
        }
    }
}

$conn->close();
echo json_encode($response);
exit();
?>
