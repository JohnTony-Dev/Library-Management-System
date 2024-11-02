<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <style>
        <?php include "styles.css" ?>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .register-link {
            text-align: center;
            margin-top: 10px;
        }

        .register-link a {
            color: #007bff;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Đăng nhập</h2>
    <?php
    // Hiển thị thông báo lỗi nếu có
    if (isset($_GET['error']) && $_GET['error'] === 'incorrect_credentials') {
        echo '<p class="error-message">Tên đăng nhập hoặc mật khẩu không đúng, vui lòng đăng nhập lại.</p>';
    } elseif (isset($_GET['error']) && $_GET['error'] === 'blacklisted') {
        echo '<p class="error-message">Tài khoản của bạn đang bị chặn. Vui lòng liên hệ với quản trị viên để biết thêm chi tiết.</p>';
    }
    ?>
    <form action="login.php" method="post">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Đăng nhập</button>
    </form>
    <div class="register-link">
        <a href='register.php' title='Đăng ký'>Đăng ký</a>
    </div>
</div>
</body>
</html>
