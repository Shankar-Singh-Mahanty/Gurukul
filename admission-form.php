<?php
session_start();
include 'db_connect.php'; // Database connection

$errors = [];
$success_message = "";

// Function to sanitize input
function sanitizeInput($data)
{
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}

// Function to validate and prepare file paths
function prepareFilePaths($file_fields)
{
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_paths = [];

    $allowed_mime_types = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg'
    ];

    foreach ($file_fields as $field => $allowed_types) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $mime_type = mime_content_type($_FILES[$field]['tmp_name']);
            $max_size_kb = in_array($file_ext, ['jpg', 'jpeg']) ? 50 : 1024; // 50KB for images, 1MB for PDFs

            // Validate file type
            if (!isset($allowed_mime_types[$file_ext]) || $mime_type !== $allowed_mime_types[$file_ext]) {
                $file_paths['error'] = "Invalid file type for $field! Allowed types: " . implode(", ", array_keys($allowed_mime_types));
                return $file_paths;
            }

            // Validate file size
            if ($_FILES[$field]['size'] > $max_size_kb * 1024) {
                $file_paths['error'] = "$field file is too large! Max size: " . ($max_size_kb >= 1024 ? ($max_size_kb / 1024) . " MB" : "$max_size_kb KB") . ".";
                return $file_paths;
            }

            // Generate unique file name
            $file_name = uniqid($field . "_") . "." . $file_ext;
            $file_paths[$field] = $upload_dir . $file_name;
        } else {
            $file_paths[$field] = NULL; // No file uploaded
        }
    }

    return $file_paths;
}

