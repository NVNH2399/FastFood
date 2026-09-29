<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ MÃ GIẢM GIÁ</h2>

    <!-- Nút thêm mới -->
    <div class="mb-3">
        <a href="them_magiamgia.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo Mã Giảm Giá Mới
        </a>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" id="success-alert">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" id="error-alert">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Danh sách mã giảm giá -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Mã Giảm Giá</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Mã giảm giá</th>
                            <th>Giảm giá</th>
                            <th>Lượt sử dụng</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT c.*, 
                                (SELECT COUNT(*) FROM order_coupons WHERE idCoupon = c.coupon_id) as times_used 
                                FROM coupons c 
                                ORDER BY coupon_id DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            $stt = 1;
                            while ($row = $result->fetch_assoc()):
                                $status = $row['status'];
                                if($row['max_usage'] !== NULL && $row['times_used'] >= $row['max_usage']) {
                                    $status = 'inactive';
                                }

                                // Xác định class cho trạng thái
                                $status_class = '';
                                $status_text = '';
                                if($status == 'active') {
                                    $status_class = 'bg-success';
                                    $status_text = 'Còn hiệu lực';
                                } else {
                                    $status_class = 'bg-secondary';
                                    $status_text = ($row['max_usage'] !== NULL && $row['times_used'] >= $row['max_usage']) 
                                                 ? 'Hết lượt dùng' : 'Hết hiệu lực';
                                }
                        ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td>
                                <strong class="text-primary"><?php echo $row['code']; ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $row['discount']; ?>%</span>
                            </td>
                            <td>
                                <?php if($row['max_usage'] !== NULL): ?>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-success"
                                            style="width: <?php echo ($row['times_used']/$row['max_usage'])*100; ?>%">
                                        </div>
                                    </div>
                                    <span class="ms-2">
                                        <?php echo $row['times_used'] . '/' . $row['max_usage']; ?>
                                    </span>
                                </div>
                                <?php else: ?>
                                <span class="badge bg-primary"><?php echo $row['times_used']; ?> lần</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="sua_magiamgia.php?id=<?php echo $row['coupon_id']; ?>"
                                    class="btn btn-primary btn-sm" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="xoa_magiamgia.php?id=<?php echo $row['coupon_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc muốn xóa mã giảm giá này?')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có mã giảm giá nào</td>
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

<script>
// Auto hide alerts
function hideAlert(elementId) {
    const alert = document.getElementById(elementId);
    if (alert) {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 150);
        }, 3000);
    }
}

hideAlert('success-alert');
hideAlert('error-alert');
</script>