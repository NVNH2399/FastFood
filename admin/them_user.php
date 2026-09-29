<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Xử lý thêm tài khoản
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']); 
    $email = trim($_POST['mail']);
    $password = $_POST['password'];
    $rule = 2; // Default member role
    
    $err = [];
    
    // Validate dữ liệu
    if(empty($username)) {
        $err['username'] = "Vui lòng nhập tên đăng nhập";
    }
    if(empty($password)) {
        $err['password'] = "Vui lòng nhập mật khẩu";
    }
    if(empty($email)) {
        $err['mail'] = "Vui lòng nhập email";
    }
    if(empty($fullname)) {
        $err['fullname'] = "Vui lòng nhập họ và tên";
    }
    
    // Kiểm tra username đã tồn tại
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $err['username'] = "Tên đăng nhập đã tồn tại";
    }
    
    // Thêm tài khoản nếu không có lỗi
    if(empty($err)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, fullname, password, mail, rule) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $username, $fullname, $hashed_password, $email, $rule);
        
        if($stmt->execute()) {
            echo "<script>alert('Thêm tài khoản thành công!'); window.location.href='index_user.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi thêm tài khoản!');</script>";
        }
    }
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">THÊM TÀI KHOẢN MỚI</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Thêm Tài Khoản</h5>
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control"
                                value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control"
                                value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="mail" class="form-control"
                                value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()"
                                    tabindex="-1">
                                    <i class="fas fa-eye" id="eye-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="add_user" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Tài Khoản
                    </button>
                    <a href="index_user.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('eye-icon');

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>