// If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Define required fields
    $fields = [
        'name', 'gender', 'dob', 'contact_no', 'email', 'blood_group', 'hobby', 'course',
        'level', 'institute_regn_no', 'exam_due', 'aadhar_number', 'father_name', 'father_occupation',
        'father_contact_no', 'highest_qualification', 'coaching_centre', 'college', 'source_info',
        'permanent_address', 'city', 'pin_code', 'amount_paid'
    ];

    // Retrieve and sanitize form data
    foreach ($fields as $field) {
        $$field = isset($_POST[$field]) ? sanitizeInput($_POST[$field]) : '';
    }

    // Validate required fields
    foreach ($fields as $field) {
        if (empty($$field)) {
            $errors[] = ucfirst(str_replace("_", " ", $field)) . " is required.";
        }
    }

    // Format Validations
    if (!preg_match('/^[0-9]{10}$/', $contact_no)) $errors[] = "Invalid contact number format.";
    if (!preg_match('/^[0-9]{10}$/', $father_contact_no)) $errors[] = "Invalid father's contact number format.";
    if (!preg_match('/^[0-9]{12}$/', $aadhar_number)) $errors[] = "Invalid Aadhar number format.";
    if (!preg_match('/^[0-9]{6}$/', $pin_code)) $errors[] = "Invalid PIN code format.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!is_numeric($amount_paid) || $amount_paid < 0) $errors[] = "Invalid amount paid. Must be a positive number.";

    // File fields to be uploaded
    $file_fields = [
        'aadhar_card' => ['pdf'],
        'matriculation_marksheet' => ['pdf'],
        'twelveth_marksheet' => ['pdf'],
        'inter_marksheet' => ['pdf'],
        'graduation_marksheet' => ['pdf'],
        'institute_registration_letter' => ['pdf'],
        'photo' => ['jpg', 'jpeg'],
        'student_signature' => ['jpg', 'jpeg'],
        'parent_signature' => ['jpg', 'jpeg']
    ];

    // Validate and get file paths
    $uploaded_files = prepareFilePaths($file_fields);
    if (isset($uploaded_files['error'])) {
        $errors[] = $uploaded_files['error'];
    }

    // Check for duplicate entries
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM student_admission WHERE email = ? OR aadhar_number = ? OR contact_no = ? OR institute_regn_no = ?");
    $check_stmt->bind_param("ssss", $email, $aadhar_number, $contact_no, $institute_regn_no);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        $errors[] = "Duplicate Entry Detected: Email ($email), Aadhar Number ($aadhar_number), Contact Number ($contact_no), or Institute Registration Number ($institute_regn_no) already exists!";
    }

    // Proceed only if no errors
    if (empty($errors)) {
        $conn->begin_transaction();

        $stmt = $conn->prepare("INSERT INTO student_admission 
            (name, gender, dob, contact_no, email, blood_group, hobby, course, level, institute_regn_no, exam_due, aadhar_number,
            father_name, father_occupation, father_contact_no, highest_qualification, coaching_centre, college, source_info, 
            permanent_address, city, pin_code, amount_paid, aadhar_card, matriculation_marksheet, twelveth_marksheet, inter_marksheet, 
            graduation_marksheet, institute_registration_letter, photo, student_signature, parent_signature, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param(
            "ssssssssssssssssssssssssssssssss",
            $name, $gender, $dob, $contact_no, $email, $blood_group, $hobby, $course, $level, $institute_regn_no, $exam_due, 
            $aadhar_number, $father_name, $father_occupation, $father_contact_no, $highest_qualification, $coaching_centre, 
            $college, $source_info, $permanent_address, $city, $pin_code, $amount_paid, 
            $uploaded_files['aadhar_card'], $uploaded_files['matriculation_marksheet'], $uploaded_files['twelveth_marksheet'], 
            $uploaded_files['inter_marksheet'], $uploaded_files['graduation_marksheet'], $uploaded_files['institute_registration_letter'], 
            $uploaded_files['photo'], $uploaded_files['student_signature'], $uploaded_files['parent_signature']
        );

        if ($stmt->execute()) {
            foreach ($file_fields as $field => $allowed_types) {
                if (!empty($uploaded_files[$field])) {
                    move_uploaded_file($_FILES[$field]['tmp_name'], $uploaded_files[$field]);
                }
            }
            $conn->commit();
            $_SESSION['success_message'] = "Student Admission Successful!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $conn->rollback();
            $errors[] = "Database error! Could not insert data.";
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Form</title>
    <link rel="stylesheet" href="./css/admission-form.css">
</head>
<body>

<div class="container">
    <div class="header">
        <img src="./images/banner/headerSB.png" alt="Institution Header Image">
    </div>
    <h2>Admission Form</h2>
    <div style="color:burlywood; font-weight: bold; text-align:center;">
        Instructions: The maximum allowed file size is 50KB for images and 1MB for PDF documents.
    </div>

    <!-- Display Errors -->
    <?php if (!empty($errors)): ?>
        <div class="message-box error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Display Success Message and Redirect -->
    <?php if (!empty($_SESSION['success_message'])): ?>
    <div class="message-box success">
        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        <p>Redirecting to the home page in 5 seconds...</p>
    </div>

    <!-- Redirect after 5 seconds -->
    <script>
        setTimeout(function () {
            window.location.href = 'index.html';
        }, 5000);
    </script>

    <?php unset($_SESSION['success_message']); // Clear session message after displaying ?>
<?php endif; ?>



    <form action="" method="POST" enctype="multipart/form-data">

        <!-- Course, Level & Photo Upload -->
        <div class="form-group">
            <!-- Left Section: Course & Level -->
            <div class="left-section">
                <div class="form-group">
                    <label>Applying for Course:<span class="required">*</span></label>
                    <select name="course" required>
                        <option value="" selected disabled>-- Select Course --</option>
                        <option value="CA" <?php if(isset($course) && $course == "CA") echo "selected"; ?>>CA</option>
                        <option value="CMA" <?php if(isset($course) && $course == "CMA") echo "selected"; ?>>CMA</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Level:<span class="required">*</span></label>
                    <select name="level" required>
                        <option value="" selected disabled>-- Select Level --</option>
                        <option value="Foundation" <?php if(isset($level) && $level == "Foundation") echo "selected"; ?>>Foundation</option>
                        <option value="Intermediate" <?php if(isset($level) && $level == "Intermediate") echo "selected"; ?>>Intermediate</option>
                        <option value="Final" <?php if(isset($level) && $level == "Final") echo "selected"; ?>>Final</option>
                    </select>
                </div>
            </div>

            <!-- Right Section: Photo Upload -->
            <div class="upload-container">
                <div class="photo-preview" id="photoPreviewBox" onclick="document.getElementById('fileInput').click();">
                    <?php if (!empty($photoPath)) : ?>
                        <img id="photoPreview" src="<?php echo htmlspecialchars($photoPath); ?>" alt="Passport Photo Preview">
                    <?php else : ?>
                        <img id="photoPreview" src="#" alt="Passport Photo Preview">
                    <?php endif; ?>
                </div>
                <input type="file" name="photo" id="fileInput" accept="image/jpeg, image/jpg">
                <label>Upload Passport Photo<span class="required">*</span></label>
            </div>
        </div>

        <!-- A. Student Information -->
        <h3>A. Student Information</h3>
        <div class="form-group">
            <div>
                <label>Name:<span class="required">*</span></label>
                <input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>Email:<span class="required">*</span></label>
                <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>Gender:<span class="required">*</span></label>
                <select name="gender" required>
                    <option value="" selected disabled>-- Select Gender --</option>
                    <option value="Male" <?php if(isset($gender) && $gender == "Male") echo "selected"; ?>>Male</option>
                    <option value="Female" <?php if(isset($gender) && $gender == "Female") echo "selected"; ?>>Female</option>
                    <option value="Others" <?php if(isset($gender) && $gender == "Others") echo "selected"; ?>>Others</option>
                </select>
            </div>

            <div>
                <label>Date of Birth:<span class="required">*</span></label>
                <input type="date" name="dob" value="<?php echo isset($dob) ? htmlspecialchars($dob) : ''; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>Contact No:<span class="required">*</span></label>
                <input type="number" name="contact_no" value="<?php echo isset($contact_no) ? htmlspecialchars($contact_no) : ''; ?>" required>
            </div>
            
            <div>
                <label>Blood Group:<span class="required">*</span></label>
                <select name="blood_group" required>
                    <option value="" selected disabled>-- Select Blood Group --</option>
                    <option value="A+" <?php if(isset($blood_group) && $blood_group == "A+") echo "selected"; ?>>A+</option>
                    <option value="A-" <?php if(isset($blood_group) && $blood_group == "A-") echo "selected"; ?>>A-</option>
                    <option value="B+" <?php if(isset($blood_group) && $blood_group == "B+") echo "selected"; ?>>B+</option>
                    <option value="B-" <?php if(isset($blood_group) && $blood_group == "B-") echo "selected"; ?>>B-</option>
                    <option value="O+" <?php if(isset($blood_group) && $blood_group == "O+") echo "selected"; ?>>O+</option>
                    <option value="O-" <?php if(isset($blood_group) && $blood_group == "O-") echo "selected"; ?>>O-</option>
                    <option value="AB+" <?php if(isset($blood_group) && $blood_group == "AB+") echo "selected"; ?>>AB+</option>
                    <option value="AB-" <?php if(isset($blood_group) && $blood_group == "AB-") echo "selected"; ?>>AB-</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>Aadhar Number:<span class="required">*</span></label>
                <input type="number" name="aadhar_number" value="<?php echo isset($aadhar_number) ? htmlspecialchars($aadhar_number) : ''; ?>" required>
            </div>

            <div>
                <label>Hobby:</label>
                <input type="text" name="hobby" value="<?php echo isset($hobby) ? htmlspecialchars($hobby) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>Institute Regn. No:<span class="required">*</span></label>
                <input type="number" name="institute_regn_no" value="<?php echo isset($institute_regn_no) ? htmlspecialchars($institute_regn_no) : ''; ?>" required>
            </div>
            
            <div>
                <label>Exam Due:<span class="required">*</span></label>
                <input type="text" name="exam_due" value="<?php echo isset($exam_due) ? htmlspecialchars($exam_due) : ''; ?>" required>
            </div>
        </div>


        <!-- B. Parent Information -->
        <h3>B. Parent Information</h3>
        <div class="form-group">
            <div>
                <label>Father's Name:<span class="required">*</span></label>
                <input type="text" name="father_name" value="<?php echo isset($father_name) ? htmlspecialchars($father_name) : ''; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>Father's Occupation:<span class="required">*</span></label>
                <input type="text" name="father_occupation" value="<?php echo isset($father_occupation) ? htmlspecialchars($father_occupation) : ''; ?>" required>
            </div>

            <div>
                <label>Father's Contact No:<span class="required">*</span></label>
                <input type="number" name="father_contact_no" value="<?php echo isset($father_contact_no) ? htmlspecialchars($father_contact_no) : ''; ?>" required>
            </div>
        </div>

        <!-- C. Education Details -->
        <h3>C. Previous Education Details</h3>
        <div class="form-group">
            <div>
                <label>Highest Qualification:<span class="required">*</span></label>
                <input type="text" name="highest_qualification" value="<?php echo isset($highest_qualification) ? htmlspecialchars($highest_qualification) : ''; ?>" required>
            </div>

            <div>
                <label>Coaching Centre (If any):</label>
                <input type="text" name="coaching_centre" value="<?php echo isset($coaching_centre) ? htmlspecialchars($coaching_centre) : ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>College/University:<span class="required">*</span></label>
                <input type="text" name="college" value="<?php echo isset($college) ? htmlspecialchars($college) : ''; ?>" required>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>How did you hear about us?<span class="required">*</span></label>
                <select name="source_info" required>
                    <option value="" selected disabled>-- Select Source --</option>
                    <option value="Advertisement" <?php if(isset($source_info) && $source_info == "Advertisement") echo "selected"; ?>>Advertisement</option>
                    <option value="College Faculty" <?php if(isset($source_info) && $source_info == "College Faculty") echo "selected"; ?>>College Faculty</option>
                    <option value="Friend/Relative" <?php if(isset($source_info) && $source_info == "Friend/Relative") echo "selected"; ?>>Friend/Relative</option>
                    <option value="SkyBird Pass Out Students" <?php if(isset($source_info) && $source_info == "SkyBird Pass Out Students") echo "selected"; ?>>SkyBird Pass Out Students</option>
                    <option value="Social Media" <?php if(isset($source_info) && $source_info == "Social Media") echo "selected"; ?>>Social Media</option>
                    <option value="Website" <?php if(isset($source_info) && $source_info == "Website") echo "selected"; ?>>Website</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>Amount Paid:<span class="required">*</span></label>
                <input type="number" name="amount_paid" value="<?php echo isset($amount_paid) ? htmlspecialchars($amount_paid) : ''; ?>" required>
            </div>
        </div>

        <!-- D. Address -->
        <h3>D. Address</h3>
        <div class="form-group">
            <div>
                <label>Permanent Address:<span class="required">*</span></label>
                <textarea name="permanent_address" class="full-width" required><?php echo isset($permanent_address) ? htmlspecialchars($permanent_address) : ''; ?></textarea>
            </div>
        </div>

        <div class="form-group">
            <div>
                <label>City:<span class="required">*</span></label>
                <input type="text" name="city" value="<?php echo isset($city) ? htmlspecialchars($city) : ''; ?>" required>
            </div>

            <div>
                <label>Pin Code:<span class="required">*</span></label>
                <input type="number" name="pin_code" value="<?php echo isset($pin_code) ? htmlspecialchars($pin_code) : ''; ?>" required>
            </div>
        </div>

        <!-- E. Upload Documents -->
        <h3>E. Upload Documents</h3>
        
        <div class="form-group file-upload">
            <div>
                <label>Aadhar Card:<span class="required">*</span></label>
                <input type="file" name="aadhar_card" accept="application/pdf" required>
            </div>

            <div>
                <label>Matriculation Marksheet:<span class="required">*</span></label>
                <input type="file" name="matriculation_marksheet" accept="application/pdf" required>
            </div>
        </div>

        <div class="form-group file-upload">
            <div>
                <label>+2 / XII / 12<sup>th</sup> Marksheet:<span class="required">*</span></label>
                <input type="file" name="twelveth_marksheet" accept="application/pdf" required>
            </div>

            <div>
                <label>Graduation Marksheet:</label>
                <input type="file" name="graduation_marksheet" accept="application/pdf">
            </div>
        </div>

        <div class="form-group file-upload">
            <div>
                <label>Copy of Institute Registration Letter:<span class="required">*</span></label>
                <input type="file" name="institute_registration_letter" accept="application/pdf" required>
            </div>
        </div>

        <div class="form-group file-upload">
            <div>
                <label>Inter Marksheet (For Final Students):</label>
                <input type="file" name="inter_marksheet" accept="application/pdf">
            </div>
        </div>

        <!-- F. Consent Form -->
        <h3>F. Consent Form</h3>
        <div class="rules-container">
            <h4>The following rules have been established for the students of <span class="highlight">SKY BIRD</span></h4>
            <p><strong>Ensuring optimal time management, eliminating causes of failure, and promoting discipline and focus.</strong></p>
            
            <ul>
                <li><span class="highlight">Mobile phones</span> are strictly prohibited in the library, except in <strong>Flight Mode</strong>.</li>
                <li>The library is a place for <strong>focused study</strong> (not a bedroom). Discussions inside the library are not permitted.</li>
                <li>Students in <span class="highlight">romantic relationships</span> may face distractions, potentially affecting their academic performance. 
                    <strong>Therefore, students in relationships are not eligible for seat allotment in the SKY BIRD Batch.</strong></li>
                <li>The use of <span class="highlight">tobacco and alcohol</span> is strictly prohibited on the premises.</li>
                <li><strong>Violation of rules</strong> may result in forfeiture of the security deposit and parental notification (in specific cases).</li>
                <li>The library must be kept clean. <strong>No littering</strong>, food, or snacks are allowed inside.</li>
                <li><span class="highlight">Guests and friends</span> are not permitted as they may disrupt others.</li>
                <li><strong>Outings</strong> are allowed only for essential purposes with prior approval.</li>
                <li>The management holds <strong>no responsibility</strong> for any abnormal activities.</li>
                <li><strong>Security Deposit:</strong> A <span class="highlight">2-month food deposit</span> must be paid in advance and will be refunded only at the end of the hostel tenure. 
                    <strong>Other fees are non-refundable.</strong> Monthly charges must be paid on time to avoid penalties.</li>
                <li>Students should refer to the <span class="highlight">detailed fee structure</span> available with the administrator.</li>
                <li>The <strong>selection process</strong> for <span class="highlight">SKY BIRD</span> is final, and no personal requests will be entertained.</li>
                <li><strong>Professional behavior</strong> is expected at all times. Any disruptive activities will result in strict action.</li>
                <li>Additional rules may be enforced by the management to ensure <strong>uninterrupted exam preparation</strong>.</li>
            </ul>

            <div class="checkbox-container">
                <input type="checkbox" id="agree" name="agree" required>
                <label for="agree">I agree to the terms and conditions<span class="required">*</span></label>
            </div>

            <div class="form-group">
                <div class="form-group">
                    <input type="file" name="student_signature" accept="image/jpeg, image/jpg" required 
                        value="<?php echo isset($_POST['student_signature']) ? htmlspecialchars($_POST['student_signature']) : ''; ?>">
                    <label>Signature of Student:<span class="required">*</span></label>
                </div>

                <div class="form-group">
                    <input type="file" name="parent_signature" accept="image/jpeg, image/jpg"
                        value="<?php echo isset($_POST['parent_signature']) ? htmlspecialchars($_POST['parent_signature']) : ''; ?>">
                    <label>Signature of Parent (In case of minor):</label>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit">Submit Admission Form</button>

    </form>
</div>

<script>
        document.addEventListener("DOMContentLoaded", function() {
            const fileInput = document.getElementById("fileInput");
            const photoPreview = document.getElementById("photoPreview");
            const previewBox = document.getElementById("photoPreviewBox");

            // Set default text to "Choose Photo"
            previewBox.setAttribute("data-text", "Choose Photo");

            fileInput.addEventListener("change", function(event) {
                const file = event.target.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        photoPreview.src = e.target.result;
                        photoPreview.style.display = "block";
                        previewBox.style.border = "2px solid #007bff";
                        previewBox.style.background = "none";
                        previewBox.style.cursor = "pointer";

                        // Change text to "Click to Change Photo" after image upload
                        previewBox.setAttribute("data-text", "Click to Change Photo");
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>

<!-- Prevent Form Resubmission on Back Button -->
<script>
    window.onload = function() {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    };
</script>


</body>
</html>
