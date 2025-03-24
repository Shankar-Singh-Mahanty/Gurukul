<?php
session_start();
include 'db_connect.php'; // Include database connection

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid Student ID.";
    header("Location: view-students-admission-record.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch student details
$sql = "SELECT * FROM student_admission WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Check if student exists
if ($result->num_rows == 0) {
    $_SESSION['error'] = "Student not found.";
    header("Location: view-admission-students.php");
    exit;
}

$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .container { margin-top: 30px; }
        .card { max-width: 900px; margin: auto; padding: 20px; }
        .profile-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
        .file-link { color: blue; text-decoration: none; }
        .file-link:hover { text-decoration: underline; }
        .row-data { display: flex; justify-content: space-between; border-bottom: 1px solid #ddd; padding: 8px 0; }
        .row-data strong { width: 40%; }
        .section-title { font-weight: bold; font-size: 1.2em; margin-top: 20px; border-bottom: 2px solid #007BFF; padding-bottom: 5px; }
        .signature { width: 120px; height: auto; }
        .back-btn { margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center">Student Details</h2>

    <div class="card shadow">
        <!-- Profile Image -->
        <div class="text-center mb-3">
            <?php if (!empty($student['photo'])): ?>
                <img src="<?= htmlspecialchars($student['photo']) ?>" class="profile-img img-thumbnail" alt="Student Photo">
            <?php endif; ?>
        </div>

        <!-- Student Information -->
        <div class="section-title">Personal Information</div>
        <div class="row-data"><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></div>
        <div class="row-data"><strong>Gender:</strong> <?= htmlspecialchars($student['gender']) ?></div>
        <div class="row-data"><strong>Date of Birth:</strong> <?= htmlspecialchars($student['dob']) ?></div>
        <div class="row-data"><strong>Contact No:</strong> <?= htmlspecialchars($student['contact_no']) ?></div>
        <div class="row-data"><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></div>
        <div class="row-data"><strong>Blood Group:</strong> <?= htmlspecialchars($student['blood_group']) ?></div>
        <div class="row-data"><strong>Hobby:</strong> <?= htmlspecialchars($student['hobby']) ?></div>

        <!-- Education Details -->
        <div class="section-title">Education Details</div>
        <div class="row-data"><strong>Course:</strong> <?= htmlspecialchars($student['course']) ?></div>
        <div class="row-data"><strong>Level:</strong> <?= htmlspecialchars($student['level']) ?></div>
        <div class="row-data"><strong>Institute Registration No:</strong> <?= htmlspecialchars($student['institute_regn_no']) ?></div>
        <div class="row-data"><strong>Exam Due:</strong> <?= htmlspecialchars($student['exam_due']) ?></div>
        <div class="row-data"><strong>Highest Qualification:</strong> <?= htmlspecialchars($student['highest_qualification']) ?></div>
        <div class="row-data"><strong>Coaching Centre:</strong> <?= htmlspecialchars($student['coaching_centre']) ?></div>
        <div class="row-data"><strong>College:</strong> <?= htmlspecialchars($student['college']) ?></div>

        <!-- Parent Details -->
        <div class="section-title">Parent Details</div>
        <div class="row-data"><strong>Father's Name:</strong> <?= htmlspecialchars($student['father_name']) ?></div>
        <div class="row-data"><strong>Father's Occupation:</strong> <?= htmlspecialchars($student['father_occupation']) ?></div>
        <div class="row-data"><strong>Father's Contact No:</strong> <?= htmlspecialchars($student['father_contact_no']) ?></div>

        <!-- Address -->
        <div class="section-title">Address</div>
        <div class="row-data"><strong>Permanent Address:</strong> <?= htmlspecialchars($student['permanent_address']) ?></div>
        <div class="row-data"><strong>City:</strong> <?= htmlspecialchars($student['city']) ?></div>
        <div class="row-data"><strong>Pin Code:</strong> <?= htmlspecialchars($student['pin_code']) ?></div>

        <!-- Uploaded Documents -->
        <div class="section-title">Uploaded Documents</div>
        <div class="row-data"><strong>Aadhar Card:</strong> <a href="<?= htmlspecialchars($student['aadhar_card']) ?>" target="_blank" class="file-link">View</a></div>
        <div class="row-data"><strong>Matriculation Marksheet:</strong> <a href="<?= htmlspecialchars($student['matriculation_marksheet']) ?>" target="_blank" class="file-link">View</a></div>
        <div class="row-data"><strong>12th Marksheet:</strong> <a href="<?= htmlspecialchars($student['twelveth_marksheet']) ?>" target="_blank" class="file-link">View</a></div>
        <div class="row-data"><strong>Intermediate Marksheet:</strong> <a href="<?= htmlspecialchars($student['inter_marksheet']) ?>" target="_blank" class="file-link">View</a></div>
        <div class="row-data"><strong>Graduation Marksheet:</strong> <a href="<?= htmlspecialchars($student['graduation_marksheet']) ?>" target="_blank" class="file-link">View</a></div>
        <div class="row-data"><strong>Institute Registration Letter:</strong> <a href="<?= htmlspecialchars($student['institute_registration_letter']) ?>" target="_blank" class="file-link">View</a></div>

        <!-- Signatures -->
        <div class="section-title">Signatures</div>
        <div class="text-center">
            <?php if (!empty($student['student_signature'])): ?>
                <div class="mb-2">
                    <strong>Student Signature:</strong><br>
                    <img src="<?= htmlspecialchars($student['student_signature']) ?>" class="signature img-thumbnail">
                </div>
            <?php endif; ?>

            <?php if (!empty($student['parent_signature'])): ?>
                <div>
                    <strong>Parent Signature:</strong><br>
                    <img src="<?= htmlspecialchars($student['parent_signature']) ?>" class="signature img-thumbnail">
                </div>
            <?php endif; ?>
        </div>

        <!-- Amount Paid -->
        <div class="section-title">Payment</div>
        <div class="row-data"><strong>Amount Paid:</strong> ₹<?= htmlspecialchars($student['amount_paid']) ?></div>

        <!-- Back Button -->
        <div class="text-center back-btn">
            <a href="view-students-admission-record.php" class="btn btn-primary">🔙 Back to List</a>
        </div>
    </div>
</div>

</body>
</html>
