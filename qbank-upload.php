<?php
include 'authenticate.php';
checkUser("admin");

include 'db_connect.php';

require 'phpspreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['question_file']) && $_FILES['question_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['question_file']['tmp_name'];
        $fileName = $_FILES['question_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['xlsx', 'xls'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            $sheet_name = $_POST['sheet_name'];

            // Check if sheet name already exists, delete if present
            $checkQuery = "SELECT COUNT(*) FROM question_bank WHERE sheet_name = ?";
            $stmtCheck = $conn->prepare($checkQuery);
            $stmtCheck->bind_param("s", $sheet_name);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                $deleteQuery = "DELETE FROM question_bank WHERE sheet_name = ?";
                $stmtDelete = $conn->prepare($deleteQuery);
                $stmtDelete->bind_param("s", $sheet_name);
                $stmtDelete->execute();
                $stmtDelete->close();
            }

            // Read the header row and normalize column names
            $headerRow = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE)[0];
            $headers = array_map(fn($header) => strtolower(str_replace([' ', '_'], '', trim($header))), $headerRow);

            // Define expected column names and their possible variations
            $columnMap = [
                'question'    => ['question', 'questions', 'ques'], 
                'option_a'    => ['optiona', 'option_a', 'a', 'opta'],  
                'option_b'    => ['optionb', 'option_b', 'b', 'optb'],  
                'option_c'    => ['optionc', 'option_c', 'c', 'optc'],  
                'option_d'    => ['optiond', 'option_d', 'd', 'optd'],  
                'answer'      => ['answer', 'ans'],  
                'explanation' => ['explanation', 'exp', 'explain']  
            ];

            // Map actual column indexes
            $expectedColumns = [];
            foreach ($columnMap as $key => $aliases) {
                foreach ($aliases as $alias) {
                    $index = array_search($alias, $headers);
                    if ($index !== false) {
                        $expectedColumns[$key] = $index;
                        break; // Stop searching once found
                    }
                }
                if (!isset($expectedColumns[$key])) {
                    $_SESSION['message'] = '<div class="message error">Missing required column: ' . ucfirst($key) . '</div>';
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                }
            }

            // Prepare SQL statement for insertion
            $sql = "INSERT INTO question_bank (sheet_name, question, option_a, option_b, option_c, option_d, answer, explanation, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Not Published', NOW())";
            $stmt = $conn->prepare($sql);

            $insertedCount = 0;

            for ($row = 2; $row <= $highestRow; $row++) {
                // Read the entire row to get all data
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];

                // Extract values based on mapped indexes
                $question = trim($rowData[$expectedColumns['question']] ?? '');
                $option_a = trim($rowData[$expectedColumns['option_a']] ?? '');
                $option_b = trim($rowData[$expectedColumns['option_b']] ?? '');
                $option_c = trim($rowData[$expectedColumns['option_c']] ?? '');
                $option_d = trim($rowData[$expectedColumns['option_d']] ?? '');
                $answer = trim($rowData[$expectedColumns['answer']] ?? '');
                $explanation = trim($rowData[$expectedColumns['explanation']] ?? '');

                // Ensure essential fields are present
                if ($question === '' || $answer === '' || !in_array(strtoupper($answer), ['A', 'B', 'C', 'D'])) {
                    continue;
                }

                $stmt->bind_param("ssssssss", $sheet_name, $question, $option_a, $option_b, $option_c, $option_d, $answer, $explanation);
                
                try {
                    $stmt->execute();
                    $insertedCount++;
                } catch (Exception $e) {
                    $_SESSION['message'] .= '<div class="message error">Error inserting record: ' . $e->getMessage() . '</div>';
                    continue;
                }
            }
            
            $_SESSION['message'] .= '<div class="message success">' . $insertedCount . ' questions successfully uploaded!</div>';
        } else {
            $_SESSION['message'] = '<div class="message error">Invalid file type. Allowed types: ' . implode(', ', $allowedfileExtensions) . '</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="message error">No file uploaded or an error occurred during upload.</div>';
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Close database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Question Bank</title>
    <link rel="icon" type="image/webp" href="./images/SB logo.webp" />
    <link rel="stylesheet" href="./css/base.css" />
    <link rel="stylesheet" href="./css/qbank-upload.css">
</head>
<body>
    <header>
        <div class="header-top">
            <a href="./">
                <h1>SKYBIRD</h1>
                <h1>Under the guidance of Gurukul Professionals.</h1>
            </a>
        </div>
    </header>
    <main>
        <section id="create-student">
            <div class="heading-container">
                <button class="back-btn" onclick="window.location.href = './admin-page.php'">
                    <img src="icons/back-button.webp" alt="back button">
                </button>
                <h2 class="heading">Upload Question Bank</h2>
            </div>
            <form class="form_container" action="" method="post" enctype="multipart/form-data">
                <div class="input_box">
                    <label for="sheet_name">Sheet Name:</label>
                    <input type="text" name="sheet_name" placeholder="Enter Sheet Name" required />
                </div>
                <div class="input_box">
                    <label for="question_file">Upload Excel File:</label>
                    <input type="file" name="question_file" accept=".xlsx, .xls" required />
                </div>

                <button type="submit" class="submit-button">Upload</button>
            </form>

            <?php
            // Display the session message if it exists
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']); // Clear the message after displaying
            }
            ?>
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
        document.addEventListener("DOMContentLoaded", function ()
        {
            const errorMessages = document.querySelectorAll(".error");
            const moreButton = document.createElement("button");
            moreButton.textContent = "More Errors";
            moreButton.classList.add("more-button");
            let firstFiveErrors = Array.from(errorMessages).slice(0, 5);
            let remainingErrors = Array.from(errorMessages).slice(5);

            remainingErrors.forEach(error => error.classList.add("hidden"));

            if (errorMessages.length > 5)
            {
                document.querySelector("#create-student").appendChild(moreButton);
            }

            moreButton.addEventListener("click", function ()
            {
                remainingErrors.forEach(error => error.classList.remove("hidden"));
                moreButton.remove();
            });


            // Onload button loading
            const formElement = document.querySelector(".form_container");
            const submitButton = document.querySelector(".submit-button");

            formElement.addEventListener('submit', () =>
            {
                submitButton.textContent = "Loading...";
                submitButton.disabled = true;
            })

        });
    </script>
</body>

</html>