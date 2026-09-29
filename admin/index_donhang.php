<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Lấy thống kê đơn hàng
$count_sql = "SELECT 
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'delivering' THEN 1 ELSE 0 END) as delivering_count,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled_count,
    COUNT(*) as total_count
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
                            <th>Thanh toán</th>
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
                                <?php
                                $status_class = '';
                                $status_text = '';
                                switch ($row['status']) {
                                    case 'pending':
                                        $status_class = 'bg-warning';
                                        $status_text = 'Đang xử lý';
                                        break;
                                    case 'delivering':
                                        $status_class = 'bg-info';
                                        $status_text = 'Đang giao hàng';
                                        break;
                                    case 'completed':
                                        $status_class = 'bg-success';
                                        $status_text = 'Đã giao thành công';
                                        break;
                                    case 'canceled':
                                        $status_class = 'bg-danger';
                                        $status_text = 'Đã hủy';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    <?php
                                    echo $row['payment_method'] == 'cod' ? 'COD' : 'Momo';
                                    ?>
                                </div>
                                <?php
$payment_class = '';
$payment_text = '';
switch ($row['payment_status']) {
    case 'completed':
        $payment_class = 'bg-success';
        $payment_text = 'Đã thanh toán';
        break;
    case 'pending':
        $payment_class = 'bg-warning';
        $payment_text = 'Chưa thanh toán';
        break;
    case 'pending_payment':
        $payment_class = 'bg-warning';
        $payment_text = 'Chờ thanh toán lại';
        break;
    case 'failed':
        $payment_class = 'bg-danger';
        $payment_text = 'Thanh toán lỗi';
        break;
    default:
        $payment_class = 'bg-secondary';
        $payment_text = 'Không xác định';
}
?>
                                <span class="badge <?php echo $payment_class; ?>">
                                    <?php echo $payment_text; ?>
                                </span>
                            </td>
                            <td>
                                <a href="chitiet_donhang.php?id=<?php echo $row['order_id']; ?>"
                                    class="btn btn-primary btn-sm" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="9" class="text-center">Không có đơn hàng nào</td>
                        </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- <div class="modal fade" id="updatePaymentModal" tabindex="-1">
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
    </div> -->
</div>

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

.badge {
    padding: 0.5em 0.75em;
}
</style>

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