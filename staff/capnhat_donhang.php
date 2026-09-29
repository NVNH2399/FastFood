<?php
session_start();
include '../connect.php';

// Kiểm tra quyền nhân viên
if (!isset($_SESSION['username']) || $_SESSION['rule'] !== 3) {
    http_response_code(403);
    exit('Không có quyền truy cập');
}

// Kiểm tra dữ liệu gửi lên
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    http_response_code(400);
    exit('Thiếu dữ liệu cần thiết');
}

$id = intval($_POST['id']);
$status = $_POST['status'];

// Validate status
$allowed_status = ['pending', 'delivering', 'completed'];
if (!in_array($status, $allowed_status)) {
    http_response_code(400);
    exit('Trạng thái không hợp lệ');
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // Cập nhật trạng thái đơn hàng
    if ($status == 'completed') {
        // Nếu là completed và COD thì cập nhật cả payment_status
        $sql = "UPDATE orders SET status = ?, payment_status = CASE 
                WHEN payment_method = 'cod' THEN 'completed' 
                ELSE payment_status END 
                WHERE order_id = ?";
    } else {
        $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Lỗi prepare statement: " . $conn->error);
    }

    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Lỗi execute statement: " . $stmt->error);
    }

    // Kiểm tra xem có dòng nào được cập nhật không
    if ($stmt->affected_rows === 0) {
        throw new Exception("Không tìm thấy đơn hàng hoặc không có thay đổi");
    }

    // Commit transaction
    $conn->commit();
    
    // Trả về thành công
    echo "Success";
} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    
    // Log lỗi
    error_log("Error updating order status: " . $e->getMessage());
    
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>