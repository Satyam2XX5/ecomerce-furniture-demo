<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'furniture_db');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Helper function - Safe query
function db_query($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    return $result;
}

// Helper - Sanitize input
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}
?>