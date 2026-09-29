<?php
ob_start();
session_start();

// Kiểm tra đăng nhập
if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

include '../pages/header.php';
?>

<div class="container">
    <!-- <div class="checkout-steps">
        <div class="step ">
            <div class="step-number">1</div>
            <div class="step-label">Giỏ hàng</div>
        </div>
        <div class="step ">
            <div class="step-number">2</div>
            <div class="step-label">Thông tin giao hàng</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-label">Phương thức thanh toán</div>
        </div>
        <div class="step active">
            <div class="step-number ">4</div>
            <div class="step-label">Hoàn thành</div>
        </div>
    </div> -->
    <div class="success-message">
        <h1>Cảm ơn bạn đã đặt hàng!</h1>
        <p>Chúng tôi sẽ liên hệ và giao hàng đến bạn trong thời gian sớm nhất.</p>
        <div class="countdown">Tự động chuyển về trang chủ sau <span id="timer">5</span> giây</div>
        <a href="dathang.php" class="continue-shopping">Tiếp tục mua sắm</a>
    </div>
</div>

<style>
.container {
    max-width: 800px;
    margin: 50px auto;
    text-align: center;
    padding: 20px;
}

.success-message {
    background: #fff;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h1 {
    color: #28a745;
    margin-bottom: 20px;
}

p {
    color: #666;
    margin: 20px 0;
    font-size: 18px;
}

.countdown {
    color: #666;
    margin: 15px 0;
    font-size: 14px;
}

.continue-shopping {
    display: inline-block;
    padding: 12px 24px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 20px;
    transition: background 0.3s;
}

.continue-shopping:hover {
    background: #0056b3;
}

.checkout-steps {
    display: flex;
    justify-content: center;
    margin: 30px 0;
}

.step {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    position: relative;
}

.step:not(:last-child)::after {
    content: '';
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    width: 30px;
    height: 2px;
    background: #ddd;
}

.step.active {
    color: var(--primary-red);
}

.step.active .step-number {
    background: var(--primary-red);
    color: white;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}
</style>

<script>
// Đếm ngược và chuyển hướng
let timeLeft = 5;
const timerElement = document.getElementById('timer');

const timer = setInterval(() => {
    timeLeft--;
    timerElement.textContent = timeLeft;

    if (timeLeft <= 0) {
        clearInterval(timer);
        window.location.href = 'dathang.php';
    }
}, 1000);
</script>

<?php 
include '../pages/footer.php';
ob_end_flush();
?>