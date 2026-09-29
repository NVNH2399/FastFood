<?php
ob_start();
session_start();

if(!isset($_SESSION['username']) || !isset($_SESSION['shipping_info'])) {
    header("Location: thongtin_thanhtoan.php");
    exit;
}

include '../pages/header.php';

$username = $_SESSION['username'];
$shipping_info = $_SESSION['shipping_info'];

// Lấy thông tin giỏ hàng
$sql = "SELECT c.*, p.product_name, p.price, p.image, p.product_id 
        FROM cart c 
        JOIN products p ON c.idProduct = p.product_id 
        WHERE c.username = ?
        ORDER BY c.cart_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Tính tổng tiền
$totalAmount = 0;
$cart_items = [];
while($row = $result->fetch_assoc()) {
    $totalAmount += ($row['quantity'] * $row['price']);
    $cart_items[] = $row;
}

// Xử lý giảm giá
$discount = 0;
$discount_percent = 0;
$finalAmount = $totalAmount;

if(isset($_SESSION['applied_coupon'])) {
    $discount_percent = $_SESSION['applied_coupon']['discount'];
    $discount = ($totalAmount * $discount_percent) / 100;
    $finalAmount = $totalAmount - $discount;
}

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    
    // Lấy user ID và thông tin
    $sql = "SELECT id, mail, fullname FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if($user) {
        $conn->begin_transaction();
        
        try {
            // Tạo đơn hàng mới
$sql = "INSERT INTO orders (idUser, idProduct, address, phone, status, total_amount, discount, final_amount, payment_method, payment_status) 
VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);
$firstProductId = $cart_items[0]['product_id'];

$stmt->bind_param("iissddds", 
$user['id'],
$firstProductId,
$shipping_info['address'],
$shipping_info['phone'],
$totalAmount,
$discount,
$finalAmount,
$payment_method
);
            $stmt->execute();
            $order_id = $conn->insert_id;

            // Thêm chi tiết đơn hàng
            $sql = "INSERT INTO order_details (idOrder, idProduct, quantity, unit_price) 
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

foreach($cart_items as $item) {
    $stmt->bind_param("iiid", 
        $order_id,
        $item['product_id'],
        $item['quantity'],
        $item['price']
    );
    $stmt->execute();
}

            // Xử lý mã giảm giá nếu có
            if(isset($_SESSION['applied_coupon'])) {
                $coupon_code = $_SESSION['applied_coupon']['code'];
                $sql = "SELECT coupon_id FROM coupons WHERE code = ? AND status = 'active'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $coupon_code);
                $stmt->execute();
                $coupon_result = $stmt->get_result();
                $coupon = $coupon_result->fetch_assoc();
                
                if($coupon) {
                    $sql = "INSERT INTO order_coupons (idOrder, idCoupon, percent) 
                            VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iii", 
                        $order_id,
                        $coupon['coupon_id'],
                        $discount_percent
                    );
                    $stmt->execute();
                }
            }

            // Xóa giỏ hàng
            $sql = "DELETE FROM cart WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();

            $conn->commit();

            // Xóa thông tin shipping khỏi session
            unset($_SESSION['shipping_info']);
            if(isset($_SESSION['applied_coupon'])) {
                unset($_SESSION['applied_coupon']);
            }

            // Gửi email theo phương thức thanh toán
            if($payment_method == 'cod') {
                $body = '
                <div style="background-color: #f4f4f4; padding: 20px;">
                    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 5px;">
                        <h1 style="color: #333;">Xác Nhận Đơn Hàng #' . $order_id . '</h1>
                        ' . generateOrderDetailsHtml($cart_items, $totalAmount, $discount, $finalAmount) . '
                        <div style="margin-top: 20px;">
                            <h3>Thông tin giao hàng:</h3>
                            <p>Địa chỉ: ' . htmlspecialchars($shipping_info['diachi']) . '</p>
                            <p>Số điện thoại: ' . htmlspecialchars($shipping_info['sdt']) . '</p>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                                <h3>Thanh toán khi nhận hàng (COD)</h3>
                                <p>Bạn sẽ thanh toán số tiền ' . number_format($finalAmount) . '₫ khi nhận hàng</p>
                            </div>
                        </div>
                    </div>
                </div>';
                
                // Gửi email xác nhận
                sendOrderConfirmationEmail($user['mail'], $order_id, $body);
                
                header("Location: hoantat_thanhtoan.php?order_id=" . $order_id);
            } else {
                header("Location: thanhtoan_momo.php?order_id=" . $order_id);
            }
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Có lỗi xảy ra: " . $e->getMessage();
        }
    }
}

// Reset result để hiển thị giỏ hàng
$result->data_seek(0);
?>

<link rel="stylesheet" href="../catalog/css/style_thanhtoan.css">

