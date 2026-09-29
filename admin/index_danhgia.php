<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}
?>

<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ ĐÁNH GIÁ</h2>

    <!-- Nút thêm mới -->
    <!-- <div class="mb-3">
        <a href="them_danhgia.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm Đánh Giá Mới
        </a>
    </div> -->

    <!-- Danh sách đánh giá -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Đánh Giá</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Người đánh giá</th>
                            <th>Sản phẩm</th>
                            <th>Đánh giá</th>
                            <th>Nội dung</th>
                            <th style="width: 100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT r.*, p.product_name 
                               FROM reviews r
                               JOIN products p ON r.idProduct = p.product_id 
                               ORDER BY r.review_id DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['review_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td>
                                <div class="stars">
                                    <?php 
                                    $rating = $row['rating'];
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $rating) {
                                            echo '<i class="fas fa-star text-warning"></i>';
                                        } else {
                                            echo '<i class="far fa-star text-muted"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="ms-2 badge bg-warning text-dark">
                                        <?php echo $rating; ?>/5
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 300px;"
                                    title="<?php echo htmlspecialchars($row['content']); ?>">
                                    <?php echo htmlspecialchars($row['content']); ?>
                                </div>
                            </td>
                            <td>
                                <a href="sua_danhgia.php?id=<?php echo $row['review_id']; ?>"
                                    class="btn btn-primary btn-sm" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- <a href="javascript:void(0)" onclick="deleteReview(<?php echo $row['review_id']; ?>)"
                                    class="btn btn-danger btn-sm" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </a> -->
                                <a href="chitiet_danhgia.php?id=<?php echo $row['review_id']; ?>"
                                    class="btn btn-info btn-sm" title="Chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có đánh giá nào</td>
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

<style>
.stars {
    display: inline-flex;
    align-items: center;
    gap: 2px;
}

.stars i {
    font-size: 14px;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}
</style>

<script>
function deleteReview(id) {
    if (confirm('Bạn có chắc muốn xóa đánh giá này?')) {
        $.post('xoa_danhgia.php', {
            id: id // Đổi review_id thành id cho thống nhất
        }, function(response) {
            alert('Xóa đánh giá thành công!');
            location.reload();
        }).fail(function() {
            alert('Có lỗi xảy ra khi xóa đánh giá!');
        });
    }
}
</script>