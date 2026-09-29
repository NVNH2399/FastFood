<?php
include '../connect.php';

// Lấy ID từ tham số URL
$id = isset($_GET['id']) ? $_GET['id'] : 0;

if(!$id) {
    echo "ID không hợp lệ";
    exit;
}

try {
    // Bắt đầu transaction
    mysqli_begin_transaction($conn);

    // Kiểm tra xem liên hệ có tồn tại không
    $check_sql = "SELECT contact_id FROM contact WHERE contact_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if(mysqli_num_rows($check_result) == 0) {
        throw new Exception("Không tìm thấy liên hệ cần xóa");
    }

    // Thực hiện xóa liên hệ
    $delete_sql = "DELETE FROM contact WHERE contact_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    
    if(!mysqli_stmt_execute($delete_stmt)) {
        throw new Exception("Lỗi khi xóa liên hệ: " . mysqli_error($conn));
    }

    // Commit transaction nếu mọi thứ OK
    mysqli_commit($conn);
    
    // Chuyển hướng về trang danh sách với thông báo thành công
    $_SESSION['success_msg'] = "Đã xóa liên hệ thành công!";
    header('Location: index_contact.php');
    exit();

} catch (Exception $e) {
    // Rollback nếu có lỗi
    mysqli_rollback($conn);
    
    // Lưu thông báo lỗi vào session
    $_SESSION['error_msg'] = $e->getMessage();
    header('Location: index_contact.php');
    exit();
} finally {
    // Đóng các statement
    if(isset($check_stmt)) mysqli_stmt_close($check_stmt);
    if(isset($delete_stmt)) mysqli_stmt_close($delete_stmt);
    
    // Đóng kết nối
    mysqli_close($conn);
}
?>