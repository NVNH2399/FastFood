<?php
include './header.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Kiểm tra id và status có hợp lệ
    if (!empty($id) && !empty($status)) {
        $sql = "UPDATE contact SET status = ? WHERE contact_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Trạng thái đã được cập nhật";
        } else {
            http_response_code(500);
            echo "Lỗi: " . mysqli_error($conn);
        }
    } else {
        http_response_code(400);
        echo "Dữ liệu không hợp lệ";
    }
}
?>