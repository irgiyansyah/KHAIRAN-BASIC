<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/img/logo.jpg" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
            KHAIRAN BASIC
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">Tentang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="simulation.php">Simulasi</a>
                </li>
                <?php if (!isset($_SESSION['user_id'])): // Jika belum login ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-light text-primary ms-lg-3 px-3 rounded-pill" href="login.php">Login</a>
                    </li>
                <?php else: // Jika sudah login ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <?php if (is_admin()): ?>
                                <li><a class="dropdown-item" href="admin/dashboard.php">Dashboard Admin</a></li>
                            <?php elseif (is_user()): ?>
                                <li><a class="dropdown-item" href="user/dashboard.php">Dashboard User</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>