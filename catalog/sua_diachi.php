<?php
ob_start();
$title = "Sửa địa chỉ";
include '../pages/header.php';

if(!isset($_SESSION['username'])) {
   header("Location: ../login.php"); 
   exit;
}

$username = $_SESSION['username'];
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Lấy thông tin user
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Lấy thông tin địa chỉ
$address_sql = "SELECT * FROM addresses WHERE address_id = ? AND idUser = ? AND status = 'active'";  
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("ii", $id, $user['id']);
$address_stmt->execute();
$address = $address_stmt->get_result()->fetch_assoc();

if(!$address) {
   header("Location: thongtin_cuatoi.php");
   exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $address_text = trim($_POST['address']); // Sửa tên biến từ addresses thành address
   $phone = trim($_POST['phone']);
   $is_default = isset($_POST['is_default']) ? 1 : 0;
   
   if(empty($address_text) || empty($phone)) {
       $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
   } else if(!preg_match('/^0\d{9}$/', $phone)) {
       $_SESSION['error'] = "Số điện thoại không hợp lệ!";
   } else {
       $conn->begin_transaction();
       try {
           // Nếu đặt làm mặc định, bỏ mặc định các địa chỉ khác
           if($is_default) {
               $reset_sql = "UPDATE addresses SET is_default = 0 
                            WHERE idUser = ? AND address_id != ? AND status = 'active'";
               $reset_stmt = $conn->prepare($reset_sql);
               $reset_stmt->bind_param("ii", $user['id'], $id);
               $reset_stmt->execute();
           }
           
           // Cập nhật địa chỉ
           $update_sql = "UPDATE addresses 
                         SET address = ?, phone = ?, is_default = ? 
                         WHERE address_id = ? AND idUser = ?";
           $update_stmt = $conn->prepare($update_sql);
           $update_stmt->bind_param("ssiii", $address_text, $phone, $is_default, $id, $user['id']);
           $update_stmt->execute();
           
           $conn->commit();
           $_SESSION['success'] = "Cập nhật địa chỉ thành công!";
           header("Location: thongtin_cuatoi.php");
           exit;
           
       } catch (Exception $e) {
           $conn->rollback();
           $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật địa chỉ!";
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

/* Giữ nguyên CSS cơ bản và thêm media queries chi tiết hơn */
@media screen and (max-width: 768px) {
    .container {
        margin: 15px auto;
        padding: 0 10px;
    }

    .card {
        border-radius: 0;
        box-shadow: none;
        margin: -15px; /* Mở rộng card full màn hình */
    }

    .card-header {
        padding: 15px;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .card-header h2 {
        font-size: 1.2em;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body {
        padding: 15px;
    }

    /* Form elements */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-size: 14px;
        display: block;
        margin-bottom: 8px;
    }

    .form-control {
        padding: 12px;
        font-size: 16px; /* Tăng font size cho dễ đọc/nhập trên mobile */
        height: auto;
        margin-bottom: 10px;
    }

    textarea.form-control {
        min-height: 80px;
    }

    /* Checkbox style */
    .form-check {
        margin: 20px 0;
        display: flex;
        align-items: center;
    }

    .form-check-input {
        width: 20px;
        height: 20px;
        margin-right: 10px;
    }

    .form-check-label {
        font-size: 14px;
        line-height: 20px;
    }

    /* Button group */
    .button-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }

    .btn {
        width: 100%;
        padding: 14px 20px;
        font-size: 16px;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-secondary {
        margin-left: 0;
    }

    /* Alert messages */
    .alert {
        padding: 12px 15px;
        font-size: 14px;
        border-radius: 8px;
        margin: 10px 0;
    }

    /* Touch optimization */
    input, textarea, select, button {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }
}

/* Extra small devices */
@media screen and (max-width: 375px) {
    .card-header h2 {
        font-size: 1.1em;
    }

    .form-control {
        font-size: 14px;
        padding: 10px;
    }

    .btn {
        padding: 12px 16px;
        font-size: 14px;
    }

    .form-check-label {
        font-size: 13px;
    }
}
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-map-marker-alt"></i> Sửa địa chỉ</h2>
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
                    <label for="address" class="form-label">Địa chỉ:</label>
                    <textarea class="form-control" id="address" name="address" rows="3"
                        placeholder="Nhập địa chỉ chi tiết"
                        required><?php echo htmlspecialchars($address['address']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Số điện thoại:</label>
                    <input type="tel" class="form-control" id="phone" name="phone" pattern="0[0-9]{9}"
                        placeholder="0xxxxxxxxx" value="<?php echo htmlspecialchars($address['phone']); ?>" required>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_default" name="is_default"
                        <?php echo $address['is_default'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_default">
                        Đặt làm địa chỉ mặc định
                    </label>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu thay đổi
                    </button>
                    <a href="thongtin_cuatoi.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
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