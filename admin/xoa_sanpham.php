<?php
include '../connect.php';

$product_id = $_GET['product_id'];

// Get image filename to delete
$sql1 = "SELECT image FROM products WHERE product_id=?";
$stmt = mysqli_prepare($conn, $sql1);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
   $row = mysqli_fetch_array($result);
   $image = basename($row['image']);

   $image_path = "../upload/" . $image;
   if (file_exists($image_path)) {
       unlink($image_path); 
   }
}

// Delete product
$sql = "DELETE FROM products WHERE product_id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);

if (mysqli_stmt_execute($stmt)) {
   header('location: index_sanpham.php');
} else {
   echo "Lỗi xóa dữ liệu: " . mysqli_error($conn);
}

mysqli_close($conn);
?>