<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Kiểm tra và lấy thông tin mã giảm giá
if (!isset($_GET['id'])) {
    echo "<script>alert('Không tìm thấy mã giảm giá!'); window.location.href='index_magiamgia.php';</script>";
    exit();
}

$coupon_id = $_GET['id'];
$sql = "SELECT * FROM coupons WHERE coupon_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $coupon_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Không tìm thấy mã giảm giá!'); window.location.href='index_magiamgia.php';</script>";
    exit();
}

$coupon = $result->fetch_assoc();
?>

<div class="container-fluid">
    <h2 class="text-center my-4">CHỈNH SỬA MÃ GIẢM GIÁ</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Sửa Mã Giảm Giá</h5>
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

            <form action="xuly_sua_magiamgia.php" method="POST">
                <input type="hidden" name="coupon_id" value="<?php echo $coupon['coupon_id']; ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mã giảm giá</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($coupon['code']); ?>" readonly>
                            <div class="form-text">Mã giảm giá không thể thay đổi sau khi tạo</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giảm giá (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="discount"
                                value="<?php echo $coupon['discount']; ?>" min="1" max="100" required>
                            <div class="form-text">Nhập giá trị từ 1 đến 100</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Số lượt sử dụng tối đa</label>
                            <input type="number" name="max_usage" class="form-control" min="1"
                                value="<?php echo $coupon['max_usage']; ?>" placeholder="Để trống nếu không giới hạn"
                                <?php echo isset($coupon['times_used']) && $coupon['times_used'] > 0 ? 'readonly' : ''; ?>>
                            <?php if(isset($coupon['times_used']) && $coupon['times_used'] > 0): ?>
                            <div class="form-text text-warning">
                                Không thể thay đổi giới hạn khi mã đã được sử dụng
                            </div>
                            <?php else: ?>
                            <div class="form-text">Bỏ trống nếu muốn không giới hạn số lần sử dụng</div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php echo $coupon['status'] == 'active' ? 'selected' : ''; ?>>
                                    Còn hiệu lực
                                </option>
                                <option value="inactive"
                                    <?php echo $coupon['status'] == 'inactive' ? 'selected' : ''; ?>>
                                    Hết hiệu lực
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Thay Đổi
                    </button>
                    <a href="index_magiamgia.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>