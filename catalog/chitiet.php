<?php
include '../pages/header.php';

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get product information
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.idCategory = c.category_id 
            WHERE p.product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../catalog/css/style_chitiet.css">
    <style>
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .has-reviewed {
        opacity: 0.7;
        pointer-events: none;
    }

    .review-status {
        margin: 10px 0;
        font-style: italic;
        color: #666;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="product-details">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            </div>

            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
                <p class="category">Danh mục: <?php echo htmlspecialchars($product['category_name']); ?></p>
                <p class="price"><?php echo number_format($product['price']); ?>₫</p>

                <?php if(isset($_SESSION['username'])): ?>
                <div class="product-actions">
                    <div class="quantity-wrapper">
                        <button type="button" onclick="decreaseQuantity()">-</button>
                        <input type="number" id="quantity" value="1" min="1" max="99"
                            oninput="this.value = Math.max(1, Math.min(99, this.value))">
                        <button type="button" onclick="increaseQuantity()">+</button>
                    </div>
                    <button type="button" onclick="addToCart()" class="add-to-cart-btn">
                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                    </button>
                </div>
                <?php else: ?>
                <p class="login-notice">
                    Vui lòng <a href="login.php">đăng nhập</a> để mua hàng
                </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="openTab(event, 'description')">Mô tả</button>
                <button class="tab-button" onclick="openTab(event, 'reviews')">Đánh giá</button>
            </div>

            <div id="description" class="tab-content active">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>

            <div id="reviews" class="tab-content">
                <?php if(isset($_SESSION['username'])): 
                    // Check if user has already reviewed
                    $check_review_sql = "SELECT review_id, rating, content FROM reviews 
                                       WHERE idUser = (SELECT id FROM users WHERE username = ?) 
                                       AND idProduct = ?";
                    $stmt = mysqli_prepare($conn, $check_review_sql);
                    mysqli_stmt_bind_param($stmt, "si", $_SESSION['username'], $product['product_id']);
                    mysqli_stmt_execute($stmt);
                    $existing_review = mysqli_stmt_get_result($stmt)->fetch_assoc();
                ?>

                <?php if(isset($_SESSION['review_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                            echo $_SESSION['review_message'];
                            unset($_SESSION['review_message']); 
                            ?>
                </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['review_error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                            echo $_SESSION['review_error'];
                            unset($_SESSION['review_error']); 
                            ?>
                </div>
                <?php endif; ?>

                <form action="review.php" method="post" onsubmit="return validateReview()">
                    <input type="hidden" name="id" value="<?php echo $product['product_id']; ?>">
                    <div class="comment-form">
                        <textarea name="content" placeholder="Viết đánh giá của bạn..."
                            required><?php echo $existing_review ? htmlspecialchars($existing_review['content']) : ''; ?></textarea>
                        <div class="rating">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>"
                                <?php echo ($existing_review && $existing_review['rating'] == $i) ? 'checked' : ''; ?>>
                            <label for="star-<?php echo $i; ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path pathLength="360"
                                        d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                    </path>
                                </svg>
                            </label>
                            <?php endfor; ?>
                        </div>
                        <button type="submit" class="submit-comment">
                            <?php echo $existing_review ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?>
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <p class="login-notice">
                    Vui lòng <a href="login.php">đăng nhập</a> để viết đánh giá
                </p>
                <?php endif; ?>

                <!-- Display Reviews -->
                <?php
                $review_sql = "SELECT r.*, u.username, u.fullname FROM reviews r 
                              JOIN users u ON r.idUser = u.id 
                              WHERE r.idProduct = ? 
                              ORDER BY r.review_id DESC";
                $stmt = mysqli_prepare($conn, $review_sql);
                mysqli_stmt_bind_param($stmt, "i", $product['product_id']);
                mysqli_stmt_execute($stmt);
                $reviews = mysqli_stmt_get_result($stmt);
                ?>

                <div class="comment-section">
                    <?php if(mysqli_num_rows($reviews) > 0): 
                        while($review = mysqli_fetch_assoc($reviews)): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <strong><?php echo htmlspecialchars($review['fullname']); ?></strong>
                            <div class="rating-stars">
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    echo ($i <= $review['rating']) ? '★' : '☆';
                                }
                                ?>
                            </div>
                        </div>
                        <p class="comment-content"><?php echo nl2br(htmlspecialchars($review['content'])); ?></p>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <div class="comment">
                        <p>Chưa có đánh giá nào cho sản phẩm này.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function validateReview() {
        const content = document.querySelector('textarea[name="noidung"]').value.trim();
        const rating = document.querySelector('input[name="sodiem"]:checked');

        if (!content) {
            alert("Vui lòng nhập nội dung đánh giá");
            return false;
        }

        if (!rating) {
            alert("Vui lòng chọn số sao đánh giá");
            return false;
        }

        return true;
    }

    function decreaseQuantity() {
        const input = document.getElementById('quantity');
        const currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1;
        }
    }

    function increaseQuantity() {
        const input = document.getElementById('quantity');
        const currentValue = parseInt(input.value);
        if (currentValue < 99) {
            input.value = currentValue + 1;
        }
    }

    function addToCart() {
        const quantity = document.getElementById('quantity').value;
        const productId = <?php echo $product['product_id']; ?>;

        if (quantity < 1) {
            alert('Vui lòng chọn số lượng hợp lệ');
            return;
        }

        $.ajax({
            url: 'themgiohang.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert('Đã thêm vào giỏ hàng');
                        // Update cart count in header
                        const cartCountElements = document.querySelectorAll('.cart-count');
                        cartCountElements.forEach(element => {
                            element.textContent = result.cart_count;
                        });
                        // Update total amount
                        const cartTotalElements = document.querySelectorAll('.cart-total');
                        cartTotalElements.forEach(element => {
                            element.textContent = result.cart_total + '₫';
                        });
                    } else {
                        alert(result.message || 'Có lỗi xảy ra');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Có lỗi xảy ra khi xử lý phản hồi');
                }
            },
            error: function() {
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
            }
        });
    }

    function openTab(evt, tabName) {
        const tabContents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].classList.remove('active');
        }

        const tabButtons = document.getElementsByClassName("tab-button");
        for (let i = 0; i < tabButtons.length; i++) {
            tabButtons[i].classList.remove('active');
        }

        document.getElementById(tabName).classList.add('active');
        evt.currentTarget.classList.add('active');
    }

    // Handle direct quantity input
    document.getElementById('quantity').addEventListener('change', function(e) {
        let value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 99) {
            this.value = 99;
        }
    });
    </script>

</body>

</html>

<?php
    } else {
        echo "<div class='container'><p>Không tìm thấy sản phẩm.</p></div>";
    }
} else {
    echo "<div class='container'><p>Không có thông tin sản phẩm.</p></div>";
}

include '../pages/footer.php';
?>