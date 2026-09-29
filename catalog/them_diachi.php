<?php
ob_start();
session_start();

if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$title = "Thêm địa chỉ";
include '../pages/header.php';

$username = $_SESSION['username'];

// Lấy thông tin user
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);      // Đổi từ diachi thành address
    $phone = trim($_POST['phone']);          // Đổi từ sdt thành phone
    $is_default = isset($_POST['is_default']) ? 1 : 0;  // Đổi từ macdinh thành is_default
    
    if(empty($address) || empty($phone)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    } else if(!preg_match('/^0\d{9}$/', $phone)) {
        $_SESSION['error'] = "Số điện thoại không hợp lệ!";
    } else {
        $conn->begin_transaction();
        try {
            // Nếu đặt làm địa chỉ mặc định, cập nhật các địa chỉ khác
            if($is_default == 1) {
                $update_sql = "UPDATE addresses SET is_default = 0 WHERE idUser = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
            }
            
            // Thêm địa chỉ mới
            $insert_sql = "INSERT INTO addresses (idUser, address, phone, is_default, status) 
                          VALUES (?, ?, ?, ?, 'active')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("issi", $user['id'], $address, $phone, $is_default);
            $insert_stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = "Thêm địa chỉ thành công!";
            header("Location: thongtin_cuatoi.php");
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Có lỗi xảy ra khi thêm địa chỉ!";
        }
    }
}
?>

<style>
.container {
    max-width: 800px;
    margin: 30px auto;
    padding: 0 15px;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    background: #ff4757;
    color: white;
    padding: 20px;
    border-bottom: none;
}

.card-header h2 {
    margin: 0;
    font-size: 1.5em;
}

.card-body {
    padding: 25px;
}

.form-label {
    font-weight: 500;
    margin-bottom: 8px;
    color: #2c3e50;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    width: 100%;
    margin-bottom: 15px;
    transition: border-color 0.3s;
}

.form-control:focus {
    border-color: #ff4757;
    box-shadow: 0 0 0 0.2rem rgba(255, 71, 87, 0.25);
    outline: none;
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.form-check {
    margin: 20px 0;
}

.form-check-input {
    margin-right: 8px;
}

.form-check-input:checked {
    background-color: #ff4757;
    border-color: #ff4757;
}

.btn {
    padding: 10px 20px;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
}

.btn-primary {
    background: #ff4757;
    color: white;
}

.btn-primary:hover {
    background: #ff6b81;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    margin-left: 10px;
}

a.btn-secondary {
    text-decoration: none;
}

.btn-secondary:hover {
    background: #5a6268;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 576px) {
    .container {
        padding: 0 10px;
    }

    .card-body {
        padding: 15px;
    }

    .btn {
        width: 100%;
        margin: 5px 0;
    }
}

/* Giữ nguyên CSS cơ bản và thêm/sửa media queries */
@media screen and (max-width: 768px) {
    .container {
        margin: 15px auto;
        padding: 0 10px;
    }

    .card {
        border-radius: 0;
        box-shadow: none;
    }

    .card-header {
        padding: 15px;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .card-header h2 {
        font-size: 1.2em;
    }

    .card-body {
        padding: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        font-size: 14px;
    }

    .form-control {
        padding: 12px;
        font-size: 16px; /* Tăng font size để dễ nhập trên mobile */
        margin-bottom: 10px;
    }

    textarea.form-control {
        min-height: 80px;
    }

    .form-check {
        margin: 15px 0;
    }

    .form-check-input {
        width: 20px; /* Tăng kích thước checkbox */
        height: 20px;
    }

    .form-check-label {
        font-size: 14px;
        padding-left: 5px;
    }

    .button-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        width: 100%;
        padding: 12px 20px;
        font-size: 16px;
        margin: 0;
    }

    .btn i {
        margin-right: 8px;
    }

    .alert {
        padding: 12px;
        font-size: 14px;
        margin-bottom: 15px;
    }

    /* Tối ưu cho màn hình cảm ứng */
    input, textarea, select, button {
        -webkit-appearance: none;
        border-radius: 4px;
    }

    /* Tăng khoảng cách giữa các elements để dễ chạm */
    .form-group + .form-group {
        margin-top: 20px;
    }

    /* Cải thiện khả năng đọc trên màn hình nhỏ */
    .alert-danger {
        border-radius: 8px;
        font-size: 14px;
        line-height: 1.4;
    }
}

/* Thêm media query cho màn hình rất nhỏ */
@media screen and (max-width: 320px) {
    .card-header h2 {
        font-size: 1.1em;
    }

    .form-control {
        font-size: 14px;
    }

    .btn {
        font-size: 14px;
        padding: 10px 15px;
    }
}
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-map-marker-alt"></i> Thêm địa chỉ mới</h2>
        </div>
        <div class="card-body">
            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="diachi" class="form-label">Địa chỉ:</label>
                    <textarea class="form-control" id="diachi" name="address" rows="3"
                        placeholder="Nhập địa chỉ chi tiết" required></textarea>
                </div>

                <div class="form-group">
                    <label for="sdt" class="form-label">Số điện thoại:</label>
                    <input type="tel" class="form-control" id="sdt" name="phone" pattern="0[0-9]{9}"
                        placeholder="0xxxxxxxxx" required>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="macdinh" name="is_default">
                    <label class="form-check-label" for="macdinh">
                        Đặt làm địa chỉ mặc định
                    </label>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm địa chỉ
                    </button>
                    <a href="thongtin_cuatoi.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>

<script>
function validateForm() {
    const address = document.getElementById('address').value.trim();
    const phone = document.getElementById('phone').value.trim();

    if (!address) {
        alert('Vui lòng nhập địa chỉ!');
        return false;
    }

    if (!phone) {
        alert('Vui lòng nhập số điện thoại!');
        return false;
    }

    if (!/^0\d{9}$/.test(phone)) {
        alert('Số điện thoại không hợp lệ!');
        return false;
    }

    return true;
}
</script>

<?php include '../pages/footer.php'; 
ob_end_flush();
?>