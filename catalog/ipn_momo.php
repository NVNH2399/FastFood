<?php
require_once('../config.php');

// Log MoMo request để debug
$rawData = file_get_contents("php://input");
$logFile = __DIR__ . "/momo_ipn.log";
$currentTime = date('Y-m-d H:i:s');
error_log("[$currentTime] MoMo IPN Request: " . $rawData . "\n", 3, $logFile);

try {
    // Decode JSON data
    $inputData = json_decode($rawData, true);
    
    // Kiểm tra response từ MoMo
    if(isset($inputData['resultCode']) && isset($inputData['orderInfo'])) {
        // Lấy order_id từ orderInfo (format: "Thanh toán đơn hàng #123")
        preg_match('/#(\d+)/', $inputData['orderInfo'], $matches);
        $order_id = isset($matches[1]) ? $matches[1] : null;

        if($order_id) {
            $conn->begin_transaction();

            if($inputData['resultCode'] == 0) {
                // Thanh toán thành công - CHỈ cập nhật payment_status
                $sql = "UPDATE orders 
                        SET payment_status = 'completed'
                        WHERE order_id = ? AND payment_method = 'momo'
                        AND payment_status != 'completed'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();

                // Log thành công
                error_log("[$currentTime] Payment completed for order #$order_id\n", 3, $logFile);
                
                // Gửi email xác nhận
                $sql = "SELECT o.*, u.mail, u.fullname 
                        FROM orders o
                        JOIN users u ON o.idUser = u.id 
                        WHERE o.order_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                $order = $stmt->get_result()->fetch_assoc();

                if($order) {
                    // Lấy chi tiết đơn hàng
    $sql = "SELECT od.*, p.product_name 
    FROM order_details od 
    JOIN products p ON od.idProduct = p.product_id
    WHERE od.idOrder = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_details = $stmt->get_result();

// Tạo HTML cho bảng sản phẩm
$order_items = '';
while($item = $order_details->fetch_assoc()) {
$order_items .= '
<tr>
    <td style="padding: 8px; border-bottom: 1px solid #ddd;">'.$item['product_name'].'</td>
    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">'.$item['quantity'].'</td>
    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">'.number_format($item['unit_price']).'₫</td>
    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">'.number_format($item['total_amount']).'₫</td>
</tr>';
}

// Tạo nội dung email với bảng sản phẩm
$body = '
<div style="background-color: #f4f4f4; padding: 20px;">
<div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 5px;">
    <h1 style="color: #28a745; text-align: center;">Xác nhận thanh toán</h1>
    <p>Xin chào ' . htmlspecialchars($order['fullname']) . ',</p>
    <p>Đơn hàng #' . $order_id . ' của bạn đã được thanh toán thành công qua MoMo.</p>
    
    <div style="margin: 20px 0;">
        <h3 style="color: #333;">Chi tiết đơn hàng:</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f8f9fa;">
                <th style="padding: 8px; text-align: left;">Sản phẩm</th>
                <th style="padding: 8px; text-align: center;">Số lượng</th>
                <th style="padding: 8px; text-align: right;">Đơn giá</th>
                <th style="padding: 8px; text-align: right;">Thành tiền</th>
            </tr>
            '.$order_items.'
            <tr style="background: #f8f9fa;">
                <td colspan="3" style="padding: 8px; text-align: right;"><strong>Tổng tiền:</strong></td>
                <td style="padding: 8px; text-align: right;"><strong>'.number_format($order['total_amount']).'₫</strong></td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td colspan="3" style="padding: 8px; text-align: right;"><strong>Giảm giá:</strong></td>
                <td style="padding: 8px; text-align: right;"><strong>'.number_format($order['discount']).'₫</strong></td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td colspan="3" style="padding: 8px; text-align: right;"><strong>Thanh toán:</strong></td>
                <td style="padding: 8px; text-align: right;"><strong>'.number_format($order['final_amount']).'₫</strong></td>
            </tr>
        </table>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
        <p><strong>Địa chỉ giao hàng:</strong> '.$order['address'].'</p>
        <p><strong>Số điện thoại:</strong> '.$order['phone'].'</p>
        <p><strong>Thời gian thanh toán:</strong> '.date('d/m/Y H:i:s').'</p>
        <p><strong>Trạng thái thanh toán:</strong> Thành công</p>
        <p><strong>Trạng thái đơn hàng:</strong> Đang xử lý</p>
    </div>

    <p>Chúng tôi sẽ sớm xử lý đơn hàng của bạn.</p>
    <p>Cảm ơn bạn đã mua hàng!</p>
</div>
</div>';

                    // Gửi email
                    $url = 'https://namhoai.id.vn/api/v1/send-mail.php';
                    $mailData = [
                        'to' => $order['mail'],
                        'subject' => 'Xác nhận thanh toán đơn hàng #' . $order_id,
                        'body' => $body
                    ];
                    
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Authorization: xY7tG9wAq4Zr5vB1nP2m'
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mailData));
                    curl_exec($ch);
                    curl_close($ch);
                }
            } else {
                // Thanh toán thất bại
                $sql = "UPDATE orders 
                        SET payment_status = 'failed'
                        WHERE order_id = ? AND payment_method = 'momo'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();

                // Log thất bại
                error_log("[$currentTime] Payment failed for order #$order_id\n", 3, $logFile);
            }

            $conn->commit();
        } else {
            error_log("[$currentTime] Cannot extract order_id from orderInfo\n", 3, $logFile);
        }
    } else {
        error_log("[$currentTime] Invalid MoMo response format\n", 3, $logFile);
    }
} catch (Exception $e) {
    if($conn->error) {
        $conn->rollback();
    }
    error_log("[$currentTime] Error processing IPN: " . $e->getMessage() . "\n", 3, $logFile);
}

// Trả về response cho MoMo
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'Processed',
    'timestamp' => $currentTime
]);