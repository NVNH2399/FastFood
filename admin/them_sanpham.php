<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Xử lý thêm sản phẩm
if (isset($_POST['add_product'])) {
    $product_name = trim($_POST['product_name']);
    $price = $_POST['price'];
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];
    
    $err = [];
    
    // Validate dữ liệu
    if(empty($product_name)) {
        $err['product_name'] = "Vui lòng nhập tên sản phẩm";
    } else {
        // Kiểm tra trùng tên
        $check_sql = "SELECT COUNT(*) as count FROM products WHERE product_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $product_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $count = $check_result->fetch_assoc()['count'];
        
        if($count > 0) {
            $err['product_name'] = "Tên sản phẩm này đã tồn tại";
        }
    }

    if(empty($price)) {
        $err['price'] = "Vui lòng nhập giá sản phẩm";
    }

    if(empty($category_id)) {
        $err['category_id'] = "Vui lòng chọn danh mục";
    }

    // Xử lý upload ảnh
    $target_dir = "../upload/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if($_FILES['image']['size'] == 0) {
        $err['image'] = "Vui lòng chọn ảnh sản phẩm";
    } else if($_FILES['image']['size'] > 5*1024*1024) {
        $err['image'] = "Ảnh không được quá 5MB";
    } else {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed)) {
            $err['image'] = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)";
        }
    }

    // Thêm sản phẩm nếu không có lỗi
    if(empty($err)) {
        $image = $target_dir . time() . '_' . basename($_FILES['image']['name']);
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
            $sql = "INSERT INTO products (product_name, price, image, description, idCategory) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissi", $product_name, $price, $image, $description, $category_id);
            
            if($stmt->execute()) {
                echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href='index_sanpham.php';</script>";
            } else {
                echo "<script>alert('Có lỗi xảy ra khi thêm sản phẩm!');</script>";
                if(file_exists($image)) {
                    unlink($image);
                }
            }
        } else {
            echo "<script>alert('Có lỗi khi upload ảnh!');</script>";
        }
    }
}

// Lấy danh sách danh mục
$sql_categories = "SELECT * FROM categories ORDER BY category_name ASC"; 
$result_categories = $conn->query($sql_categories);
?>

<div class="container-fluid">
    <h2 class="text-center my-4">THÊM SẢN PHẨM MỚI</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Thêm Sản Phẩm</h5>
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php while($category = $result_categories->fetch_assoc()): ?>
                                <option value="<?php echo $category['category_id']; ?>"
                                    <?php echo (isset($category_id) && $category_id == $category['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control"
                                value="<?php echo isset($product_name) ? htmlspecialchars($product_name) : ''; ?>"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giá <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control"
                                value="<?php echo isset($price) ? $price : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ảnh sản phẩm <span class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control" accept="image/*"
                                onchange="previewImage(event)" required>
                            <img id="preview" class="mt-2" style="max-width: 200px; display: none;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control"
                                rows="4"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="add_product" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Sản Phẩm
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