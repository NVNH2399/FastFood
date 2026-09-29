<?php
  include '../connect.php';

  $err = [];
  $username = $fullname = $mail = $password = '';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username']));
    $fullname = trim($_POST['fullname']);
    $mail = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Kiểm tra độ mạnh của mật khẩu
    if (strlen($password) < 8) {
        $err['err_pass'] = "Mật khẩu phải có ít nhất 8 ký tự!";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $err['err_pass'] = "Mật khẩu phải có ít nhất 1 chữ in hoa!";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $err['err_pass'] = "Mật khẩu phải có ít nhất 1 chữ thường!";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $err['err_pass'] = "Mật khẩu phải có ít nhất 1 số!";
    }
    if (!preg_match("/[@$!%*?&]/", $password)) {
        $err['err_pass'] = "Mật khẩu phải có ít nhất 1 ký tự đặc biệt (@$!%*?&)!";
    }

    if(empty($err)) {
        $sql = "SELECT * FROM users WHERE username = '$username' OR mail = '$mail'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if($row['username'] == $username){
                $err['err_username'] = "Tên đăng nhập đã tồn tại vui lòng chọn tên khác!";
            }
            if($row['mail'] == $mail){
                $err['err_email'] = "Gmail đã tồn tại vui lòng chọn gmail khác!";
            }
        } else {
            $passhash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO `users`(`username`, `fullname`, `password`, `mail`, `rule`) 
                    VALUES ('$username','$fullname','$passhash','$mail','2')";
            if (mysqli_query($conn, $sql)){
                $msg = "Đăng ký tài khoản thành công.";
                header("location: login.php?msg=$msg");
            }
        }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../catalog/css/style_signup.css">

</head>

<body>
    <?php if(!empty($err)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 15px;">
        <strong>Lỗi</strong><br>
        <ul>
            <?php foreach ($err as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="register-container">
        <form class="register-form" id="registerForm" method="POST" onsubmit="return validateForm()">
            <h2>Đăng Ký</h2>

            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required placeholder="Nhập tên đăng nhập"
                    value="<?php echo $username; ?>">
                <div class="error-message" id="usernameError">
                    Tên đăng nhập phải lớn hơn 3 ký tự.
                </div>
            </div>

            <div class="form-group">
                <label for="fullname">Họ và tên:</label>
                <input type="text" id="fullname" name="fullname" required placeholder="Nhập họ và tên"
                    value="<?php echo $fullname; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Nhập email"
                    value="<?php echo $mail; ?>">
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu"
                    pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
                <i class="far fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
                <div class="password-requirements">
                    <p>Mật khẩu phải có ít nhất:</p>
                    <ul>
                        <li id="length">8 ký tự</li>
                        <li id="uppercase">1 chữ in hoa</li>
                        <li id="lowercase">1 chữ thường</li>
                        <li id="number">1 số</li>
                        <li id="special">1 ký tự đặc biệt (@$!%*?&)</li>
                    </ul>
                </div>
            </div>

            <div class="form-group">
                <label for="comfirmpass">Nhập lại mật khẩu:</label>
                <input type="password" id="comfirmpass" name="comfirmpass" required placeholder="Nhập lại mật khẩu">
                <i class="far fa-eye toggle-password" onclick="togglePassword('comfirmpass', this)"></i>
                <div class="error-message" id="passwordError">
                    Mật khẩu và xác nhận mật khẩu phải giống nhau.
                </div>
            </div>

            <button type="submit">Đăng Ký</button>

            <div class="form-footer">
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
            </div>
        </form>
    </div>

    <script>
    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function validatePassword(password) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[@$!%*?&]/.test(password);

        document.getElementById('length').classList.toggle('valid', password.length >= minLength);
        document.getElementById('uppercase').classList.toggle('valid', hasUpperCase);
        document.getElementById('lowercase').classList.toggle('valid', hasLowerCase);
        document.getElementById('number').classList.toggle('valid', hasNumbers);
        document.getElementById('special').classList.toggle('valid', hasSpecialChar);

        return password.length >= minLength && hasUpperCase && hasLowerCase &&
            hasNumbers && hasSpecialChar;
    }

    function validateForm() {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('comfirmpass').value;

        const usernameError = document.getElementById('usernameError');
        const passwordError = document.getElementById('passwordError');

        usernameError.style.display = 'none';
        passwordError.style.display = 'none';

        let isValid = true;

        if (username.length <= 3) {
            usernameError.style.display = 'block';
            isValid = false;
        }

        if (!validatePassword(password)) {
            document.querySelector('.password-requirements').style.display = 'block';
            isValid = false;
        }

        if (password !== confirmPassword) {
            passwordError.style.display = 'block';
            isValid = false;
        }

        return isValid;
    }

    // Kiểm tra mật khẩu khi nhập
    document.getElementById('password').addEventListener('input', function() {
        validatePassword(this.value);
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>