<?php
session_start();
include '../connect.php';
header('Content-Type: application/json');

if(!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Phiên đăng nhập không hợp lệ'
    ]);
    exit;
}

try {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $username = $_SESSION['username'];
    
    // Giới hạn số lượng
    if($quantity < 1) $quantity = 1;
    if($quantity > 99) $quantity = 99;
    
    // Cập nhật số lượng trong database
    $sql = "UPDATE cart SET quantity = ? WHERE username = ? AND idProduct = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $quantity, $username, $product_id);
    
    if($stmt->execute()) {
        // Tính lại tổng tiền
        $sql = "SELECT SUM(c.quantity * p.price) as total 
                FROM cart c 
                JOIN products p ON c.idProduct = p.product_id 
                WHERE c.username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total'];

        // Tính tổng số lượng sản phẩm trong giỏ hàng
        $sql = "SELECT SUM(quantity) as cart_count 
                FROM cart 
                WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_count = $result->fetch_assoc()['cart_count'];

        echo json_encode([
            'success' => true,
            'total' => $total,
            'cart_count' => $cart_count,
            'cart_total' => number_format($total) . '₫'
        ]);
    } else {
        throw new Exception("Không thể cập nhật giỏ hàng");
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}

$conn->close();
?>