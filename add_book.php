<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kiểm tra quyền truy cập của người dùng
if ($_SESSION['role'] !== 'admin') {
    header('Location: access_denied.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Xử lý khi người dùng gửi form thêm sách
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin sách từ form
    $title = $_POST['title'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];
    
    // Upload ảnh sách và lưu đường dẫn vào thư mục Book
    $image_path = 'Book/' . $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

    // Thêm sách mới vào cơ sở dữ liệu
    $stmt = $pdo->prepare('INSERT INTO books (title, author, quantity, image) VALUES (?, ?, ?, ?)');
    $stmt->execute([$title, $author, $quantity, $image_path]);

    // Chuyển hướng người dùng về trang quản lý sách
    header('Location: admin_book.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sách Mới</title>
    <style>
        <?php include "styles.css" ?>
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>Thêm Sách Mới</h1>
            <div class="user-options">
                <a href="admin.php">Trang Admin</a>
                <a href="admin_user.php">Quản lý người dùng</a>
                <a href="admin_borrow.php">Quản lý mượn sách</a>
                <a href="admin_booking.php">Quản lý đặt hẹn</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
    </header>
    <h2>Thông Tin Sách Mới</h2>
    <br>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Tiêu đề sách" required>
        <input type="text" name="author" placeholder="Tác giả" required>
        <input type="number" name="quantity" placeholder="Số lượng" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Thêm sách</button>
    </form>
</body>
</html>
