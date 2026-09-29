<?php
session_start();

// Xóa session
session_unset();
session_destroy();

// Xóa cookie
setcookie('username', '', time() - 3600, "/");
setcookie('password', '', time() - 3600, "/");

// Điều hướng về trang đăng nhập
$msg = $_GET['msg'];
header("Location: login.php?msg=$msg");
exit();
?>