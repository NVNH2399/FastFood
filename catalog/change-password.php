<?php
session_start();
  include '../connect.php';
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_SESSION['id'])){
      
      $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
      $id = $_SESSION['id'];
      $sql = "UPDATE `users` SET `password`='$password' WHERE  `id`='$id'";
      if(mysqli_query($conn, $sql)){
        //thành công
        $msg = "Thay đổi mật khẩu thành công. Vui lòng đăng nhập lại";
        header("Location: logout.php?msg=$msg");
      }
      
    }else{
      $msg = "Vui lòng đăng nhập trước khi thực hiện";
        header("Location: logout.php?msg=$msg");
    }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET'){
    header("Location: 404.php");
  }
?>