<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ BANNER</h2>

    <!-- Nút thêm mới -->
    <div class="mb-3">
        <a href="them_banner.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Banner Mới
        </a>
    </div>

    <!-- Danh sách banner -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Banner</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Link</th>
                            <th>Thứ tự</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM banners ORDER BY sort_order ASC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['banner_id']; ?></td>
                            <td>
                                <img src="<?php echo $row['image']; ?>" alt="Banner"
                                    style="max-width: 150px; height: auto;">
                            </td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['link']); ?></td>
                            <td><?php echo $row['sort_order']; ?></td>
                            <td>
                                <span
                                    class="badge <?php echo $row['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $row['status'] == 'active' ? 'Hiển thị' : 'Ẩn'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="sua_banner.php?id=<?php echo $row['banner_id']; ?>"
                                    class="btn btn-primary btn-sm" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="xoa_banner.php?id=<?php echo $row['banner_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc muốn xóa banner này?')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có banner nào</td>
                        </tr>
                        <?php
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>