<?php
/**
 * Session Management & Authentication
 */

// Configure session settings BEFORE starting session
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
    
    // Start the session
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user is student
 */
function isStudent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth($redirectTo = '/login.php') {
    if (!isLoggedIn()) {
        $currentPage = $_SERVER['PHP_SELF'];
        if (strpos($currentPage, 'login.php') === false) {
            header("Location: $redirectTo");
            exit();
        }
    }
}

/**
 * Require admin role - redirect if not admin
 */
function requireAdmin($redirectTo = '/login.php') {
    if (!isLoggedIn() || !isAdmin()) {
        $currentPage = $_SERVER['PHP_SELF'];
        if (strpos($currentPage, 'login.php') === false) {
            header("Location: $redirectTo");
            exit();
        }
    }
}

/**
 * Require student role - redirect if not student
 */
function requireStudent($redirectTo = '/login.php') {
    if (!isLoggedIn() || !isStudent()) {
        $currentPage = $_SERVER['PHP_SELF'];
        if (strpos($currentPage, 'login.php') === false) {
            header("Location: $redirectTo");
            exit();
        }
    }
}

/**
 * Get current user ID
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user name
 */
function currentUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Get current user role
 */
function currentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Redirect to appropriate dashboard based on role
 */
function redirectToDashboard() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
    
    if (isAdmin()) {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../student/dashboard.php");
    }
    exit();
}
