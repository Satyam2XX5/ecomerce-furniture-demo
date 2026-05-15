<?php
session_start();

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: /furniture-website/admin/login.php");
        exit();
    }
}

function adminLogout() {
    session_destroy();
    header("Location: /furniture-website/admin/login.php");
    exit();
}
?>
