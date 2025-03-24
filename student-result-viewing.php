<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['email'])) {
    die("Error: User not logged in.");
}

$email = $_SESSION['email'];

// Fetch available test attempts
$tests_query = "SELECT DISTINCT sheet_name FROM test_attempts WHERE email = ?";
$stmt = $conn->prepare($tests_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$tests = [];
while ($row = $result->fetch_assoc()) {
    $tests[] = $row['sheet_name'];
}
$stmt->close();

$sheet_name = $_GET['sheet_name'] ?? '';
$questions = [];

if ($sheet_name) {
    $query = "SELECT qb.id, qb.question, qb.option_a, qb.option_b, qb.option_c, qb.option_d, 
                    UPPER(qb.answer) as correct_answer, qb.explanation, ta.selected_answer
            FROM question_bank qb
            JOIN test_responses ta ON qb.id = ta.question_id
            WHERE ta.email = ? AND ta.sheet_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $sheet_name);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Review</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; padding: 10px; background: #f9f9f9; }
        .container { max-width: 800px; margin: 0 auto; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .question-box { padding: 15px; border: 1px solid #ddd; margin-bottom: 20px; border-radius: 5px; background: #fff; }
        .correct { background: lightgreen; }
        .wrong { background: lightcoral; }
        .explanation { margin-top: 10px; font-style: italic; color: #555; }
        .btn { padding: 10px 20px; background: blue; color: white; border-radius: 5px; text-decoration: none; }
        select { padding: 5px; font-size: 16px; }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h2>Test Review</h2>
        <a href="student-page.php" class="btn">Back to Dashboard</a>
    </div>
    <form method="GET">
        <label for="testSelect"><strong>Select Test:</strong></label>
        <select name="sheet_name" id="testSelect" onchange="this.form.submit()">
            <option value="">-- Select --</option>
            <?php foreach ($tests as $test): ?>
                <option value="<?= htmlspecialchars($test) ?>" <?= ($sheet_name === $test) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($test) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    
    <?php if ($sheet_name && count($questions) > 0): ?>
        <h3>Review for: <?= htmlspecialchars($sheet_name) ?></h3>
        <?php foreach ($questions as $index => $q): ?>
            <?php
            $selected = strtoupper($q['selected_answer']);
            $correct = strtoupper($q['correct_answer']);
            $is_correct = ($selected === $correct);
            ?>
            <div class="question-box <?= $is_correct ? 'correct' : 'wrong'; ?>">
                <p><strong>Q<?= $index + 1; ?>. <?= htmlspecialchars($q['question']); ?></strong></p>
                <p><strong>Your Answer:</strong> <?= $selected ? $selected . ") " . htmlspecialchars($q["option_" . strtolower($selected)]) : "Not Answered"; ?></p>
                <p><strong>Correct Answer:</strong> <?= $correct ? $correct . ") " . htmlspecialchars($q["option_" . strtolower($correct)]) : "N/A"; ?></p>
                <p class="explanation"><strong>Explanation:</strong> <?= htmlspecialchars($q['explanation']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php elseif ($sheet_name): ?>
        <p>No questions found for this test.</p>
    <?php endif; ?>
</div>
</body>
</html>
