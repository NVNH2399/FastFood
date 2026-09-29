<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Kiểm tra và lấy thông tin đánh giá
if (!isset($_GET['id'])) {
    echo "<script>alert('Không tìm thấy ID đánh giá!'); window.location.href='index_danhgia.php';</script>";
    exit();
}

$review_id = $_GET['id'];

// Lấy thông tin đánh giá
$sql = "SELECT * FROM reviews WHERE review_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();

if (!$review) {
    echo "<script>alert('Không tìm thấy đánh giá!'); window.location.href='index_danhgia.php';</script>";
    exit();
}

// Lấy danh sách sản phẩm
$sql_products = "SELECT product_id, product_name FROM products ORDER BY product_name ASC";
$products_result = $conn->query($sql_products);

// Xử lý cập nhật đánh giá
if (isset($_POST['update_review'])) {
    $fullname = trim($_POST['fullname']);
    $idProduct = (int)$_POST['idProduct'];
    $rating = (int)$_POST['rating'];
    $content = trim($_POST['content']);
    
    $err = [];
    
    // Validate dữ liệu
    if(empty($fullname)) {
        $err['fullname'] = "Vui lòng nhập họ tên";
    }
    if(empty($idProduct)) {
        $err['product'] = "Vui lòng chọn sản phẩm";
    }
    if($rating < 1 || $rating > 5) {
        $err['rating'] = "Số điểm phải từ 1-5";
    }
    if(empty($content)) {
        $err['content'] = "Vui lòng nhập nội dung đánh giá";
    }

    if(empty($err)) {
        $sql = "UPDATE reviews SET fullname = ?, idProduct = ?, rating = ?, content = ? WHERE review_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siisi", $fullname, $idProduct, $rating, $content, $review_id);

        if($stmt->execute()) {
            echo "<script>alert('Cập nhật đánh giá thành công!'); window.location.href='index_danhgia.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi cập nhật đánh giá!');</script>";
        }
    }
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">CHỈNH SỬA ĐÁNH GIÁ</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Sửa Đánh Giá</h5>
        </div>
        <div class="card-body">
            <?php if(!empty($err)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach($err as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateForm()">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Họ tên người đánh giá <span class="text-danger">*</span></label>
                            <input type="text" name="fullname" class="form-control"
                                value="<?php echo htmlspecialchars($review['fullname']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                            <select name="idProduct" class="form-select" required>
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php while ($product = $products_result->fetch_assoc()): ?>
                                <option value="<?php echo $product['product_id']; ?>"
                                    <?php echo ($review['idProduct'] == $product['product_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Số điểm <span class="text-danger">*</span></label>
                            <div class="rating-stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rating"
                                        id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" required
                                        <?php echo ($review['rating'] == $i) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="rating<?php echo $i; ?>">
                                        <?php echo $i; ?> <i class="fas fa-star text-warning"></i>
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="4"
                                required><?php echo htmlspecialchars($review['content']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" name="update_review" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Thay Đổi
                    </button>
                    <a href="index_danhgia.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rating-stars {
    margin-top: 8px;
}

.rating-stars .form-check-inline {
    margin-right: 1.5rem;
}

.rating-stars .fa-star {
    font-size: 14px;
}
</style>

<script>
function validateForm() {
    const fullname = document.querySelector('input[name="fullname"]').value.trim();
    const idProduct = document.querySelector('select[name="idProduct"]').value;
    const rating = document.querySelector('input[name="rating"]:checked');
    const content = document.querySelector('textarea[name="content"]').value.trim();

    if (!fullname) {
        alert('Vui lòng nhập họ tên người đánh giá');
        return false;
    }
    if (!idProduct) {
        alert('Vui lòng chọn sản phẩm');
        return false;
    }
    if (!rating) {
        alert('Vui lòng chọn số điểm đánh giá');
        return false;
    }
    if (!content) {
        alert('Vui lòng nhập nội dung đánh giá');
        return false;
    }

    return true;
}
</script>