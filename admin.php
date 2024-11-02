<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-image: url(Image/Image_1.gif);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 15px 0;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            overflow: hidden;
        }

        h1 {
            margin: 0;
        }

        .user-options {
            float: right;
            margin-top: 5px;
        }

        .user-options a {
            color: #fff;
            text-decoration: none;
            margin-left: 15px;
        }

        .user-options a:hover {
            text-decoration: underline;
        }

        .admin-options {
            margin-top: 50px;
            text-align: center;
        }

        .admin-options a {
            display: block;
            width: 200px;
            margin: 10px auto;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .admin-options a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Trang admin</h1>
            <div class="user-options">
                <a href="admin_user.php">Quản lý người dùng</a>
                <a href="admin_book.php">Quản lý sách</a>
                <a href="admin_borrow.php">Quản lý mượn sách</a>
                <a href="admin_booking.php">Quản lý đặt hẹn</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="admin-options">
            <a href="admin_user.php">Quản lý người dùng</a>
            <a href="admin_book.php">Quản lý sách</a>
            <a href="admin_borrow.php">Quản lý mượn sách</a>
            <a href="admin_booking.php">Quản lý đặt hẹn</a>
        </div>
    </div>
</body>
</html>
