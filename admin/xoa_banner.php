<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Lấy thông tin ảnh để xóa file
    $sql = "SELECT image FROM banners WHERE banner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $banner = $result->fetch_assoc();

    // Xóa file ảnh
    if ($banner && file_exists($banner['image'])) {
        unlink($banner['image']);
    }

    // Xóa record từ database
    $sql = "DELETE FROM banners WHERE banner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Xóa banner thành công!'); window.location.href='index_banner.php';</script>";
    } else {
        echo "<script>alert('Có lỗi xảy ra khi xóa banner!'); window.location.href='index_banner.php';</script>";
    }
} else {
    header("Location: index_banner.php");
}
?>