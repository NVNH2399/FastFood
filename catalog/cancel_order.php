<?php
include '../connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']);
    exit;
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // Kiểm tra đơn hàng có thuộc về user không và lấy thông tin
    $sql = "SELECT o.*, u.fullname, u.mail, u.username 
            FROM orders o 
            JOIN users u ON o.idUser = u.id
            WHERE o.order_id = ? AND u.username = ? AND o.status = 'pending'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $order_id, $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        throw new Exception('Không tìm thấy đơn hàng hoặc không có quyền hủy');
    }

    // Kiểm tra thời gian (5 phút)
    $orderTime = new DateTime($order['created_at']);
    $now = new DateTime();
    $timeDiff = $now->getTimestamp() - $orderTime->getTimestamp();
    
    if ($timeDiff > 300) { // 5 phút
        throw new Exception('Đã quá thời gian cho phép hủy đơn hàng');
    }

    // Cập nhật trạng thái đơn hàng
    $sql_update = "UPDATE orders SET status = 'canceled' WHERE order_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Không thể hủy đơn hàng');
    }

    // Nếu đã thanh toán MoMo, gửi email thông báo
    if ($order['payment_method'] == 'momo' && $order['payment_status'] == 'completed') {
        // Tạo nội dung email
        $body = '
        <div style="background-color: #f4f4f4; padding: 20px;">
            <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 5px;">
                <h1 style="color: #dc3545; text-align: center;">Xác nhận yêu cầu hủy đơn hàng</h1>
                <p>Xin chào ' . htmlspecialchars($order['fullname']) . ',</p>
                <p>Chúng tôi đã nhận được yêu cầu hủy đơn hàng #' . $order_id . ' của bạn.</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Mã đơn hàng:</strong> #' . $order_id . '</p>
                    <p><strong>Ngày đặt:</strong> ' . date('d/m/Y H:i', strtotime($order['created_at'])) . '</p>
                    <p><strong>Số tiền:</strong> ' . number_format($order['final_amount']) . '₫</p>
                    <p><strong>Phương thức thanh toán:</strong> MoMo</p>
                    <p><strong>Trạng thái thanh toán:</strong> Đã thanh toán</p>
                </div>

                <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Quy trình hoàn tiền:</strong></p>
                    <ol style="margin-left: 20px;">
                        <li>Nhân viên của chúng tôi sẽ liên hệ với bạn qua số điện thoại ' . $order['phone'] . ' 
                            để xác nhận thông tin</li>
                        <li>Tiến hành hoàn tiền qua MoMo trong vòng 3-5 ngày làm việc</li>
                        <li>Gửi email xác nhận khi hoàn tất quá trình hoàn tiền</li>
                    </ol>
                </div>

                <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua:</p>
                <ul style="list-style: none; padding-left: 0;">
                    <li>📞 Hotline: 1900 xxxx</li>
                    <li>✉️ Email: support@foodstore.com</li>
                </ul>

                <p style="color: #6c757d; font-style: italic; margin-top: 20px;">
                    Đây là email tự động, vui lòng không trả lời email này.
                </p>
            </div>
        </div>';

        // Phần gửi email vẫn giữ nguyên
        $url = 'https://namhoai.id.vn/api/v1/send-mail.php';
        $emailData = [
            'to' => $order['mail'],
            'subject' => 'Xác nhận hủy đơn hàng #' . $order_id,
            'body' => $body
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: xY7tG9wAq4Zr5vB1nP2m'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Failed to send cancellation email for order " . $order_id);
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'payment_status' => $order['payment_status'] ?? 'pending',
        'payment_method' => $order['payment_method'] ?? 'cod',
        'message' => 'Hủy đơn hàng thành công'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>