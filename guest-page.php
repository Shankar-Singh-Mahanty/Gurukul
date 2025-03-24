<?php
include 'authenticate.php';
checkUser("guest");
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>SKYBIRD Guest Page</title>
	<link rel="icon" type="image/webp" href="logo.webp" />
	<link rel="stylesheet" href="base.css" />
	<link rel="stylesheet" href="css/admin-page.css" />
</head>

<body>
	<div class="content">
		<header>
			<div class="header-top">
				<a href="./">
					<h1>SKYBIRD</h1>
					<h1>Under the guidance of Gurkul Professionals</h1>
				</a>
				<a class="logout-button" href="logout.php">
					Logout
				</a>
			</div>
		</header>

		<section id="admin-services">
			<h2>Guest Services</h2>
			<div class="services-container">
				<div class="service">
					<a href="profile-management.php">
						<img src="icons/profile_mangement.jpg" alt="Student Profile Management" />
						<h3>Profile Management</h3>
						<p>
                            View and Update personal profile information.
                            Change password.
                        </p>
					</a>
				</div>
				<div class="service">
					<a href="./guest-test-taking.php">
						<img src="icons/test_taking.webp" alt="Take Test" />
						<h3>Test Taking</h3>
						<p>
                            View available tests and respective timeframes.
                            Receive feedback on incorrect answer and Scorecard.
                        </p>
					</a>
				</div>
		</section>
	</div>

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
</body>

</html>