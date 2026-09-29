<?php
ob_start();
session_start();

if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

include '../pages/header.php';

$username = $_SESSION['username'];

// Kiểm tra giỏ hàng có sản phẩm không
$check_sql = "SELECT COUNT(*) as count FROM cart WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$cart_count = $check_result->fetch_assoc()['count'];

if($cart_count == 0) {
    $_SESSION['error'] = "Giỏ hàng của bạn đang trống!";
    header("Location: giohang.php");
    exit;
}

// Lấy thông tin user và danh sách địa chỉ
$user_sql = "SELECT id FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$address_sql = "SELECT * FROM addresses WHERE idUser = ? AND status = 'active' ORDER BY is_default DESC, created_at DESC";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("i", $user['id']);
$address_stmt->execute();
$addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy thông tin giỏ hàng và tính tổng tiền
$sql = "SELECT c.*, p.product_name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.idProduct = p.product_id 
        WHERE c.username = ?
        ORDER BY c.cart_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Tính tổng tiền và lưu items
$totalAmount = 0;
$cart_items = [];
while($row = $result->fetch_assoc()) {
    $total_item = $row['quantity'] * $row['price'];
    $totalAmount += $total_item;
    $row['total_item'] = $total_item;
    $cart_items[] = $row;
}

// Xử lý giảm giá
$discount = 0;
$discount_percent = 0;
$finalAmount = $totalAmount;

if(isset($_SESSION['temp_coupon'])) {
    $discount_percent = $_SESSION['temp_coupon']['discount'];
    $discount = ($totalAmount * $discount_percent) / 100;
    $finalAmount = $totalAmount - $discount;
    $_SESSION['applied_coupon'] = $_SESSION['temp_coupon'];
    unset($_SESSION['temp_coupon']);
}
elseif(isset($_SESSION['applied_coupon'])) {
    $discount_percent = $_SESSION['applied_coupon']['discount'];
    $discount = ($totalAmount * $discount_percent) / 100;
    $finalAmount = $totalAmount - $discount;
}

// Xử lý form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['address_type']) && $_POST['address_type'] === 'existing') {
        // Xử lý khi chọn địa chỉ có sẵn
        $selected_address_id = $_POST['existing_address'];
        
        $get_address_sql = "SELECT address, phone FROM addresses 
                           WHERE address_id = ? AND idUser = ? AND status = 'active'";
        $get_address_stmt = $conn->prepare($get_address_sql);
        $get_address_stmt->bind_param("ii", $selected_address_id, $user['id']);
        $get_address_stmt->execute();
        $selected_address = $get_address_stmt->get_result()->fetch_assoc();
        
        if($selected_address) {
            $_SESSION['shipping_info'] = [
                'address' => $selected_address['address'],
                'phone' => $selected_address['phone'],
                'address_type' => 'existing',
                'address_id' => $selected_address_id
            ];
            header("Location: phuongthuc_thanhtoan.php");
            exit;
        }
    } else {
        // Xử lý khi nhập địa chỉ mới
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        
        if (empty($address) || empty($phone)) {
            $error = "Vui lòng nhập đầy đủ thông tin";
        } else if (!preg_match('/^0\d{9}$/', $phone)) {
            $error = "Số điện thoại không hợp lệ";
        } else {
            $_SESSION['shipping_info'] = [
                'address' => $address,
                'phone' => $phone,
                'address_type' => 'new'
            ];
            header("Location: phuongthuc_thanhtoan.php");
            exit;
        }
    }
}

// Lấy lại thông tin đã nhập nếu có
$saved_info = isset($_SESSION['shipping_info']) ? $_SESSION['shipping_info'] : null;

?>

<!-- HTML Structure -->
<div class="container">
    <div class="checkout-steps">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-label">Giỏ hàng</div>
        </div>
        <div class="step active">
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

    <div class="checkout-container">
        <!-- Form địa chỉ giao hàng -->
        <div class="shipping-form-container">
            <h2>Thông tin giao hàng</h2>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="checkout-form" onsubmit="return validateForm()">
                <div class="address-selection">
                    <div class="address-type-tabs">
                        <label class="tab-label">
                            <input type="radio" name="address_type" value="existing"
                                <?php echo !empty($addresses) ? 'checked' : 'disabled'; ?>>
                            Chọn từ địa chỉ đã lưu
                        </label>
                        <label class="tab-label">
                            <input type="radio" name="address_type" value="new"
                                <?php echo empty($addresses) ? 'checked' : ''; ?>>
                            Nhập địa chỉ mới
                        </label>
                    </div>

                    <!-- Địa chỉ đã lưu -->
                    <div id="existing-address" class="address-section">
                        <?php if(!empty($addresses)): ?>
                        <?php foreach($addresses as $address): ?>
                        <label class="address-option">
                            <input type="radio" name="existing_address" value="<?php echo $address['address_id']; ?>"
                                <?php echo $address['is_default'] == 1 ? 'checked' : ''; ?>>
                            <div class="address-details">
                                <p class="address-text"><?php echo htmlspecialchars($address['address']); ?></p>
                                <p class="phone-text"><?php echo htmlspecialchars($address['phone']); ?></p>
                                <?php if($address['is_default'] == 1): ?>
                                <span class="default-badge">Mặc định</span>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p class="no-address">Bạn chưa có địa chỉ nào được lưu</p>
                        <?php endif; ?>
                    </div>

                    <!-- Form địa chỉ mới -->
                    <div id="new-address" class="address-section">
                        <div class="form-group">
                            <label>Địa chỉ giao hàng:</label>
                            <textarea name="address" class="form-control"
                                placeholder="Vui lòng nhập địa chỉ giao hàng chi tiết"><?php 
                                echo isset($saved_info['address']) ? htmlspecialchars($saved_info['address']) : ''; 
                            ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Số điện thoại:</label>
                            <input type="tel" name="phone" class="form-control"
                                placeholder="Nhập số điện thoại của bạn (VD: 0912345678)"
                                value="<?php echo isset($saved_info['phone']) ? htmlspecialchars($saved_info['phone']) : ''; ?>"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);">
                        </div>

                        <!-- <div class="form-group">
                            <label class="save-address-label">
                                <input type="checkbox" name="save_address" checked>
                                Lưu địa chỉ này cho lần sau
                            </label>
                        </div> -->
                    </div>
                </div>

                <button type="submit" class="submit-btn">Tiếp tục</button>
            </form>
        </div>

        <!-- Tổng quan đơn hàng -->
        <div class="order-summary">
            <h2>Đơn hàng của bạn</h2>
            <div class="product-list">
                <?php foreach($cart_items as $item): ?>
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
                            <span class="subtotal"><?php echo number_format($item['total_item']); ?>₫</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
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
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.checkout-steps {
    display: flex;
    justify-content: center;
    margin: 30px 0;
}

