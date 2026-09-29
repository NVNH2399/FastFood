<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Xử lý đổi quyền
if (isset($_POST['update_role'])) {
    $username = trim($_POST['username']);
    $new_role = $_POST['new_role'];
    $err = [];

    if(empty($username)) {
        $err['username'] = "Vui lòng chọn tài khoản";
    }
    if(empty($new_role)) {
        $err['new_role'] = "Vui lòng chọn quyền mới";
    }

    // Kiểm tra tài khoản tồn tại
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if($user['username'] == 'admin') {
            $err['username'] = "Không thể thay đổi quyền của tài khoản admin!";
        }
    } else {
        $err['username'] = "Không tìm thấy tài khoản!";
    }

    if(empty($err)) {
        // Cập nhật quyền
        $sql = "UPDATE users SET rule = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $new_role, $username);
        
        if($stmt->execute()) {
            echo "<script>alert('Cập nhật quyền thành công!'); window.location.href='index_user.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi cập nhật quyền!');</script>";
        }
    }
}

// Lấy danh sách users trừ admin
$sql = "SELECT username, rule FROM users WHERE username != 'admin' ORDER BY id";
$users_result = $conn->query($sql);
?>

<div class="container-fluid">
    <h2 class="text-center my-4">ĐỔI QUYỀN TÀI KHOẢN</h2>

    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Cập Nhật Quyền</h5>
        </div>
        <div class="card-body">
            <?php if(!empty($err)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach($err as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Tài khoản <span class="text-danger">*</span></label>
                    <select name="username" class="form-select" required>
                        <option value="">-- Chọn tài khoản --</option>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                        <option value="<?php echo $user['username']; ?>"
                            <?php echo (isset($_POST['username']) && $_POST['username'] == $user['username']) ? 'selected' : ''; ?>>
                            <?php 
                                $role_text = $user['rule'] == 2 ? 'Thành viên' : 'Nhân viên';
                                echo htmlspecialchars($user['username'] . ' (' . $role_text . ')'); 
                                ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quyền mới <span class="text-danger">*</span></label>
                    <select name="new_role" class="form-select" required>
                        <option value="">-- Chọn quyền --</option>
                        <option value="2"
                            <?php echo (isset($_POST['new_role']) && $_POST['new_role'] == 2) ? 'selected' : ''; ?>>
                            Thành viên
                        </option>
                        <option value="3"
                            <?php echo (isset($_POST['new_role']) && $_POST['new_role'] == 3) ? 'selected' : ''; ?>>
                            Nhân viên
                        </option>
                    </select>
                </div>

                <div class="mt-3">
                    <button type="submit" name="update_role" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập Nhật Quyền
                    </button>
                    <a href="index_user.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>