<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Xử lý thêm banner
if (isset($_POST['add_banner'])) {
    $title = $_POST['title'];
    $link = $_POST['link'] ?: '#';
    $sort_order = $_POST['sort_order'];
    $status = $_POST['status'];

    // Xử lý upload ảnh
    $target_dir = "../upload/banner/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image = $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];
    $image_path = $target_dir . basename($image);

    if (move_uploaded_file($temp_name, $image_path)) {
        $sql = "INSERT INTO banners (title, image, link, sort_order, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $title, $image_path, $link, $sort_order, $status);

        if ($stmt->execute()) {
            echo "<script>alert('Thêm banner thành công!'); window.location.href='index_banner.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi thêm banner!');</script>";
        }
    } else {
        echo "<script>alert('Có lỗi khi upload ảnh!');</script>";
    }
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">THÊM BANNER MỚI</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Thêm Banner</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link</label>
                            <input type="text" name="link" class="form-control" value="#" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ảnh banner</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thứ tự hiển thị</label>
                                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-control">
                                        <option value="active">Hiển thị</option>
                                        <option value="inactive">Ẩn</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" name="add_banner" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Banner
                    </button>
                    <a href="index_banner.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>