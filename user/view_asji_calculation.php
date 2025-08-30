<?php
include_once 'includes/header.php';
include_once '../includes/calculation_logic.php';

$asji_input_id = isset($_GET['input_id']) ? (int)$_GET['input_id'] : 0;
if ($asji_input_id == 0) {
    redirect('calculation_results.php');
}

$asji_data = null;
$assumption_data = null;
$calculation_result_data = null;
$user_id = $_SESSION['user_id'];

// Fetch Asji input data
$stmt_asji = $conn->prepare("SELECT * FROM asji_inputs WHERE id = ? AND user_id = ?");
$stmt_asji->bind_param("ii", $asji_input_id, $user_id);
$stmt_asji->execute();
$result_asji = $stmt_asji->get_result();
if ($result_asji->num_rows > 0) {
    $asji_data = $result_asji->fetch_assoc();
} else {
    redirect('calculation_results.php');
}
$stmt_asji->close();

// Fetch assumptions for this user
$stmt_assumption = $conn->prepare("SELECT * FROM assumptions WHERE user_id = ?");
$stmt_assumption->bind_param("i", $user_id);
$stmt_assumption->execute();
$result_assumption = $stmt_assumption->get_result();
if ($result_assumption->num_rows > 0) {
    $assumption_data = $result_assumption->fetch_assoc();
}
$stmt_assumption->close();

if (!$assumption_data) {
    echo "<div class='alert alert-warning'>Admin belum menetapkan asumsi untuk akun Anda. Hasil perhitungan belum dapat ditampilkan.</div>";
    include_once 'includes/footer.php';
    exit();
}

$calculation_result_data = calculate_asji($asji_data, $assumption_data, $conn);
?>

<h1 class="mb-4 display-5 fw-bold text-success">Hasil Perhitungan Asuransi Jiwa</h1>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white fw-bold">Detail Pengajuan #<?php echo $asji_data['id']; ?></div>
    <div class="card-body">
        <ul>
            <li><strong>Jenis Asuransi:</strong> <?php echo htmlspecialchars($asji_data['jenis_asji']); ?></li>
            <li><strong>Usia Saat Ini:</strong> <?php echo htmlspecialchars($asji_data['usia_saat_ini']); ?> tahun</li>
            <?php if ($asji_data['jangka_waktu'] !== NULL): ?>
                <li><strong>Jangka Waktu Pertanggungan:</strong> <?php echo htmlspecialchars($asji_data['jangka_waktu']); ?> tahun</li>
            <?php endif; ?>
            <li><strong>Besar Santunan:</strong> Rp <?php echo number_format($asji_data['besar_santunan'], 0, ',', '.'); ?></li>
            <li><strong>Pembayaran:</strong> <?php echo htmlspecialchars($asji_data['metode_pembayaran']); ?></li>
            <li><strong>Frekuensi Pembayaran:</strong> <?php echo htmlspecialchars($asji_data['jumlah_pembayaran_setahun']); ?> kali/tahun</li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold">Asumsi yang Digunakan Admin</div>
    <div class="card-body">
        <ul>
            <li><strong>Tabel Mortalita:</strong> <?php echo $assumption_data['mortalita_usia_kerja']; ?></li>
            <li><strong>Tingkat Suku Bunga:</strong> <?php echo ($assumption_data['tingkat_suku_bunga'] * 100); ?>%</li>
            <li><strong>Penentuan:</strong> <?php echo number_format($assumption_data['l0'], 0, ',', '.'); ?></li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">Ringkasan Hasil Perhitungan</div>
    <div class="card-body">
        <p><strong>Premi per 1 kali pembayaran:</strong> <br>
            <span class="fs-4 text-primary">Rp <?php echo number_format($calculation_result_data['premi_per_bayar'], 0, ',', '.'); ?></span></p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-secondary text-white fw-bold">Detail Perhitungan Tahunan</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th>Usia</th>
                        <th>Jumlah Orang</th>
                        <th>Komputasi (D-X) Aktuaria</th>
                        <th>Komputasi (C-X) Aktuaria</th>
                        <th>Nilai Masa Depan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($calculation_result_data['detail_tahunan'])) {
                        foreach ($calculation_result_data['detail_tahunan'] as $usia => $detail): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usia); ?></td>
                                <td><?php echo number_format($detail['lx'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($detail['dx'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($detail['cx'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($detail['mx'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach;
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>Data perhitungan tahunan tidak tersedia.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>