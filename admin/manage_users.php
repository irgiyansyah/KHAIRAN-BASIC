<?php include_once 'includes/header.php'; ?>

<h1 class="mb-4 display-5 fw-bold text-primary">Kelola User dan Pengajuan</h1>

<a href="create_user.php" class="btn btn-success mb-3">
    <i class="fas fa-plus-circle"></i> Tambah User Baru
</a>

<?php
$delete_message = "";
if (isset($_GET['delete_user_id'])) {
    $user_id_to_delete = (int)$_GET['delete_user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $user_id_to_delete);
    if ($stmt->execute()) {
        $delete_message = "<div class='alert alert-success'>Akun user berhasil dihapus.</div>";
    } else {
        $delete_message = "<div class='alert alert-danger'>Gagal menghapus akun user: " . $conn->error . "</div>";
    }
    $stmt->close();
}
echo $delete_message;
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">Daftar User</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID User</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->prepare("SELECT id, username, role, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($user = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                            echo "<td>" . htmlspecialchars(date('d M Y H:i', strtotime($user['created_at']))) . "</td>";
                            echo "<td>";
                            echo "<a href='set_assumptions.php?user_id=" . $user['id'] . "' class='btn btn-sm btn-info me-2' title='Kelola Pengajuan'><i class='fas fa-cogs'></i></a>";
                            echo "<a href='#' onclick='confirmDeleteUser(" . $user['id'] . ")' class='btn btn-sm btn-danger' title='Hapus User'><i class='fas fa-trash-alt'></i></a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>Tidak ada user terdaftar.</td></tr>";
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function confirmDeleteUser(userId) {
    if (confirm("Apakah Anda yakin ingin menghapus user ini dan semua pengajuannya?")) {
        window.location.href = 'manage_users.php?delete_user_id=' + userId;
    }
}
</script>

<?php include_once 'includes/footer.php'; ?>