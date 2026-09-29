<?php
include '../pages/header.php';

$totalAmount = 0;
$total_items = 0;

// Lấy danh sách sản phẩm trong giỏ hàng
$sql = "SELECT c.*, p.product_name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.idProduct = p.product_id 
        WHERE c.username = ?
        ORDER BY c.cart_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Lấy tổng số sản phẩm trong giỏ hàng
$total_items = $result->num_rows;

// Khởi tạo biến cho mã giảm giá
$temp_discount = 0;
$coupon_code = '';
$coupon_message = '';

// Xử lý áp dụng mã giảm giá
if(isset($_POST['apply_coupon']) && !empty($_POST['coupon_code'])) {
    $coupon_code = trim($_POST['coupon_code']);
    
    // Kiểm tra mã giảm giá và trạng thái
    $sql = "SELECT * FROM coupons WHERE code = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $coupon_code);
    $stmt->execute();
    $coupon_result = $stmt->get_result();
    
    if($coupon = $coupon_result->fetch_assoc()) {
        // Kiểm tra ngày hết hạn nếu có
        $isValid = true;
        if($coupon['expiry_date'] !== null) {
            $expiry_date = strtotime($coupon['expiry_date']);
            $current_date = strtotime(date('Y-m-d H:i:s'));
            if($current_date > $expiry_date) {
                $isValid = false;
            }
        }

        // Kiểm tra số lần sử dụng tối đa
        if($isValid && $coupon['max_usage'] !== null) {
            if($coupon['current_usage'] >= $coupon['max_usage']) {
                $isValid = false;
            }
        }

        if($isValid) {
            $temp_discount = $coupon['discount'];
            $_SESSION['temp_coupon'] = [
                'code' => $coupon_code,
                'discount' => $temp_discount
            ];
            $coupon_message = '<div class="alert alert-success">Áp dụng mã giảm giá thành công! (Giảm giá sẽ được áp dụng khi thanh toán)</div>';
        } else {
            $coupon_message = '<div class="alert alert-danger">Mã giảm giá đã hết hạn hoặc đã hết lượt sử dụng!</div>';
            unset($_SESSION['temp_coupon']);
        }
    } else {
        $coupon_message = '<div class="alert alert-danger">Mã giảm giá không hợp lệ!</div>';
        unset($_SESSION['temp_coupon']);
    }
}

// Lấy giảm giá tạm thời từ session nếu có
if(isset($_SESSION['temp_coupon'])) {
    $temp_discount = $_SESSION['temp_coupon']['discount'];
    $coupon_code = $_SESSION['temp_coupon']['code'];
}
?>

