<?php 
include '../staff/header.php';    

// Lấy thống kê từ database
$sql_orders = "SELECT COUNT(*) as total FROM orders";
$result_orders = mysqli_query($conn, $sql_orders);
$total_orders = mysqli_fetch_assoc($result_orders)['total'];

$sql_sales = "SELECT SUM(final_amount) as total FROM orders WHERE status = 'completed'";
$result_sales = mysqli_query($conn, $sql_sales);
$total_sales = mysqli_fetch_assoc($result_sales)['total']; 
$total_sales = number_format($total_sales, 0, ',', '.') . ' VNĐ';

$sql_contact = "SELECT COUNT(*) as total FROM contact";
$result_contact = mysqli_query($conn, $sql_contact);
$total_contact = mysqli_fetch_assoc($result_contact)['total'];

$sql_staff = "SELECT COUNT(*) as total FROM users WHERE rule = 3";
$result_staff = mysqli_query($conn, $sql_staff);
$total_staff = mysqli_fetch_assoc($result_staff)['total'];

// Lấy dữ liệu cho biểu đồ - Sửa lại cú pháp SQL
$sql_chart_data = "SELECT 
                    DATE_FORMAT(created_at, '%m/%Y') as month,
                    COUNT(*) as orders,
                    COALESCE(SUM(final_amount), 0) as revenue 
                   FROM orders 
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                   AND status = 'completed'
                   GROUP BY DATE_FORMAT(created_at, '%m/%Y')
                   ORDER BY MIN(created_at) ASC";

$result_chart = mysqli_query($conn, $sql_chart_data);
if (!$result_chart) {
    echo "Error: " . mysqli_error($conn);
    exit;
}

$chart_data = array();
while($row = mysqli_fetch_assoc($result_chart)) {
    $chart_data[] = $row;
}

// Lấy đơn hàng mới nhất
$sql_recent_orders = "SELECT o.*, u.fullname 
                     FROM orders o
                     JOIN users u ON o.idUser = u.id 
                     ORDER BY o.created_at LIMIT 10";
$result_recent_orders = mysqli_query($conn, $sql_recent_orders);

// Debug để kiểm tra
if (!$result_recent_orders) {
    echo "Error in recent orders query: " . mysqli_error($conn);
    exit;
}
?>

<link rel="stylesheet" href="../staff/css/style.css">

<div class="stats-container">
    <div class="stat-box">
        <h3>TỔNG ĐƠN HÀNG</h3>
        <div class="stat-value"><?php echo $total_orders; ?></div>
        <a href="index_donhang.php" class="view-more">Xem thêm...</a>
    </div>

    <div class="stat-box">
        <h3>TỔNG DOANH THU</h3>
        <div class="stat-value"><?php echo $total_sales; ?></div>
        <a href="index_donhang.php" class="view-more">Xem thêm...</a>
    </div>
    <div class="stat-box">
        <h3>TỔNG LIÊN HỆ</h3>
        <div class="stat-value"><?php echo $total_contact; ?> Lượt</div>
        <a href="index_lienhe.php" class="view-more">Xem thêm...</a>
    </div>

</div>

<div class="orders-table">
    <h3>Đơn hàng gần đây</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = mysqli_fetch_assoc($result_recent_orders)): ?>
            <tr>
                <td><?php echo $order['order_id']; ?></td> <!-- Đổi id thành order_id -->
                <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                <td>
                    <span class="status-<?php echo $order['status']; ?>">
                        <?php
                       switch ($order['status']) {  // Đổi trangthai thành status
                           case 'pending':
                               echo "Đang xử lý";
                               break;
                           case 'delivering':
                               echo "Đang giao hàng";
                               break;
                           case 'completed':
                               echo "Đã giao thành công";
                               break;
                           case 'canceled':
                               echo "Đơn hàng đã bị hủy";
                               break;
                           default:
                               echo "Đang xử lý";
                               break;
                       }
                       ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>