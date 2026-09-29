<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../connect.php';

// Lấy thông tin user
$fullname = '';
$username = '';
if(isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT u.fullname FROM users u WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()) {
        $fullname = $row['fullname'];
    }
    $stmt->close();
}

// Lấy số lượng sản phẩm trong giỏ hàng và tổng tiền
$itemCount = 0;
$totalAmount = 0;
if(isset($_SESSION['username'])) {
    $stmt = $conn->prepare("SELECT c.quantity, p.price 
                           FROM cart c
                           JOIN products p ON c.idProduct = p.product_id 
                           WHERE c.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $itemCount += $row['quantity'];
        $totalAmount += ($row['quantity'] * $row['price']);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web bán đồ ăn nhanh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../pages/css/style_header.css">
</head>

<body>
    <header class="header">
        <div class="header-container">
            <!-- Logo -->
            <div class="logo">
                <a href="../catalog/index.php">
                    <img src="../upload/NVNHSHOP.png" alt="FastFood Logo">
                </a>
            </div>

            <!-- Search Bar -->
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Tìm kiếm món ăn yêu thích...">
                <button type="submit" id="searchButton"><i class="fas fa-search"></i></button>
            </div>

            <!-- Account & Cart -->
            <div class="header-right">
                <div class="account-dropdown">
                    <?php if(isset($_SESSION['username'])): ?>
                    <a href="#"><i class="fas fa-user"></i> <?php echo htmlspecialchars($fullname); ?></a>
                    <div class="dropdown-content">
                        <a href="../catalog/thongtin_cuatoi.php"><i class="fas fa-user-circle"></i> Thông tin của
                            tôi</a>
                        <a href="../catalog/lichsu_muahang.php"><i class="fas fa-shopping-bag"></i> Đơn hàng của tôi</a>
                        <a href="../catalog/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                    </div>
                    <?php else: ?>
                    <a href="#"><i class="fas fa-user"></i> Tài khoản</a>
                    <div class="dropdown-content">
                        <a href="../catalog/login.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                        <a href="../catalog/signup.php"><i class="fas fa-user-plus"></i> Đăng ký</a>
                    </div>
                    <?php endif; ?>
                </div>

                <a href="../catalog/giohang.php" class="cart-button">
                    <div class="cart-icon-wrapper">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count"><?php echo $itemCount; ?></span>
                    </div>
                    <span class="cart-total"><?php echo $itemCount; ?> món -
                        <?php echo number_format($totalAmount, 0, ',', '.'); ?>đ</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav-bar">
        <div class="nav-container">
            <ul class="nav-menu">
                <li>
                    <a href="../catalog/index.php"
                        <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>
                        <i class="fas fa-home"></i> TRANG CHỦ
                    </a>
                </li>
                <li>
                    <a href="../catalog/dathang.php"
                        <?php echo basename($_SERVER['PHP_SELF']) == 'dathang.php' ? 'class="active"' : ''; ?>>
                        ĐẶT HÀNG
                    </a>
                </li>
                <li>
                    <a href="../catalog/lienhe.php"
                        <?php echo basename($_SERVER['PHP_SELF']) == 'lienhe.php' ? 'class="active"' : ''; ?>>
                        LIÊN HỆ
                    </a>
                </li>
                <li>
                    <a href="../pages/vechungtoi.php"
                        <?php echo basename($_SERVER['PHP_SELF']) == 'vechungtoi.php' ? 'class="active"' : ''; ?>>
                        VỀ CHÚNG TÔI
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');

        // Hàm xử lý tìm kiếm
        function handleSearch() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                window.location.href = `timkiem.php?q=${encodeURIComponent(searchTerm)}`;
            }
        }

        // Xử lý click button tìm kiếm
        searchButton.addEventListener('click', handleSearch);

        // Xử lý nhấn Enter trong input
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSearch();
            }
        });

        // Cập nhật giỏ hàng realtime
        function updateCart() {
            fetch('get_cart_info.php')
                .then(response => response.json())
                .then(data => {
                    document.querySelectorAll('.cart-count').forEach(element => {
                        element.textContent = data.count;
                    });

                    document.querySelectorAll('.cart-total').forEach(element => {
                        element.textContent =
                            `${data.count} món - ${new Intl.NumberFormat('vi-VN').format(data.total)}đ`;
                    });
                })
                .catch(error => console.error('Error updating cart:', error));
        }

        // Cập nhật giỏ hàng mỗi 30 giây
        setInterval(updateCart, 30000);
    });
    </script>
</body>

</html>