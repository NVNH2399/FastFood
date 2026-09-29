<?php
session_start();
include '../connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    try {
        // Kiểm tra sản phẩm có tồn tại không
        $check_product = "SELECT product_id FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($check_product);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Sản phẩm không tồn tại");
        }

        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $check_cart = "SELECT cart_id, quantity FROM cart WHERE username = ? AND idProduct = ?";
        $stmt = $conn->prepare($check_cart);
        $stmt->bind_param("si", $username, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cập nhật số lượng nếu sản phẩm đã có trong giỏ
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;

            $update_sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $new_quantity, $row['cart_id']);
            $stmt->execute();
        } else {
            // Thêm mới nếu sản phẩm chưa có trong giỏ
            $insert_sql = "INSERT INTO cart (username, idProduct, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sii", $username, $product_id, $quantity);
            $stmt->execute();
        }

        // Lấy tổng số lượng và tổng tiền trong giỏ hàng
        $cart_query = "SELECT c.quantity, p.price 
                      FROM cart c
                      JOIN products p ON c.idProduct = p.product_id
                      WHERE c.username = ?";
        $stmt = $conn->prepare($cart_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $cart_result = $stmt->get_result();

        $total_items = 0;
        $total_amount = 0;

        while ($row = $cart_result->fetch_assoc()) {
            $total_items += $row['quantity'];
            $total_amount += ($row['quantity'] * $row['price']);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'cart_count' => $total_items,
            'cart_total' => number_format($total_amount)
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không hợp lệ'
    ]);
}

mysqli_close($conn);
?>