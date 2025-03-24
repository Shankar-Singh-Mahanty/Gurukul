<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['sheet_name']) || !isset($_SESSION['email'])) {
    die("Error: Test not specified or user not logged in.");
}

$sheet_name = mysqli_real_escape_string($conn, $_GET['sheet_name']);
$email = mysqli_real_escape_string($conn, $_SESSION['email']);

// Check if the user has already attempted the test
$attempt_check_query = "SELECT 1 FROM test_attempts WHERE email = ? AND sheet_name = ?";
$stmt = $conn->prepare($attempt_check_query);
$stmt->bind_param("ss", $email, $sheet_name);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    die("You have already attempted this test.");
}
$stmt->close();

// Fetch test questions including explanations (Randomized)
$query = "SELECT id, question, option_a, option_b, option_c, option_d, UPPER(answer) as answer, explanation 
        FROM question_bank 
        WHERE sheet_name = ? 
        AND status IN ('Published for Student', 'Published for Both Guest & Student') 
        ORDER BY RAND()";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sheet_name);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $option_values = [
        'A' => htmlspecialchars($row['option_a']),
        'B' => htmlspecialchars($row['option_b']),
        'C' => htmlspecialchars($row['option_c']),
        'D' => htmlspecialchars($row['option_d']),
    ];
    
    $correct_label = $row['answer'];
    
    $row['options'] = $option_values;
    $row['correct_label'] = $correct_label;
    $questions[] = $row;
}
$stmt->close();

// Fetch time_limit from test_schedule
$time_limit = 20; // Default to 30 minutes if not found
$time_query = "SELECT time_limit FROM test_schedule WHERE sheet_name = ?";
$stmt = $conn->prepare($time_query);
$stmt->bind_param("s", $sheet_name);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $time_limit = (int) $row['time_limit'];
}
$stmt->close();

$_SESSION['test_questions'] = $questions;
$_SESSION['sheet_name'] = $sheet_name;
$_SESSION['time_limit'] = $time_limit;
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sheet_name); ?> - MCQ Test</title>
    <link rel="stylesheet" href="./css/student-start-test.css" />
    <script>
        // Countdown timer
        let timeLeft = <?= $time_limit * 60 ?>; // Convert minutes to seconds
        function startCountdown() {
            let timerDisplay = document.getElementById("timer");
            let countdown = setInterval(function () {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                timerDisplay.textContent = `Time Left: ${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;

                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    document.getElementById("quiz-form").submit(); // Auto-submit when time runs out
                }
                timeLeft--;
            }, 1000);
        }

        window.onload = startCountdown;
    </script>
</head>
<body>
    <h2><?= htmlspecialchars($sheet_name); ?> - MCQ Test</h2>
    <h3 id="timer" style="color: red; font-weight: bold;">Time Left: <?= $time_limit ?>:00</h3>

    <form id="quiz-form" action="student-score-page.php" method="POST">
        <?php foreach ($questions as $index => $q): ?>
            <div class="question-box">
                <p><strong>Q<?= $index + 1; ?>. <?= htmlspecialchars($q['question']); ?></strong></p>
                <?php foreach ($q['options'] as $key => $option): ?>
                    <div class="option" data-question-id="<?= $q['id']; ?>" data-correct="<?= $q['correct_label']; ?>">
                        <?= $key . ") " . $option; ?>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" name="answer_<?= $q['id']; ?>" id="input_<?= $q['id']; ?>">
                <p class="explanation" id="exp-<?= $q['id']; ?>" style="display: none;">
                    <strong>Correct Answer:</strong> <?= $q['correct_label']; ?><br>
                    <strong>Explanation:</strong> <?= htmlspecialchars($q['explanation']); ?>
                </p>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn">Submit Test</button>
    </form>

    <script>
        document.querySelectorAll('.option').forEach(option => {
            option.addEventListener('click', function() {
                let questionBox = this.parentElement;
                if (questionBox.classList.contains('answered')) return;
                questionBox.classList.add('answered');

                let selectedAnswer = this.textContent.trim().charAt(0);
                let correctAnswer = this.getAttribute('data-correct');

                this.classList.add(selectedAnswer === correctAnswer ? 'correct' : 'wrong');
                document.getElementById("input_" + this.getAttribute("data-question-id")).value = selectedAnswer;

                let explanation = questionBox.querySelector('.explanation');
                if (explanation) explanation.style.display = 'block';
            });
        });
    </script>

    <!-- Disable browser back button -->
    <script type="text/javascript">
        (function () {
            // Disable back button
            history.pushState(null, "", location.href);
            history.pushState(null, "", location.href);
            window.addEventListener("popstate", function () {
                history.pushState(null, "", location.href);
            });

            let formSubmitted = false;

            window.onbeforeunload = function (event) {
                if (!formSubmitted) {
                    const message = "Changes you made may not be saved.";
                    event.returnValue = message; // Show default browser pop-up
                    return message;
                }
            };

            window.addEventListener("beforeunload", function (event) {
                if (!formSubmitted) {
                    formSubmitted = true; 
                    setTimeout(function () {
                        document.getElementById("quiz-form").submit();
                    }, 0);
                }
            });
        })();
    </script>

</body>
</html>
