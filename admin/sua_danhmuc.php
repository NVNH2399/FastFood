<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Kiểm tra và lấy thông tin danh mục
if (!isset($_GET['id'])) { // Sửa từ category_id thành id để đồng nhất với link trong index
    echo "<script>alert('Không tìm thấy ID danh mục!'); window.location.href='index_danhmucsanpham.php';</script>";
    exit();
}

$category_id = $_GET['id'];
$sql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Không tìm thấy danh mục!'); window.location.href='index_danhmucsanpham.php';</script>";
    exit();
}

$category = $result->fetch_assoc();

// Xử lý cập nhật danh mục
if (isset($_POST['update_category'])) {
    $category_name = trim($_POST['category_name']);
    $old_image = $_POST['old_image'];
    $err = [];
    
    // Kiểm tra tên danh mục
    if(empty($category_name)) {
        $err['category_name'] = "Vui lòng nhập tên danh mục";
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
                    // Xóa ảnh cũ nếu có
                    if(!empty($old_image) && file_exists($old_image)) {
                        unlink($old_image);
                    }
                    
                    $sql = "UPDATE categories SET category_name = ?, image = ? WHERE category_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $category_name, $image_path, $category_id);
                } else {
                    throw new Exception("Không thể upload ảnh!");
                }
            } else {
                $sql = "UPDATE categories SET category_name = ? WHERE category_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $category_name, $category_id);
            }

            if ($stmt->execute()) {
                echo "<script>alert('Cập nhật danh mục thành công!'); window.location.href='index_danhmucsanpham.php';</script>";
                exit();
            } else {
                throw new Exception("Lỗi cập nhật dữ liệu: " . $conn->error);
            }
        } catch(Exception $e) {
            $err['db'] = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">CHỈNH SỬA DANH MỤC</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Sửa Danh Mục</h5>
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
                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                <input type="hidden" name="old_image" value="<?php echo $category['image']; ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="category_name" class="form-control"
                                value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ảnh hiện tại</label>
                            <?php if(!empty($category['image'])): ?>
                            <img src="<?php echo $category['image']; ?>" alt="Ảnh danh mục" class="d-block mb-2"
                                style="max-width: 200px">
                            <?php else: ?>
                            <p class="text-muted">Chưa có ảnh</p>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Thay đổi ảnh</label>
                            <input type="file" name="image" class="form-control" accept="image/*"
                                onchange="previewImage(event)">
                            <img id="preview" class="mt-2" style="max-width: 200px; display: none;">
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" name="update_category" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu Thay Đổi
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