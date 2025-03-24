<?php
include 'authenticate.php';
checkUser("admin");
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Admin Page</title>
	<link rel="icon" type="image/webp" href="images/SB logo.webp" />
	<link rel="stylesheet" href="css/base.css" />
	<link rel="stylesheet" href="css/admin-page.css" />

</head>

<body>
	<header>
		<div class="header-top">
			<a href="./">
				<h1>Admin Dashboard</h1>
			</a>
			<!-- Add this button -->
			<a class="logout-button" href="logout.php">
				Logout
			</a>
		</div>
	</header>

	<section id="admin-services">
		<h2>Admin Services</h2>
		<div class="services-container">
			<div class="service">
				<a href="./g2s.php">
					<img src="icons/g2s.webp" alt="Create Student" />
					<h3>G2S</h3>
					<p>View guest details and approve guest into student(G2S).</p>
				</a>
			</div>
			<div class="service">
				<a href="view-students-admission-record.php">
					<img src="icons/admission_record.jpg" alt="View Student Admission Record" />
					<h3>Student Admission Record</h3>
					<p>
						View student admission details.
					</p>
				</a>
			</div>
			<div class="service">
				<a href="student-details.php">
					<img src="icons/student_details.webp" alt="Student Details" />
					<h3>Student Details</h3>
					<p>
						View student details and revoke their permission to guest.
					</p>
				</a>
			</div>
			<div class="service">
				<a href="./qbank-upload.php">
					<img src="icons/question_upload.webp" alt="Question bank upload" />
					<h3>Question Bank Management</h3>
					<p>
                        Upload questions in bulk as per sheet name.
                    </p>
				</a>
			</div>
			<div class="service">
				<a href="test-management.php">
					<img src="icons/test_management.avif" alt="Test Management" />
					<h3>Test Management</h3>
					<p>
						Preview, Publish and Delete tests.
                        Define test parameters and Schedule them.
					</p>
				</a>
			</div>
			<div class="service">
				<a href="./result-management.php">
					<img src="icons/result_management.webp" alt="View student results" />
					<h3>Result Management</h3>
					<p>
                        View and analyze student test results.
                        Generate reports on student performance.
                        Export result data for further analysis.
                    </p>
				</a>
			</div>
			
	</section>

	<footer>
		<div class="col-sm-7 text-sm-left text-center">
			<p class="mb-0">
				Copyright
				<script>
					var CurrentYear = new Date().getFullYear();
					document.write(CurrentYear);
				</script>
				© SKYBIRD. All rights reserved.
			</p>
			<div class="footer-links">
				<a href="#">Privacy Policy</a>
				<a href="#">Terms of Service</a>
			</div>
		</div>
	</footer>

	
	<script>
		document.addEventListener("DOMContentLoaded", () =>
		{
			const card = document.querySelector(".service");

			card.addEventListener("mousemove", (e) =>
			{
				const rect = card.getBoundingClientRect();
				const x = e.clientX - rect.left;
				const y = e.clientY - rect.top;
				const centerX = rect.width / 2;
				const centerY = rect.height / 2;

				const shadowX = (x - centerX) / 10;
				const shadowY = (y - centerY) / 10;

				document.documentElement.style.setProperty(
					"--card-shadow-x",
					`${ shadowX }rem`
				);
				document.documentElement.style.setProperty(
					"--card-shadow-y",
					`-${ shadowY }rem`
				);
			});

			card.addEventListener("mouseleave", () =>
			{
				document.documentElement.style.setProperty(
					"--card-shadow-x",
					"0px"
				);
				document.documentElement.style.setProperty(
					"--card-shadow-y",
					"0px"
				);
			});
		});
	</script>
</body>

</html>