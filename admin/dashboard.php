<?php include_once 'includes/header.php'; ?>

<h1 class="mb-4 display-5 fw-bold text-primary">Dashboard Admin</h1>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card dashboard-card border-primary shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-uppercase text-primary mb-1">Total User Terdaftar</h5>
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM users WHERE role = 'user'");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $data = $result->fetch_assoc();
                        $total_users = $data['total_users'];
                        $stmt->close();
                        ?>
                        <div class="h2 mb-0 fw-bold"><?php echo $total_users; ?></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 pt-0 pb-0">
                <a href="manage_users.php" class="text-primary text-decoration-none small">Lihat Detail <i class="fas fa-arrow-circle-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card dashboard-card border-success shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-file-invoice fa-3x text-success"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-uppercase text-success mb-1">Pengajuan Dapen Baru</h5>
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) AS total_dapen FROM dapen_inputs WHERE has_assumption = 0"); // Asumsi kolom `has_assumption` di tabel `dapen_inputs`
                        // Anda perlu menambahkan kolom `has_assumption` BOOLEAN DEFAULT 0 ke tabel `dapen_inputs`
                        // ALTER TABLE dapen_inputs ADD COLUMN has_assumption BOOLEAN DEFAULT 0;
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $data = $result->fetch_assoc();
                        $total_dapen_new = $data['total_dapen'];
                        $stmt->close();
                        ?>
                        <div class="h2 mb-0 fw-bold"><?php echo $total_dapen_new; ?></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 pt-0 pb-0">
                <a href="manage_users.php" class="text-success text-decoration-none small">Proses Pengajuan <i class="fas fa-arrow-circle-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card dashboard-card border-warning shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-3x text-warning"></i>
                    </div>
                    <div class="col">
                        <h5 class="card-title text-uppercase text-warning mb-1">Pengajuan Asji Baru</h5>
                        <?php
                        $stmt = $conn->prepare("SELECT COUNT(*) AS total_asji FROM asji_inputs WHERE has_assumption = 0"); // Asumsi kolom `has_assumption` di tabel `asji_inputs`
                        // Anda perlu menambahkan kolom `has_assumption` BOOLEAN DEFAULT 0;
                        // ALTER TABLE asji_inputs ADD COLUMN has_assumption BOOLEAN DEFAULT 0;
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $data = $result->fetch_assoc();
                        $total_asji_new = $data['total_asji'];
                        $stmt->close();
                        ?>
                        <div class="h2 mb-0 fw-bold"><?php echo $total_asji_new; ?></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 pt-0 pb-0">
                <a href="manage_users.php" class="text-warning text-decoration-none small">Proses Pengajuan <i class="fas fa-arrow-circle-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white fw-bold">Pengajuan User Terbaru</div>
    <div class="card-body">
        <p class="text-muted">Daftar pengajuan terbaru dari user akan muncul di sini.</p>
        <a href="manage_users.php" class="btn btn-outline-primary">Lihat Semua Pengajuan</a>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>