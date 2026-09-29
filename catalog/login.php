<?php
session_start();
ob_start();
include '../connect.php';
?>

<?php
// Kiểm tra đăng nhập bằng cookie
if(isset($_COOKIE['username']) && isset($_COOKIE['password'])){
   $userCookie = $_COOKIE['username'];
   $sql = "SELECT * FROM users WHERE username = '$userCookie'";
   $result = mysqli_query($conn, $sql);
   
   if ($result && mysqli_num_rows($result) > 0) {
       $row = mysqli_fetch_assoc($result);
       // Thêm kiểm tra trạng thái tài khoản
       if($row['status'] === 'blocked') {
           setcookie('username', '', time() - 3600, "/");
           setcookie('password', '', time() - 3600, "/");
           header("Location: login.php?msg=Tài khoản của bạn đã bị khóa. Vui lòng liên hệ admin để được hỗ trợ.");
           exit();
       }
       
       if(password_verify($_COOKIE['password'], $row['password'])){
           // Lưu session
           $_SESSION['username'] = $userCookie;
           $_SESSION['fullname'] = $row['fullname'];
           $_SESSION['id'] = $row['id'];
           $_SESSION['rule'] = $row['rule'];

           // Điều hướng theo role
           switch($row['rule']) {
               case 1: // Admin
                   header("Location: ../admin/index.php"); 
                   break;
               case 3: // Nhân viên
                   header("Location: ../staff/index.php");
                   break; 
               default: // Khách hàng
                   header("Location: index.php");
                   break;
           }
           exit();
       }
   }
}

// Xử lý đăng nhập form
$err = false;
$err_msg = '';
$redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '../';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $username = strtolower(trim($_POST['username']));
   $password = $_POST['password'];

   if(empty($username)){
       $err = true;
       $err_msg = 'Vui lòng nhập tên đăng nhập!';
   } else {
       $sql = "SELECT * FROM users WHERE username = ?";
       $stmt = mysqli_prepare($conn, $sql);
       mysqli_stmt_bind_param($stmt, "s", $username);
       mysqli_stmt_execute($stmt);
       $result = mysqli_stmt_get_result($stmt);

       if ($result && mysqli_num_rows($result) > 0) {
           $row = mysqli_fetch_assoc($result);
           
           // Kiểm tra trạng thái tài khoản
           if($row['status'] === 'blocked') {
               $err = true;
               $err_msg = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ admin để được hỗ trợ.';
           }
           else if(password_verify($password, $row['password'])){
               $err = false;
               // Lưu session
               $_SESSION['username'] = $username;
               $_SESSION['fullname'] = $row['fullname']; 
               $_SESSION['id'] = $row['id'];
               $_SESSION['rule'] = $row['rule'];

               // Lưu cookie nếu tích "Lưu đăng nhập"
               if(isset($_POST['save'])){
                   setcookie('username', $username, time() + (30 * 24 * 60 * 60), "/");
                   setcookie('password', $password, time() + (30 * 24 * 60 * 60), "/");
               }

               // Điều hướng theo role  
               switch($row['rule']) {
                   case 1: // Admin
                       header("Location: ../admin/index.php");
                       break;
                   case 3: // Nhân viên 
                       header("Location: ../staff/index.php");
                       break;
                   default: // Khách hàng
                       header("Location: $redirect_url");
                       break;
               }
               exit();
           } else {
               $err = true;
               $err_msg = 'Mật khẩu không chính xác!';
           }
       } else {
           $err = true;
           $err_msg = 'Tài khoản không tồn tại!';
       }
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../catalog/css/style_login.css">
</head>

<body>
    <?php
    if (isset($_GET['msg']) && trim($_GET['msg']) != '') { ?>
    <div style="width: 400px; border-radius: 15px; margin: 20px;"
        class="alert alert-primary alert-dismissible fade show" role="alert">
        <strong>Thông báo</strong>
        <p><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php } ?>

    <?php
    if ($err) { ?>
    <div style="width: 400px; border-radius: 15px; margin: 20px;" class="alert alert-danger alert-dismissible fade show"
        role="alert">
        <strong>Đăng nhập thất bại!</strong>
        <p><?php echo htmlspecialchars($err_msg); ?></p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php } ?>

    <div class="login-container">
        <form class="login-form" id="loginForm" method="POST">
            <h2>Đăng Nhập</h2>

            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required placeholder="Nhập tên đăng nhập">
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu">
                <i class="far fa-eye toggle-password" id="toggleConfirmPassword"
                    onclick="togglePassword('password', this)"></i>
            </div>
            <div class="form-group">
                <label>
                    <input name="save" type="checkbox" id="rememberMe" value='1' style="width: 20px" />
                    Lưu thông tin đăng nhập
                </label>
            </div>
            <button type="submit">Đăng Nhập</button>

            <div class="form-footer">
                <p>Bạn chưa có tài khoản? <a href="signup.php">Đăng ký ngay</a></p>
            </div>
            <div class="form-footer">
                <p>Quên mật khẩu? <a href="forgot-password.php">Nhấp vào đây!</a></p>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script>
    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
        const iconClass = icon.classList;

        if (input.type === "password") {
            input.type = "text";
            iconClass.remove('fa-eye');
            iconClass.add('fa-eye-slash');
        } else {
            input.type = "password";
            iconClass.remove('fa-eye-slash');
            iconClass.add('fa-eye');
        }
    }
    </script>
</body>

</html>

<?php
  $conn->close();
?>