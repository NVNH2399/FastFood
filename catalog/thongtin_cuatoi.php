<?php
ob_start();
$title = "Thông tin của tôi";
include '../pages/header.php';

if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// Lấy thông tin user
$sql = "SELECT * FROM users WHERE username = ?"; 
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$user = mysqli_stmt_get_result($stmt)->fetch_assoc();

// Truy vấn lấy địa chỉ từ bảng addresses
$address_sql = "SELECT * FROM addresses 
                WHERE idUser = ? 
                AND status = 'active' 
                ORDER BY is_default DESC, created_at DESC";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("i", $user['id']);
$address_stmt->execute();
$addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý cập nhật thông tin cá nhân
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['update_info'])) {
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        
        if(empty($fullname)) {
            $_SESSION['error'] = "Vui lòng nhập họ tên!";
        } else {
            $update_sql = "UPDATE users SET fullname = ?, mail = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sss", $fullname, $email, $username);
            
            if($update_stmt->execute()) {
                $_SESSION['success'] = "Cập nhật thông tin thành công!";
                header("Location: thongtin_cuatoi.php");
                exit;
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật thông tin!";
            }
        }
    }
    
    // Xử lý đổi mật khẩu
if(isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kiểm tra mật khẩu cũ
    if(!password_verify($old_password, $user['password'])) {
        $_SESSION['error'] = "Mật khẩu cũ không chính xác!";
    }
    // Kiểm tra mật khẩu mới và xác nhận
    else if($new_password !== $confirm_password) {
        $_SESSION['error'] = "Mật khẩu mới và xác nhận không khớp!";
    }
    // Kiểm tra độ mạnh mật khẩu
    else if(strlen($new_password) < 8 || 
            !preg_match("/[A-Z]/", $new_password) || 
            !preg_match("/[a-z]/", $new_password) || 
            !preg_match("/[0-9]/", $new_password) || 
            !preg_match("/[@$!%*?&]/", $new_password)) {
        $_SESSION['error'] = "Mật khẩu mới không đủ mạnh!";
    }
    else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass_sql = "UPDATE users SET password = ? WHERE username = ?";
        $update_pass_stmt = $conn->prepare($update_pass_sql);
        $update_pass_stmt->bind_param("ss", $hashed_password, $username);
        
        if($update_pass_stmt->execute()) {
            $_SESSION['success'] = "Đổi mật khẩu thành công!";
            header("Location: thongtin_cuatoi.php");
            exit;
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi đổi mật khẩu!";
        }
    }
}
    // Xử lý thêm địa chỉ mới
if(isset($_POST['add_address'])) {
    $diachi = trim($_POST['diachi']);
    $sdt = trim($_POST['sdt']);
    
    if(empty($diachi) || empty($sdt)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin địa chỉ!";
    } else if(!preg_match('/^0\d{9}$/', $sdt)) {
        $_SESSION['error'] = "Số điện thoại không hợp lệ!";
    } else {
        // Thêm địa chỉ vào bảng donhang
        $insert_sql = "INSERT INTO donhang (idUser, idSanPham, diachi, sdt, trangthai) VALUES (?, 0, ?, ?, 'pending')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iss", $user['id'], $diachi, $sdt);
        
        if($insert_stmt->execute()) {
            $_SESSION['success'] = "Thêm địa chỉ thành công!";
            header("Location: thongtin_cuatoi.php");
            exit;
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi thêm địa chỉ!";
        }
    }
}

// Lấy danh sách địa chỉ của user
$address_sql = "SELECT * FROM addresses WHERE idUser = ? AND status = 'active' ORDER BY is_default DESC, created_at DESC";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("i", $user['id']);
$address_stmt->execute();
$addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý thêm địa chỉ mới
if(isset($_POST['add_address'])) {
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    
    if(empty($address) || empty($phone)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin địa chỉ!";
    } else if(!preg_match('/^0\d{9}$/', $phone)) {
        $_SESSION['error'] = "Số điện thoại không hợp lệ!";
    } else {
        $insert_sql = "INSERT INTO addresses (idUser, address, phone, status) VALUES (?, ?, ?, 'active')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iss", $user['id'], $address, $phone);
        
        if($insert_stmt->execute()) {
            $_SESSION['success'] = "Thêm địa chỉ thành công!";
            header("Location: thongtin_cuatoi.php");
            exit;
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi thêm địa chỉ!";
        }
    }
}

