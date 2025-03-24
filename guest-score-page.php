<?php
session_start();
include 'db_connect.php';

// Prevent direct access if session data is missing
if (!isset($_SESSION['test_questions'], $_SESSION['sheet_name'])) {
    header("Location: guest-test-taking.php");
    exit;
}

$questions = $_SESSION['test_questions'];
$sheet_name = $_SESSION['sheet_name'];
$total_questions = count($questions);
$correct_answers = 0;

// Evaluate answers
foreach ($questions as $q) {
    $qid = $q['id'];
    if (isset($_POST["answer_$qid"]) && strtoupper(trim($_POST["answer_$qid"])) === strtoupper(trim($q['answer']))) {
        $correct_answers++;
    }
}

$score = $correct_answers * 2;
$status = ($score >= ($total_questions * 2) / 2) ? 'Passed' : 'Failed';

// Get user details
$email = $_SESSION['email'] ?? throw new Exception("Error: User not logged in.");

// Store test attempt in test_attempts table
$attempt_stmt = $conn->prepare("INSERT INTO guest_test_attempts (email, sheet_name, score) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE score = VALUES(score)");
$attempt_stmt->bind_param("ssi", $email, $sheet_name, $score);
$attempt_stmt->execute();
$attempt_stmt->close();

// Store result in test_results table
$result_stmt = $conn->prepare("INSERT INTO guest_test_results (email, sheet_name, total_questions, correct_answers, score, status) 
                            VALUES (?, ?, ?, ?, ?, ?)");
$result_stmt->bind_param("ssiiis", $email, $sheet_name, $total_questions, $correct_answers, $score, $status);
$result_stmt->execute();
$result_stmt->close();

$conn->close();


// Destroy session data to prevent reattempting via back button
unset($_SESSION['test_questions']);
unset($_SESSION['sheet_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Result</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        .result-box { display: inline-block; padding: 30px; border-radius: 10px; font-size: 24px; font-weight: bold; }
        .pass { background: lightgreen; color: green; }
        .fail { background: lightcoral; color: red; }
        .animate { animation: fadeIn 2s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
    <script>
        // Prevent back navigation
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Redirect to student-test-taking.php after 5 seconds
        setTimeout(() => {
            window.location.href = "guest-test-taking.php";
        }, 5000);
    </script>
</head>
<body>

<h2>Your Test Result</h2>
<div class="result-box animate <?php echo ($status === 'Passed') ? 'pass' : 'fail'; ?>">
    Score: <?php echo $score; ?>/<?php echo $total_questions * 2; ?><br>
    Status: <?php echo $status; ?>
</div>

<p>You will be redirected to the test-taking page in 5 seconds...</p>

</body>
</html>
