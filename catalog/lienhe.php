<?php 
include '../pages/header.php';

// Nếu đã đăng nhập, lấy thông tin user từ database
$userInfo = null;
if(isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT fullname, mail FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $userInfo = $result->fetch_assoc();
}
?>

<link rel="stylesheet" href="../catalog/css/style_lienhe.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

<div class="contact-container">
    <div class="contact-card">
        <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
        <?php endif; ?>

        <div class="card-header">
            <h3>Liên hệ với chúng tôi</h3>
        </div>

        <div class="card-body">
            <form method="POST" action="xuly_lienhe.php">
                <div class="form-group">
                    <label for="hoten">Họ tên:</label>
                    <div class="input-container">
                        <?php if(isset($_SESSION['username'])): ?>
                        <input type="text" class="form-control" id="hoten" name="hoten"
                            value="<?php echo htmlspecialchars($userInfo['fullname']); ?>" readonly>
                        <?php else: ?>
                        <input type="text" class="form-control" id="hoten" name="hoten" required
                            placeholder="Nhập họ tên của bạn">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <div class="input-container">
                        <?php if(isset($_SESSION['username'])): ?>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($userInfo['mail']); ?>" readonly>
                        <?php else: ?>
                        <input type="email" class="form-control" id="email" name="email" required
                            placeholder="Nhập địa chỉ email">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sdt">Số điện thoại:</label>
                    <div class="input-container">
                        <input type="tel" class="form-control" id="sdt" name="phone" pattern="^0\d{9}$"
                            title="Số điện thoại phải bắt đầu bằng số 0 và có 10 chữ số"
                            placeholder="Nhập số điện thoại">
                    </div>
                </div>

                <div class="form-group">
                    <label for="noidung">Nội dung:</label>
                    <div class="input-container">
                        <textarea class="form-control" id="noidung" name="content" required
                            placeholder="Nhập nội dung liên hệ"></textarea>
                    </div>
                </div>

                <div class="btn-submit-container">
                    <button type="submit" class="btn-submit">Gửi liên hệ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Thêm style cho input readonly */
input[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
    opacity: 0.7;
}
</style>

<?php include '../pages/footer.php';?>