<?php
session_start();
ob_start();
include '../function.php';
include '../connect.php';

// Kiểm tra đăng nhập và quyền nhân viên
if(!isset($_SESSION['username'])){
    header("Location: ../catalog/login.php");
    exit;
}
else if($_SESSION['rule'] != 3){ // Rule 3 là nhân viên
    header("Location: ../catalog/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : "Quản lý đơn hàng"; ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .sidebar {
        width: 250px;
        height: 100vh;
        background-color: #2c3e50;
        position: fixed;
        left: 0;
        top: 0;
        color: white;
        padding-top: 20px;
    }

    .sidebar img {
        max-width: 180px;
        display: block;
        margin: 0 auto 30px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .sidebar ul li {
        padding: 15px 25px;
        margin: 5px 0;
    }

    .sidebar ul li:hover {
        background-color: rgba(255, 255, 255, 0.1);
        cursor: pointer;
    }

    .sidebar ul li a {
        color: white;
        text-decoration: none;
        display: block;
    }

    .sidebar ul li a i {
        margin-right: 10px;
    }

    .main-content {
        margin-left: 250px;
    }

    .top-bar {
        background: white;
        padding: 15px 25px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .welcome-text {
        font-size: 16px;
        margin-right: 15px;
    }

    .sidebar {
        width: 250px;
        height: 100vh;
        background-color: #2c3e50;
        position: fixed;
        left: 0;
        top: 0;
        color: white;
        padding-top: 20px;
        z-index: 1030;
    }

    .sidebar img {
        max-width: 180px;
        display: block;
        margin: 0 auto 30px;
    }

    .sidebar-toast-container {
        padding: 0 15px;
        margin-bottom: 20px;
    }

    .custom-toast {
        background-color: rgba(255, 255, 255, 0.1);
        border-left: 4px solid #ffc107;
        border-radius: 6px;
        width: 100%;
    }

    .custom-toast .toast-header {
        background-color: transparent;
        border-bottom: none;
        color: white;
        padding: 8px 12px;
    }

    .toast-icon {
        color: #ffc107;
        margin-right: 8px;
        font-size: 16px;
    }

    .custom-toast .toast-body {
        padding: 8px 12px;
        color: white;
        font-size: 14px;
    }

    .custom-toast .btn-close {
        display: none;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
    }

    .sidebar ul li {
        padding: 15px 25px;
        margin: 5px 0;
    }

    .sidebar ul li:hover {
        background-color: rgba(255, 255, 255, 0.1);
        cursor: pointer;
    }

    .sidebar ul li a {
        color: white;
        text-decoration: none;
        display: block;
    }

    .sidebar ul li a i {
        margin-right: 10px;
    }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="../upload/NVNHSHOP.png" alt="NVNH FOOD" style="cursor: pointer"
            onclick="window.location.href='../catalog/'">

        <ul>
            <li>
                <a href="index.php" class="<?php echo isActive('index'); ?>">
                    <i class="fas fa-home"></i> Bảng điều khiển
                </a>
            </li>
            <li>
                <a href="index_donhang.php" class="<?php echo isActive('index_donhang'); ?>">
                    <i class="fas fa-shopping-cart"></i> Quản lý đơn hàng
                </a>
            </li>
            <li>
                <a href="index_lienhe.php" class="<?php echo isActive('index_lienhe'); ?>">
                    <i class="fas fa-headset"></i> Quản lý CSKH
                </a>
            </li>
        </ul>

        <!-- Toast trong sidebar -->
        <div class="sidebar-toast-container">
            <div class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas fa-user-tie toast-icon"></i>
                    <strong class="me-auto">NVNHSHOP - FASTFOOD</strong>
                </div>
                <div class="toast-body">
                    <i class="fas fa-info-circle me-2"></i>
                    Trang làm việc của nhân viên
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar d-flex justify-content-end align-items-center">
            <span class="welcome-text me-4" id="datetime"></span>
            <span class="welcome-text">
                Xin chào, Nhân viên <?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : ''; ?>
            </span>
            <a href="../catalog/logout.php" class="btn btn-danger ms-3">
                <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </a>
        </div>
        <div class="container-fluid p-4">

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
            <script>
            function updateDateTime() {
                const now = new Date();
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                document.getElementById('datetime').innerHTML = now.toLocaleDateString('vi-VN', options);
            }

            // Cập nhật thời gian mỗi giây
            updateDateTime();
            setInterval(updateDateTime, 1000);
            document.addEventListener('DOMContentLoaded', function() {
                var staffToast = new bootstrap.Toast(document.querySelector('.toast'), {
                    animation: true,
                    autohide: true,
                    delay: 2000 // 2 giây
                });
                staffToast.show();

                // Hiển thị lại toast khi refresh trang
                if (performance.navigation.type === 1) {
                    staffToast.show();
                }
            });
            </script>