<?php
$servername = "localhost";
$username = "root"; // Sesuai dengan username MySQL Anda
$password = "";     // Sesuai dengan password MySQL Anda (default XAMPP kosong)
$dbname = "dapen_asji_db"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>