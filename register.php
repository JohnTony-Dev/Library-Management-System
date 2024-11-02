<?php
session_start();

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Khởi tạo biến lỗi
$error_message = '';

// Xử lý khi người dùng gửi biểu mẫu đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin đăng ký từ biểu mẫu
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $existing_user = $stmt->fetch();

    if ($existing_user) {
        $error_message = "Tên đăng nhập đã tồn tại.";
    } else {
        // Thêm người dùng mới vào cơ sở dữ liệu
        $stmt = $pdo->prepare('INSERT INTO users (username, password, name, email) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $password, $name, $email]);

        // Lấy ID của người dùng mới được thêm vào
        $user_id = $pdo->lastInsertId();

        // Thêm vai trò "user" cho người dùng mới vào bảng user_roles
        $stmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
        $stmt->execute([$user_id, 1]); // Role_id của vai trò "user" là 1

        // Chuyển hướng người dùng đến trang đăng nhập
        header('Location: index.php');
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="style2.css">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="vendor/bootstrap.css">
        <link rel="stylesheet" href="vendor/angular-material.min.css">
        <link rel="stylesheet" href="vendor/font-awesome.css">
        <link rel="stylesheet" href="2.css"> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
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
        input[type="email"],
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
    <h2>Đăng Ký</h2>
        <?php if (!empty($error_message)) { ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php } ?>
        <form method="post">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>
            <label for="name">Họ và Tên:</label>
            <input type="text" id="name" name="name" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <br>
            <button type="submit">Đăng Ký</button>
        </form>
        <p>Đã có tài khoản? <a href="index.php">Đăng nhập</a></p>
    </div>
</body>
</html>
