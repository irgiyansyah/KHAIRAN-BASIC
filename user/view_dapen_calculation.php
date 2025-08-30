<?php
include_once 'includes/header.php';
include_once '../includes/calculation_logic.php';

$dapen_input_id = isset($_GET['input_id']) ? (int)$_GET['input_id'] : 0;
if ($dapen_input_id == 0) {
    redirect('calculation_results.php');
}

$dapen_data = null;
$assumption_data = null;
$calculation_result_data = null;
$user_id = $_SESSION['user_id'];

// Fetch Dapen input data
$stmt_dapen = $conn->prepare("SELECT * FROM dapen_inputs WHERE id = ? AND user_id = ?");
$stmt_dapen->bind_param("ii", $dapen_input_id, $user_id);
$stmt_dapen->execute();
$result_dapen = $stmt_dapen->get_result();

if ($result_dapen->num_rows > 0) {
    $dapen_data = $result_dapen->fetch_assoc();
} else {
    redirect('calculation_results.php');
}
$stmt_dapen->close();

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
    echo "<div class='alert alert-warning'>
              Admin belum menetapkan asumsi untuk akun Anda. 
              Hasil perhitungan belum dapat ditampilkan.
          </div>";
    include_once 'includes/footer.php';
    exit();
}

// =========================================================================
// PENTING: KODE MEMILIH FUNGSI PERHITUNGAN SESUAI METODE
// =========================================================================
$metode = $assumption_data['metode_perhitungan'];
$results = [];

switch ($metode) {
    case 'EAN':
        $results = calculate_dapen_EAN($dapen_data, $assumption_data, $conn);
        break;
    case 'AAN':
        $results = calculate_dapen_AAN($dapen_data, $assumption_data, $conn);
        break;
    case 'PUC':
        $results = calculate_dapen_PUC($dapen_data, $assumption_data, $conn);
        break;
    case 'ILP':
        $results = calculate_dapen_ILP($dapen_data, $assumption_data, $conn);
        break;
    default:
        echo "<div class='alert alert-danger'>Metode perhitungan tidak valid.</div>";
        include_once 'includes/footer.php';
        exit();
}

// Periksa apakah hasil perhitungan sudah ada di database
$stmt_check_calc = $conn->prepare(
    "SELECT * FROM calculation_results 
     WHERE input_type = 'dapen' AND input_id = ?"
);
$stmt_check_calc->bind_param("i", $dapen_input_id);
$stmt_check_calc->execute();
$result_check_calc = $stmt_check_calc->get_result();

