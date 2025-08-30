<?php
include_once 'includes/header.php';

// Fetch user's submitted Dapen inputs
$user_dapen_inputs = [];
$stmt_dapen_user = $conn->prepare("SELECT di.*, a.id AS assumption_exists FROM dapen_inputs di LEFT JOIN assumptions a ON di.user_id = a.user_id WHERE di.user_id = ? ORDER BY di.created_at DESC");
$stmt_dapen_user->bind_param("i", $_SESSION['user_id']);
$stmt_dapen_user->execute();
$user_dapen_inputs = $stmt_dapen_user->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_dapen_user->close();

// Fetch user's submitted Asji inputs
$user_asji_inputs = [];
$stmt_asji_user = $conn->prepare("SELECT ai.*, a.id AS assumption_exists FROM asji_inputs ai LEFT JOIN assumptions a ON ai.user_id = a.user_id WHERE ai.user_id = ? ORDER BY ai.created_at DESC");
$stmt_asji_user->bind_param("i", $_SESSION['user_id']);
$stmt_asji_user->execute();
$user_asji_inputs = $stmt_asji_user->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_asji_user->close();
?>

<h1 class="mb-4 display-5 fw-bold text-primary">Hasil Perhitungan Simulasi</h1>

<div class="alert alert-info" role="alert">
    Hasil perhitungan Anda akan muncul di sini setelah Admin menetapkan asumsi untuk pengajuan Anda.
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">Riwayat Pengajuan Dana Pensiun</div>
    <div class="card-body">
        <?php if (!empty($user_dapen_inputs)): ?>
            <div class="accordion" id="accordionUserDapen">
                <?php foreach ($user_dapen_inputs as $index => $input): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingUserDapen<?php echo $index; ?>">
                            <button class="accordion-button <?php echo ($input['assumption_exists'] ? '' : 'bg-warning text-dark'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUserDapen<?php echo $index; ?>" aria-expanded="false" aria-controls="collapseUserDapen<?php echo $index; ?>">
                                Pengajuan Dapen #<?php echo ($index + 1); ?> - Tanggal: <?php echo date('d M Y H:i', strtotime($input['created_at'])); ?>
                                <?php if (!$input['assumption_exists']): ?>
                                    <span class="badge bg-danger ms-2">Menunggu Asumsi Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-2">Siap Dihitung</span>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseUserDapen<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="headingUserDapen<?php echo $index; ?>" data-bs-parent="#accordionUserDapen">
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
                                <?php if ($input['assumption_exists']): ?>
                                    <a href="view_dapen_calculation.php?input_id=<?php echo $input['id']; ?>" class="btn btn-sm btn-primary">Lihat Hasil Perhitungan</a>
                                <?php else: ?>
                                    <span class="text-danger">Hasil akan tersedia setelah admin menetapkan asumsi.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Anda belum mengajukan simulasi Dana Pensiun.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-success text-white fw-bold">Riwayat Pengajuan Asuransi Jiwa</div>
    <div class="card-body">
        <?php if (!empty($user_asji_inputs)): ?>
            <div class="accordion" id="accordionUserAsji">
                <?php foreach ($user_asji_inputs as $index => $input): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingUserAsji<?php echo $index; ?>">
                            <button class="accordion-button <?php echo ($input['assumption_exists'] ? '' : 'bg-warning text-dark'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUserAsji<?php echo $index; ?>" aria-expanded="false" aria-controls="collapseAsji<?php echo $index; ?>">
                                Pengajuan Asji #<?php echo ($index + 1); ?> - Tanggal: <?php echo date('d M Y H:i', strtotime($input['created_at'])); ?>
                                <?php if (!$input['assumption_exists']): ?>
                                    <span class="badge bg-danger ms-2">Menunggu Asumsi Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-2">Siap Dihitung</span>
                                <?php endif; ?>
                            </button>
                        </h2>
                        <div id="collapseUserAsji<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="headingUserAsji<?php echo $index; ?>" data-bs-parent="#accordionUserAsji">
                            <div class="accordion-body">
                                <h5>Detail Input:</h5>
                                <ul>
                                    <li>Jenis Asuransi: <?php echo htmlspecialchars($input['jenis_asji']); ?></li>
                                    <li>Usia Saat Ini: <?php echo htmlspecialchars($input['usia_saat_ini']); ?> tahun</li>
                                    <li>Jangka Waktu: <?php echo htmlspecialchars($input['jangka_waktu'] ?? 'Seumur Hidup'); ?></li>
                                    <li>Besar Santunan: Rp <?php echo number_format($input['besar_santunan'], 0, ',', '.'); ?></li>
                                    <li>Metode Pembayaran: <?php echo htmlspecialchars($input['metode_pembayaran']); ?></li>
                                    <li>Frekuensi Pembayaran: <?php echo htmlspecialchars($input['jumlah_pembayaran_setahun']); ?> kali/tahun</li>
                                </ul>
                                <?php if ($input['assumption_exists']): ?>
                                    <a href="view_asji_calculation.php?input_id=<?php echo $input['id']; ?>" class="btn btn-sm btn-success">Lihat Hasil Perhitungan</a>
                                <?php else: ?>
                                    <span class="text-danger">Hasil akan tersedia setelah admin menetapkan asumsi.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Anda belum mengajukan simulasi Asuransi Jiwa.</p>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>