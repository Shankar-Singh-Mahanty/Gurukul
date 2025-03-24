<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sheet_name = $_POST['sheet_name'];

    $sql = "DELETE FROM question_bank WHERE sheet_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $sheet_name);

    if ($stmt->execute()) {
        echo "Test deleted successfully!";
    } else {
        echo "Error deleting test.";
    }
}
?>
