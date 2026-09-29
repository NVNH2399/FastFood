<?php
session_start();
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['rule'] !== 3) { // Kiểm tra quyền nhân viên
    header("Location: ../catalog/login.php");
    exit();
}

// Lấy thống kê đơn hàng
$count_sql = "SELECT 
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'delivering' THEN 1 ELSE 0 END) as delivering_count,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled_count
    FROM orders";
$count_result = $conn->query($count_sql);
$counts = $count_result->fetch_assoc();
?>

<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ ĐƠN HÀNG</h2>

    <!-- Thống kê đơn hàng -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning bg-opacity-10 h-100">
                <div class="card-body">
                    <h5 class="card-title">Đang xử lý</h5>
                    <p class="card-text"><?php echo $counts['pending_count']; ?> đơn hàng</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info bg-opacity-10 h-100">
                <div class="card-body">
                    <h5 class="card-title">Đang giao hàng</h5>
                    <p class="card-text"><?php echo $counts['delivering_count']; ?> đơn hàng</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success bg-opacity-10 h-100">
                <div class="card-body">
                    <h5 class="card-title">Đã giao</h5>
                    <p class="card-text"><?php echo $counts['completed_count']; ?> đơn hàng</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger bg-opacity-10 h-100">
                <div class="card-body">
                    <h5 class="card-title">Đã hủy</h5>
                    <p class="card-text"><?php echo $counts['canceled_count']; ?> đơn hàng</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách đơn hàng -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Đơn Hàng</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Địa chỉ</th>
                            <th>Số điện thoại</th>
                            <th>Thời gian</th>
                            <th>Thành tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT orders.*, users.fullname 
                               FROM orders 
                               JOIN users ON orders.idUser = users.id 
                               ORDER BY orders.created_at DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if($row['discount'] > 0): ?>
                                <del class="text-muted small"><?php echo number_format($row['total_amount']); ?>₫</del>
                                <div class="text-success small">-<?php echo number_format($row['discount']); ?>₫</div>
                                <?php endif; ?>
                                <strong class="text-danger"><?php echo number_format($row['final_amount']); ?>₫</strong>
                            </td>
                            <td>
                                <?php if($row['status'] == 'canceled'): ?>
                                <span class="badge bg-danger">Đã hủy</span>
                                <?php else: ?>
                                <select class="form-select status-select"
                                    onchange="updateStatus(<?php echo $row['order_id']; ?>, this.value)">
                                    <?php if($row['status'] == 'pending'): ?>
                                    <option value="pending" selected>Đang xử lý</option>
                                    <option value="delivering">Đang giao hàng</option>
                                    <?php elseif($row['status'] == 'delivering'): ?>
                                    <option value="delivering" selected>Đang giao hàng</option>
                                    <option value="completed">Đã giao</option>
                                    <?php elseif($row['status'] == 'completed'): ?>
                                    <option value="completed" selected>Đã giao</option>
                                    <?php endif; ?>
                                </select>

                                <div class="mt-2">
                                    <?php if($row['payment_method'] == 'momo'): ?>
                                    <?php
    $payment_class = '';
    $payment_icon = '';
    $payment_text = '';
    switch ($row['payment_status']) {
        case 'completed':
            $payment_class = 'bg-success';
            $payment_icon = 'fa-check-circle';
            $payment_text = 'Đã thanh toán';
            break;
        case 'pending':
            $payment_class = 'bg-warning';
            $payment_icon = 'fa-clock';
            $payment_text = 'Chờ thanh toán';
            break;
        case 'pending_payment':
            $payment_class = 'bg-warning';
            $payment_icon = 'fa-clock';
            $payment_text = 'Chờ thanh toán lại';
            break;
        case 'failed':
            $payment_class = 'bg-danger';
            $payment_icon = 'fa-times-circle';
            $payment_text = 'Thanh toán thất bại';
            break;
    }
?>
                                    <span class="badge <?php echo $payment_class; ?>">
                                        <i class="fas <?php echo $payment_icon; ?>"></i> <?php echo $payment_text; ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-money-bill-wave"></i> COD
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="chitiet_donhang.php?id=<?php echo $row['order_id']; ?>"
                                    class="btn btn-primary btn-sm" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($row['payment_status'] == 'pending_payment' || $row['payment_status'] == 'failed'): ?>
                                <button type="button" class="btn btn-warning btn-sm" title="Cập nhật thanh toán"
                                    onclick="openPaymentModal(<?php echo $row['order_id']; ?>)">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">Không có đơn hàng nào</td>
                        </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="updatePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật trạng thái thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="xuly_thanhtoan.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="orderIdInput">
                        <div class="mb-3">
                            <label class="form-label">Trạng thái thanh toán</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="completed">Đã thanh toán</option>
                                <option value="failed">Thanh toán lỗi</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal giữ nguyên -->

<style>
.card {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.card-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.card-text {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0;
}

.status-select {
    min-width: 160px;
    padding: 0.4rem 0.75rem;
    font-size: 0.9rem;
    border-radius: 4px;
    height: auto;
    background-color: #fff;
    border: 1px solid #dee2e6;
}

.status-select:hover {
    border-color: #6c757d;
}

.status-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    outline: none;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.badge i {
    margin-right: 4px;
}
</style>

<script>
function updateStatus(id, status) {
    if (confirm('Bạn có chắc muốn thay đổi trạng thái?')) {
        $.ajax({
            url: 'capnhat_donhang.php',
            type: 'POST',
            data: {
                id: id,
                status: status
            },
            success: function(response) {
                if (response === "Success") {
                    alert('Cập nhật trạng thái thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + response);
                }
            },
            error: function(xhr, status, error) {
                alert('Lỗi: ' + (xhr.responseText || error));
            }
        });
    }
}

// Function để cập nhật options của select
function updateSelectOptions(selectElement, currentStatus) {
    selectElement.empty(); // Xóa tất cả options hiện tại

    switch (currentStatus) {
        case 'pending':
            selectElement.append(`
                <option value="pending" selected>Đang xử lý</option>
                <option value="delivering">Đang giao hàng</option>
                <option value="completed">Đã giao</option>
            `);
            break;
        case 'delivering':
            selectElement.append(`
                <option value="delivering" selected>Đang giao hàng</option>
                <option value="completed">Đã giao</option>
            `);
            break;
        case 'completed':
            selectElement.append(`
                <option value="completed" selected>Đã giao</option>
            `);
            break;
    }
}
</script>
<script>
function openPaymentModal(orderId) {
    document.getElementById('orderIdInput').value = orderId;
    new bootstrap.Modal(document.getElementById('updatePaymentModal')).show();
}

// Hiển thị thông báo nếu có
<?php if (isset($_SESSION['success_message'])): ?>
alert("<?php echo $_SESSION['success_message']; ?>");
<?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
alert("<?php echo $_SESSION['error_message']; ?>");
<?php unset($_SESSION['error_message']); ?>
<?php endif; ?>
</script>