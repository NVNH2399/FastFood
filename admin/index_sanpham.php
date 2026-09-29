<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ SẢN PHẨM</h2>

    <!-- Nút thêm mới -->
    <div class="mb-3">
        <a href="them_sanpham.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Sản Phẩm Mới
        </a>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Sản Phẩm</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Giá</th>
                            <th>Danh mục</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get products list with category names
                        $sql = "SELECT p.*, c.category_name 
                               FROM products p 
                               LEFT JOIN categories c ON p.idCategory = c.category_id 
                               ORDER BY p.product_id DESC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['product_id']; ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($row['image']); ?>"
                                    alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                    style="max-width: 100px; height: auto;">
                            </td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo number_format($row['price'], 0, ',', '.'); ?>₫</td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'Chưa phân loại'); ?></td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;"
                                    title="<?php echo htmlspecialchars($row['description']); ?>">
                                    <?php echo htmlspecialchars($row['description'] ?: 'Chưa có mô tả'); ?>
                                </div>
                            </td>
                            <td>
                                <a href="sua_sanpham.php?id=<?php echo $row['product_id']; ?>"
                                    class="btn btn-primary btn-sm" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="xoa_sanpham.php?id=<?php echo $row['product_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có sản phẩm nào</td>
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