// Xử lý cập nhật địa chỉ
if(isset($_POST['edit_address'])) {
    $address_id = $_POST['id'];
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    
    if(empty($address) || empty($phone)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin địa chỉ!";
    } else if(!preg_match('/^0\d{9}$/', $phone)) {
        $_SESSION['error'] = "Số điện thoại không hợp lệ!";
    } else {
        $update_sql = "UPDATE addresses SET address = ?, phone = ? WHERE address_id = ? AND idUser = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssii", $address, $phone, $address_id, $user['id']);
        
        if($update_stmt->execute()) {
            $_SESSION['success'] = "Cập nhật địa chỉ thành công!";
            header("Location: thongtin_cuatoi.php");
            exit;
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật địa chỉ!";
        }
    }
}

// Xử lý xóa địa chỉ
if(isset($_POST['delete_address'])) {
    $address_id = $_POST['id'];
    
    $delete_sql = "UPDATE addresses SET status = 'inactive' WHERE address_id = ? AND idUser = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $address_id, $user['id']);
    
    if($delete_stmt->execute()) {
        $_SESSION['success'] = "Xóa địa chỉ thành công!";
        header("Location: thongtin_cuatoi.php");
        exit;
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra khi xóa địa chỉ!";
    }
}
}
?>
<link rel="stylesheet" href="../catalog/css/style_thongtincuatoi.css">

<div>
    <?php if(isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        <i class="fas fa-check-circle"></i>
    </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        <i class="fas fa-exclamation-circle"></i>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<div class="profile-container">
    <!-- Form thông tin tài khoản -->
    <div class="info-card">
        <div class="card-header">
            <h2>Thông tin tài khoản</h2>
        </div>

        <form method="POST" id="info-form">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['mail']); ?>">
            </div>

            <div class="btn-group">
                <button type="submit" name="update_info" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <button type="button" onclick="togglePasswordForm()" class="btn btn-outline">
                    <i class="fas fa-key"></i> Đổi mật khẩu
                </button>
            </div>
        </form>

        <form method="POST" id="password-form" class="password-form">
            <div class="form-group">
                <label>Mật khẩu cũ</label>
                <div style="position: relative;">
                    <input type="password" name="old_password" id="old_password" required>
                    <span onclick="togglePassword('old_password')" class="toggle-password"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                        <!-- <i class="fas fa-eye"></i> -->
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label>Mật khẩu mới</label>
                <div style="position: relative;">
                    <input type="password" name="new_password" id="new_password" required
                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
                    <span onclick="togglePassword('new_password')" class="toggle-password"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                        <!-- <i class="fas fa-eye"></i> -->
                    </span>
                </div>
                <div class="password-requirements">
                    <p>Mật khẩu phải có ít nhất:</p>
                    <ul>
                        <li id="length">8 ký tự</li>
                        <li id="uppercase">1 chữ in hoa</li>
                        <li id="lowercase">1 chữ thường</li>
                        <li id="number">1 số</li>
                        <li id="special">1 ký tự đặc biệt (@$!%*?&)</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label>Xác nhận mật khẩu mới</label>
                <div style="position: relative;">
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <span onclick="togglePassword('confirm_password')" class="toggle-password"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                        <!-- <i class="fas fa-eye"></i> -->
                    </span>
                </div>
            </div>

            <button type="submit" name="change_password" class="btn btn-primary">
                <i class="fas fa-save"></i> Cập nhật mật khẩu
            </button>
        </form>
    </div>

    <!-- Form địa chỉ giao hàng -->
    <div class="info-card">
        <div class="card-header">
            <h2>Địa chỉ giao hàng</h2>
            <a href="them_diachi.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm địa chỉ
            </a>
        </div>

        <div class="address-list">
            <?php if(empty($addresses)): ?>
            <div class="empty-state">
                <i class="fas fa-map-marker-alt"></i>
                <p>Bạn chưa có địa chỉ giao hàng nào</p>
            </div>
            <?php else: ?>
            <?php foreach($addresses as $address): ?>
            <div class="address-item">
                <div class="address-marker">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="address-details">
                    <div class="address">
                        <?php echo htmlspecialchars($address['address']); ?>
                        <?php if($address['is_default'] == 1): ?>
                        <span class="badge-default">Địa chỉ mặc định</span>
                        <?php endif; ?>
                    </div>
                    <div class="phone"><?php echo htmlspecialchars($address['phone']); ?></div>
                </div>
                <div class="address-actions">
                    <a href="sua_diachi.php?id=<?php echo $address['address_id']; ?>" class="btn-icon btn-edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn-icon btn-delete"
                        onclick="deleteAddress(<?php echo $address['address_id']; ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// ----- Xử lý form mật khẩu -----
