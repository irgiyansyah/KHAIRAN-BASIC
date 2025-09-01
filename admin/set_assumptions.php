<?php
include_once 'includes/header.php';
include_once '../includes/calculation_logic.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($user_id == 0) {
    redirect('manage_users.php');
}

$user_info = null;
$assumption_data = null;
$dapen_inputs = [];
$asji_inputs = [];
$mortality_tables_list = [
    "tm_gam71_pria" => "TM GAM 71 Pria",
    "tm_gam71_wanita" => "TM GAM 71 Wanita",
    "tm_jkn2023_pria" => "TM JKN 2023 Laki-Laki",
    "tm_jkn2023_wanita" => "TM JKN 2023 Perempuan",
    "tm_tmi4_2019_pria" => "TMI IV 2019 Laki-Laki",
    "tm_tmi4_2019_wanita" => "TMI IV 2019 Perempuan",
    "tm_taspen_2012" => "TM Taspen 2012 (Gabungan)",
    "tm_amt_49_pria" => "TM AMT 49 Pria",
    "tm_amt_49_wanita" => "TM AMT 49 Wanita",
    "tm_gam_83_pria" => "TM GAM 83 Pria",
    "tm_gam_83_wanita" => "TM GAM 83 Wanita",
    "tm_cso_58_pria" => "TM CSO 58 Pria",
    "tm_cso_58_wanita" => "TM CSO 58 Wanita",
    "tm_cso_80_pria" => "TM CSO 80 Pria",
    "tm_cso_80_wanita" => "TM CSO 80 Wanita",
    "tm_tmi3_pria" => "TMI III Pria",
    "tm_tmi3_wanita" => "TMI III Wanita",
    "tm_tmi99_pria" => "TMI 99 Pria",
    "tm_tmi99_wanita" => "TMI 99 Wanita",
];
$methods_list = ['EAN', 'AAN', 'PUC', 'ILP'];

// Fetch user info
$stmt_user = $conn->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'user'");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($result_user->num_rows > 0) {
    $user_info = $result_user->fetch_assoc();
} else {
    redirect('manage_users.php');
}
$stmt_user->close();

