<?php
include_once 'includes/header.php';

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jenis_asji = sanitize_input($_POST['jenis_asji']);
    $usia_saat_ini = (int)sanitize_input($_POST['usia_saat_ini']);
    $jangka_waktu = (isset($_POST['jangka_waktu']) && $_POST['jangka_waktu'] != '') ? (int)sanitize_input($_POST['jangka_waktu']) : NULL;
    $besar_santunan = (float)str_replace(['.', ','], '', $_POST['besar_santunan']);
    $metode_pembayaran = sanitize_input($_POST['metode_pembayaran']);
    $jumlah_pembayaran_setahun = (int)sanitize_input($_POST['jumlah_pembayaran_setahun']);

    $user_id = $_SESSION['user_id'];

    // Basic validation
    if (empty($jenis_asji) || $usia_saat_ini <= 0 || $besar_santunan <= 0 || empty($metode_pembayaran) || $jumlah_pembayaran_setahun <= 0) {
        $error_message = "Validasi input gagal. Mohon periksa kembali semua kolom.";
    } elseif (($jenis_asji == 'Berjangka' || $jenis_asji == 'Dwiguna') && ($jangka_waktu <= 0 || $jangka_waktu > (100 - $usia_saat_ini))) {
        $error_message = "Validasi input gagal. Jangka waktu tidak valid.";
    } else {
        $stmt = $conn->prepare("INSERT INTO asji_inputs (user_id, jenis_asji, usia_saat_ini, jangka_waktu, besar_santunan, metode_pembayaran, jumlah_pembayaran_setahun) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isidisi", $user_id, $jenis_asji, $usia_saat_ini, $jangka_waktu, $besar_santunan, $metode_pembayaran, $jumlah_pembayaran_setahun);

        if ($stmt->execute()) {
            $success_message = "Data Asuransi Jiwa berhasil diajukan! Admin akan segera menetapkan asumsi dan hasilnya akan tersedia.";
        } else {
            $error_message = "Gagal menyimpan pengajuan: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<h1 class="mb-4 display-5 fw-bold text-success">Formulir Simulasi Asuransi Jiwa</h1>

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
    <div class="card-header bg-success text-white fw-bold">Pilih Produk & Input Data</div>
    <div class="card-body">
        <form action="asji_form.php" method="POST">
            <div class="mb-3">
                <label for="jenis_asji" class="form-label">Jenis Asuransi Jiwa</label>
                <select class="form-select" id="jenis_asji" name="jenis_asji" required>
                    <option value="">Pilih</option>
                    <option value="Seumur Hidup">Asuransi Jiwa Seumur Hidup</option>
                    <option value="Berjangka">Asuransi Jiwa Berjangka</option>
                    <option value="Dwiguna">Asuransi Jiwa Dwiguna</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="usia_saat_ini" class="form-label">Usia Saat Ini (tahun)</label>
                <input type="number" min="0" max="120" class="form-control" id="usia_saat_ini" name="usia_saat_ini" placeholder="Cth: 30" required>
            </div>
            <div class="mb-3" id="jangka-waktu-group" style="display: none;">
                <label for="jangka_waktu" class="form-label">Jangka Waktu Pertanggungan (tahun)</label>
                <input type="number" min="1" max="100" class="form-control" id="jangka_waktu" name="jangka_waktu" placeholder="Cth: 20">
            </div>
            <div class="mb-3">
                <label for="besar_santunan" class="form-label">Besar Santunan yang Diharapkan (Rp)</label>
                <input type="text" class="form-control" id="besar_santunan" name="besar_santunan" placeholder="Cth: 500.000.000" required data-type="currency">
            </div>
            <div class="mb-3">
                <label for="metode_pembayaran" class="form-label">Pembayaran</label>
                <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                    <option value="">Pilih</option>
                    <option value="Awal Periode">Awal Periode</option>
                    <option value="Akhir Periode">Akhir Periode</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="jumlah_pembayaran_setahun" class="form-label">Frekuensi Pembayaran per Tahun</label>
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
            <button type="submit" class="btn btn-success mt-3">Lihat Hasil Perhitungan</button>
        </form>
    </div>
</div>

<script>
    // JavaScript untuk menampilkan/menyembunyikan kolom jangka waktu
    document.addEventListener('DOMContentLoaded', function() {
        const jenisAsji = document.getElementById('jenis_asji');
        const jangkaWaktuGroup = document.getElementById('jangka-waktu-group');
        const jangkaWaktuInput = document.getElementById('jangka_waktu');

        function toggleJangkaWaktu() {
            if (jenisAsji.value === 'Berjangka' || jenisAsji.value === 'Dwiguna') {
                jangkaWaktuGroup.style.display = 'block';
                jangkaWaktuInput.setAttribute('required', 'required');
            } else {
                jangkaWaktuGroup.style.display = 'none';
                jangkaWaktuInput.removeAttribute('required');
            }
        }

        jenisAsji.addEventListener('change', toggleJangkaWaktu);
        toggleJangkaWaktu(); // Panggil saat halaman dimuat untuk kondisi awal

        // JavaScript for currency formatting
        const currencyInput = document.getElementById('besar_santunan');
        currencyInput.addEventListener('keyup', function() {
            let value = this.value.replace(/\./g, '');
            value = value.replace(/[^0-9]/g, '');
            if (value) {
                this.value = parseFloat(value).toLocaleString('id-ID');
            } else {
                this.value = '';
            }
        });
    });
</script>

<?php include_once 'includes/footer.php'; ?>