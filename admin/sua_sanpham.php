<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Kiểm tra và lấy thông tin sản phẩm
if (!isset($_GET['id'])) { // Sửa từ product_id thành id 
    echo "<script>alert('Không tìm thấy ID sản phẩm!'); window.location.href='index_sanpham.php';</script>";
    exit();
}

$product_id = $_GET['id'];
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Không tìm thấy sản phẩm!'); window.location.href='index_sanpham.php';</script>";
    exit();
}

$product = $result->fetch_assoc();

// Xử lý cập nhật sản phẩm
if (isset($_POST['update_product'])) {
    $product_name = trim($_POST['product_name']);
    $price = $_POST['price'];
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    $old_image = $_POST['old_image'];
    
    $err = [];

    // Validate dữ liệu
    if(empty($product_name)) {
        $err['product_name'] = "Vui lòng nhập tên sản phẩm";
    }
    if(empty($price)) {
        $err['price'] = "Vui lòng nhập giá sản phẩm";
    }
    if(empty($category_id)) {
        $err['category_id'] = "Vui lòng chọn danh mục";
    }

    // Xử lý upload ảnh mới nếu có
    if($_FILES['image']['size'] > 0) {
        if($_FILES['image']['size'] > 5*1024*1024) {
            $err['image'] = "Ảnh không được quá 5MB";
        }
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed)) {
            $err['image'] = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)";
        }
    }

    if(empty($err)) {
        try {
            if($_FILES['image']['size'] > 0) {
                $target_dir = "../upload/";
                $image_path = $target_dir . time() . '_' . basename($_FILES['image']['name']);
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    // Xóa ảnh cũ
                    if(!empty($old_image) && file_exists($old_image)) {
                        unlink($old_image);
                    }
                    
                    $sql = "UPDATE products SET product_name = ?, price = ?, image = ?, description = ?, idCategory = ? WHERE product_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sissii", $product_name, $price, $image_path, $description, $category_id, $product_id);
                } else {
                    throw new Exception("Không thể upload ảnh!");
                }
            } else {
                $sql = "UPDATE products SET product_name = ?, price = ?, description = ?, idCategory = ? WHERE product_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sisii", $product_name, $price, $description, $category_id, $product_id);
            }

            if ($stmt->execute()) {
                echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location.href='index_sanpham.php';</script>";
                exit();
            } else {
                throw new Exception("Lỗi cập nhật dữ liệu: " . $conn->error);
            }
        } catch(Exception $e) {
            $err['db'] = "Lỗi: " . $e->getMessage();
        }
    }
}

// Lấy danh sách danh mục
$sql_categories = "SELECT * FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
?>

<div class="container-fluid">
    <h2 class="text-center my-4">CHỈNH SỬA SẢN PHẨM</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Sửa Sản Phẩm</h5>
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

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="old_image" value="<?php echo $product['image']; ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php while($category = $result_categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo ($product['idCategory'] == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control"
                                value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giá sản phẩm <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control"
                                value="<?php echo $product['price']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ảnh hiện tại</label>
                            <div>
                                <img src="<?php echo $product['image']; ?>" alt="Ảnh sản phẩm" class="d-block mb-2"
                                    style="max-width: 200px">
                            </div>
                            <label class="form-label">Thay đổi ảnh</label>
                            <input type="file" name="image" class="form-control" accept="image/*"
                                onchange="previewImage(event)">
                            <img id="preview" class="mt-2" style="max-width: 200px; display: none;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả sản phẩm</label>
                            <textarea name="description" class="form-control"
                                rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" name="update_product" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Thay Đổi
                    </button>
                    <a href="index_sanpham.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        const preview = document.getElementById('preview');

        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }

        reader.readAsDataURL(file);
    }
}
</script>