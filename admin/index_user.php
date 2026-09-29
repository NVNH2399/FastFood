<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ TÀI KHOẢN</h2>

    <!-- Các nút thao tác -->
    <div class="mb-3">
        <a href="them_user.php" class="btn btn-primary me-2">
            <i class="fas fa-plus"></i> Thêm Tài Khoản
        </a>
        <a href="change-user-role.php" class="btn btn-info text-white">
            <i class="fas fa-user-cog"></i> Đổi Quyền Tài Khoản
        </a>
    </div>

    <!-- Danh sách tài khoản -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Tài Khoản</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tài khoản</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Quyền</th>
                            <th>Trạng thái</th>
                            <th style="width: 180px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM users ORDER BY id";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                // Xác định badge cho quyền
                                $role_badge = '';
                                switch ($row['rule']) {
                                    case 1:
                                        $role_badge = 'bg-danger';
                                        $role_text = 'Admin';
                                        break;
                                    case 2:
                                        $role_badge = 'bg-success';
                                        $role_text = 'Thành viên';
                                        break;
                                    case 3:
                                        $role_badge = 'bg-primary';
                                        $role_text = 'Nhân viên';
                                        break;
                                }

                                // Badge cho trạng thái
                                $status_badge = $row['status'] === 'active' ? 'bg-success' : 'bg-danger';
                                $status_text = $row['status'] === 'active' ? 'Hoạt động' : 'Đã khóa';
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['mail']); ?></td>
                            <td>
                                <span class="badge <?php echo $role_badge; ?>">
                                    <?php echo $role_text; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $status_badge; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm me-1" title="Reset mật khẩu"
                                    onclick="resetPassword('<?php echo $row['username']; ?>', '<?php echo $row['mail']; ?>')">
                                    <i class="fa-solid fa-rotate-right"></i>
                                </button>
                                <?php if($row['username'] != 'admin'): ?>
                                <button
                                    class="btn <?php echo $row['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?> btn-sm"
                                    title="<?php echo $row['status'] === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản'; ?>"
                                    onclick="toggleUserStatus(<?php echo $row['id']; ?>, '<?php echo $row['username']; ?>', '<?php echo $row['status']; ?>')">
                                    <i
                                        class="fa-solid <?php echo $row['status'] === 'active' ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có tài khoản nào</td>
                        </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="overlay d-none">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<style>
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.d-none {
    display: none !important;
}
</style>

<script>
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('d-none');
}

function resetPassword(username, email) {
    if (!email) {
        alert("Không tìm thấy email!");
        return;
    }

    if (!confirm("Bạn có chắc muốn reset mật khẩu cho tài khoản " + username + "?")) {
        return;
    }

    showLoading();

    $.post('reset_password.php', {
            username: username,
            mail: email
        })
        .done(function(response) {
            alert("Đã gửi mật khẩu mới qua email!");
        })
        .fail(function() {
            alert("Có lỗi xảy ra khi reset mật khẩu!");
        })
        .always(function() {
            hideLoading();
        });
}

function toggleUserStatus(id, username, currentStatus) {
    if (username === 'admin') {
        alert("Không thể khóa tài khoản admin!");
        return;
    }

    const action = currentStatus === 'active' ? 'khóa' : 'mở khóa';
    if (!confirm(`Bạn có chắc muốn ${action} tài khoản ${username}?`)) {
        return;
    }

    showLoading();

    $.post('toggle_user_status.php', {
            id: id,
            status: currentStatus === 'active' ? 'blocked' : 'active'
        })
        .done(function(response) {
            alert(`${action} tài khoản thành công!`);
            location.reload();
        })
        .fail(function() {
            alert(`Có lỗi xảy ra khi ${action} tài khoản!`);
        })
        .always(function() {
            hideLoading();
        });
}
</script>