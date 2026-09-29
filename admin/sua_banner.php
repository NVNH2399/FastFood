<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index_banner.php");
    exit();
}

$banner_id = $_GET['id'];

// Xử lý cập nhật banner
if (isset($_POST['update_banner'])) {
    $title = $_POST['title'];
    $link = $_POST['link'];
    $sort_order = $_POST['sort_order'];
    $status = $_POST['status'];
    
    // Kiểm tra xem có upload ảnh mới không
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../upload/banner/";
        $image = $_FILES['image']['name'];
        $temp_name = $_FILES['image']['tmp_name'];
        $image_path = $target_dir . basename($image);

        // Lấy và xóa ảnh cũ
        $sql = "SELECT image FROM banners WHERE banner_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $banner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_banner = $result->fetch_assoc();

        if ($old_banner && file_exists($old_banner['image'])) {
            unlink($old_banner['image']);
        }

        // Upload ảnh mới
        if (move_uploaded_file($temp_name, $image_path)) {
            $sql = "UPDATE banners SET title = ?, image = ?, link = ?, sort_order = ?, status = ? WHERE banner_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssisi", $title, $image_path, $link, $sort_order, $status, $banner_id);
        } else {
            echo "<script>alert('Có lỗi khi upload ảnh mới!');</script>";
            exit();
        }
    } else {
        // Cập nhật không có ảnh mới
        $sql = "UPDATE banners SET title = ?, link = ?, sort_order = ?, status = ? WHERE banner_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $title, $link, $sort_order, $status, $banner_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật banner thành công!'); window.location.href='index_banner.php';</script>";
    } else {
        echo "<script>alert('Có lỗi xảy ra khi cập nhật banner!');</script>";
    }
}

// Lấy thông tin banner hiện tại
$sql = "SELECT * FROM banners WHERE banner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $banner_id);
$stmt->execute();
$result = $stmt->get_result();
$banner = $result->fetch_assoc();

if (!$banner) {
    header("Location: index_banner.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">CHỈNH SỬA BANNER</h2>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Cập Nhật Banner</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" name="title" class="form-control"
                                value="<?php echo htmlspecialchars($banner['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Link</label>
                            <input type="text" name="link" class="form-control"
                                value="<?php echo htmlspecialchars($banner['link']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thứ tự hiển thị</label>
                                    <input type="number" name="sort_order" class="form-control"
                                        value="<?php echo $banner['sort_order']; ?>" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-control">
                                        <option value="active"
                                            <?php echo $banner['status'] == 'active' ? 'selected' : ''; ?>>
                                            Hiển thị
                                        </option>
                                        <option value="inactive"
                                            <?php echo $banner['status'] == 'inactive' ? 'selected' : ''; ?>>
                                            Ẩn
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ảnh hiện tại</label>
                            <div class="mb-2">
                                <img src="<?php echo $banner['image']; ?>" alt="Current Banner"
                                    style="max-width: 100%; height: auto;">
                            </div>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Để trống nếu không muốn thay đổi ảnh</small>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" name="update_banner" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                    <a href="index_banner.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>