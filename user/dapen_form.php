<?php
include_once 'includes/header.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gaji_awal_per_bulan = (float)sanitize_input(str_replace('.', '', $_POST['gaji_awal_per_bulan'])); // Remove dot for number format
    $persentase_kenaikan_gaji = (float)sanitize_input($_POST['persentase_kenaikan_gaji']);
    $tahun_pertama_kerja = (int)sanitize_input($_POST['tahun_pertama_kerja']);
    $tahun_saat_ini = (int)sanitize_input($_POST['tahun_saat_ini']);
    $usia_kerja_pertama = (int)sanitize_input($_POST['usia_kerja_pertama']);
    $usia_saat_ini = (int)sanitize_input($_POST['usia_saat_ini']);
    $usia_pensiun = (int)sanitize_input($_POST['usia_pensiun']);
    $metode_pembayaran = sanitize_input($_POST['metode_pembayaran']);
    $jumlah_pembayaran_setahun = (int)sanitize_input($_POST['jumlah_pembayaran_setahun']);

    $user_id = $_SESSION['user_id'];

    // Basic validation
    if ($gaji_awal_per_bulan <= 0 || $persentase_kenaikan_gaji < 0 ||
        $tahun_pertama_kerja <= 0 || $tahun_saat_ini <= 0 || $tahun_saat_ini < $tahun_pertama_kerja ||
        $usia_kerja_pertama <= 0 || $usia_saat_ini <= 0 || $usia_saat_ini < $usia_kerja_pertama ||
        $usia_pensiun <= $usia_saat_ini ||
        !in_array($metode_pembayaran, ['Awal Periode', 'Akhir Periode']) ||
        !in_array($jumlah_pembayaran_setahun, [1, 2, 3, 4, 6, 12])
    ) {
        $error_message = "Validasi input gagal. Mohon periksa kembali semua kolom.";
    } else {
        $stmt = $conn->prepare("INSERT INTO dapen_inputs (user_id, gaji_awal_per_bulan, persentase_kenaikan_gaji, tahun_pertama_kerja, tahun_saat_ini, usia_kerja_pertama, usia_saat_ini, usia_pensiun, metode_pembayaran, jumlah_pembayaran_setahun) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iddddiiiis", $user_id, $gaji_awal_per_bulan, $persentase_kenaikan_gaji, $tahun_pertama_kerja, $tahun_saat_ini, $usia_kerja_pertama, $usia_saat_ini, $usia_pensiun, $metode_pembayaran, $jumlah_pembayaran_setahun);

        if ($stmt->execute()) {
            $success_message = "Data Dapen berhasil diajukan! Admin akan segera menetapkan asumsi dan hasilnya akan tersedia.";
            // Redirect to results or stay and clear form
            // redirect('calculation_results.php?new_input=true&type=dapen'); // Consider redirecting after successful submission
        } else {
            $error_message = "Gagal menyimpan pengajuan: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<h1 class="mb-4 display-5 fw-bold text-primary">Formulir Simulasi Dana Pensiun</h1>

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
    <div class="card-header bg-primary text-white fw-bold">Input Data Perhitungan Dapen</div>
    <div class="card-body">
        <form action="dapen_form.php" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="gaji_awal_per_bulan" class="form-label">Gaji Awal per Bulan (Rp)</label>
                    <input type="text" class="form-control" id="gaji_awal_per_bulan" name="gaji_awal_per_bulan" placeholder="Cth: 5.000.000" required data-type="currency">
                </div>
                <div class="col-md-6">
                    <label for="persentase_kenaikan_gaji" class="form-label">Persentase Kenaikan Gaji per Tahun (%)</label>
                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="persentase_kenaikan_gaji" name="persentase_kenaikan_gaji" placeholder="Cth: 5 (untuk 5%)" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="tahun_pertama_kerja" class="form-label">Tahun Pertama Kali Bekerja</label>
                    <input type="number" min="1900" max="<?php echo date('Y'); ?>" class="form-control" id="tahun_pertama_kerja" name="tahun_pertama_kerja" placeholder="Cth: 2010" required>
                </div>
                <div class="col-md-6">
                    <label for="tahun_saat_ini" class="form-label">Tahun Saat Ini</label>
                    <input type="number" min="1900" max="<?php echo date('Y') + 50; ?>" class="form-control" id="tahun_saat_ini" name="tahun_saat_ini" value="<?php echo date('Y'); ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="usia_kerja_pertama" class="form-label">Usia Kerja Pertama Kali</label>
                    <input type="number" min="15" max="60" class="form-control" id="usia_kerja_pertama" name="usia_kerja_pertama" placeholder="Cth: 22" required>
                </div>
                <div class="col-md-4">
                    <label for="usia_saat_ini" class="form-label">Usia Saat Ini</label>
                    <input type="number" min="15" max="100" class="form-control" id="usia_saat_ini" name="usia_saat_ini" placeholder="Cth: 30" required>
                </div>
                <div class="col-md-4">
                    <label for="usia_pensiun" class="form-label">Usia Pensiun</label>
                    <input type="number" min="40" max="100" class="form-control" id="usia_pensiun" name="usia_pensiun" placeholder="Cth: 60" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">Pilih</option>
                        <option value="Awal Periode">Awal Periode</option>
                        <option value="Akhir Periode">Akhir Periode</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="jumlah_pembayaran_setahun" class="form-label">Jumlah Pembayaran dalam 1 Tahun</label>
                    <select class="form-select" id="jumlah_pembayaran_setahun" name="jumlah_pembayaran_setahun" required>
                        <option value="">Pilih</option>
                        <option value="1">1 kali (Tahunan)</option>
                        <option value="2">2 kali (Semi-Tahunan)</option>
                        <option value="3">3 kali (Tiga Bulanan)</option>
                        <option value="4">4 kali (Kuartalan)</option>
                        <option value="6">6 kali (Dua Bulanan)</option>
                        <option value="12">12 kali (Bulanan)</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Lihat Hasil Perhitungan</button>
        </form>
    </div>
</div>

<script>
    // JavaScript for currency formatting
    document.addEventListener('DOMContentLoaded', function() {
        var currencyInput = document.querySelector('input[data-type="currency"]');

        currencyInput.addEventListener('keyup', function() {
            var value = this.value.replace(/\./g, ''); // Remove existing dots
            value = value.replace(/[^0-9]/g, ''); // Keep only numbers
            if (value) {
                this.value = parseFloat(value).toLocaleString('id-ID'); // Format with dot as thousands separator
            } else {
                this.value = '';
            }
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>