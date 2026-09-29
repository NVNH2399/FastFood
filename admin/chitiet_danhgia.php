<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Lấy ID đánh giá từ URL
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Truy vấn để lấy thông tin chi tiết đánh giá
$sql = "SELECT r.*, p.product_name, p.image as product_image 
        FROM reviews r
        JOIN products p ON r.idProduct = p.product_id 
        WHERE r.review_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();

if (!$review) {
    echo "<div class='alert alert-danger'>Không tìm thấy đánh giá!</div>";
    exit();
}
?>

<div class="container-fluid">
    <div class="card my-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Chi Tiết Đánh Giá</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <img src="<?php echo $review['product_image']; ?>" class="img-fluid rounded" alt="Product Image">
                </div>
                <div class="col-md-8">
                    <h4>Thông tin đánh giá</h4>
                    <table class="table">
                        <tr>
                            <th width="200">ID Đánh giá:</th>
                            <td><?php echo $review['review_id']; ?></td>
                        </tr>
                        <tr>
                            <th>Người đánh giá:</th>
                            <td><?php echo htmlspecialchars($review['fullname']); ?></td>
                        </tr>
                        <tr>
                            <th>Sản phẩm:</th>
                            <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Đánh giá:</th>
                            <td>
                                <div class="stars">
                                    <?php 
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $review['rating']) {
                                            echo '<i class="fas fa-star text-warning"></i>';
                                        } else {
                                            echo '<i class="far fa-star text-muted"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="ms-2 badge bg-warning text-dark">
                                        <?php echo $review['rating']; ?>/5
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Nội dung:</th>
                            <td><?php echo nl2br(htmlspecialchars($review['content'])); ?></td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="index_danhgia.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="sua_danhgia.php?id=<?php echo $review['review_id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Sửa đánh giá
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stars {
    display: inline-flex;
    align-items: center;
    gap: 2px;
}

.stars i {
    font-size: 14px;
}
</style>