.step {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    position: relative;
}

.step:not(:last-child)::after {
    content: '';
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 2px;
    background: #ddd;
}

.step.active {
    color: var(--primary-red);
}

.step.active .step-number {
    background: var(--primary-red);
    color: white;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

.checkout-container {
    display: flex;
    gap: 30px;
    margin-top: 30px;
}

.shipping-form-container {
    flex: 1;
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.order-summary {
    width: 400px;
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.address-selection {
    margin-bottom: 30px;
}

.address-type-tabs {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.tab-label {
    cursor: pointer;
    padding: 10px 15px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.tab-label:hover {
    background: #f8f9fa;
}

.tab-label input[type="radio"] {
    margin-right: 8px;
}

.address-section {
    display: none;
    margin-top: 20px;
}

.address-section.active {
    display: block;
}

.address-option {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.address-option:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.address-option input[type="radio"] {
    margin-right: 15px;
    margin-top: 5px;
}

.address-details {
    flex: 1;
}

.address-text {
    margin: 0 0 5px 0;
    font-weight: 500;
}

.phone-text {
    margin: 0;
    color: #666;
}

.default-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #28a745;
    color: white;
    border-radius: 4px;
    font-size: 12px;
    margin-top: 5px;
}

.no-address {
    color: #666;
    font-style: italic;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

.save-address-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    cursor: pointer;
}

.save-address-label input[type="checkbox"] {
    margin: 0;
}

.submit-btn {
    width: 100%;
    padding: 15px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.3s;
}

.submit-btn:hover {
    background: #0056b3;
}

.product-list {
    margin-bottom: 20px;
}

.product-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.product-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 500;
    margin-bottom: 5px;
}

.quantity-info {
    color: #666;
    margin-bottom: 5px;
}

.price-info {
    color: #666;
    font-size: 14px;
}

.price-summary {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    color: #666;
}

.price-row.discount {
    color: #28a745;
}

.price-row.total {
    font-size: 18px;
    font-weight: 500;
    color: #333;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.final-price {
    color: #dc3545;
    font-weight: bold;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

@media (max-width: 768px) {
    .checkout-container {
        flex-direction: column;
    }

    .order-summary {
        width: 100%;
    }

    .checkout-steps {
        flex-wrap: wrap;
        gap: 10px;
    }

    .step {
        font-size: 14px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý chuyển đổi tab địa chỉ
    const addressTypeTabs = document.querySelectorAll('input[name="address_type"]');
    const addressSections = document.querySelectorAll('.address-section');

    function showAddressSection(type) {
        addressSections.forEach(section => {
            section.style.display = 'none';
        });

        if (type === 'existing') {
            document.getElementById('existing-address').style.display = 'block';
        } else {
            document.getElementById('new-address').style.display = 'block';
        }
    }

    // Hiển thị section phù hợp khi load trang
    const initialType = document.querySelector('input[name="address_type"]:checked').value;
    showAddressSection(initialType);

    // Xử lý sự kiện thay đổi tab
    addressTypeTabs.forEach(tab => {
        tab.addEventListener('change', function() {
            showAddressSection(this.value);
        });
    });

    // Xử lý chọn địa chỉ đã lưu
    const addressOptions = document.querySelectorAll('.address-option');
    addressOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
        });
    });
});

function validateForm() {
    const addressType = document.querySelector('input[name="address_type"]:checked').value;

    if (addressType === 'existing') {
        const selectedAddress = document.querySelector('input[name="existing_address"]:checked');
        if (!selectedAddress) {
            alert('Vui lòng chọn một địa chỉ giao hàng');
            return false;
        }
    } else {
        const diachi = document.querySelector('textarea[name="diachi"]').value.trim();
        const sdt = document.querySelector('input[name="sdt"]').value.trim();

        if (!diachi) {
            alert('Vui lòng nhập địa chỉ giao hàng');
            return false;
        }

        if (!sdt) {
            alert('Vui lòng nhập số điện thoại');
            return false;
        }

        if (!/^0\d{9}$/.test(sdt)) {
            alert('Số điện thoại không hợp lệ. Vui lòng nhập số điện thoại bắt đầu bằng số 0 và có 10 chữ số');
            return false;
        }
    }

    return true;
}
</script>

<?php 
include '../pages/footer.php';
ob_end_flush();
?>