<?php
session_start();

function checkUser($role)
{
    // Define allowed roles
    $allowedRoles = ["admin", "student", "guest"];

    // Check if the role provided is valid
    if (!in_array($role, $allowedRoles)) {
        die("Invalid role specified.");
    }

    // Check if the user is authenticated
    if (!isset($_SESSION['role'])) {
        header('Location: logout.php');
        exit();
    }

    // Redirect users to their respective pages if they don’t match the required role
    if ($_SESSION['role'] !== $role) {
        switch ($_SESSION['role']) {
            case "admin":
                header("Location: admin-page.php");
                break;
            case "student":
                header("Location: student-page.php");
                break;
            case "guest":
                header("Location: guest-page.php");
                break;
        }
        exit();
    }
}