if ($result_check_calc->num_rows > 0) {
    $calculation_result_data = $result_check_calc->fetch_assoc();

    // Decode JSON strings back to arrays
    $calculation_result_data['total_gaji_per_bulan']         = json_decode($calculation_result_data['total_gaji_per_bulan'], true);
    $calculation_result_data['total_gaji_per_tahun']         = json_decode($calculation_result_data['total_gaji_per_tahun'], true);
    $calculation_result_data['present_value_future_benefit'] = json_decode($calculation_result_data['present_value_future_benefit'], true);
    $calculation_result_data['kewajiban_aktuaria']           = json_decode($calculation_result_data['kewajiban_aktuaria'], true);

} else {
    // Jika tidak, lakukan perhitungan dan simpan
    $total_gaji_per_bulan_json          = json_encode($results['total_gaji_per_bulan']);
    $total_gaji_per_tahun_json          = json_encode($results['total_gaji_per_tahun']);
    $pvfb_json                          = json_encode($results['present_value_future_benefit']);
    $kewajiban_aktuaria_json            = json_encode($results['kewajiban_aktuaria']);

    // Store results in calculation_results table
    $stmt_insert_results = $conn->prepare(
        "INSERT INTO calculation_results 
         (input_type, input_id, total_gaji_per_bulan, total_gaji_per_tahun, 
          present_value_future_benefit, iuran_normal_bulan, iuran_normal_tahun, 
          iuran_normal_total, kewajiban_aktuaria, besar_manfaat_pensiun_persen_val, 
          besar_manfaat_pensiun_100_val) 
          VALUES ('dapen', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt_insert_results->bind_param(
        "isssdddsdd",
        $dapen_data['id'],
        $total_gaji_per_bulan_json,
        $total_gaji_per_tahun_json,
        $pvfb_json,
        $results['iuran_normal_bulanan'],
        $results['iuran_normal_tahunan'],
        $results['iuran_normal_total'],
        $kewajiban_aktuaria_json,
        $results['besar_manfaat_pensiun_persen_val'],
        $results['besar_manfaat_pensiun_100_val']
    );

    $stmt_insert_results->execute();
    $stmt_insert_results->close();
    
    // Ambil kembali data dari database untuk memastikan formatnya sama
    $stmt_check_calc->execute();
    $calculation_result_data = $stmt_check_calc->get_result()->fetch_assoc();

    $calculation_result_data['total_gaji_per_bulan']         = json_decode($calculation_result_data['total_gaji_per_bulan'], true);
    $calculation_result_data['total_gaji_per_tahun']         = json_decode($calculation_result_data['total_gaji_per_tahun'], true);
    $calculation_result_data['present_value_future_benefit'] = json_decode($calculation_result_data['present_value_future_benefit'], true);
    $calculation_result_data['kewajiban_aktuaria']           = json_decode($calculation_result_data['kewajiban_aktuaria'], true);
}
$stmt_check_calc->close();
?>

<h1 class="mb-4 display-5 fw-bold text-primary">
    Hasil Perhitungan Dana Pensiun (<?php echo htmlspecialchars($metode); ?>)
</h1>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">
        Detail Pengajuan #<?php echo $dapen_data['id']; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul>
                    <li><strong>Gaji Awal per Bulan:</strong> Rp <?php echo number_format($dapen_data['gaji_awal_per_bulan'], 0, ',', '.'); ?></li>
                    <li><strong>Persentase Kenaikan Gaji:</strong> <?php echo $dapen_data['persentase_kenaikan_gaji']; ?>%</li>
                    <li><strong>Tahun Kerja:</strong> <?php echo $dapen_data['tahun_pertama_kerja']; ?> - <?php echo $dapen_data['tahun_saat_ini']; ?></li>
                    <li><strong>Usia Kerja Pertama:</strong> <?php echo $dapen_data['usia_kerja_pertama']; ?> tahun</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul>
                    <li><strong>Usia Saat Ini:</strong> <?php echo $dapen_data['usia_saat_ini']; ?> tahun</li>
                    <li><strong>Usia Pensiun:</strong> <?php echo $dapen_data['usia_pensiun']; ?> tahun</li>
                    <li><strong>Metode Pembayaran:</strong> <?php echo $dapen_data['metode_pembayaran']; ?></li>
                    <li><strong>Frekuensi Pembayaran:</strong> <?php echo $dapen_data['jumlah_pembayaran_setahun']; ?> kali/tahun</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold">Asumsi yang Digunakan Admin</div>
    <div class="card-body">
        <ul>
            <li><strong>Tabel Mortalita Usia Kerja:</strong> <?php echo $assumption_data['mortalita_usia_kerja']; ?></li>
            <li><strong>Tabel Mortalita Usia Pensiun:</strong> <?php echo $assumption_data['mortalita_usia_pensiun']; ?></li>
            <li><strong>Metode Perhitungan:</strong> <?php echo $assumption_data['metode_perhitungan']; ?></li>
            <li><strong>Penentuan:</strong> <?php echo number_format($assumption_data['l0'], 0, ',', '.'); ?></li>
            <li><strong>Tingkat Suku Bunga:</strong> <?php echo ($assumption_data['tingkat_suku_bunga'] * 100); ?>%</li>
            <li><strong>Proporsi Manfaat Pensiun:</strong> <?php echo $assumption_data['proporsi_manfaat_pensiun']; ?>%</li>
            <li><strong>Besar Manfaat Pensiun:</strong> <?php echo $assumption_data['besar_manfaat_pensiun_persen']; ?>%</li>
        </ul>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white fw-bold">Ringkasan Hasil Perhitungan</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p>
                    <strong>Besar Manfaat Pensiun (berdasarkan <?php echo $assumption_data['besar_manfaat_pensiun_persen']; ?>% dari gaji terakhir):</strong> <br>
                    <span class="fs-4 text-success">Rp <?php echo number_format($calculation_result_data['besar_manfaat_pensiun_persen_val'], 0, ',', '.'); ?></span>
                </p>
            </div>
            <div class="col-md-6">
                <p>
                    <strong>Besar Manfaat Pensiun (100% dari gaji terakhir):</strong> <br>
                    <span class="fs-4 text-success">Rp <?php echo number_format($calculation_result_data['besar_manfaat_pensiun_100_val'], 0, ',', '.'); ?></span>
                </p>
            </div>
        </div>
        <hr>
        <p><strong>Total Iuran Normal (per Bulan):</strong> Rp <?php echo number_format($calculation_result_data['iuran_normal_bulan'], 0, ',', '.'); ?></p>
        <p><strong>Total Iuran Normal (per Tahun):</strong> Rp <?php echo number_format($calculation_result_data['iuran_normal_tahun'], 0, ',', '.'); ?></p>
        <p><strong>Total Iuran Normal (Total Akumulasi):</strong> Rp <?php echo number_format($calculation_result_data['iuran_normal_total'], 0, ',', '.'); ?></p>
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
                        <th>Gaji per Bulan</th>
                        <th>Gaji per Tahun</th>
                        <th>PV Future Benefit</th>
                        <th>Kewajiban Aktuaria</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Pastikan $calculation_result_data['total_gaji_per_bulan'] adalah array sebelum mengiterasi
                    if (!empty($calculation_result_data['total_gaji_per_bulan']) && is_array($calculation_result_data['total_gaji_per_bulan'])) {
                        foreach ($calculation_result_data['total_gaji_per_bulan'] as $usia => $gaji_bulan) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($usia) . "</td>";
                            echo "<td>Rp " . number_format($gaji_bulan, 0, ',', '.') . "</td>";
                            echo "<td>Rp " . number_format($calculation_result_data['total_gaji_per_tahun'][$usia], 0, ',', '.') . "</td>";
                            echo "<td>Rp " . number_format($calculation_result_data['present_value_future_benefit'][$usia], 2, ',', '.') . "</td>";
                            echo "<td>Rp " . number_format($calculation_result_data['kewajiban_aktuaria'][$usia], 2, ',', '.') . "</td>";
                            echo "</tr>";
                        }
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