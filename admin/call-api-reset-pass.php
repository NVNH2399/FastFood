<?php

include '../connect.php';
include '../function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mail = $_POST['mail'];
  $username = $_POST['username'];
  $pass = randomNumberString();

  $body = '
  <div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
      <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
          <div style="text-align: center; margin-bottom: 20px;">
              <h1 style="color: #333;">Khôi phục Mật khẩu</h1>
          </div>
          <div style="font-size: 16px; line-height: 1.5; color: #555;">
              <p>Xin chào,</p>
              <p>Chúng tôi đã nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn.</p>
              <p>Tên đăng nhập của bạn là: <strong style="color: red;">' . htmlspecialchars($username) . '</strong></p>
              <p>Mật khẩu mới của bạn là: <strong style="color: red;">' . htmlspecialchars($pass) . '</strong></p>
              <p>Vui lòng đăng nhập và thay đổi mật khẩu nếu cần thiết.</p>
              <p>Cảm ơn bạn!</p>
          </div>
          <div style="margin-top: 20px; text-align: center; font-size: 14px; color: #999;">
              
              <p>Đội ngũ hỗ trợ khách hàng</p>
          </div>
      </div>
  </div>
  ';
  
  // URL của API
  $url = 'https://namhoai.id.vn/api/v1/send-mail.php';
  $data = [
      'to' => "$mail",
      'subject' => 'Resend password',
      'body' => "$body"
  ];
  
  // Khởi tạo cURL
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: xY7tG9wAq4Zr5vB1nP2m'
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  
  // Gửi yêu cầu và nhận phản hồi
  $response = curl_exec($ch);
  
  // Đóng cURL
  curl_close($ch);
  //lưu vào db
  $passHash = password_hash($pass, PASSWORD_DEFAULT);
  $sql = "UPDATE `users` SET `password`='$passHash' WHERE  `mail`='$mail'";
  if(!mysqli_query($conn, $sql)){
    echo "save db fail ";
  }
  else{
  // Trả về phản hồi cho AJAX
  echo $response;
  }
}
else{
  echo 'error fail';
}
?>