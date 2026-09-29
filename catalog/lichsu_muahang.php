<?php
$title = "Lịch sử mua hàng";
include '../pages/header.php';

if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// Lấy ID của user
$sql = "SELECT * FROM users WHERE username = ?"; 
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$user = mysqli_stmt_get_result($stmt)->fetch_assoc();

// Lấy lịch sử đơn hàng với thời gian hủy
$sql_orders = "SELECT o.*, od.quantity, od.unit_price, od.total_amount,
               p.product_name, p.image, u.fullname,
               o.payment_method, o.payment_status,
               TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_passed
               FROM orders o
               JOIN order_details od ON o.order_id = od.idOrder
               JOIN products p ON od.idProduct = p.product_id
               JOIN users u ON o.idUser = u.id 
               WHERE o.idUser = ? 
               ORDER BY o.created_at DESC";

$stmt = mysqli_prepare($conn, $sql_orders);
mysqli_stmt_bind_param($stmt, "i", $user['id']);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>

<link rel="stylesheet" href="../catalog/css/style_lichsumuahang.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<div class="container">
    <div class="group">
        <h2 class="lichsu-title">Lịch sử đơn hàng</h2>

        <?php if(mysqli_num_rows($orders) > 0):
    $order_items = array();
    $order_info = array();
    
    while($order = mysqli_fetch_assoc($orders)) {
        if (!isset($order_items[$order['order_id']])) {
            $order_items[$order['order_id']] = array();
            $order_info[$order['order_id']] = array(
                'order_id' => $order['order_id'],
                'created_at' => $order['created_at'],
                'status' => $order['status'],
                'address' => $order['address'],
                'phone' => $order['phone'],
                'total_amount' => $order['total_amount'],
                'discount' => $order['discount'],
                'final_amount' => $order['final_amount'],
                'payment_method' => $order['payment_method'],
                'payment_status' => $order['payment_status'],
                'minutes_passed' => $order['minutes_passed']
            );
        }
        $order_items[$order['order_id']][] = $order;
    }

    foreach($order_info as $order_id => $order): 
        $canCancel = $order['status'] == 'pending' && 
                    $order['minutes_passed'] <= 5 && 
                    ($order['payment_method'] == 'cod' || 
                    ($order['payment_method'] == 'momo' && $order['payment_status'] == 'completed'));
?>
        <div class="order-card" data-order-time="<?php echo $order['created_at']; ?>">
            <div class="order-header">
                <div>
                    <span class="order-id">Đơn hàng #<?php echo $order_id; ?></span>
                    <span class="order-date">
                        <i class="fas fa-clock"></i>
                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                    </span>
                </div>
                <div class="order-statuses">
                    <!-- Trạng thái đơn hàng -->
                    <span class="order-status status-<?php echo $order['status']; ?>">
                        <?php
        switch($order['status']) {
            case 'completed':
                echo '<i class="fas fa-box-check"></i> Đã giao hàng';
                break;
            case 'delivering':
                    echo '<i class="fas fa-truck-fast"></i> Đang giao hàng';
                    break;
            case 'canceled':
                echo '<i class="fas fa-times"></i> Đã hủy';
                break;
            case 'pending':
            default:
                echo '<i class="fas fa-clock"></i> Đang xử lý';
                break;
        }
        ?>
                    </span>

                    <!-- Phương thức thanh toán và trạng thái -->
                    <?php if($order['payment_method'] == 'momo'): ?>
                    <span class="payment-method-momo">
                        <i class="fas fa-credit-card"></i> MoMo
                    </span>
                    <span class="payment-status status-<?php echo $order['payment_status']; ?>">
                        <?php 
            switch($order['payment_status']) {
                case 'completed':
                    echo '<i class="fas fa-check-circle"></i> Đã thanh toán';
                    break;
                case 'pending_payment':
                    echo 'Chờ thanh toán lại';
                    break;
                case 'failed':
                    echo '<i class="fas fa-times-circle"></i> Thanh toán thất bại';
                    break;
                case 'pending':
                default:
                    echo '<i class="fas fa-clock"></i> Chờ thanh toán';
                    break;
            }
            ?>
                    </span>
                    <?php else: ?>
                    <span class="payment-method-cod">
                        <i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Danh sách sản phẩm -->
            <div class="order-content">
                <div class="product-list">
                    <?php foreach($order_items[$order_id] as $item): ?>
                    <div class="product-item">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        </div>
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                            <div class="quantity-info">Số lượng: <strong><?php echo $item['quantity']; ?></strong></div>
                            <div class="price-info">
                                <span class="unit-price"><?php echo number_format($item['unit_price']); ?>₫</span> x
                                <span class="quantity"><?php echo $item['quantity']; ?></span> =
                                <span class="subtotal"><?php echo number_format($item['total_amount']); ?>₫</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Footer đơn hàng -->
            <div class="order-footer">
                <div class="shipping-info">
                    <div><i class="fas fa-map-marker-alt"></i> Địa chỉ:
                        <?php echo htmlspecialchars($order['address']); ?></div>
                    <div><i class="fas fa-phone"></i> SĐT: <?php echo htmlspecialchars($order['phone']); ?></div>
                </div>

                <div class="price-summary">
                    <div class="price-row">
                        <span>Tạm tính:</span>
                        <span class="subtotal-price"><?php echo number_format($order['total_amount']); ?>₫</span>
                    </div>

                    <?php if($order['discount'] > 0): ?>
                    <div class="price-row discount">
                        <span>Giảm giá:</span>
                        <span class="discount-amount">-<?php echo number_format($order['discount']); ?>₫</span>
                    </div>
                    <?php endif; ?>

                    <div class="price-row total">
                        <span>Tổng tiền:</span>
                        <span class="final-price"><?php echo number_format($order['final_amount']); ?>₫</span>
                    </div>
                </div>


                <?php if($canCancel): ?>
                <div class="order-actions">
                    <div class="countdown" id="countdown-<?php echo $order_id; ?>">
                        Thời gian hủy đơn còn: <span class="time-left">5:00</span>
                    </div>
                    <button class="btn-action btn-outline" onclick="cancelOrder(<?php echo $order_id; ?>)"
                        id="cancel-btn-<?php echo $order_id; ?>">
                        <i class="fas fa-times"></i> Hủy đơn hàng
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="no-orders">
            <p>Bạn chưa có đơn hàng nào</p>
            <a href="dathang.php" class="btn-action btn-primary" style="text-decoration: none">
                <i class="fas fa-shopping-cart"></i> Mua sắm ngay
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>