// Proses penghapusan pengajuan
$delete_message = "";
if (isset($_GET['delete_dapen_id'])) {
    $id = (int)$_GET['delete_dapen_id'];
    $stmt = $conn->prepare("DELETE FROM dapen_inputs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        $delete_message = "<div class='alert alert-success'>Pengajuan Dapen berhasil dihapus.</div>";
    } else {
        $delete_message = "<div class='alert alert-danger'>Gagal menghapus pengajuan: " . $conn->error . "</div>";
    }
    $stmt->close();
} elseif (isset($_GET['delete_asji_id'])) {
    $id = (int)$_GET['delete_asji_id'];
    $stmt = $conn->prepare("DELETE FROM asji_inputs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        $delete_message = "<div class='alert alert-success'>Pengajuan Asuransi Jiwa berhasil dihapus.</div>";
    } else {
        $delete_message = "<div class='alert alert-danger'>Gagal menghapus pengajuan: " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Fetch existing assumptions for this user
$stmt_assumption = $conn->prepare("SELECT * FROM assumptions WHERE user_id = ?");
$stmt_assumption->bind_param("i", $user_id);
$stmt_assumption->execute();
$result_assumption = $stmt_assumption->get_result();
if ($result_assumption->num_rows > 0) {
    $assumption_data = $result_assumption->fetch_assoc();
}
$stmt_assumption->close();

// Proses form submission
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mortalita_usia_kerja = sanitize_input($_POST['mortalita_usia_kerja']);
    $mortalita_usia_pensiun = sanitize_input($_POST['mortalita_usia_pensiun']);
    $metode_perhitungan = sanitize_input($_POST['metode_perhitungan']);
    $l0 = (int)sanitize_input($_POST['l0']);
    $tingkat_suku_bunga_persen = (float)sanitize_input($_POST['tingkat_suku_bunga']);
    $proporsi_manfaat_pensiun = (float)sanitize_input($_POST['proporsi_manfaat_pensiun']);
    $besar_manfaat_pensiun_persen = (float)sanitize_input($_POST['besar_manfaat_pensiun_persen']);
    
    $tingkat_suku_bunga = $tingkat_suku_bunga_persen / 100;

    if (empty($mortalita_usia_kerja) || empty($mortalita_usia_pensiun) || empty($metode_perhitungan) || empty($l0)) {
        $error_message = "Validasi input gagal. Semua kolom pilihan harus diisi.";
    } elseif (!array_key_exists($mortalita_usia_kerja, $mortality_tables_list) || !array_key_exists($mortalita_usia_pensiun, $mortality_tables_list)) {
        $error_message = "Tabel mortalita tidak valid.";
    } elseif (!in_array($metode_perhitungan, $methods_list)) {
        $error_message = "Metode perhitungan tidak valid.";
    } elseif (($l0 != 10000 && $l0 != 100000)) {
        $error_message = "Nilai l0 tidak valid.";
    } elseif ($tingkat_suku_bunga <= 0 || $tingkat_suku_bunga > 1) {
        $error_message = "Tingkat Suku Bunga harus lebih besar dari 0 dan tidak lebih dari 100%.";
    } elseif ($proporsi_manfaat_pensiun < 0 || $proporsi_manfaat_pensiun > 100) {
        $error_message = "Persentase Proporsi Manfaat Pensiun harus antara 0 dan 100.";
    } elseif ($besar_manfaat_pensiun_persen < 0 || $besar_manfaat_pensiun_persen > 100) {
        $error_message = "Persentase Besar Manfaat Pensiun harus antara 0 dan 100.";
    } else {
        if ($assumption_data) {
            $stmt = $conn->prepare("UPDATE assumptions SET mortalita_usia_kerja=?, mortalita_usia_pensiun=?, metode_perhitungan=?, l0=?, tingkat_suku_bunga=?, proporsi_manfaat_pensiun=?, besar_manfaat_pensiun_persen=?, admin_id=? WHERE user_id=?");
            $stmt->bind_param("sssidddii", $mortalita_usia_kerja, $mortalita_usia_pensiun, $metode_perhitungan, $l0, $tingkat_suku_bunga, $proporsi_manfaat_pensiun, $besar_manfaat_pensiun_persen, $_SESSION['user_id'], $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO assumptions (user_id, mortalita_usia_kerja, mortalita_usia_pensiun, metode_perhitungan, l0, tingkat_suku_bunga, proporsi_manfaat_pensiun, besar_manfaat_pensiun_persen, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssidddi", $user_id, $mortalita_usia_kerja, $mortalita_usia_pensiun, $metode_perhitungan, $l0, $tingkat_suku_bunga, $proporsi_manfaat_pensiun, $besar_manfaat_pensiun_persen, $_SESSION['user_id']);
        }

        if ($stmt->execute()) {
            $success_message = "Asumsi berhasil ditetapkan/diperbarui untuk " . htmlspecialchars($user_info['username']) . ".";
            $stmt_assumption = $conn->prepare("SELECT * FROM assumptions WHERE user_id = ?");
            $stmt_assumption->bind_param("i", $user_id);
            $stmt_assumption->execute();
            $result_assumption = $stmt_assumption->get_result();
            if ($result_assumption->num_rows > 0) {
                $assumption_data = $result_assumption->fetch_assoc();
            }
            $stmt_assumption->close();

            $conn->query("UPDATE dapen_inputs SET has_assumption = 1 WHERE user_id = $user_id");
            $conn->query("UPDATE asji_inputs SET has_assumption = 1 WHERE user_id = $user_id");
        } else {
            $error_message = "Gagal menyimpan asumsi: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch Dapen inputs from this user
$stmt_dapen = $conn->prepare("SELECT * FROM dapen_inputs WHERE user_id = ? ORDER BY created_at DESC");
$stmt_dapen->bind_param("i", $user_id);
$stmt_dapen->execute();
$dapen_inputs = $stmt_dapen->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_dapen->close();

// Fetch Asji inputs from this user
$stmt_asji = $conn->prepare("SELECT * FROM asji_inputs WHERE user_id = ? ORDER BY created_at DESC");
$stmt_asji->bind_param("i", $user_id);
$stmt_asji->execute();
$asji_inputs = $stmt_asji->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_asji->close();
?>

<h1 class="mb-4 display-5 fw-bold text-primary">Atur Asumsi untuk User: <?php echo htmlspecialchars($user_info['username']); ?></h1>

<?php echo $delete_message; ?>
<?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold">Penetapan Asumsi Aktuaria</div>
    <div class="card-body">
        <form action="set_assumptions.php?user_id=<?php echo $user_id; ?>" method="POST">
            <div class="mb-3">
                <label for="mortalita_usia_kerja" class="form-label">Tabel Mortalita Usia Kerja</label>
                <select class="form-select" id="mortalita_usia_kerja" name="mortalita_usia_kerja" required>
                    <option value="">Pilih Tabel</option>
                    <?php foreach ($mortality_tables_list as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($assumption_data && $assumption_data['mortalita_usia_kerja'] == $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="mortalita_usia_pensiun" class="form-label">Tabel Mortalita Usia Pensiun</label>
                <select class="form-select" id="mortalita_usia_pensiun" name="mortalita_usia_pensiun" required>
                    <option value="">Pilih Tabel</option>
                    <?php foreach ($mortality_tables_list as $key => $value): ?>
                        <option value="<?php echo $key; ?>" <?php echo ($assumption_data && $assumption_data['mortalita_usia_pensiun'] == $key) ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="metode_perhitungan" class="form-label">Metode Perhitungan Dapen/Asji</label>
                <select class="form-select" id="metode_perhitungan" name="metode_perhitungan" required>
                    <option value="">Pilih Metode</option>
                    <?php foreach ($methods_list as $method): ?>
                        <option value="<?php echo $method; ?>" <?php echo ($assumption_data && $assumption_data['metode_perhitungan'] == $method) ? 'selected' : ''; ?>>
                            <?php echo $method; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="l0" class="form-label">Penentuan</label>
                <select class="form-select" id="l0" name="l0" required>
                    <option value="">Pilih Nilai</option>
                    <option value="10000" <?php echo ($assumption_data && $assumption_data['l0'] == 10000) ? 'selected' : ''; ?>>10.000</option>
                    <option value="100000" <?php echo ($assumption_data && $assumption_data['l0'] == 100000) ? 'selected' : ''; ?>>100.000</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tingkat_suku_bunga" class="form-label">Tingkat Suku Bunga per Tahun (%)</label>
                <input type="number" step="0.0001" min="0" max="100" class="form-control" id="tingkat_suku_bunga" name="tingkat_suku_bunga" value="<?php echo ($assumption_data ? ($assumption_data['tingkat_suku_bunga'] * 100) : ''); ?>" placeholder="Contoh: 5 untuk 5%" required>
                <div class="form-text">Masukkan dalam persentase (misal: 5 untuk 5%, 3.5 untuk 3.5%). Akan dikonversi menjadi desimal saat disimpan.</div>
            </div>
            <div class="mb-3">
                <label for="proporsi_manfaat_pensiun" class="form-label">Persentase Proporsi Manfaat Pensiun (%)</label>
                <input type="number" step="0.01" min="0" max="100" class="form-control" id="proporsi_manfaat_pensiun" name="proporsi_manfaat_pensiun" value="<?php echo ($assumption_data ? $assumption_data['proporsi_manfaat_pensiun'] : ''); ?>" placeholder="Contoh: 70" required>
            </div>
            <div class="mb-3">
                <label for="besar_manfaat_pensiun_persen" class="form-label">Persentase Besar Manfaat Pensiun (%)</label>
                <input type="number" step="0.01" min="0" max="100" class="form-control" id="besar_manfaat_pensiun_persen" name="besar_manfaat_pensiun_persen" value="<?php echo ($assumption_data ? $assumption_data['besar_manfaat_pensiun_persen'] : ''); ?>" placeholder="Contoh: 80" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Asumsi</button>
        </form>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white fw-bold">Pengajuan Dapen dari <?php echo htmlspecialchars($user_info['username']); ?></div>
    <div class="card-body">
        <?php if (!empty($dapen_inputs)): ?>
            <div class="accordion" id="accordionDapen">
                <?php foreach ($dapen_inputs as $index => $input): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingDapen<?php echo $index; ?>">
                            <button class="accordion-button <?php echo ($input['has_assumption'] ? '' : 'bg-warning text-dark'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDapen<?php echo $index; ?>" aria-expanded="false" aria-controls="collapseDapen<?php echo $index; ?>">
                                Pengajuan Dapen #<?php echo ($index + 1); ?> - Tanggal: <?php echo date('d M Y H:i', strtotime($input['created_at'])); ?>
                                <?php if (!$input['has_assumption']): ?>
                                    <span class="badge bg-danger ms-2">BELUM ADA ASUMSI</span>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseDapen<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="headingDapen<?php echo $index; ?>" data-bs-parent="#accordionDapen">
                            <div class="accordion-body">
                                <h5>Detail Input:</h5>
                                <ul>
                                    <li>Gaji Awal per Bulan: Rp <?php echo number_format($input['gaji_awal_per_bulan'], 0, ',', '.'); ?></li>
                                    <li>Persentase Kenaikan Gaji: <?php echo $input['persentase_kenaikan_gaji']; ?>%</li>
                                    <li>Tahun Pertama Kerja: <?php echo $input['tahun_pertama_kerja']; ?></li>
                                    <li>Tahun Saat Ini: <?php echo $input['tahun_saat_ini']; ?></li>
                                    <li>Usia Kerja Pertama Kali: <?php echo $input['usia_kerja_pertama']; ?></li>
                                    <li>Usia Saat Ini: <?php echo $input['usia_saat_ini']; ?></li>
                                    <li>Usia Pensiun: <?php echo $input['usia_pensiun']; ?></li>
                                    <li>Metode Pembayaran: <?php echo $input['metode_pembayaran']; ?></li>
                                    <li>Jumlah Pembayaran dalam 1 Tahun: <?php echo $input['jumlah_pembayaran_setahun']; ?> kali</li>
                                </ul>
                                <?php if ($assumption_data): ?>
                                    <a href="view_calculation.php?type=dapen&input_id=<?php echo $input['id']; ?>" class="btn btn-sm btn-primary">Lihat Hasil Perhitungan</a>
                                <?php else: ?>
                                    <span class="text-danger">Belum dapat melihat hasil karena asumsi belum ditetapkan.</span>
                                <?php endif; ?>
                                <a href="#" onclick="confirmDeleteDapen(<?php echo $input['id']; ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>User ini belum mengajukan simulasi Dana Pensiun.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white fw-bold">Pengajuan Asuransi Jiwa dari <?php echo htmlspecialchars($user_info['username']); ?></div>
    <div class="card-body">
        <?php if (!empty($asji_inputs)): ?>
            <div class="accordion" id="accordionAsji">
                <?php foreach ($asji_inputs as $index => $input): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingAsji<?php echo $index; ?>">
                            <button class="accordion-button <?php echo ($input['has_assumption'] ? '' : 'bg-warning text-dark'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAsji<?php echo $index; ?>" aria-expanded="false" aria-controls="collapseAsji<?php echo $index; ?>">
                                Pengajuan Asji #<?php echo ($index + 1); ?> - Tanggal: <?php echo date('d M Y H:i', strtotime($input['created_at'])); ?>
                                <?php if (!$input['has_assumption']): ?>
                                    <span class="badge bg-danger ms-2">BELUM ADA ASUMSI</span>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseAsji<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="headingAsji<?php echo $index; ?>" data-bs-parent="#accordionAsji">
                            <div class="accordion-body">
                                <h5>Detail Input:</h5>
                                <ul>
                                    <li>Jenis Asuransi: <?php echo htmlspecialchars($input['jenis_asji']); ?></li>
                                    <li>Usia Saat Ini: <?php echo htmlspecialchars($input['usia_saat_ini']); ?> tahun</li>
                                    <?php if ($input['jangka_waktu'] !== NULL): ?>
                                        <li>Jangka Waktu Pertanggungan: <?php echo htmlspecialchars($input['jangka_waktu']); ?> tahun</li>
                                    <?php endif; ?>
                                    <li>Besar Santunan: Rp <?php echo number_format($input['besar_santunan'], 0, ',', '.'); ?></li>
                                    <li>Pembayaran: <?php echo htmlspecialchars($input['metode_pembayaran']); ?></li>
                                    <li>Frekuensi Pembayaran: <?php echo htmlspecialchars($input['jumlah_pembayaran_setahun']); ?> kali/tahun</li>
                                </ul>
                                <?php if ($assumption_data): ?>
                                    <a href="view_calculation.php?type=asji&input_id=<?php echo $input['id']; ?>" class="btn btn-sm btn-primary">Lihat Hasil Perhitungan</a>
                                <?php else: ?>
                                    <span class="text-danger">Belum dapat melihat hasil karena asumsi belum ditetapkan.</span>
                                <?php endif; ?>
                                <a href="#" onclick="confirmDeleteAsji(<?php echo $input['id']; ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>User ini belum mengajukan simulasi Asuransi Jiwa.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDeleteDapen(id) {
    if (confirm("Apakah Anda yakin ingin menghapus pengajuan Dana Pensiun ini?")) {
        window.location.href = 'set_assumptions.php?user_id=<?php echo $user_id; ?>&delete_dapen_id=' + id;
    }
}
function confirmDeleteAsji(id) {
    if (confirm("Apakah Anda yakin ingin menghapus pengajuan Asuransi Jiwa ini?")) {
        window.location.href = 'set_assumptions.php?user_id=<?php echo $user_id; ?>&delete_asji_id=' + id;
    }
}
</script>

<?php include_once 'includes/footer.php'; ?>
