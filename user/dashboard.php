<?php include_once 'includes/header.php'; ?>

<h1 class="mb-4 display-5 fw-bold text-primary">Dashboard User</h1>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card dashboard-card border-primary shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-calculator fa-3x text-primary"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-uppercase text-primary mb-1">Mulai Simulasi Dana Pensiun</h5>
                        <p class="card-text text-muted">Hitung proyeksi dana pensiun Anda.</p>
                        <a href="dapen_form.php" class="btn btn-primary btn-sm">Mulai Sekarang <i class="fas fa-arrow-circle-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card dashboard-card border-success shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-heart fa-3x text-success"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-uppercase text-success mb-1">Mulai Simulasi Asuransi Jiwa</h5>
                        <p class="card-text text-muted">Estimasi kebutuhan asuransi jiwa Anda.</p>
                        <a href="asji_form.php" class="btn btn-success btn-sm">Mulai Sekarang <i class="fas fa-arrow-circle-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-info text-white fw-bold">Riwayat Pengajuan & Hasil</div>
    <div class="card-body">
        <p class="text-muted">Lihat semua pengajuan simulasi Anda dan hasil perhitungannya di sini.</p>
        <a href="calculation_results.php" class="btn btn-outline-info">Lihat Riwayat <i class="fas fa-history ms-1"></i></a>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>