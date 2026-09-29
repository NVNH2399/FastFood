<?php
session_start();
include '../connect.php';

if(!isset($_SESSION['username']) || $_SESSION['username'] != 'admin'){
   header("Location: ../catalog/login.php");
   exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
   $code = $_POST['code'];
   $discount = $_POST['discount'];
   $max_usage = !empty($_POST['max_usage']) ? $_POST['max_usage'] : NULL;
   
   // Check if code exists
   $check = $conn->prepare("SELECT * FROM coupons WHERE code = ?");
   $check->bind_param("s", $code);
   $check->execute();
   $result = $check->get_result();
   
   if($result->num_rows > 0) {
       $_SESSION['error'] = "Mã giảm giá đã tồn tại!";
   } else {
       $sql = "INSERT INTO coupons (code, discount, max_usage, status, created_at) 
               VALUES (?, ?, ?, 'active', NOW())";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("sii", $code, $discount, $max_usage);
       
       if($stmt->execute()) {
           $_SESSION['success'] = "Thêm mã giảm giá thành công!";
       } else {
           $_SESSION['error'] = "Có lỗi xảy ra: " . $conn->error;
       }
   }
}

header("Location: index_magiamgia.php"); 
exit();
?>