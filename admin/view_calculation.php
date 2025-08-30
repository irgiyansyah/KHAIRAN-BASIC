<?php
include_once 'includes/header.php';
include_once '../includes/calculation_logic.php';

$input_id = isset($_GET['input_id']) ? (int)$_GET['input_id'] : 0;
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$user_id_admin = $_SESSION['user_id'];

if ($input_id == 0 || !in_array($type, ['dapen', 'asji'])) {
    redirect('manage_users.php');
}

$input_data = null;
$assumption_data = null;
$calculation_result_data = null;

// Fetch input data based on type
if ($type == 'dapen') {
    $stmt = $conn->prepare("SELECT * FROM dapen_inputs WHERE id = ?");
    $stmt->bind_param("i", $input_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $input_data = $result->fetch_assoc();
    }
    $stmt->close();
} elseif ($type == 'asji') {
    $stmt = $conn->prepare("SELECT * FROM asji_inputs WHERE id = ?");
    $stmt->bind_param("i", $input_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $input_data = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$input_data) {
    echo "<div class='alert alert-danger'>Pengajuan tidak ditemukan.</div>";
    include_once 'includes/footer.php';
    exit();
}

// Fetch assumptions for this user
$stmt_assumption = $conn->prepare("SELECT * FROM assumptions WHERE user_id = ?");
$stmt_assumption->bind_param("i", $input_data['user_id']);
$stmt_assumption->execute();
$result_assumption = $stmt_assumption->get_result();
if ($result_assumption->num_rows > 0) {
    $assumption_data = $result_assumption->fetch_assoc();
}
$stmt_assumption->close();

if (!$assumption_data) {
    echo "<div class='alert alert-warning'>Asumsi belum ditetapkan oleh Admin.</div>";
    include_once 'includes/footer.php';
    exit();
}

// Perform calculation based on type
if ($type == 'dapen') {
    $metode = $assumption_data['metode_perhitungan'];
    $results = [];
    switch ($metode) {
        case 'EAN':
            $results = calculate_dapen_EAN($input_data, $assumption_data, $conn);
            break;
        case 'AAN':
            $results = calculate_dapen_AAN($input_data, $assumption_data, $conn);
            break;
        case 'PUC':
            $results = calculate_dapen_PUC($input_data, $assumption_data, $conn);
            break;
        case 'ILP':
            $results = calculate_dapen_ILP($input_data, $assumption_data, $conn);
            break;
        default:
            echo "<div class='alert alert-danger'>Metode perhitungan Dapen tidak valid.</div>";
            include_once 'includes/footer.php';
            exit();
    }
    $calculation_result_data = $results;

} elseif ($type == 'asji') {
    $calculation_result_data = calculate_asji($input_data, $assumption_data, $conn);
}
?>

<h1 class="mb-4 display-5 fw-bold text-primary">Hasil Perhitungan Admin</h1>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">
        Detail Pengajuan #<?php echo $input_data['id']; ?> (User: <?php echo htmlspecialchars($input_data['user_id']); ?>)
    </div>
    <div class="card-body">
        <?php if ($type == 'dapen'): ?>
            <ul>
                <li><strong>Gaji Awal per Bulan:</strong> Rp <?php echo number_format($input_data['gaji_awal_per_bulan'], 0, ',', '.'); ?></li>
                <li><strong>Persentase Kenaikan Gaji:</strong> <?php echo $input_data['persentase_kenaikan_gaji']; ?>%</li>
                <li><strong>Tahun Kerja:</strong> <?php echo $input_data['tahun_pertama_kerja']; ?> - <?php echo $input_data['tahun_saat_ini']; ?></li>
                <li><strong>Usia Kerja Pertama:</strong> <?php echo $input_data['usia_kerja_pertama']; ?> tahun</li>
                <li><strong>Usia Saat Ini:</strong> <?php echo $input_data['usia_saat_ini']; ?> tahun</li>
                <li><strong>Usia Pensiun:</strong> <?php echo $input_data['usia_pensiun']; ?> tahun</li>
                <li><strong>Metode Pembayaran:</strong> <?php echo $input_data['metode_pembayaran']; ?></li>
                <li><strong>Frekuensi Pembayaran:</strong> <?php echo $input_data['jumlah_pembayaran_setahun']; ?> kali/tahun</li>
            </ul>
        <?php elseif ($type == 'asji'): ?>
            <ul>
                <li><strong>Jenis Asuransi:</strong> <?php echo htmlspecialchars($input_data['jenis_asji']); ?></li>
                <li><strong>Usia Saat Ini:</strong> <?php echo htmlspecialchars($input_data['usia_saat_ini']); ?> tahun</li>
                <?php if ($input_data['jangka_waktu'] !== NULL): ?>
                    <li><strong>Jangka Waktu Pertanggungan:</strong> <?php echo htmlspecialchars($input_data['jangka_waktu']); ?> tahun</li>
                <?php endif; ?>
                <li><strong>Besar Santunan:</strong> Rp <?php echo number_format($input_data['besar_santunan'], 0, ',', '.'); ?></li>
                <li><strong>Pembayaran:</strong> <?php echo htmlspecialchars($input_data['metode_pembayaran']); ?></li>
                <li><strong>Frekuensi Pembayaran:</strong> <?php echo htmlspecialchars($input_data['jumlah_pembayaran_setahun']); ?> kali/tahun</li>
            </ul>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold">Asumsi yang Digunakan Admin</div>
    <div class="card-body">
        <ul>
            <li><strong>Tabel Mortalita Usia Kerja:</strong> <?php echo $assumption_data['mortalita_usia_kerja']; ?></li>
            <li><strong>Tabel Mortalita Usia Pensiun:</strong> <?php echo $assumption_data['mortalita_usia_pensiun']; ?></li>
            <li><strong>Metode Perhitungan:</strong> <?php echo $assumption_data['metode_perhitungan']; ?></li>
            <li><strong>Penentuan $l_0$:</strong> <?php echo number_format($assumption_data['l0'], 0, ',', '.'); ?></li>
            <li><strong>Tingkat Suku Bunga:</strong> <?php echo ($assumption_data['tingkat_suku_bunga'] * 100); ?>%</li>
            <li><strong>Proporsi Manfaat Pensiun:</strong> <?php echo $assumption_data['proporsi_manfaat_pensiun']; ?>%</li>
            <li><strong>Besar Manfaat Pensiun:</strong> <?php echo $assumption_data['besar_manfaat_pensiun_persen']; ?>%</li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white fw-bold">Ringkasan Hasil Perhitungan</div>
    <div class="card-body">
        <?php if ($type == 'dapen'): ?>
            <p><strong>Besar Manfaat Pensiun (berdasarkan <?php echo $assumption_data['besar_manfaat_pensiun_persen']; ?>% dari gaji terakhir):</strong> <br>
                <span class="fs-4 text-success">Rp <?php echo number_format($calculation_result_data['besar_manfaat_pensiun_persen_val'], 0, ',', '.'); ?></span></p>
            <p><strong>Besar Manfaat Pensiun (100% dari gaji terakhir):</strong> <br>
                <span class="fs-4 text-success">Rp <?php echo number_format($calculation_result_data['besar_manfaat_pensiun_100_val'], 0, ',', '.'); ?></span></p>
            <hr>
            <p><strong>Total Iuran Normal (per Bulan):</strong> Rp <?php echo number_format($calculation_result_data['iuran_normal_bulanan'], 0, ',', '.'); ?></p>
            <p><strong>Total Iuran Normal (per Tahun):</strong> Rp <?php echo number_format($calculation_result_data['iuran_normal_tahunan'], 0, ',', '.'); ?></p>
            <p><strong>Total Iuran Normal (Total Akumulasi):</strong> Rp <?php echo number_format($calculation_result_data['iuran_normal_total'], 0, ',', '.'); ?></p>
        <?php elseif ($type == 'asji'): ?>
            <p><strong>Premi per 1 kali pembayaran:</strong> <br>
                <span class="fs-4 text-primary">Rp <?php echo number_format($calculation_result_data['premi_per_bayar'], 0, ',', '.'); ?></span></p>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-secondary text-white fw-bold">Detail Perhitungan Tahunan</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead>
                    <?php if ($type == 'dapen'): ?>
                        <tr>
                            <th>Usia</th>
                            <th>Gaji per Bulan</th>
                            <th>Gaji per Tahun</th>
                            <th>PV Future Benefit</th>
                            <th>Kewajiban Aktuaria</th>
                        </tr>
                    <?php elseif ($type == 'asji'): ?>
                        <tr>
                            <th>Usia</th>
                            <th>$l_x$</th>
                            <th>$D_x$</th>
                            <th>$C_x$</th>
                            <th>$M_x$</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if ($type == 'dapen' && is_array($calculation_result_data['total_gaji_per_bulan'])): ?>
                        <?php foreach ($calculation_result_data['total_gaji_per_bulan'] as $usia => $gaji_bulan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usia); ?></td>
                                <td>Rp <?php echo number_format($gaji_bulan, 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($calculation_result_data['total_gaji_per_tahun'][$usia], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($calculation_result_data['present_value_future_benefit'][$usia], 2, ',', '.') . ' '; ?></td>
                                <td>Rp <?php echo number_format($calculation_result_data['kewajiban_aktuaria'][$usia], 2, ',', '.') . ' '; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php elseif ($type == 'asji' && is_array($calculation_result_data['detail_tahunan'])): ?>
                        <?php foreach ($calculation_result_data['detail_tahunan'] as $usia => $detail): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usia); ?></td>
                                <td><?php echo number_format($detail['lx'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($detail['dx'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($detail['cx'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($detail['mx'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Data perhitungan tahunan tidak tersedia.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>