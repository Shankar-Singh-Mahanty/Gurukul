<?php
include 'authenticate.php';
checkUser("admin");
include 'db_connect.php';

// Fetch distinct sheet names (test names)
$query = "SELECT DISTINCT sheet_name FROM question_bank";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container mt-4">

    <h2 class="mb-4">Test Management</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Test Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sheet_name']); ?></td>
                    <td>
                        <a href="preview-uploaded-questions.php?sheet_name=<?php echo urlencode($row['sheet_name']); ?>" class="btn btn-info btn-sm">Preview</a>
                        <button class="btn btn-primary btn-sm schedule-btn" data-sheet="<?php echo $row['sheet_name']; ?>">Schedule Test</button>
                        <button class="btn btn-danger btn-sm delete-btn" data-sheet="<?php echo $row['sheet_name']; ?>">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Modal for Scheduling Test -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleForm">
                        <input type="hidden" id="sheetName" name="sheet_name">
                        <label for="time_limit">Time Limit (in minutes):</label>
                        <input type="number" id="time_limit" name="time_limit" class="form-control" required>
                        
                        <label for="start_time" class="mt-3">Start Time:</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control" required>

                        <button type="submit" class="btn btn-success mt-3">Save Schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Handle Schedule button click
            $(".schedule-btn").click(function() {
                var sheetName = $(this).data("sheet");
                $("#sheetName").val(sheetName);

                // Set minimum start time (5 minutes from now)
                var now = new Date();
                now.setMinutes(now.getMinutes() + 5);
                now.setSeconds(0); // Remove seconds for compatibility

                // Convert to proper datetime-local format (YYYY-MM-DDTHH:MM)
                var minTime = now.toISOString().slice(0, 16);
                $("#start_time").attr("min", minTime);

                $("#scheduleModal").modal("show");
            });

            // Handle Schedule Form submission
            $("#scheduleForm").submit(function(event) {
                event.preventDefault();
                $.ajax({
                    url: "save-schedule.php",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response);
                        $("#scheduleModal").modal("hide");
                    }
                });
            });

            // Handle Delete button click
            $(".delete-btn").click(function() {
                var sheetName = $(this).data("sheet");
                if (confirm("Are you sure you want to delete this test?")) {
                    $.ajax({
                        url: "delete-test.php",
                        type: "POST",
                        data: { sheet_name: sheetName },
                        success: function(response) {
                            alert(response);
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
