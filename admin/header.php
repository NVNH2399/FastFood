<?php
session_start();
ob_start();
include '../function.php';
include '../connect.php';
if(!isset($_SESSION['username'])){
    header("Location: ../catalog/login.php");
}
else if($_SESSION['username'] != 'admin'){
    header("Location: ../catalog/login.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : "Bảng điều khiển"; ?></title>
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
    </style>

</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <img src="../upload/NVNHSHOP.png" alt="NVNH FOOD" style="cursor: pointer"
            onclick="window.location.href='../catalog/'">
        <ul>
            <li><a href="index.php" class="<?php echo isActive('index'); ?>">
                    <i class="fas fa-home"></i> Bảng điều khiển
                </a></li>
            <li><a href="index_danhmucsanpham.php" class="<?php echo isActive('index_danhmucsanpham'); ?>">
                    <i class="fas fa-list"></i> Danh mục
                </a></li>
            <li><a href="index_banner.php" class="<?php echo isActive('index_banner'); ?>">
                    <i class="fas fa-images"></i> Banner
                </a></li>
            <li><a href="index_sanpham.php" class="<?php echo isActive('index_sanpham'); ?>">
                    <i class="fas fa-box"></i> Sản Phẩm
                </a></li>
            <li><a href="index_user.php" class="<?php echo isActive('index_user'); ?>">
                    <i class="fas fa-user"></i> Tài Khoản
                </a></li>
            <li><a href="index_donhang.php" class="<?php echo isActive('index_donhang'); ?>">
                    <i class="fas fa-shopping-cart"></i> Đơn Hàng
                </a></li>
            <li><a href="index_danhgia.php" class="<?php echo isActive('index_danhgia'); ?>">
                    <i class="fas fa-star"></i> Đánh Giá
                </a></li>
            <li><a href="index_magiamgia.php" class="<?php echo isActive('index_magiamgia'); ?>">
                    <i class="fas fa-percent"></i> Phiếu giảm giá
                </a></li>
            <li><a href="index_lienhe.php" class="<?php echo isActive('index_lienhe'); ?>">
                    <i class="fas fa-headset"></i> Liên hệ & CSKH
                </a></li>
            <h6 style="margin-left: 20px;">Tài khoản Quản trị viên</h6>
            <li><a href="index_profile_admin.php" class="<?php echo isActive('index_profile_admin'); ?>">
                    <i class="fa-regular fa-user"></i> Hồ sơ Admin
                </a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar d-flex justify-content-end align-items-center">
            <span class="welcome-text me-4" id="datetime"></span>
            <span class="welcome-text">
                Xin chào, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>
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
            </script>