function togglePasswordForm() {
    const passwordForm = document.getElementById('password-form');
    if (passwordForm.style.display === 'none' || !passwordForm.style.display) {
        passwordForm.style.display = 'block';
    } else {
        passwordForm.style.display = 'none';
    }
}

// ----- Validate form thông tin cá nhân -----
document.getElementById('info-form').addEventListener('submit', function(e) {
    const fullname = document.querySelector('input[name="fullname"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();

    if (!fullname) {
        e.preventDefault();
        alert('Vui lòng nhập họ tên!');
        return false;
    }

    if (email && !isValidEmail(email)) {
        e.preventDefault();
        alert('Email không hợp lệ!');
        return false;
    }
});

// ----- Validate form đổi mật khẩu -----
document.getElementById('password-form').addEventListener('submit', function(e) {
    const oldPassword = document.querySelector('input[name="old_password"]').value;
    const newPassword = document.querySelector('input[name="new_password"]').value;
    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

    if (!oldPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Vui lòng nhập đầy đủ thông tin!');
        return false;
    }

    if (newPassword.length < 8) {
        e.preventDefault();
        alert('Mật khẩu mới phải có ít nhất 8 ký tự!');
        return false;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu mới và xác nhận không khớp!');
        return false;
    }
});

// ----- Xử lý thông báo alert -----
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Hiệu ứng fade out sau 3 giây
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.5s ease-in-out forwards';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 3000);
    });
});

// ----- Helper functions -----
function isValidEmail(email) {
    const re =
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// ----- Password strength checker (tùy chọn) -----
function checkPasswordStrength(password) {
    let strength = 0;

    // Kiểm tra độ dài
    if (password.length >= 8) strength++;

    // Kiểm tra chữ thường
    if (/[a-z]/.test(password)) strength++;

    // Kiểm tra chữ hoa
    if (/[A-Z]/.test(password)) strength++;

    // Kiểm tra số
    if (/[0-9]/.test(password)) strength++;

    // Kiểm tra ký tự đặc biệt
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    return strength;
}

// ----- Event Listeners -----
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator (tùy chọn)
    const newPasswordInput = document.querySelector('input[name="new_password"]');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            // Thêm code hiển thị strength indicator nếu cần
        });
    }

    // Prevent form resubmission
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
});

// ----- Xử lý confirm trước khi rời form có thay đổi -----
let formChanged = false;

document.getElementById('info-form').addEventListener('change', function() {
    formChanged = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Reset formChanged khi submit form
document.getElementById('info-form').addEventListener('submit', function() {
    formChanged = false;
});

function deleteAddress(id) {
    if (confirm('Bạn có chắc chắn muốn xóa địa chỉ này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="delete_address" value="1">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = passwordField.nextElementSibling.querySelector("i");
    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Kiểm tra độ mạnh mật khẩu
function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[@$!%*?&]/.test(password);

    document.getElementById('length').classList.toggle('valid', password.length >= minLength);
    document.getElementById('uppercase').classList.toggle('valid', hasUpperCase);
    document.getElementById('lowercase').classList.toggle('valid', hasLowerCase);
    document.getElementById('number').classList.toggle('valid', hasNumbers);
    document.getElementById('special').classList.toggle('valid', hasSpecialChar);

    return password.length >= minLength && hasUpperCase && hasLowerCase &&
        hasNumbers && hasSpecialChar;
}

// Sửa lại phần validate form mật khẩu
document.getElementById('password-form').addEventListener('submit', function(e) {
    const oldPassword = document.querySelector('input[name="old_password"]').value;
    const newPassword = document.querySelector('input[name="new_password"]').value;
    const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

    if (!oldPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Vui lòng nhập đầy đủ thông tin!');
        return false;
    }

    if (!validatePassword(newPassword)) {
        e.preventDefault();
        alert('Mật khẩu mới không đủ mạnh!');
        return false;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Mật khẩu mới và xác nhận không khớp!');
        return false;
    }
});

// Hiển thị yêu cầu mật khẩu khi focus vào input
document.getElementById('new_password').addEventListener('focus', function() {
    document.querySelector('.password-requirements').style.display = 'block';
});

// Kiểm tra mật khẩu khi nhập
document.getElementById('new_password').addEventListener('input', function() {
    validatePassword(this.value);
});

// Ẩn yêu cầu mật khẩu khi click ra ngoài
document.addEventListener('click', function(e) {
    if (!e.target.closest('.form-group')) {
        document.querySelector('.password-requirements').style.display = 'none';
    }
});
</script>

<?php include '../pages/footer.php'; 
ob_end_flush();
?>