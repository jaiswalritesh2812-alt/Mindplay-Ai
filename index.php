<?php
// Initialize session
require_once __DIR__ . '/config/session.php';

// Redirect to appropriate page based on login status
if(isLoggedIn()){
    if(isAdmin()){
        header("Location: admin/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>
