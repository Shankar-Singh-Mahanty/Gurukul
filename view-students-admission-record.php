<?php
session_start();
include 'db_connect.php'; // Include database connection

// Pagination settings
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search feature
$search = isset($_GET['search']) ? $_GET['search'] : "";

// Fetch students data
$sql = "SELECT id, name, email, contact_no, course, level, institute_regn_no, exam_due, photo, father_contact_no, permanent_address 
        FROM student_admission 
        WHERE name LIKE ? OR email LIKE ? 
        ORDER BY id ASC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param("ssii", $search_param, $search_param, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Count total records for pagination
$total_sql = "SELECT COUNT(*) FROM student_admission WHERE name LIKE ? OR email LIKE ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("ss", $search_param, $search_param);
$total_stmt->execute();
$total_stmt->bind_result($total_records);
$total_stmt->fetch();
$total_stmt->close();

$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .container { margin-top: 20px; }
        .table img { max-width: 50px; height: auto; }
        .pagination { justify-content: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Student Admissions</h2>

        <!-- Search Box -->
        <form method="GET" class="mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
        </form>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact No</th>
                        <th>Course</th>
                        <th>Level</th>
                        <th>Institute Reg. No</th>
                        <th>Exam Due</th>
                        <th>Photo</th>
                        <th>Father's Contact</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact_no']) ?></td>
                            <td><?= htmlspecialchars($row['course']) ?></td>
                            <td><?= htmlspecialchars($row['level']) ?></td>
                            <td><?= htmlspecialchars($row['institute_regn_no']) ?></td>
                            <td><?= htmlspecialchars($row['exam_due']) ?></td>

                            <!-- Display Photo -->
                            <td>
                                <?php if (!empty($row['photo'])): ?>
                                    <a href="<?= $row['photo'] ?>" target="_blank">
                                        <img src="<?= $row['photo'] ?>" class="img-thumbnail" width="50">
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($row['father_contact_no']) ?></td>
                            <td><?= htmlspecialchars($row['permanent_address']) ?></td>

                            <!-- Actions -->
                            <td>
                                <a href="view-student-record.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">🔍 View</a>
                                <a href="delete-student.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">❌ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php else: ?>
            <div class="alert alert-warning">No students found.</div>
        <?php endif; ?>
    </div>

    <script>
        $(document).ready(function(){
            $("input[name='search']").on("keyup", function(){
                let value = $(this).val().toLowerCase();
                $("tbody tr").filter(function(){
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>