<link rel="stylesheet" href="../catalog/css/style_giohang.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="cart-container">
    <div class="checkout-steps">
        <div class="step active">
            <div class="step-number">1</div>
            <div class="step-label">Giỏ hàng</div>
        </div>
        <div class="step ">
            <div class="step-number">2</div>
            <div class="step-label">Thông tin giao hàng</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-label">Phương thức thanh toán</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-label">Hoàn thành</div>
        </div>
    </div>

    <h1 class="cart-title">Giỏ hàng của bạn (<?php echo $total_items; ?> sản phẩm)</h1>

    <?php if($total_items > 0): ?>
    <!-- Form áp dụng mã giảm giá -->
    <div class="coupon-section">
        <form method="POST" class="coupon-form">
            <div class="input-group">
                <input type="text" name="coupon_code" class="form-control" placeholder="Nhập mã giảm giá"
                    value="<?php echo htmlspecialchars($coupon_code); ?>">
                <button type="submit" name="apply_coupon" class="btn-apply">Áp dụng</button>
            </div>
            <?php if($coupon_message): ?>
            <div class="coupon-message"><?php echo $coupon_message; ?></div>
            <?php endif; ?>
        </form>
        <?php
        // Lấy danh sách mã giảm giá còn hiệu lực
        $voucher_sql = "SELECT code, discount FROM coupons 
                WHERE status = 'active' 
                AND (expiry_date IS NULL OR expiry_date > NOW())
                AND (max_usage IS NULL OR current_usage < max_usage)
                LIMIT 5";
        $voucher_result = $conn->query($voucher_sql);
        if($voucher_result && $voucher_result->num_rows > 0): 
        ?>
        <div class="available-vouchers">
            <p>Mã giảm giá hiện có:</p>
            <?php while($voucher = $voucher_result->fetch_assoc()): ?>
            <span class="voucher-code">
                <?php echo $voucher['code']; ?> (-<?php echo $voucher['discount']; ?>%)
            </span>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>

    <table class="cart-table">
        <thead>
            <tr>
                <th>Hình ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php 
        while($row = mysqli_fetch_assoc($result)): 
            $subtotal = $row['quantity'] * $row['price'];
            $totalAmount += $subtotal;
        ?>
            <tr class="product-row">
                <td data-label="Hình ảnh">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>"
                        alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-img">
                </td>
                <td data-label="Tên sản phẩm" class="product-name">
                    <?php echo htmlspecialchars($row['product_name']); ?>
                </td>
                <td data-label="Đơn giá" class="price">
                    <?php echo number_format($row['price']); ?>₫
                </td>
                <td data-label="Số lượng">
                    <div class="quantity-control">
                        <button type="button" class="btn-decrease">-</button>
                        <input type="number" class="quantity-input num" value="<?php echo $row['quantity']; ?>" min="1"
                            max="99" data-id="<?php echo $row['idProduct']; ?>"
                            data-price="<?php echo $row['price']; ?>" readonly>
                        <button type="button" class="btn-increase">+</button>
                    </div>
                </td>
                <td data-label="Thành tiền" class="subtotal">
                    <?php echo number_format($subtotal); ?>₫
                </td>
                <td data-label="Thao tác">
                    <a href="xoa_giohang.php?id=<?php echo $row['idProduct']; ?>" class="delete-btn"
                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>

            <tr class="summary-row">
                <td colspan="4" data-label="Tạm tính" class="text-right">Tạm tính:</td>
                <td colspan="2" class="temp-total"><?php echo number_format($totalAmount); ?>₫</td>
            </tr>

            <?php if($temp_discount > 0): 
            $discount_amount = ($totalAmount * $temp_discount) / 100;
            $finalAmount = $totalAmount - $discount_amount;
        ?>
            <tr class="summary-row discount">
                <td colspan="4" data-label="Giảm giá" class="text-right">Giảm giá tạm tính
                    (<?php echo $temp_discount; ?>%):</td>
                <td colspan="2" class="discount-amount">-<?php echo number_format($discount_amount); ?>₫</td>
            </tr>
            <?php endif; ?>

            <tr class="total-row">
                <td colspan="4" data-label="Tổng tiền" class="text-right">Tổng tiền:</td>
                <td colspan="2" class="final-price">
                    <?php echo number_format(isset($finalAmount) ? $finalAmount : $totalAmount); ?>₫
                </td>
            </tr>
        </tbody>
    </table>

    <div class="cart-actions">
        <a href="index.php" class="btn btn-continue">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        <a href="thongtin_thanhtoan.php" class="btn btn-checkout">
            Tiếp tục thanh toán <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <?php else: ?>
    <div class="empty-cart">
        <p>Giỏ hàng của bạn đang trống</p>
        <a href="dathang.php" class="btn btn-continue">
            <i class="fas fa-arrow-left"></i> Tiếp tục mua hàng
        </a>
    </div>
    <?php endif; ?>

</div>

