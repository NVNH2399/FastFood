<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Lấy thông tin admin
$sql = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($sql);
$admin = $result->fetch_assoc();

// Xử lý cập nhật thông tin
if(isset($_POST['update_profile'])) {
    $fullname = trim($_POST['fullname']); 
    $email = trim($_POST['email']);
    $err = [];
    
    if(empty($fullname)) {
        $err['fullname'] = "Vui lòng nhập họ tên";
    }
    
    if(empty($err)) {
        $sql = "UPDATE users SET fullname = ?, mail = ? WHERE username = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fullname, $email);
        
        if($stmt->execute()) {
            $_SESSION['fullname'] = $fullname; // Cập nhật session
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Cập nhật thông tin thành công!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = 'index_profile_admin.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra khi cập nhật thông tin!'
                });
            </script>";
        }
    }
}

// Xử lý đổi mật khẩu
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $err = [];
    
    if(!password_verify($current_password, $admin['password'])) {
        $err['current_password'] = "Mật khẩu hiện tại không đúng";
    }
    
    if(strlen($new_password) < 6) {
        $err['new_password'] = "Mật khẩu mới phải có ít nhất 6 ký tự";
    }
    
    if($new_password !== $confirm_password) {
        $err['confirm_password'] = "Xác nhận mật khẩu không khớp";
    }
    
    if(empty($err)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE username = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $hashed_password);
        
        if($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Đổi mật khẩu thành công!',
                    showConfirmButton: false,
                    timer: 1500
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Có lỗi xảy ra khi đổi mật khẩu!'
                });
            </script>";
        }
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-center">HỒ SƠ ADMIN</h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Thông tin cá nhân -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle me-2"></i>Thông Tin Cá Nhân
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($err) && isset($_POST['update_profile'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach($err as $error): ?>
                            <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <!-- <div class="mb-3">
                            <label class="form-label">ID</label>
                            <input type="text" class="form-control" value="<?php echo $admin['id']; ?>" readonly>
                        </div> -->

                        <div class="mb-3">
                            <label class="form-label">Tài khoản</label>
                            <input type="text" class="form-control" value="<?php echo $admin['username']; ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control"
                                value="<?php echo htmlspecialchars($admin['fullname']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger"></span></label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($admin['mail']); ?>" required readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quyền hạn</label>
                            <input type="text" class="form-control" value="Admin" readonly>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Cập Nhật Thông Tin
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key me-2"></i>Đổi Mật Khẩu
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($err) && isset($_POST['change_password'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
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
                            <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password"
                                    class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePassword('current_password')">
                                    <i class="far fa-eye" id="current_password_icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" class="form-control"
                                    required>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePassword('new_password')">
                                    <i class="far fa-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm_password"
                                    class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePassword('confirm_password')">
                                    <i class="far fa-eye" id="confirm_password_icon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Đổi Mật Khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>