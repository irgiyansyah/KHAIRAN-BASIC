<?php
// Fungsi untuk membersihkan input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fungsi untuk memeriksa apakah user login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk memeriksa peran user
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function is_user() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
}
?>