<div class="container">
    <div class="checkout-steps">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-label">Giỏ hàng</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-label">Thông tin giao hàng</div>
        </div>
        <div class="step active">
            <div class="step-number">3</div>
            <div class="step-label">Phương thức thanh toán</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-label">Hoàn thành</div>
        </div>
    </div>

    <h2>Xác nhận đơn hàng và chọn phương thức thanh toán</h2>

    <!-- Hiển thị thông tin giao hàng -->
    <div class="shipping-info">
        <h3>Thông tin giao hàng</h3>
        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($shipping_info['address']); ?></p>
        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($shipping_info['phone']); ?></p>
        <a href="thongtin_thanhtoan.php" class="edit-btn">Chỉnh sửa</a>
    </div>

    <!-- Hiển thị đơn hàng -->
    <div class="order-summary">
        <div class="product-list">
            <?php while($item = $result->fetch_assoc()): 
                $subtotal = $item['quantity'] * $item['price'];
            ?>
            <div class="product-item">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>"
                        alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                </div>
                <div class="product-info">
                    <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <div class="quantity-info">Số lượng: <strong><?php echo $item['quantity']; ?></strong></div>
                    <div class="price-info">
                        <span class="unit-price"><?php echo number_format($item['price']); ?>₫</span> x
                        <span class="quantity"><?php echo $item['quantity']; ?></span> =
                        <span class="subtotal"><?php echo number_format($subtotal); ?>₫</span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="price-summary">
            <div class="price-row">
                <span>Tạm tính:</span>
                <span class="subtotal-price"><?php echo number_format($totalAmount); ?>₫</span>
            </div>

            <?php if($discount > 0): ?>
            <div class="price-row discount">
                <span>Giảm giá (<?php echo $discount_percent; ?>%):</span>
                <span class="discount-amount">-<?php echo number_format($discount); ?>₫</span>
            </div>
            <?php endif; ?>

            <div class="price-row total">
                <span>Tổng tiền:</span>
                <span class="final-price"><?php echo number_format($finalAmount); ?>₫</span>
            </div>
        </div>
    </div>

    <!-- Form chọn phương thức thanh toán -->
    <form method="POST" class="payment-form" onsubmit="return confirmPayment()">
        <div class="payment-methods">
            <h3>Phương thức thanh toán</h3>

            <div class="payment-options">
                <div class="payment-option">
                    <input type="radio" id="cod" name="payment_method" value="cod" checked>
                    <label for="cod">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Thanh toán khi nhận hàng</span>
                        <small>Thanh toán bằng tiền mặt khi nhận hàng</small>
                    </label>
                </div>

                <div class="payment-option">
                    <input type="radio" id="momo" name="payment_method" value="momo">
                    <label for="momo">
                        <img src="../upload/momo-icon.png" alt="MoMo" width="30">
                        <span>Thanh toán qua MoMo ATM</span>
                        <small>Thanh toán bằng thẻ ATM/Tài khoản ngân hàng qua MoMo</small>
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="submit-btn">Đặt hàng</button>
    </form>
</div>

<style>
.shipping-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    border: 1px dashed #0056b3;
}

.shipping-info h3 {
    margin-top: 0;
    color: #333;
}

.edit-btn {
    display: inline-block;
    padding: 5px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    margin-top: 10px;
}

.edit-btn:hover {
    background: #0056b3;
}

.payment-methods {
    margin: 30px 0;
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.payment-option {
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    transition: border-color 0.3s;
}

.payment-option:hover {
    border-color: #007bff;
}

.payment-option input[type="radio"] {
    margin-right: 10px;
}

.payment-option label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.payment-option small {
    display: block;
    color: #666;
    margin-top: 5px;
    margin-left: 25px;
}

.submit-btn {
    width: 100%;
    padding: 15px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s;
}

.submit-btn:hover {
    background: #218838;
}
</style>

<script>
function confirmPayment() {
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    if (paymentMethod === 'momo') {
        return confirm('Bạn sẽ được chuyển đến trang thanh toán MoMo. Tiếp tục?');
    }

    return true;
}

document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
    });
});
</script>

<?php
// Helper function để tạo HTML chi tiết đơn hàng cho email
function generateOrderDetailsHtml($cart_items, $totalAmount, $discount, $finalAmount) {
    $html = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr style="background: #f8f9fa;">
                    <th style="padding: 10px; border: 1px solid #ddd;">Sản phẩm</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Số lượng</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Đơn giá</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Thành tiền</th>
                </tr>';
    
    foreach($cart_items as $item) {
        $subtotal = $item['quantity'] * $item['price'];
        $html .= '<tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($item['product_name']) . '</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">' . $item['quantity'] . '</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">' . number_format($item['price']) . '₫</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">' . number_format($subtotal) . '₫</td>
                </tr>';
    }
    
    $html .= '<tr>
                <td colspan="3" style="padding: 10px; border: 1px solid #ddd; text-align: right;">Tạm tính:</td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">' . number_format($totalAmount) . '₫</td>
            </tr>';
            
    if($discount > 0) {
        $html .= '<tr>
                    <td colspan="3" style="padding: 10px; border: 1px solid #ddd; text-align: right;">Giảm giá:</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right; color: #28a745;">-' . number_format($discount) . '₫</td>
                </tr>';
    }
    
    $html .= '<tr>
                <td colspan="3" style="padding: 10px; border: 1px solid #ddd; text-align: right;"><strong>Tổng tiền:</strong></td>
                <td style="padding: 10px; border: 1px solid #ddd; text-align: right; color: #dc3545;"><strong>' . number_format($finalAmount) . '₫</strong></td>
            </tr>
        </table>';
    
    return $html;
}

// Helper function để gửi email
function sendOrderConfirmationEmail($to, $order_id, $body) {
    $url = 'https://namhoai.id.vn/api/v1/send-mail.php';
    $data = [
        'to' => $to,
        'subject' => 'Xác nhận đơn hàng #' . $order_id,
        'body' => $body
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: xY7tG9wAq4Zr5vB1nP2m'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

include '../pages/footer.php';
ob_end_flush();
?>