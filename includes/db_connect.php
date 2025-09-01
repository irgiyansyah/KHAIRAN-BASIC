<?php
$env = parse_ini_file(__DIR__ . '/../.env');

$servername = $env['DB_HOST'];
$username   = $env['DB_USERNAME'];
$password   = $env['DB_PASSWORD'];
$dbname     = $env['DB_DATABASE'];

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
