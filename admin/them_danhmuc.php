<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Xử lý thêm danh mục
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    $err = [];

    // Kiểm tra tên danh mục
    if(empty($category_name)) {
        $err['category_name'] = "Vui lòng nhập tên danh mục";
    } else {
        // Kiểm tra trùng tên
        $check_sql = "SELECT COUNT(*) as count FROM categories WHERE category_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $category_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $count = $check_result->fetch_assoc()['count'];
        
        if($count > 0) {
            $err['category_name'] = "Tên danh mục này đã tồn tại. Vui lòng chọn tên khác.";
        }
    }

    // Xử lý upload ảnh
    $target_dir = "../upload/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if($_FILES['image']['size'] > 0) {
        $image = $_FILES['image']['name'];
        $temp_name = $_FILES['image']['tmp_name'];
        $image_path = $target_dir . time() . '_' . basename($image);

        // Kiểm tra file ảnh
        if($_FILES['image']['size'] > 5*1024*1024) {
            $err['image'] = "Ảnh không được quá 5MB";
        }
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        if(!in_array($ext, $allowed)) {
            $err['image'] = "Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif)";
        }
    } else {
        $err['image'] = "Vui lòng chọn ảnh cho danh mục";
    }

    // Nếu không có lỗi thì thêm vào database
    if(empty($err)) {
        if (move_uploaded_file($temp_name, $image_path)) {
            $sql = "INSERT INTO categories (category_name, image) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $category_name, $image_path);

            if ($stmt->execute()) {
                echo "<script>alert('Thêm danh mục thành công!'); window.location.href='index_danhmucsanpham.php';</script>";
            } else {
                echo "<script>alert('Có lỗi xảy ra khi thêm danh mục!');</script>";
                if(file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        } else {
            echo "<script>alert('Có lỗi khi upload ảnh!');</script>";
        }
    }
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">THÊM DANH MỤC MỚI</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Thêm Danh Mục</h5>
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
                            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="category_name" class="form-control"
                                value="<?php echo isset($category_name) ? htmlspecialchars($category_name) : ''; ?>"
                                required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh <span class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control" accept="image/*" required
                                onchange="previewImage(event)">
                            <img id="preview" class="mt-2" style="max-width: 200px; display: none;">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="add_category" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Danh Mục
                    </button>
                    <a href="index_danhmucsanpham.php" class="btn btn-secondary">
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