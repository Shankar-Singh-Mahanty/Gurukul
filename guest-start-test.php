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
        AND status = 'Published for Both Guest & Student'
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
$_SESSION['test_questions'] = $questions;
$_SESSION['sheet_name'] = $sheet_name;
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sheet_name); ?> - MCQ Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            padding: 10px;
            max-width: 100%;
            box-sizing: border-box;
        }

        .question-box { 
            margin-bottom: 20px; 
            padding: 15px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            background: #fff;
        }

        .option { 
            padding: 12px; 
            border: 1px solid #ccc; 
            cursor: pointer; 
            display: block; 
            margin: 8px 0; 
            border-radius: 5px; 
            text-align: left;
            transition: background 0.3s ease-in-out;
        }

        .option:hover { 
            background: #f4f4f4; 
        }

        .correct { 
            background: lightgreen !important; 
            pointer-events: none; 
        }

        .wrong { 
            background: lightcoral !important; 
            pointer-events: none; 
        }

        .btn { 
            display: block; 
            margin: 20px auto; 
            padding: 12px; 
            background: blue; 
            color: white; 
            text-align: center; 
            border-radius: 5px; 
            text-decoration: none; 
            width: 100%; 
            max-width: 200px; 
            transition: background 0.3s ease-in-out;
        }

        .btn:hover { 
            background: darkblue; 
        }

        /* Responsive Styling */
        @media (max-width: 600px) {
            body { 
                margin: 10px; 
                padding: 5px;
            }

            .question-box, .option, .btn { 
                padding: 10px;
            }

            .option { 
                font-size: 14px;
            }

            .btn { 
                width: 90%; 
            }
        }
    </style>
</head>
<body>
    <h2><?= htmlspecialchars($sheet_name); ?> - MCQ Test</h2>
    <form id="quiz-form" action="guest-score-page.php" method="POST">
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
</body>
</html>
