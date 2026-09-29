<?php
include '../pages/header.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Nếu không có từ khóa hoặc từ khóa rỗng sau khi trim
if (empty($search)) {
    header("HTTP/1.0 404 Not Found");
    include "404.php";
    exit();
}
$total_items = 0;

if($search) {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.idCategory = c.category_id 
            WHERE p.product_name LIKE ? 
            ORDER BY p.product_id DESC";
            
    $stmt = $conn->prepare($sql);
    $searchTerm = "%".$search."%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_items = $result->num_rows;
}
?>

<link rel="stylesheet" href="../catalog/css/style_dathang.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
</style>
<div class="products-container">
    <div class="search-container">
        <?php if($search): ?>
        <div class="search-results">
            <h2 class="section-title">Kết quả tìm kiếm cho "<?php echo htmlspecialchars($search); ?>"</h2>
            <p class="result-count"><?php echo $total_items; ?> sản phẩm được tìm thấy</p>
            <?php if($total_items > 0): ?>
            <div class="products-grid">
                <?php while($product = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    </div>
                    <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <?php if($product['category_name']): ?>
                    <p class="product-description">
                        Danh mục: <?php echo htmlspecialchars($product['category_name']); ?>
                    </p>
                    <?php endif; ?>
                    <div class="price-section">
                        <span class="current-price"><?php echo number_format($product['price']); ?>₫</span>
                    </div>
                    <div class="btn-sp">
                        <a href="chitiet.php?id=<?php echo $product['product_id']; ?>" class="view-detail"
                            title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button type="button" onclick="addToCart(<?php echo $product['product_id']; ?>)"
                            class="add-to-cart-btn" title="Thêm vào giỏ">
                            <i class="fas fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="no-products">
            <p>Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?php echo htmlspecialchars($search); ?>"</p>
            <a href="index.php" class="view-detail" style="text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Quay lại trang chủ
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div><br>
<script>
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