<?php
include 'header.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../catalog/login.php");
    exit();
}

// Đếm số lượng theo trạng thái
$count_sql = "SELECT 
    SUM(CASE WHEN status = 'unprocessed' THEN 1 ELSE 0 END) as unprocessed_count,
    SUM(CASE WHEN status = 'processed' THEN 1 ELSE 0 END) as processed_count,
    COUNT(*) as total_count
    FROM contact";
$count_result = $conn->query($count_sql);
$counts = $count_result->fetch_assoc();
?>
<style>
.card {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.card-title {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.card-text {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0;
}

.badge {
    padding: 0.5em 0.75em;
}
</style>
<div class="container-fluid">
    <h2 class="text-center my-4">QUẢN LÝ LIÊN HỆ & CSKH</h2>

    <!-- Thống kê -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning bg-opacity-10 h-100">
                <div class="card-body">
                    <h5 class="card-title">Chưa xử lý</h5>
                    <p class="card-text"><?php echo $counts['unprocessed_count']; ?> liên hệ</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success bg-opacity-10 h-100">
                <div class="card-body">
                    <h5 class="card-title">Đã xử lý</h5>
                    <p class="card-text"><?php echo $counts['processed_count']; ?> liên hệ</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info bg-opacity-10 h-100">
                <div class="card-body">
                    <h5 class="card-title">Tổng số</h5>
                    <p class="card-text"><?php echo $counts['total_count']; ?> liên hệ</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách liên hệ -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">Danh Sách Liên Hệ</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Người gửi</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Nội dung</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT contact.*, users.fullname as user_fullname 
                                FROM contact 
                                LEFT JOIN users ON contact.idUser = users.id 
                                ORDER BY contact.created_at DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['contact_id']; ?></td>
                            <td>
                                <?php 
                                echo htmlspecialchars($row['fullname']);
                                if($row['idUser']) {
                                    echo ' <span class="badge bg-info">Thành viên</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?: 'Không có'); ?></td>
                            <td>
                                <div class="text-truncate" style="max-width: 300px;"
                                    title="<?php echo htmlspecialchars($row['content']); ?>">
                                    <?php echo htmlspecialchars($row['content']); ?>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td class="text-center">
                                <?php if($row['status'] == 'processed'): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Đã xử lý
                                </span>
                                <?php else: ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock"></i> Chưa xử lý
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="viewDetail(<?php echo $row['contact_id']; ?>)"
                                    class="btn btn-primary btn-sm" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="8" class="text-center">Không có liên hệ nào</td>
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

<!-- Modal xem chi tiết -->
<div class="modal fade" id="contactDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết liên hệ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contactDetailContent">
                <!-- Nội dung sẽ được load bằng Ajax -->
            </div>
        </div>
    </div>
</div>

<script>
function viewDetail(id) {
    $.get('chitiet_lienhe.php', {
        id: id
    }, function(response) {
        $('#contactDetailContent').html(response);
        $('#contactDetail').modal('show');
    });
}

function updateStatus(id, status) {
    if (confirm('Bạn có chắc muốn thay đổi trạng thái?')) {
        $.post('capnhat_lienhe.php', {
            id: id,
            status: status
        }, function(response) {
            alert('Cập nhật trạng thái thành công!');
            location.reload();
        }).fail(function() {
            alert('Có lỗi xảy ra khi cập nhật trạng thái!');
        });
    }
}
</script>