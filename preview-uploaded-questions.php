<?php
include 'db_connect.php';

// Check if sheet_name is provided
if (!isset($_GET['sheet_name'])) {
    die("Error: Test name not provided.");
}

$sheet_name = $_GET['sheet_name'];

// Fetch questions from database
$query = "SELECT question, option_a, option_b, option_c, option_d FROM question_bank WHERE sheet_name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sheet_name);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-4">

    <h2 class="mb-4">Test Preview: <?php echo htmlspecialchars($sheet_name); ?></h2>

    <div class="card p-3">
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $index => $q): ?>
                <div class="mb-4">
                    <h5>Q<?php echo ($index + 1) . '. ' . htmlspecialchars($q['question']); ?></h5>
                    <p>A) <?php echo htmlspecialchars($q['option_a']); ?></p>
                    <p>B) <?php echo htmlspecialchars($q['option_b']); ?></p>
                    <p>C) <?php echo htmlspecialchars($q['option_c']); ?></p>
                    <p>D) <?php echo htmlspecialchars($q['option_d']); ?></p>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-danger">No questions found for this test.</p>
        <?php endif; ?>
    </div>

    <!-- Publish Buttons -->
    <div class="text-center mt-4">
        <button class="btn btn-primary me-2" onclick="publishTest('student')">Publish for Student</button>
        <button class="btn btn-success" onclick="publishTest('both')">Publish for Both Guest & Student</button>
    </div>

    <script>
        function publishTest(type) {
            $.ajax({
                url: 'publish-test.php',
                type: 'POST',
                data: { sheet_name: "<?php echo htmlspecialchars($sheet_name); ?>", publish_type: type },
                success: function(response) {
                    alert(response);
                },
                error: function() {
                    alert('Error while publishing the test.');
                }
            });
        }
    </script>

</body>
</html>
