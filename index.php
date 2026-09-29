<?php
session_start();
if(isset($_COOKIE['username'])){
  header("location: catalog/login.php");
  exit();
}
  header("Location: catalog")
?>