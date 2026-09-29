<?php
session_start();
include '../connect.php';

if(isset($_GET['id']) && isset($_SESSION['username'])) {
    $id = (int)$_GET['id'];
    $username = $_SESSION['username'];
    
    // Sửa lại tên bảng và cột theo cấu trúc CSDL mới
    $delete_sql = "DELETE FROM cart WHERE username = ? AND idProduct = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("si", $username, $id);
    
    try {
        if($delete_stmt->execute()) {
            $_SESSION['cart_message'] = "Đã xóa sản phẩm khỏi giỏ hàng";
        } else {
            $_SESSION['cart_error'] = "Không thể xóa sản phẩm khỏi giỏ hàng";
        }
    } catch (Exception $e) {
        $_SESSION['cart_error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

header("Location: giohang.php");
exit();
?>