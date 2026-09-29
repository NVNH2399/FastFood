<?php
session_start();
include '../connect.php';

if(!isset($_SESSION['username']) || $_SESSION['username'] != 'admin'){
   header("Location: ../catalog/login.php");
   exit();
}

// Handle add coupon
if(isset($_POST['action']) && $_POST['action'] == 'add') {
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
       $sql = "INSERT INTO coupons (code, discount, max_usage, status, created_at) VALUES (?, ?, ?, 'active', NOW())";
       $stmt = $conn->prepare($sql);
       $stmt->bind_param("sii", $code, $discount, $max_usage);
       
       if($stmt->execute()) {
           $_SESSION['success'] = "Thêm mã giảm giá thành công!";
       } else {
           $_SESSION['error'] = "Có lỗi xảy ra: " . $conn->error;
       }
   }
}

// Handle delete coupon
else if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
   $id = $_GET['id'];
   
   $stmt = $conn->prepare("DELETE FROM coupons WHERE coupon_id = ?");
   $stmt->bind_param("i", $id);
   
   if($stmt->execute()) {
       $_SESSION['success'] = "Xóa mã giảm giá thành công!";
   } else {
       $_SESSION['error'] = "Có lỗi xảy ra khi xóa mã giảm giá!";
   }
}

// Coupon validation and application functions
function validateCoupon($code) {
   global $conn;
   
   $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'"); 
   $stmt->bind_param("s", $code);
   $stmt->execute();
   $result = $stmt->get_result();
   
   if($result->num_rows === 0) {
       return ["valid" => false, "message" => "Mã giảm giá không tồn tại hoặc đã hết hạn"];
   }
   
   $coupon = $result->fetch_assoc();
   
   if($coupon['max_usage'] !== NULL && $coupon['current_usage'] >= $coupon['max_usage']) {
       return ["valid" => false, "message" => "Mã giảm giá đã hết lượt sử dụng"];
   }
   
   return ["valid" => true, "data" => $coupon];
}

function applyCoupon($couponId) {
   global $conn;
   
   $stmt = $conn->prepare("UPDATE coupons SET current_usage = current_usage + 1 WHERE coupon_id = ?");
   $stmt->bind_param("i", $couponId);
   return $stmt->execute();
}

header("Location: index_magiamgia.php");
exit;
?>