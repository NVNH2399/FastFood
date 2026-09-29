<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ DANH MỤC</h2>

    <!-- Nút thêm mới -->
    <div class="mb-3">
        <a href="them_danhmuc.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Danh Mục Mới
        </a>
    </div>

    <!-- Danh sách danh mục -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Danh Mục</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên danh mục</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM categories ORDER BY category_id";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['category_id']; ?></td>
                            <td>
                                <img src="<?php echo $row['image'] ?: '/api/placeholder/80/80'; ?>"
                                    alt="<?php echo htmlspecialchars($row['category_name']); ?>"
                                    style="max-width: 150px; height: auto;">
                            </td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td>
                                <a href="sua_danhmuc.php?id=<?php echo $row['category_id']; ?>"
                                    class="btn btn-primary btn-sm" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="xoa_danhmuc.php?id=<?php echo $row['category_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc muốn xóa danh mục này?')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="4" class="text-center">Không có danh mục nào</td>
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