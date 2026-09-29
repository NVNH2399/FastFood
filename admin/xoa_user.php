<?php
include '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
   $id = $_POST['id'];
   
   // Kiểm tra không cho xóa admin
   $sql = "SELECT username FROM users WHERE id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->bind_param("i", $id);
   $stmt->execute(); 
   $result = $stmt->get_result();
   $user = $result->fetch_assoc();
   
   if($user['username'] === 'admin') {
       die("Không thể xóa tài khoản admin");
   }
   
   try {
       // Bắt đầu transaction
       $conn->begin_transaction();
       
       // Xóa các bản ghi liên quan trong các bảng con trước
       // Xóa reviews
       $sql = "DELETE FROM reviews WHERE idUser = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $id);
       $stmt->execute();
       
       // Xóa cart
       $sql = "DELETE FROM cart WHERE username = (SELECT username FROM users WHERE id = ?)"; 
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $id);
       $stmt->execute();
       
       // Xóa contact
       $sql = "DELETE FROM contact WHERE idUser = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $id);
       $stmt->execute();

       // Xóa addresses
       $sql = "DELETE FROM addresses WHERE idUser = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $id);
       $stmt->execute();
       
       // Xóa orders và order_details
       $sql = "DELETE od FROM order_details od
               INNER JOIN orders o ON od.idOrder = o.order_id 
               WHERE o.idUser = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $id);
       $stmt->execute();
       
       $sql = "DELETE FROM orders WHERE idUser = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $id);
       $stmt->execute();
       
       // Cuối cùng xóa user
       $sql = "DELETE FROM users WHERE id = ?";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("i", $id);
       $stmt->execute();
       
       // Commit transaction
       $conn->commit();
       
       echo "success";
       
   } catch (Exception $e) {
       // Rollback nếu có lỗi
       $conn->rollback();
       http_response_code(500);
       echo "error: " . $e->getMessage();
   }
}
?>