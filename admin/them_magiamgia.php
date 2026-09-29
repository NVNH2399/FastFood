<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">THÊM MÃ GIẢM GIÁ MỚI</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Tạo Mã Giảm Giá</h5>
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

            <form action="xuly_them_magiamgia.php" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Mã giảm giá <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="code" id="couponCode" readonly required
                                    placeholder="Nhấn nút bên cạnh để tạo mã">
                                <button type="button" class="btn btn-success" onclick="generateCoupon()">
                                    <i class="fas fa-sync"></i> Tạo mã
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giảm giá (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="discount" min="1" max="100" required
                                value="<?php echo isset($discount) ? $discount : ''; ?>">
                            <div class="form-text">Nhập giá trị từ 1 đến 100</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Số lượt sử dụng tối đa</label>
                            <input type="number" name="max_usage" class="form-control" min="1"
                                value="<?php echo isset($max_usage) ? $max_usage : ''; ?>"
                                placeholder="Để trống nếu không giới hạn">
                            <div class="form-text">Bỏ trống nếu muốn không giới hạn số lần sử dụng</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="active">Kích hoạt</option>
                                <option value="inactive">Chưa kích hoạt</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Mã Giảm Giá
                    </button>
                    <a href="index_magiamgia.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateCoupon() {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let code = 'FOOD';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('couponCode').value = code;
}

// Tự động tạo mã khi trang được tải
document.addEventListener('DOMContentLoaded', generateCoupon);
</script>