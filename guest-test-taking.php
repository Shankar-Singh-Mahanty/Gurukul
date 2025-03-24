<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'guest') {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

// Fetch published tests
$query = "SELECT DISTINCT sheet_name, status FROM question_bank WHERE status = 'Published for Both Guest & Student'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take a Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .btn { padding: 8px 15px; background: blue; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: darkblue; }
    </style>
</head>
<body>
    <h2>Available Tests</h2>
    <div class="heading-container">
        <button class="back-btn" onclick="window.location.href = 'guest-page.php'"><img
            src="icons/back-button.webp" alt="back button"></button>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Test Name</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sheet_name']); ?></td>
                    <td><a href="guest-start-test.php?sheet_name=<?php echo urlencode($row['sheet_name']); ?>" class="btn">Appear</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No tests available at the moment.</p>
    <?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
