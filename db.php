<?php
/**
 * Database Connection
 */

// Include config if not already included
if(!defined('DB_HOST')){
    require_once __DIR__ . '/config.php';
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better character support
$conn->set_charset("utf8mb4");
