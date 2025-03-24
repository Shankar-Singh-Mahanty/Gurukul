<?php
include 'authenticate.php';
checkUser("admin");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Summary</title>
    <link rel="icon" type="image/webp" href="images/SB logo.webp" />
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="css/student-details.css">
</head>

<body>
    <header>
        <div class="header-top">
            <a href="">
                <h1>SKYBIRD</h1>
                <h1>Under the guidance of Gurukul Professionals.</h1>
            </a>
        </div>
    </header>
    <main>
        <section id="student-details">
            <div class="heading-container">
                <button class="back-btn" onclick="window.location.href = 'admin-page.php'"><img
                        src="icons/back-button.webp" alt="back button"></button>
                <h2 class="heading">Student Result Summary</h2>
                <div class="search-bar-container">
                    <input type="text" id="search-bar" placeholder="Search here" onkeyup="filterTable()">
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Test Name</th>
                            <th>Total Questions</th>
                            <th>Correct Answers</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Attempted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Include database connection script
                        include 'db_connect.php';

                        $sql = "SELECT u.username, u.email, t.sheet_name, t.total_questions, t.correct_answers, t.score, t.status, t.attempted_at 
                                FROM users u
                                INNER JOIN test_results t ON u.email = t.email
                                WHERE u.role = 'student'";
                        
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["sheet_name"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["total_questions"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["correct_answers"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["score"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                                echo "<td>" . date('g:i A \o\n jS M, Y', strtotime($row["attempted_at"])) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>No records found</td></tr>";
                        }

                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
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
    function filterTable() {
        const input = document.getElementById('search-bar');
        const filter = input.value.toUpperCase();
        const table = document.querySelector('#student-details table');
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let tdArray = tr[i].getElementsByTagName('td');
            let match = false;
            for (let j = 0; j < tdArray.length; j++) {
                let td = tdArray[j];
                if (td) {
                    if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                        match = true;
                        break;
                    }
                }
            }
            tr[i].style.display = match ? "" : "none";
        }
    }
    </script>
</body>
</html>
