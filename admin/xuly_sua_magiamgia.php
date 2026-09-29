<?php
// process_edit_coupon.php
session_start();
include './header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   header('Location: index_magiamgia.php');
   exit;
}

$coupon_id = $_POST['coupon_id'];
$discount = $_POST['discount'];
$status = $_POST['status'];

$sql = "UPDATE coupons SET discount = ?, status = ? WHERE coupon_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $discount, $status, $coupon_id);

if($stmt->execute()) {
   $_SESSION['success'] = "Cập nhật mã giảm giá thành công!";
} else {
   $_SESSION['error'] = "Có lỗi xảy ra: " . $conn->error;
}

header('Location: index_magiamgia.php');
?>