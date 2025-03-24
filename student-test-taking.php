<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php"); // Redirect to login if not authenticated
    exit;
}

// Set timezone to Asia/Kolkata
date_default_timezone_set('Asia/Kolkata');
$current_time = date("Y-m-d H:i:s");

// Fetch available tests with their scheduled status
$query = "
    SELECT DISTINCT qb.sheet_name, qb.status AS qb_status, ts.status AS ts_status, ts.start_time 
    FROM question_bank qb
    LEFT JOIN test_schedule ts ON qb.sheet_name = ts.sheet_name
    WHERE qb.status IN ('Published for Student', 'Published for Both Guest & Student')
";
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
        .disabled { background: gray; pointer-events: none; }
    </style>
</head>
<body>
    <h2>Available Tests</h2>
    <div class="heading-container">
        <button class="back-btn" onclick="window.location.href = 'student-page.php'">
            <img src="icons/back-button.webp" alt="back button">
        </button>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Test Name</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): 
                $test_status = $row['ts_status']; 
                $start_time = $row['start_time'];

                // Determine if test can be taken
                $can_appear = ($test_status === 'published' || ($test_status === 'scheduled' && strtotime($start_time) <= strtotime($current_time)));
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sheet_name']); ?></td>
                    <td>
                        <?php if ($can_appear): ?>
                            <a href="student-start-test.php?sheet_name=<?php echo urlencode($row['sheet_name']); ?>" class="btn">Appear</a>
                        <?php else: ?>
                            <button class="btn disabled">Scheduled (Available at: <?php echo date("h:i A, d M Y", strtotime($start_time)); ?>)</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No tests available at the moment.</p>
    <?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>
