<?php
include '../connect.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['payment_status'])) {
    $order_id = $_POST['order_id'];
    $payment_status = $_POST['payment_status'];
    
    // Chỉ cho phép cập nhật khi trạng thái thanh toán đang là pending hoặc pending_payment
    $sql = "UPDATE orders 
            SET payment_status = ? 
            WHERE order_id = ? 
            AND payment_status IN ('pending', 'pending_payment')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $payment_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Cập nhật trạng thái thanh toán thành công!";
    } else {
        $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật trạng thái thanh toán!";
    }
    
    $stmt->close();
    header("Location: donhang.php");
    exit();
} else {
    header("Location: donhang.php");
    exit();
}
?>