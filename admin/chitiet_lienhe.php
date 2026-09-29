<?php
include '../connect.php';

$id = $_GET['id'] ?? 0;
$sql = "SELECT c.*, u.fullname as user_fullname 
        FROM contact c 
        LEFT JOIN users u ON c.idUser = u.id 
        WHERE c.contact_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$contact = $result->fetch_assoc();

if($contact): ?>
<div class="contact-detail">
    <p><strong>Người gửi:</strong> <?php echo htmlspecialchars($contact['fullname']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($contact['email']); ?></p>
    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($contact['phone']); ?></p>
    <p><strong>Thời gian gửi:</strong> <?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></p>
    <p><strong>Nội dung:</strong></p>
    <div class="message-box">
        <?php echo nl2br(htmlspecialchars($contact['content'])); ?>
    </div>
</div>
<?php else: ?>
<p class="text-center">Không tìm thấy thông tin liên hệ</p>
<?php endif; ?>

<style>
.contact-detail p {
    margin-bottom: 10px;
}

.message-box {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    margin-top: 5px;
}
</style>