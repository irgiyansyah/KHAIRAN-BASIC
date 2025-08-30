<?php
session_start();
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi - KHAIRAN BASIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include_once 'includes/navbar.php'; ?>

    <main class="container my-5">
        <h1 class="text-center mb-4 display-4 fw-bold">Mulai Simulasi Anda</h1>
        <hr>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg p-4 mb-4">
                    <div class="card-body text-center">
                        <p class="lead mb-4">Untuk memulai simulasi Dana Pensiun atau Asuransi Jiwa, Anda perlu login ke akun Anda.</p>
                        <p class="mb-4">Jika Anda sudah memiliki akun, silakan klik tombol Login di bawah. Jika belum, hubungi Admin untuk pembuatan akun Anda.</p>
                        <a href="login.php" class="btn btn-primary btn-lg px-5">Login Sekarang</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-md-10">
                <h2 class="text-center mb-4 fw-bold">Bagaimana Proses Simulasi Bekerja?</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 text-center border-primary shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title text-primary mb-3">Langkah 1: Login</h4>
                                <p class="card-text">Pengguna (Admin/User) masuk menggunakan Username dan Kata Sandi.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 text-center border-success shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title text-success mb-3">Langkah 2: Input Data (User)</h4>
                                <p class="card-text">User memilih produk (Dapen/Asji) dan mengisi data input yang diperlukan.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 text-center border-info shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title text-info mb-3">Langkah 3: Asumsi (Admin)</h4>
                                <p class="card-text">Admin menetapkan asumsi aktuaria untuk setiap pengajuan User.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 offset-md-2 mb-4">
                        <div class="card h-100 text-center border-warning shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title text-warning mb-3">Langkah 4: Perhitungan</h4>
                                <p class="card-text">Sistem melakukan perhitungan berdasarkan input User dan asumsi Admin.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 text-center border-danger shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title text-danger mb-3">Langkah 5: Lihat Hasil</h4>
                                <p class="card-text">User dapat melihat hasil perhitungan simulasi di akun mereka.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date("Y"); ?> Simulasi Aktuaria. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>