<style>
.coupon-section {
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.coupon-form {
    max-width: 400px;
}

.input-group {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.input-group input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn-apply {
    padding: 8px 20px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

/* Thêm style cho phần hiển thị mã */
.available-vouchers {
    margin-top: 10px;
    font-size: 14px;
}

.available-vouchers p {
    margin-bottom: 5px;
    color: #666;
}

.voucher-code {
    background: #fff;
    padding: 2px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-right: 8px;
    color: #28a745;
}

.quantity-control {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.quantity-control button {
    width: 25px;
    height: 25px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
}

.quantity-control button:hover {
    background: #f8f9fa;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 2px 5px;
}

/* Styles từ trước */
.coupon-section {
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.coupon-form {
    max-width: 400px;
}

.input-group {
    display: flex;
    gap: 10px;
}

.input-group input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn-apply {
    padding: 8px 20px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-apply:hover {
    background: #218838;
}

.coupon-message {
    margin-top: 10px;
    font-size: 14px;
}

.alert {
    padding: 10px;
    border-radius: 4px;
    margin-top: 10px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.summary-row {
    background: #f8f9fa;
}

.summary-row.discount {
    color: #28a745;
}

.discount-amount {
    color: #28a745;
    font-weight: bold;
    opacity: 0.8;
}

.preview-row {
    background: #fff;
    font-style: italic;
}

.text-muted {
    color: #6c757d;
    font-size: 0.9em;
}

.final-price {
    font-size: 1.2em;
    font-weight: bold;
    color: #dc3545;
}

/* Mobile Responsive */
@media screen and (max-width: 768px) {
    .cart-container {
        padding: 10px;
    }

    .cart-table {
        display: block;
        width: 100%;
    }

    .cart-table thead {
        display: none;
    }

    .cart-table tbody,
    .cart-table tr {
        display: block;
    }

    .cart-table td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        text-align: right;
        border: none;
    }

    .cart-table td::before {
        content: attr(data-label);
        font-weight: 500;
        text-align: left;
        margin-right: 10px;
    }

    .product-row {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
    }

    .product-img {
        max-width: 100px;
        margin: 0 auto;
    }

    .quantity-control {
        margin: 0 auto;
    }

    .btn-apply {
        width: 100%;
        margin-top: 10px;
    }

    .cart-actions {
        flex-direction: column;
        gap: 10px;
    }

    .btn {
        width: 100%;
        text-align: center;
    }

    .checkout-steps {
        display: none;
    }

    .input-group {
        flex-direction: column;
    }

    .available-vouchers {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 10px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Xử lý sự kiện thay đổi số lượng
    $(".num").change(function(e) {
        e.preventDefault();

        var sl = this.value;
        var tr = $(this).closest('tr');
        var dongia = parseInt(tr.find('.price').text().replace(/[^0-9]/g, ''));
        var tt = dongia * sl;

        // Cập nhật thành tiền cho sản phẩm
        tr.find('.subtotal').text(formatCurrency(tt));

        // Cập nhật CSDL qua AJAX
        updateCartDB($(this).data('id'), sl, dongia);

        // Tính lại tổng tiền
        updateTotalAmount();
    });

    // Xử lý nút giảm số lượng
    $('.btn-decrease').click(function() {
        var input = $(this).siblings('.num');
        var currentValue = parseInt(input.val());
        if (currentValue > 1) {
            input.val(currentValue - 1).trigger('change');
        }
    });

    // Xử lý nút tăng số lượng
    $('.btn-increase').click(function() {
        var input = $(this).siblings('.num');
        var currentValue = parseInt(input.val());
        if (currentValue < 99) {
            input.val(currentValue + 1).trigger('change');
        }
    });

    // Hàm cập nhật database
    function updateCartDB(productId, quantity, price) {
        $.ajax({
            url: 'capnhat_giohang.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                price: price
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (!result.success) {
                        alert(result.message || 'Có lỗi xảy ra');
                    }
                } catch (e) {
                    console.error(e);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi cập nhật giỏ hàng');
            }
        });
    }

    // Hàm cập nhật tổng tiền
    function updateTotalAmount() {
        var total = 0;

        // Tính tổng tiền từ các sản phẩm
        $('.product-row').each(function() {
            var subtotal = parseInt($(this).find('.subtotal').text().replace(/[^0-9]/g, ''));
            total += subtotal;
        });

        // Cập nhật tạm tính
        $('.temp-total').text(formatCurrency(total));

        // Tính giảm giá nếu có
        if ($('.summary-row.discount').length) {
            var discountPercent = parseInt($('.summary-row.discount').text().match(/\d+/)[0]);
            var discount = (total * discountPercent) / 100;
            $('.discount-amount').text('-' + formatCurrency(discount));
            total -= discount;
        }

        // Cập nhật tổng tiền cuối cùng
        $('.final-price').text(formatCurrency(total));
    }

    // Hàm format tiền tệ
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
    }

    // Validate input số lượng
    $('.num').on('input', function() {
        var value = $(this).val();
        if (value < 1) $(this).val(1);
        if (value > 99) $(this).val(99);
    });
});
</script>

<?php include '../pages/footer.php'; ?>