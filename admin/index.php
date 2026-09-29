<?php
include './header.php';

// Lấy thống kê từ database
$sql_orders = "SELECT COUNT(*) as total FROM orders";
$result_orders = mysqli_query($conn, $sql_orders);
$total_orders = mysqli_fetch_assoc($result_orders)['total'];

$sql_sales = "SELECT SUM(final_amount) as total FROM orders WHERE status = 'completed'";
$result_sales = mysqli_query($conn, $sql_sales);
$total_sales = mysqli_fetch_assoc($result_sales)['total'];
// Định dạng số tiền với dấu phẩy ngăn cách hàng nghìn và thêm đơn vị VNĐ
$total_sales = number_format($total_sales, 0, ',', '.') . ' VNĐ';

$sql_customers = "SELECT COUNT(*) as total FROM users WHERE rule = 2";
$result_customers = mysqli_query($conn, $sql_customers);
$total_customers = mysqli_fetch_assoc($result_customers)['total'];

// Lấy dữ liệu cho biểu đồ
$sql_chart = "SELECT 
    DATE_FORMAT(created_at, '%m/%Y') as month,
    COUNT(*) as orders,
    COALESCE(SUM(final_amount), 0) as revenue
FROM orders 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
AND status = 'completed'
GROUP BY DATE_FORMAT(created_at, '%m/%Y')
ORDER BY created_at ASC";

$result_chart = mysqli_query($conn, $sql_chart);
$chart_data = array();
while($row = mysqli_fetch_assoc($result_chart)) {
    $chart_data[] = $row;
}

// Lấy đơn hàng mới nhất
$sql_recent_orders = "SELECT orders.*, users.fullname 
                     FROM orders 
                     JOIN users ON orders.idUser = users.id 
                     ORDER BY created_at DESC LIMIT 10";
$result_recent_orders = mysqli_query($conn, $sql_recent_orders);
?>

<link rel="stylesheet" href="../admin/css/style.css">

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
        <h3>TỔNG KHÁCH HÀNG</h3>
        <div class="stat-value"><?php echo $total_customers; ?></div>
        <a href="index_user.php" class="view-more">Xem thêm...</a>
    </div>
</div>

<div class="charts-container">
    <div class="chart-box">
        <h3>Thống kê đơn hàng theo tháng</h3>
        <canvas id="ordersChart"></canvas>
    </div>
    <div class="chart-box">
        <h3>Thống kê doanh thu theo tháng</h3>
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<div class="orders-table">
    <h3>Đơn hàng gần đây</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Địa chỉ</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = mysqli_fetch_assoc($result_recent_orders)): ?>
            <tr>
                <td><?php echo $order['order_id']; ?></td>
                <td><?php echo htmlspecialchars($order['fullname']); ?></td>
                <td><?php echo htmlspecialchars($order['address']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                <td>
                    <span class="status-<?php echo $order['status']; ?>">
                        <?php
                        switch ($order['status']) {
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Truyền dữ liệu từ PHP sang JavaScript
const chartData = <?php echo json_encode($chart_data); ?>;
</script>
<script src="../admin/js/dashboard.js"></script>