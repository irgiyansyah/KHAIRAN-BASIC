<?php
session_start();
include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

// Pastikan hanya admin yang bisa mengakses
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Simulasi Aktuaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div class="bg-dark border-right sidebar" id="sidebar-wrapper">
            <div class="sidebar-heading text-white text-center py-4 fs-5 fw-bold">Admin Panel</div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="manage_users.php" class="list-group-item list-group-item-action bg-dark text-white <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_users.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i> Kelola User
                </a>
                <a href="../logout.php" class="list-group-item list-group-item-action bg-dark text-white mt-auto">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
        <div id="page-content-wrapper" class="main-content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-4 shadow-sm">
                <button class="btn btn-primary" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h5 class="ms-auto me-3 my-0 text-muted">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
            </nav>
            <div class="container-fluid">