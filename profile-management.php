<?php
include 'authenticate.php';
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    die("<p style='color:red;'>Unauthorized access. Please log in.</p>");
}

// Fetch user details from the database
$email = $_SESSION['email'];
$query = "SELECT user_id, role FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $role);
$stmt->fetch();
$stmt->close();

// Store user_id in session
$_SESSION['user_id'] = $user_id;

// Determine the redirect page based on role
$redirect_page = "index.html"; // Default page if role is not found
if ($role === "admin") {
    $redirect_page = "admin-page.php";
} elseif ($role === "student") {
    $redirect_page = "student-page.php";
} elseif ($role === "guest") {
    $redirect_page = "guest-page.php";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            position: relative;
        }
        .heading-container {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .back-btn {
            background: none;
            border: none;
            cursor: pointer;
        }
        .back-btn img {
            width: 30px;
            height: auto;
        }
        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="heading-container">
            <button class="back-btn" onclick="window.location.href='<?php echo $redirect_page; ?>'">
                <img src="icons/back-button.webp" alt="Back">
            </button>
        </div>

        <h2>Update User Info</h2>
        <h4>(Only fill in the fields you want to update.)</h4>
        <form method="POST" action="">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

            <label>Email:</label>
            <input type="email" name="email" placeholder="Enter new email">

            <label>New Password:</label>
            <input type="password" name="new_password" placeholder="Enter new password">

            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" placeholder="Confirm new password">

            <label>Contact:</label>
            <input type="text" name="contact" pattern="[0-9]{10}" placeholder="Enter new contact number">

            <button type="submit">Update</button>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $user_id = intval($_POST['user_id']);
            $email = !empty($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : null;
            $contact = !empty($_POST['contact']) ? $_POST['contact'] : null;
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            $errors = [];
            if ($contact && !preg_match('/^[0-9]{10}$/', $contact)) {
                $errors[] = "Invalid contact number. It must be exactly 10 digits.";
            }
            if (!empty($new_password) && $new_password !== $confirm_password) {
                $errors[] = "Passwords do not match.";
            }

            if (empty($errors)) {
                $fields = [];
                $params = [];
                $types = "";

                if ($email) {
                    $fields[] = "email = ?";
                    $params[] = $email;
                    $types .= "s";
                }
                if ($new_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $fields[] = "password = ?";
                    $params[] = $hashed_password;
                    $types .= "s";
                }
                if ($contact) {
                    $fields[] = "contact = ?";
                    $params[] = $contact;
                    $types .= "s";
                }

                if (!empty($fields)) {
                    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE user_id = ?";
                    $params[] = $user_id;
                    $types .= "i";

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);

                    if ($stmt->execute()) {
                        echo "<p style='color:green;'>User information updated successfully.</p>";
                    } else {
                        echo "<p style='color:red;'>Error updating record: " . $stmt->error . "</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p style='color:red;'>No changes were made.</p>";
                }
            } else {
                foreach ($errors as $error) {
                    echo "<p style='color:red;'>$error</p>";
                }
            }
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