<script>
function startCountdown(orderId, initialMinutes) {
    const countdownElement = document.getElementById(`countdown-${orderId}`);
    const cancelButton = document.getElementById(`cancel-btn-${orderId}`);
    const timeLeftElement = countdownElement.querySelector('.time-left');

    let timeLeft = Math.min(initialMinutes * 60, 300); // Tối đa 5 phút

    const timer = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;

        if (timeLeft <= 0) {
            clearInterval(timer);
            // Disable nút hủy
            cancelButton.classList.add('disabled');
            cancelButton.disabled = true;
            countdownElement.innerHTML = 'Đã hết thời gian hủy đơn';
            // Tự động refresh để cập nhật trạng thái
            // location.reload();
            if (!hasReloaded && !sessionStorage.getItem(`hasReloaded_${orderId}`)) {
                hasReloaded = true;
                sessionStorage.setItem(`hasReloaded_${orderId}`, 'true');
                setTimeout(() => {
                    location.reload();
                }, 1000); // Delay 1 giây trước khi reload
            }
        } else {
            timeLeftElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        timeLeft--;
    }, 1000);

    // Nếu đã hết thời gian từ đầu
    if (initialMinutes <= 0) {
        cancelButton.classList.add('disabled');
        cancelButton.disabled = true;
        countdownElement.innerHTML = 'Đã hết thời gian hủy đơn';
    }
}

function cancelOrder(orderId) {
    const cancelButton = document.getElementById(`cancel-btn-${orderId}`);

    // Kiểm tra nút có bị disabled không
    if (cancelButton.disabled || cancelButton.classList.contains('disabled')) {
        alert('Đã hết thời gian hủy đơn hàng');
        return;
    }

    if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
        return;
    }

    fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Kiểm tra trạng thái thanh toán
                if (data.payment_status === 'completed') {
                    alert(
                        'Chúng tôi sẽ liên hệ với bạn để hoàn tiền'
                    ); // nên làm gửi mail khi bấm hủy mà payment_status == completed
                } else {
                    alert('Hủy đơn hàng thành công');
                }
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi hủy đơn hàng');
        });
}


// Khởi tạo countdown cho các đơn mới
document.addEventListener('DOMContentLoaded', () => {
    const orders = document.querySelectorAll('.order-card');
    orders.forEach(order => {
        const orderId = order.querySelector('.order-id').textContent.split('#')[1];
        const orderTime = new Date(order.dataset.orderTime);
        const now = new Date();
        const diffMinutes = Math.floor((now - orderTime) / (1000 * 60));

        if (diffMinutes <= 5) {
            startCountdown(orderId, 5 - diffMinutes);
        }
    });
});
</script>

<?php include '../pages/footer.php'; ?>