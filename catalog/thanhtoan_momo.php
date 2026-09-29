<?php
ob_start(); 
session_start();

if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

include '../pages/header.php';

if(!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = (int)$_GET['order_id'];

// Lấy thông tin đơn hàng - chỉ lấy đơn pending và chưa thanh toán
$sql = "SELECT o.*, u.fullname, u.mail 
        FROM orders o
        JOIN users u ON o.idUser = u.id 
        WHERE o.order_id = ? 
        AND o.status = 'pending' 
        AND (o.payment_status = 'pending' OR o.payment_status IS NULL)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Kiểm tra điều kiện thanh toán
if(!$order || $order['payment_method'] != 'momo') {
    header("Location: lichsu_muahang.php");
    exit;
}

// Hàm gọi API MoMo
function execPostRequest($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data))
    );
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// Xử lý thanh toán
if(isset($_POST['payNow'])) {
    $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    $partnerCode = 'MOMOBKUN20180529';
    $accessKey = 'klm05TvNBzhg7h7j';
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
    
    $orderInfo = "Thanh toán đơn hàng #" . $order_id;
    $amount = $order['final_amount']; // Sửa thành final_amount
    $orderId = time() . "";
    $redirectUrl = "http://" . $_SERVER['HTTP_HOST'] . "/bandoannhanh/catalog/hoantat_thanhtoanmomo.php?order_id=" . $order_id;
    $ipnUrl = "http://" . $_SERVER['HTTP_HOST'] . "/bandoannhanh/catalog/ipn_momo.php";
    $extraData = "";
    
    $requestId = time() . "";
    $requestType = isset($_POST['paymentType']) && $_POST['paymentType'] == 'atm' ? "payWithATM" : "captureWallet";
    
    // Tạo chữ ký
    $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
    $signature = hash_hmac("sha256", $rawHash, $secretKey);
    
    $data = array(
        'partnerCode' => $partnerCode,
        'partnerName' => "Test",
        'storeId' => "MomoTestStore",
        'requestId' => $requestId,
        'amount' => $amount,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'redirectUrl' => $redirectUrl,
        'ipnUrl' => $ipnUrl,
        'lang' => 'vi',
        'extraData' => $extraData,
        'requestType' => $requestType,
        'signature' => $signature
    );
    
    $result = execPostRequest($endpoint, json_encode($data));
    $jsonResult = json_decode($result, true);
    
    if(isset($jsonResult['payUrl'])) {
        ob_end_clean();
        header('Location: ' . $jsonResult['payUrl']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán MoMo - Đơn hàng #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="payment-box">
            <div class="payment-header">
                <h2>Thanh toán đơn hàng #<?php echo $order_id; ?></h2>
                <p class="customer-name">Khách hàng: <?php echo htmlspecialchars($order['fullname']); ?></p>
            </div>

            <div class="payment-amount">
                <p class="amount-label">Số tiền cần thanh toán:</p>
                <p class="amount-value"><?php echo number_format($order['final_amount']); ?>₫</p>
            </div>

            <form method="POST" class="payment-methods">
                <div class="method-selection">
                    <h3>Chọn phương thức thanh toán:</h3>
                    <div class="method-options">
                        <label class="method-option">
                            <input type="radio" name="paymentType" value="atm" checked>
                            <span class="method-label">
                                <i class="fas fa-credit-card"></i>
                                Thẻ ATM/Internet Banking
                            </span>
                        </label>
                        <!-- <label class="method-option">
                            <input type="radio" name="paymentType" value="atm" checked>
                            <span class="method-label">
                                <i class="fas fa-credit-card"></i>
                                Thanh toán bằng QR Momo
                            </span>
                        </label> -->
                    </div>
                </div>

                <button type="submit" name="payNow" class="btn-pay">
                    <i class="fas fa-lock"></i>
                    Thanh toán ngay
                </button>
            </form>

            <div class="payment-info">
                <div class="info-item">
                    <span class="label">Thời gian thanh toán:</span>
                    <span class="value" id="timer">15:00</span>
                </div>
                <div class="info-item">
                    <span class="label">Trạng thái:</span>
                    <span class="value">Đang chờ thanh toán</span>
                </div>
            </div>

            <!-- <div class="action-buttons">
                <a href="lichsu_muahang.php" class="btn-cancel"
                    onclick="return confirm('Bạn có chắc muốn hủy thanh toán?')">
                    <i class="fas fa-times"></i> Hủy thanh toán
                </a>
            </div> -->
        </div>
    </div>

    <style>
    .container {
        max-width: 600px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .payment-box {
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .payment-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .payment-header h2 {
        color: #333;
        margin-bottom: 10px;
    }

    .customer-name {
        color: #666;
        margin: 0;
    }

    .payment-amount {
        text-align: center;
        margin-bottom: 30px;
    }

    .amount-label {
        color: #666;
        margin-bottom: 5px;
    }

    .amount-value {
        font-size: 24px;
        font-weight: bold;
        color: #A50064;
        margin: 0;
    }

    .payment-methods {
        margin-bottom: 30px;
    }

    .method-selection h3 {
        color: #333;
        margin-bottom: 15px;
    }

    .method-options {
        margin-bottom: 20px;
    }

    .method-option {
        display: block;
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
        transition: border-color 0.3s;
    }

    .method-option:hover {
        border-color: #A50064;
    }

    .method-option input[type="radio"] {
        margin-right: 10px;
    }

    .method-label {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-pay {
        width: 100%;
        padding: 15px;
        background: #A50064;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background-color 0.3s;
    }

    .btn-pay:hover {
        background: #880053;
    }

    .payment-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .info-item:last-child {
        margin-bottom: 0;
    }

    .info-item .label {
        color: #666;
    }

    .info-item .value {
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
    }

    .btn-cancel {
        padding: 12px 24px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.3s;
    }

    .btn-cancel:hover {
        background: #c82333;
    }
    </style>

    <script>
    // Đếm ngược 15 phút
    let timeLeft = 15 * 60;
    const timerElement = document.getElementById('timer');

    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            window.location.href = 'lichsu_muahang.php';
        }
        timeLeft--;
    }

    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer();
    </script>

</body>

</html>

<?php include '../pages/footer.php'; ?>