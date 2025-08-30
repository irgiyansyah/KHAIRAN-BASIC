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
    <title>Tentang Kami - KHAIRAN BASIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include_once 'includes/navbar.php'; ?>

    <main class="container my-5">
        <h1 class="text-center mb-4 display-4 fw-bold">Tentang Simulasi Aktuaria</h1>
        <hr>
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <p class="lead text-center mb-4">Kami menyediakan platform simulasi perencanaan keuangan yang komprehensif untuk Dana Pensiun (Dapen) dan Asuransi Jiwa (Asji), dirancang untuk membantu Anda membuat keputusan finansial yang lebih baik.</p>
                <p>Aplikasi ini dirancang dengan dua jenis pengguna utama:</p>
                <ul>
                    <li>
                        <strong>Admin:</strong> Bertanggung jawab untuk menetapkan asumsi aktuaria yang menjadi dasar perhitungan. Admin memiliki kontrol penuh atas parameter kunci seperti tabel mortalitas, tingkat suku bunga, dan metode perhitungan.
                    </li>
                    <li>
                        <strong>User:</strong> Dapat memasukkan data pribadi dan finansial mereka untuk mendapatkan hasil simulasi berdasarkan asumsi yang telah ditetapkan oleh Admin. Ini memungkinkan pengguna untuk memahami proyeksi dana pensiun dan kebutuhan asuransi jiwa mereka secara personal.
                    </li>
                </ul>
                <p>Fitur utama kami meliputi:</p>
                <ul>
                    <li>Input data yang mudah dan intuitif untuk simulasi Dapen dan Asji.</li>
                    <li>Penggunaan berbagai tabel mortalitas standar (TM GAM 71, TM GAM 83, dll.) untuk perhitungan yang akurat.</li>
                    <li>Pilihan metode perhitungan aktuaria seperti EAN, AAN, PUC, dan ILP.</li>
                    <li>Visualisasi hasil perhitungan yang jelas dan informatif.</li>
                </ul>
                <p>Kami berkomitmen untuk memberikan alat yang andal dan mudah digunakan untuk perencanaan keuangan masa depan Anda.</p>
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