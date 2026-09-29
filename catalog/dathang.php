<?php
include '../pages/header.php';

// Lấy danh sách danh mục cho navigation
$sql_categories = "SELECT * FROM categories ORDER BY category_name ASC";
$result_categories = mysqli_query($conn, $sql_categories);

// Lấy id danh mục từ URL nếu có
$selected_category = isset($_GET['danhmuc']) ? (int)$_GET['danhmuc'] : null;

// Chuẩn bị câu query để lấy sản phẩm
if ($selected_category) {
    $sql_products = "SELECT s.*, d.category_name 
                    FROM products s 
                    JOIN categories d ON s.idCategory = d.category_id 
                    WHERE s.idCategory = ? 
                    ORDER BY RAND()";
    $stmt = mysqli_prepare($conn, $sql_products);
    mysqli_stmt_bind_param($stmt, "i", $selected_category);
    mysqli_stmt_execute($stmt);
    $result_products = mysqli_stmt_get_result($stmt);
    
    // Lấy tên danh mục được chọn
    $sql_current_category = "SELECT category_name FROM categories WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $sql_current_category);
    mysqli_stmt_bind_param($stmt, "i", $selected_category);
    mysqli_stmt_execute($stmt);
    $category_result = mysqli_stmt_get_result($stmt);
    $current_category = mysqli_fetch_assoc($category_result);
} else {
    // Nếu không có danh mục được chọn, hiển thị tất cả sản phẩm
    $sql_products = "SELECT s.*, d.category_name 
                    FROM products s 
                    JOIN categories d ON s.idCategory = d.category_id 
                    ORDER BY RAND()";
    $result_products = mysqli_query($conn, $sql_products);
}
?>
<link rel="stylesheet" href="../catalog/css/style_dathang.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<!-- Category Navigation -->
<div class="category-nav">
    <div class="category-list">
        <a href="dathang.php" class="category-item <?php echo !$selected_category ? 'active' : ''; ?>">
            <div class="category-icon">
                <img src="../upload/all-categories.jpg" alt="Tất cả">
            </div>
            <span class="category-name">Tất cả</span>
        </a>
        <?php while($category = mysqli_fetch_assoc($result_categories)): ?>
        <a href="dathang.php?danhmuc=<?php echo $category['category_id']; ?>"
            class="category-item <?php echo ($selected_category == $category['category_id']) ? 'active' : ''; ?>">
            <div class="category-icon">
                <img src="<?php echo htmlspecialchars($category['image']); ?>"
                    alt="<?php echo htmlspecialchars($category['category_name']); ?>">
            </div>
            <span class="category-name"><?php echo htmlspecialchars($category['category_name']); ?></span>
        </a>
        <?php endwhile; ?>
    </div>
</div>

<!-- Products Section -->
<div class="products-container">
    <h2 class="section-title">
        <?php 
        if ($selected_category && isset($current_category)) {
            echo htmlspecialchars($current_category['category_name']);
        } else {
            echo "Tất cả sản phẩm";
        }
        ?>
    </h2>
    <style>
    .product-card {
        position: relative;
        background: white;
        border-radius: 10px;
        padding: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    .btn-sp {
        position: absolute;
        right: 10px;
        bottom: 10px;
        display: flex;
        gap: 8px;
    }

    .btn-sp a,
    .btn-sp button {
        width: 35px;
        height: 35px;
        padding: 0;
        background: white;
        border: 1px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #333;
        cursor: pointer;
    }

    .view-detail {
        text-decoration: none;
    }

    .view-detail:hover {
        background: #ffc107;
        border-color: #ffc107;
    }

    .add-to-cart-btn:hover {
        background: #e31837;
        border-color: #e31837;
        color: white;
    }

    /* Product card hover effect */
    .product-card:hover {
        transform: translateY(-5px);
    }

    /* Hide button text */
    .btn-sp span {
        display: none;
    }
    </style>

    <div class="products-grid">
        <?php 
        if ($result_products && mysqli_num_rows($result_products) > 0):
            while($product = mysqli_fetch_assoc($result_products)): 
        ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            </div>
            <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
            <?php if(!empty($product['description'])): ?>

            <?php endif; ?>
            <div class="price-section">
                <span class="current-price"><?php echo number_format($product['price']); ?>₫</span>
            </div>
            <div class="btn-sp">
                <a href="chitiet.php?id=<?php echo $product['product_id']; ?>" class="view-detail" title="Xem chi tiết">
                    <i class="fas fa-eye"></i>
                </a>
                <button type="button" onclick="addToCart(<?php echo $product['product_id']; ?>)" class="add-to-cart-btn"
                    title="Thêm và giỏ hàng">
                    <i class="fas fa-cart-plus"></i>
                </button>
            </div>
        </div>
        <?php 
            endwhile;
        else: 
        ?>
        <p class="no-products">Chưa có sản phẩm nào trong danh mục này</p>
        <?php endif; ?>
    </div>
</div><br>

<script>
function addToCart(productId) {
    <?php if(!isset($_SESSION['username'])): ?>
    window.location.href = 'login.php';
    return;
    <?php endif; ?>

    $.ajax({
        url: 'themgiohang.php',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: 1
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    alert(result.message);
                    $('.cart-count').text(result.cart_count);
                    $('.cart-total').text(result.cart_count + ' món - ' + result.cart_total + '₫');
                } else {
                    alert(result.message || 'Có lỗi xảy ra');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xử lý phản hồi');
            }
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
            alert('Có lỗi xảy ra. Vui lòng thử lại sau!');
        }
    });
}
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php include '../pages/footer.php'; ?>