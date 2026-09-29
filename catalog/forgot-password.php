<?php
  session_start();
  ob_start();
  include '../connect.php';
  include '../function.php';
?>


<?php
  $err = '';
  $mail = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $mail = $_POST['mail'];

      // Truy vấn để lấy thông tin sản phẩm
      $sql = "SELECT * FROM users WHERE mail = '$mail'";
      $result = mysqli_query($conn, $sql);

      if ($result && mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);
        $mail = $row['mail'];
        $username = $row['username']; 
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
    $err = "Không thành công";
  }
  else{
  $err = "Đã gửi mật khẩu mới về gmail của bạn. Vui lòng chờ và tiến hành  <a href=\"login.php\">Đăng nhập</a>";
   
  }
        
    } else {
        $err = "Không tìm thấy gmail $mail trong cơ sở dữ liệu";
    }
    
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <style>
    body {
        font-family: 'Arial', sans-serif;
        background: linear-gradient(135deg, #ece9e6, #ffffff);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.1);
        flex-direction: column;
    }

    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50px;
        font-size: 20px;
        cursor: pointer;
    }

    .login-container {
        width: 400px;
        padding: 40px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-container:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
    }

    .login-form h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
        font-weight: bold;
        font-size: 24px;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        color: #555;
        font-weight: 600;
        font-size: 14px;
    }

    .form-group input {
        width: 100%;
        padding: 15px;
        border: 2px solid #ccc;
        border-radius: 8px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        font-size: 16px;
    }

    .form-group input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    button {
        width: 100%;
        padding: 15px;
        background-color: #007bff;
        border: none;
        border-radius: 8px;
        color: #ffffff;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
    }

    button:hover {
        background-color: #0056b3;
        transform: translateY(-2px);
    }

    button:active {
        transform: translateY(1px);
    }

    .form-footer {
        text-align: center;
        margin-top: 20px;
    }

    .form-footer p {
        margin: 0;
        color: #777;
    }

    .form-footer a {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }

    .form-group label {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        color: #555;
        font-weight: 600;
        font-size: 14px;
    }

    .form-group input[type="checkbox"] {
        margin-right: 10px;
    }

    .form-group label:last-child {
        display: flex;
        justify-content: flex-end;
        /* Căn phải nội dung */
        align-items: center;
        /* Căn giữa theo chiều dọc */
    }
    </style>
</head>

<body>
    <?php
    if($err != ''){  ?>
    <div style="width: 400px;border-radius: 15px;margin: 20px;" class="alert alert-primary alert-dismissible fade show"
        role="alert">
        <strong>Thông báo</strong>
        <p><?php echo $err ?></p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php }
  ?>
    <div class="login-container">

        <form class="login-form" id="loginForm" method="POST">
            <h2>Quên mật khẩu</h2>

            <div class="form-group">
                <label for="username">Email của bạn:</label>
                <input type="text" id="username" name="mail" required placeholder="Nhập tên đăng nhập"
                    value="<?php echo $mail; ?>">
            </div>
            <button type="submit">Lấy lại mật khẩu</button>

            <div class="form-footer">
                <p>Bạn chưa có tài khoản? <a href="sigup.php">Đăng ký ngay</a></p>
            </div>
        </form>
    </div>
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
</script>
<script>
<?php
  $conn->close();
?>