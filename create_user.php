<?php
// File: create_user.php
include_once 'includes/db_connect.php';

echo "<h2>Membuat atau Memperbarui Akun User...</h2>";

// Ganti dengan username dan password yang Anda inginkan
$username_user = 'user';
$password_mentah = 'user123';

$hashed_password = password_hash($password_mentah, PASSWORD_DEFAULT);
$role_user = 'user';

// Periksa apakah user sudah ada
$stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_check->bind_param("s", $username_user);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Jika user sudah ada, perbarui password dan role
    $stmt_update = $conn->prepare("UPDATE users SET password = ?, role = ? WHERE username = ?");
    $stmt_update->bind_param("sss", $hashed_password, $role_user, $username_user);
    if ($stmt_update->execute()) {
        echo "<p style='color: green;'>Akun user <strong>" . $username_user . "</strong> berhasil diperbarui.</p>";
    } else {
        echo "<p style='color: red;'>Gagal memperbarui akun user: " . $conn->error . "</p>";
    }
    $stmt_update->close();
} else {
    // Jika user belum ada, buat baru
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("sss", $username_user, $hashed_password, $role_user);
    if ($stmt_insert->execute()) {
        echo "<p style='color: green;'>Akun user <strong>" . $username_user . "</strong> berhasil dibuat.</p>";
    } else {
        echo "<p style='color: red;'>Gagal membuat akun user: " . $conn->error . "</p>";
    }
    $stmt_insert->close();
}

echo "Detail Akun:<br>";
echo "Username: <strong>" . $username_user . "</strong><br>";
echo "Password: <strong>" . $password_mentah . "</strong> (gunakan password ini untuk login)<br>";
echo "Role: User<br>";
echo "<hr><p>Sekarang, Anda bisa mencoba login dengan kredensial di atas. Setelah berhasil, **segera hapus file create_user.php** ini dari folder proyek Anda untuk alasan keamanan.</p>";

$conn->close();
?>