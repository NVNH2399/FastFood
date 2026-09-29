<?php
include '../connect.php';

if(isset($_GET['id'])) {
   $id = $_GET['id'];
   
   // Kiểm tra xem có sản phẩm nào thuộc danh mục này không
   $sql_check = "SELECT COUNT(*) as count FROM products WHERE idCategory = ?";
   $stmt = mysqli_prepare($conn, $sql_check);
   mysqli_stmt_bind_param($stmt, "i", $id);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   $count = mysqli_fetch_assoc($result)['count'];
   
   if($count > 0) {
       echo "<script>
               alert('Không thể xóa danh mục này vì có sản phẩm đang sử dụng!');
               window.location.href = 'index_danhmucsanpham.php';
             </script>";
       exit();
   }
   
   // Lấy thông tin ảnh để xóa file
   $sql = "SELECT image FROM categories WHERE category_id = ?";
   $stmt = mysqli_prepare($conn, $sql);
   mysqli_stmt_bind_param($stmt, "i", $id);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   $danhmuc = mysqli_fetch_assoc($result);
   
   // Xóa danh mục
   $sql = "DELETE FROM categories WHERE category_id = ?";
   $stmt = mysqli_prepare($conn, $sql);
   mysqli_stmt_bind_param($stmt, "i", $id);
   
   if(mysqli_stmt_execute($stmt)) {
       // Xóa file ảnh nếu có
       if(!empty($danhmuc['image']) && file_exists($danhmuc['image'])) {
           unlink($danhmuc['image']);
       }
       
       header("Location: index_danhmucsanpham.php");
   } else {
       echo "Lỗi xóa danh mục: " . mysqli_error($conn);
   }
} else {
   header("Location: index_danhmucsanpham.php");
}

mysqli_close($conn);
?>