<?php
session_start();
include '../config/config.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// Kiểm tra có id được gửi lên không 
if(!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['error'] = "Không tìm thấy địa chỉ cần xóa!";
    header("Location: thongtin_cuatoi.php");
    exit;
}

$username = $_SESSION['username'];
$address_id = $_POST['id'];  // Đổi tên biến để rõ ràng hơn

// Lấy ID của user
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(!$user) {
    $_SESSION['error'] = "Có lỗi xảy ra!";
    header("Location: thongtin_cuatoi.php");
    exit;
}

// Kiểm tra xem địa chỉ có phải của user không
$check_sql = "SELECT address_id FROM addresses 
              WHERE address_id = ? AND idUser = ? AND status = 'active'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $address_id, $user['id']);
$check_stmt->execute();
$result = $check_stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error'] = "Bạn không có quyền xóa địa chỉ này!";
    header("Location: thongtin_cuatoi.php");
    exit;
}

// Thực hiện soft delete bằng cách cập nhật status
$update_sql = "UPDATE addresses 
               SET status = 'inactive' 
               WHERE address_id = ? AND idUser = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $address_id, $user['id']);

if($update_stmt->execute()) {
    // Nếu địa chỉ bị xóa là địa chỉ mặc định, cập nhật địa chỉ mặc định mới
    $check_default_sql = "SELECT is_default FROM addresses 
                         WHERE address_id = ? AND idUser = ?";
    $check_default_stmt = $conn->prepare($check_default_sql);
    $check_default_stmt->bind_param("ii", $address_id, $user['id']);
    $check_default_stmt->execute();
    $address_info = $check_default_stmt->get_result()->fetch_assoc();

    if($address_info && $address_info['is_default'] == 1) {
        // Tìm địa chỉ active đầu tiên để đặt làm mặc định
        $update_default_sql = "UPDATE addresses 
                             SET is_default = 1 
                             WHERE idUser = ? AND status = 'active' 
                             ORDER BY created_at DESC 
                             LIMIT 1";
        $update_default_stmt = $conn->prepare($update_default_sql);
        $update_default_stmt->bind_param("i", $user['id']);
        $update_default_stmt->execute();
    }

    $_SESSION['success'] = "Xóa địa chỉ thành công!";
} else {
    $_SESSION['error'] = "Có lỗi xảy ra khi xóa địa chỉ!";
}

header("Location: thongtin_cuatoi.php");
exit;
?>