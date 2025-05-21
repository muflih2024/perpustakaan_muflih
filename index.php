<?php
require_once __DIR__ . '/config/koneksi.php'; // Ensure BASE_URL is available

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
} else {
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}
?>

