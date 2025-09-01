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
    <title>Beranda - KHAIRAN BASIC </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include_once 'includes/navbar.php'; ?>

    <header class="hero-section text-white text-center py-5">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Solusi Perencanaan Keuangan Anda</h1>
            <p class="lead mb-5">Simulasi Dana Pensiun dan Asuransi Jiwa yang Akurat dan Mudah.</p>
            <a href="login" class="btn btn-warning btn-lg me-3">Mulai Simulasi Sekarang</a>
            <a href="about" class="btn btn-outline-light btn-lg">Pelajari Lebih Lanjut</a>
        </div>
    </header>

    <section class="features-section py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Fitur Utama</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="card-title fw-bold text-primary">Simulasi Dapen</h3>
                            <p class="card-text">Hitung proyeksi dana pensiun Anda dengan berbagai skenario gaji dan asumsi.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="card-title fw-bold text-success">Simulasi Asji</h3>
                            <p class="card-text">Dapatkan estimasi kebutuhan asuransi jiwa untuk perlindungan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="card-title fw-bold text-info">Pengaturan Asumsi Fleksibel</h3>
                            <p class="card-text">Admin dapat menyesuaikan asumsi aktuaria untuk hasil yang lebih spesifik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date("Y"); ?> Simulasi Aktuaria. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
