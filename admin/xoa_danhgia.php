<?php
include '../connect.php';

if(isset($_POST['review_id'])) {
   $review_id = $_POST['review_id'];
   
   $sql = "DELETE FROM reviews WHERE review_id = ?";
   $stmt = mysqli_prepare($conn, $sql);
   mysqli_stmt_bind_param($stmt, "i", $review_id);
   
   if(mysqli_stmt_execute($stmt)) {
       echo "success";
   } else {
       http_response_code(500);
       echo "error";
   }
}
?>