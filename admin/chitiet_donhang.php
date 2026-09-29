<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

if(!isset($_GET['id'])) {
    echo "<script>alert('Không tìm thấy đơn hàng!'); window.location.href='index_donhang.php';</script>";
    exit();
}

$order_id = (int)$_GET['id'];

// Lấy thông tin đơn hàng và khách hàng
$sql = "SELECT o.*, u.fullname, u.username, u.mail
        FROM orders o
        JOIN users u ON o.idUser = u.id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if(!$order) {
    echo "<script>alert('Không tìm thấy đơn hàng!'); window.location.href='index_donhang.php';</script>";
    exit();
}

// Lấy chi tiết đơn hàng
$sql = "SELECT od.*, p.product_name, p.image
        FROM order_details od
        JOIN products p ON od.idProduct = p.product_id
        WHERE od.idOrder = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Lấy thông tin mã giảm giá
$sql = "SELECT c.code, oc.percent
        FROM order_coupons oc
        JOIN coupons c ON oc.idCoupon = c.coupon_id
        WHERE oc.idOrder = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$coupon = $stmt->get_result()->fetch_assoc();
?>

<div class="container-fluid">
    <h2 class="text-center my-4">CHI TIẾT ĐƠN HÀNG #<?php echo $order_id; ?></h2>

    <!-- Thông tin đơn hàng -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Thông Tin Đơn Hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Thời gian:</strong>
                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Trạng thái đơn hàng:</strong>
                        <?php 
                        $status_class = '';
                        $status_text = '';
                        switch ($order['status']) {
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
                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Phương thức thanh toán:</strong>
                        <span class="badge bg-secondary">
                            <?php echo $order['payment_method'] == 'cod' ? 'COD' : 'Momo'; ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Trạng thái thanh toán:</strong>
                        <?php 
                        $payment_class = '';
                        $payment_text = '';
                        switch ($order['payment_status']) {
                            case 'completed':
                                $payment_class = 'bg-success';
                                $payment_text = 'Đã thanh toán';
                                break;
                            case 'pending':
                                $payment_class = 'bg-warning';
                                $payment_text = 'Chưa thanh toán';
                                break;
                            case 'failed':
                                $payment_class = 'bg-danger';
                                $payment_text = 'Thanh toán lỗi';
                                break;
                        }
                        ?>
                        <span class="badge <?php echo $payment_class; ?>"><?php echo $payment_text; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Thông Tin Khách Hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Họ tên:</strong>
                        <?php echo htmlspecialchars($order['fullname']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <?php echo htmlspecialchars($order['mail']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Số điện thoại:</strong>
                        <?php echo htmlspecialchars($order['phone']); ?>
                    </div>
                    <div class="mb-3">
                        <strong>Địa chỉ:</strong>
                        <?php echo htmlspecialchars($order['address']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chi tiết sản phẩm -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">Chi Tiết Sản Phẩm</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="me-3"
                                        style="width: 50px; height: 50px; object-fit: cover;">
                                    <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo number_format($item['unit_price']); ?>₫</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['quantity'] * $item['unit_price']); ?>₫</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                            <td><?php echo number_format($order['total_amount']); ?>₫</td>
                        </tr>
                        <?php if($coupon): ?>
                        <tr>
                            <td colspan="3" class="text-end">
                                <strong>Giảm giá:</strong>
                                <span class="text-muted">(Mã: <?php echo $coupon['code']; ?> -
                                    <?php echo $coupon['percent']; ?>%)</span>
                            </td>
                            <td class="text-success">-<?php echo number_format($order['discount']); ?>₫</td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Tổng tiền:</strong></td>
                            <td class="text-danger">
                                <strong><?php echo number_format($order['final_amount']); ?>₫</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Nút thao tác -->
    <div class="text-center mb-4">
        <a href="index_donhang.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
    </div>
</div>