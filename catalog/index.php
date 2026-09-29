<?php include '../pages/header.php'; ?><br>
<?php include '../pages/banner.php'; ?>
<link rel="stylesheet" href="../catalog/css/style_trangchu.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<h2 class="section-title text-center">SẢN PHẨM TIÊU BIỂU</h2>
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

.product-card:hover {
    transform: translateY(-5px);
}

.btn-sp span {
    display: none;
}

.products-scroll {
    width: 100%;
    position: relative;
    padding: 20px 0;
}

.products-wrapper {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    /* Hiển thị 4 sản phẩm trên 1 hàng */
    gap: 20px;
    /* Khoảng cách giữa các sản phẩm */
    padding: 0 20px;
    margin: 0 auto;
    max-width: 1200px;
    /* Giới hạn chiều rộng tối đa */
    overflow-x: hidden;
    /* Ẩn thanh cuộn ngang */
}

.product-card {
    width: 100%;
    /* Sản phẩm chiếm hết chiều rộng của grid column */
    margin-bottom: 20px;
}

/* Responsive cho màn hình nhỏ hơn */
@media (max-width: 992px) {
    .products-wrapper {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .products-wrapper {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .products-wrapper {
        grid-template-columns: repeat(1, 1fr);
    }
}

/* Chỉnh sửa cho ảnh sản phẩm */
.product-image img {
    width: 100%;
    height: 200px;
    /* Chiều cao cố định cho ảnh */
    object-fit: cover;
    border-radius: 8px;
}

/* Chỉnh lại scroll dots nếu không cần thiết nữa */
.scroll-dots {
    display: none;
}
</style>

<div class="products-scroll">
    <div class="products-wrapper">
        <?php
        include '../connect.php';
        
        // Sửa lại query để lấy sản phẩm theo category mới
        $sql = "SELECT p.*, c.category_name 
        FROM products p 
        INNER JOIN (
            SELECT idCategory, MIN(product_id) as min_id 
            FROM products 
            GROUP BY idCategory
        ) AS first_products 
        ON p.product_id = first_products.min_id 
        INNER JOIN categories c 
        ON p.idCategory = c.category_id 
        LIMIT 8";
        
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($row['image']); ?>"
                    alt="<?php echo htmlspecialchars($row['product_name']); ?>">
            </div>
            <h3 class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></h3>
            <div class="product-description">
                Danh mục: <?php echo htmlspecialchars($row['category_name']); ?>
            </div>
            <div class="price-section">
                <span class="current-price"><?php echo number_format($row['price'], 0, ',', '.'); ?>₫</span>
            </div>
            <div class="btn-sp">
                <a href="chitiet.php?id=<?php echo $row['product_id']; ?>" class="view-detail" title="Xem chi tiết">
                    <i class="fas fa-eye"></i>
                </a>
                <button type="button" onclick="addToCart(<?php echo $row['product_id']; ?>)" class="add-to-cart-btn"
                    title="Thêm vào giỏ">
                    <i class="fas fa-cart-plus"></i>
                </button>
            </div>
        </div>
        <?php 
    endwhile;
else: 
?>
        <p class="no-products">Không có sản phẩm nào</p>
        <?php 
endif;
?>
    </div>

    <!-- Điều khiển chấm -->
    <div class="scroll-dots">
        <?php
        if ($result && $result->num_rows > 0):
            $result->data_seek(0);
            $count = 0;
            while ($row = $result->fetch_assoc()):
        ?>
        <span class="dot <?php echo $count === 0 ? 'active' : ''; ?>"
            onclick="scrollToProduct(<?php echo $count; ?>)"></span>
        <?php
            $count++;
            endwhile;
        endif;
        $stmt->close();
        $conn->close();
        ?>
    </div>
</div>

<!-- BRAND -->
<div class="brand-section">
    <h2>THƯƠNG HIỆU ĐỐI TÁC</h2>
    <div class="brand-logos">
        <img src="../upload/momo-icon.png" alt="MoMo">
        <img src="../upload/logo-grab-food.jpg" alt="Grab">
        <img src="../upload/logo-gojek.jpg" alt="Gojek">
        <img src="../upload/logo-baemin.jpg" alt="Baemin">
        <img src="../upload/shopee-food.png" alt="ShopeeFood">
    </div>
</div>

<?php include '../pages/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function scrollToProduct(index) {
    const container = document.querySelector('.products-wrapper');
    const cards = container.querySelectorAll('.product-card');
    const card = cards[index];

    if (card) {
        container.scrollTo({
            left: card.offsetLeft - container.offsetLeft,
            behavior: 'smooth'
        });

        // Update active dot
        document.querySelectorAll('.dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }
}

// Track scroll to update active dot
document.querySelector('.products-wrapper').addEventListener('scroll', function() {
    const container = this;
    const cards = container.querySelectorAll('.product-card');
    const scrollPosition = container.scrollLeft;
    const containerWidth = container.offsetWidth;

    cards.forEach((card, index) => {
        const cardOffset = card.offsetLeft - container.offsetLeft;
        if (cardOffset >= scrollPosition && cardOffset < scrollPosition + containerWidth) {
            document.querySelectorAll('.dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }
    });
});

// Auto scroll functionality
let autoScrollInterval;

function startAutoScroll() {
    autoScrollInterval = setInterval(() => {
        const dots = document.querySelectorAll('.dot');
        const activeDotIndex = Array.from(dots).findIndex(dot => dot.classList.contains('active'));
        const nextIndex = (activeDotIndex + 1) % dots.length;
        scrollToProduct(nextIndex);
    }, 5000);
}

function stopAutoScroll() {
    clearInterval(autoScrollInterval);
}

// Start auto scroll on page load
document.addEventListener('DOMContentLoaded', startAutoScroll);

// Stop auto scroll on user interaction
document.querySelector('.products-wrapper').addEventListener('mouseover', stopAutoScroll);
document.querySelector('.products-wrapper').addEventListener('mouseout', startAutoScroll);

// Add to cart function
function addToCart(productId) {
    <?php if(!isset($_SESSION['username'])): ?>
    window.location.href = 'login.php';
    return;
    <?php endif; ?>

    let username = '<?php echo $_SESSION['username']; ?>';

    $.ajax({
        url: 'themgiohang.php',
        method: 'POST',
        data: {
            username: username,
            product_id: productId, // Đổi tên tham số
            quantity: 1
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    alert(result.message);
                    $('.cart-count').text(result.cart_count);
                    $('.cart-total').text(result.cart_count + ' món - ' +
                        new Intl.NumberFormat('vi-VN').format(result.cart_total) + '₫');
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