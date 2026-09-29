<?php
session_start();
require_once('../connect.php');

try {
    // Kiểm tra kết nối
    if (!$conn) {
        throw new Exception("Không thể kết nối database");
    }

    // Nếu đã đăng nhập, lấy thông tin user từ database
    if(isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $stmt = $conn->prepare("SELECT id, fullname, mail FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close(); // Đóng statement sau khi sử dụng xong
        
        if($user) {
            // Sử dụng thông tin từ database
            $hoten = $user['fullname'];
            $email = $user['mail'];
            $idUser = $user['id'];
        } else {
            throw new Exception("Không tìm thấy thông tin người dùng");
        }
    } else {
        // Nếu chưa đăng nhập, lấy từ form
        $hoten = $_POST['fullname'];
        $email = $_POST['email'];
        $idUser = null;
    }

    // Lấy các thông tin khác từ form
    $sdt = $_POST['phone'];
    $content = $_POST['content'];

    // Chuẩn bị câu lệnh SQL cho việc thêm liên hệ
    $insert_sql = "INSERT INTO contact (fullname, email, phone, content, idUser) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    if (!$insert_stmt) {
        throw new Exception("Lỗi prepared statement");
    }

    // Bind parameters và thực thi
    $insert_stmt->bind_param("ssssi", $hoten, $email, $sdt, $content, $idUser);
    $result = $insert_stmt->execute();
    $insert_stmt->close(); // Đóng statement sau khi sử dụng xong
    
    if ($result) {
        $_SESSION['message'] = "Gửi liên hệ thành công!";
        $_SESSION['message_type'] = "success";
    } else {
        throw new Exception("Không thể thêm liên hệ");
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Lỗi: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
} finally {
    // Chỉ đóng connection trong finally
    if(isset($conn) && $conn) $conn->close();
}

// Chuyển hướng về trang liên hệ
header("Location: lienhe.php");
exit();
?>