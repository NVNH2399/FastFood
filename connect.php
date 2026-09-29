<?php
    $server = 'localhost';
    $user = 'tvtumnvlhosting_bdan';
    $password='Hai2004@';
    $database='tvtumnvlhosting_bandoannhanh';
    $conn=new mysqli($server, $user, $password, $database);

    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
?>