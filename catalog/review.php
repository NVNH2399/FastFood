<?php
session_start();
include '../connect.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin user từ database
    $username = $_SESSION['username'];
    $user_sql = "SELECT id, fullname FROM users WHERE username = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if($user) {
        $product_id = (int)$_POST['id'];
        $content = trim($_POST['content']);  // Đổi từ noidung sang content
        $rating = (int)$_POST['rating'];     // Đổi từ sodiem sang rating

        // Kiểm tra rating có hợp lệ không (1-5)
        if($rating < 1 || $rating > 5) {
            $_SESSION['review_error'] = "Số điểm đánh giá không hợp lệ";
            header("Location: chitiet.php?id=" . $product_id);
            exit;
        }

        // Kiểm tra xem user đã đánh giá sản phẩm này chưa
        $check_sql = "SELECT review_id FROM reviews WHERE idUser = ? AND idProduct = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user['id'], $product_id);
        $check_stmt->execute();
        $existing_review = $check_stmt->get_result()->fetch_assoc();

        if($existing_review) {
            // Nếu đã có đánh giá, cập nhật đánh giá cũ
            $update_sql = "UPDATE reviews 
                          SET rating = ?, content = ? 
                          WHERE idUser = ? AND idProduct = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("isii", 
                $rating,
                $content,
                $user['id'],
                $product_id
            );

            if($stmt->execute()) {
                $_SESSION['review_message'] = "Cập nhật đánh giá thành công!";
            } else {
                $_SESSION['review_error'] = "Có lỗi xảy ra khi cập nhật đánh giá";
            }
        } else {
            // Thêm đánh giá mới
            $sql = "INSERT INTO reviews (idUser, fullname, idProduct, rating, content) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isiss", 
                $user['id'],
                $user['fullname'],
                $product_id,
                $rating,
                $content
            );

            if($stmt->execute()) {
                $_SESSION['review_message'] = "Thêm đánh giá thành công!";
            } else {
                $_SESSION['review_error'] = "Có lỗi xảy ra khi thêm đánh giá";
            }
        }

        // Chuyển về trang chi tiết sản phẩm
        header("Location: chitiet.php?id=" . $product_id);
        exit;
    }
}

// Không cho phép truy cập trực tiếp qua GET
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    header("Location: 404.php");
    exit;
}

$conn->close();
?>