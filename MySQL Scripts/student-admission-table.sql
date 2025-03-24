-- Table Creation

CREATE TABLE student_admission (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Student Information
    name VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    dob DATE NOT NULL,
    contact_no VARCHAR(10) NOT NULL UNIQUE CHECK (LENGTH(contact_no) = 10 AND contact_no REGEXP '^[0-9]{10}$'),
    email VARCHAR(255) NOT NULL UNIQUE CHECK (email REGEXP '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'),
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    hobby VARCHAR(255),
    course ENUM('CA', 'CMA', 'CS') NOT NULL,
    level ENUM('Foundation', 'Intermediate', 'Final') NOT NULL,
    institute_regn_no VARCHAR(20) NOT NULL UNIQUE CHECK (institute_regn_no REGEXP '^[0-9]+$'),
    exam_due VARCHAR(20) NOT NULL,
    aadhar_number VARCHAR(12) NOT NULL UNIQUE CHECK (LENGTH(aadhar_number) = 12 AND aadhar_number REGEXP '^[0-9]{12}$'),

    -- Parent Information
    father_name VARCHAR(255) NOT NULL,
    father_occupation VARCHAR(255),
    father_contact_no VARCHAR(10) NOT NULL CHECK (LENGTH(father_contact_no) = 10 AND father_contact_no REGEXP '^[0-9]{10}$'),

    -- Previous Education Details
    highest_qualification VARCHAR(255) NOT NULL,
    coaching_centre VARCHAR(255),
    college VARCHAR(255) NOT NULL,
    source_info VARCHAR(255) NOT NULL,

    -- Address
    permanent_address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    pin_code VARCHAR(6) NOT NULL CHECK (LENGTH(pin_code) = 6 AND pin_code REGEXP '^[0-9]{6}$'),

    -- Upload Documents
    aadhar_card VARCHAR(255) NOT NULL,
    matriculation_marksheet VARCHAR(255) NOT NULL,
    twelveth_marksheet VARCHAR(255) NOT NULL,
    inter_marksheet VARCHAR(255),
    graduation_marksheet VARCHAR(255),
    institute_registration_letter VARCHAR(255) NOT NULL,
    photo VARCHAR(255) NOT NULL,
    student_signature VARCHAR(255) NOT NULL,
    parent_signature VARCHAR(255),

    amount_paid DECIMAL(10,2) NOT NULL CHECK (amount_paid >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- View all the records
SELECT * FROM student_admission;
