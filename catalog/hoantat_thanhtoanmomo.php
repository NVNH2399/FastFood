<?php
session_start();
require_once('../connect.php');

if(!isset($_GET['order_id'])) {
    header("Location: lichsu_muahang.php");
    exit;
}

$order_id = (int)$_GET['order_id'];

// Kiểm tra kết quả từ MoMo
if(isset($_GET['resultCode'])) {
    if($_GET['resultCode'] == 0) {
        // Thanh toán thành công - CHỈ cập nhật trạng thái thanh toán
        $sql = "UPDATE orders 
                SET payment_status = 'completed'
                WHERE order_id = ? AND payment_method = 'momo'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // Lấy thông tin đơn hàng để gửi email
        $sql = "SELECT o.*, u.mail, u.fullname 
                FROM orders o
                JOIN users u ON o.idUser = u.id 
                WHERE o.order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if($order) {
            // Gửi email xác nhận
            $body = '
            <div style="background-color: #f4f4f4; padding: 20px;">
                <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 5px;">
                    <h1 style="color: #28a745; text-align: center;">Thanh toán thành công</h1>
                    <p>Xin chào ' . htmlspecialchars($order['fullname']) . ',</p>
                    <p>Đơn hàng #' . $order_id . ' của bạn đã được thanh toán thành công qua MoMo.</p>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                        <p><strong>Số tiền:</strong> ' . number_format($order['final_amount']) . '₫</p>
                        <p><strong>Thời gian:</strong> ' . date('d/m/Y H:i:s') . '</p>
                        <p><strong>Trạng thái:</strong> Đã thanh toán</p>
                    </div>
                    <p>Đơn hàng của bạn đang được xử lý. Cảm ơn bạn đã mua hàng!</p>
                </div>
            </div>';

            // Gửi email qua API
            $url = 'https://namhoai.id.vn/api/v1/send-mail.php';
            $data = [
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
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_exec($ch);
            curl_close($ch);
        }

        $_SESSION['success_message'] = "Thanh toán thành công!";
    } else {
        // Thanh toán thất bại - Chuyển sang trạng thái chờ thanh toán lại
        $sql = "UPDATE orders 
                SET payment_status = 'pending_payment'
                WHERE order_id = ? AND payment_method = 'momo'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // Lấy thông tin đơn hàng để gửi email thông báo cho nhân viên
        $sql = "SELECT o.*, u.mail, u.fullname, u.phone 
                FROM orders o
                JOIN users u ON o.idUser = u.id 
                WHERE o.order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();

        if($order) {
            // Gửi email thông báo cho nhân viên
            $staff_body = '
            <div style="background-color: #f4f4f4; padding: 20px;">
                <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 5px;">
                    <h1 style="color: #dc3545; text-align: center;">Đơn hàng cần hỗ trợ thanh toán lại</h1>
                    <p>Đơn hàng #' . $order_id . ' thanh toán MoMo thất bại và cần được hỗ trợ.</p>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                        <p><strong>Khách hàng:</strong> ' . htmlspecialchars($order['fullname']) . '</p>
                        <p><strong>Số điện thoại:</strong> ' . $order['phone'] . '</p>
                        <p><strong>Email:</strong> ' . $order['mail'] . '</p>
                        <p><strong>Số tiền:</strong> ' . number_format($order['final_amount']) . '₫</p>
                        <p><strong>Thời gian:</strong> ' . date('d/m/Y H:i:s') . '</p>
                    </div>
                    <p>Vui lòng liên hệ hỗ trợ khách hàng thanh toán lại qua Zalo.</p>
                </div>
            </div>';

            // Gửi email cho nhân viên
            $url = 'https://namhoai.id.vn/api/v1/send-mail.php';
            $staff_data = [
                'to' => 'rikakinguyen2004@gmail.com', // Thay bằng email nhân viên thực tế
                'subject' => 'Đơn hàng #' . $order_id . ' cần hỗ trợ thanh toán lại',
                'body' => $staff_body
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: xY7tG9wAq4Zr5vB1nP2m'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($staff_data));
            curl_exec($ch);
            curl_close($ch);
        }

        $_SESSION['error_message'] = "Thanh toán không thành công. Nhân viên sẽ liên hệ hỗ trợ bạn sớm nhất!";
    }
}

header("Location: lichsu_muahang.php");
exit;
?>