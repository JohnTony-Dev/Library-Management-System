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

// Kiểm tra xem có yêu cầu sửa sách không
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    // Lấy thông tin sách từ form
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $quantity = $_POST['quantity'];

    // Kiểm tra xem người dùng đã chọn hình ảnh mới hay không
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $upload_dir = 'Book/';

        // Di chuyển hình ảnh tải lên vào thư mục images
        move_uploaded_file($image_tmp_name, $upload_dir . $image_name);

        // Cập nhật thông tin sách kèm theo đường dẫn hình ảnh mới
        $stmt = $pdo->prepare('UPDATE books SET title = ?, author = ?, quantity = ?, image = ? WHERE id = ?');
        $stmt->execute([$title, $author, $quantity, $upload_dir . $image_name, $book_id]);
    } else {
        // Nếu không có hình ảnh mới, chỉ cập nhật thông tin sách
        $stmt = $pdo->prepare('UPDATE books SET title = ?, author = ?, quantity = ? WHERE id = ?');
        $stmt->execute([$title, $author, $quantity, $book_id]);
    }

    // Chuyển hướng người dùng về trang quản lý sách
    header('Location: admin_book.php');
    exit();
}

// Lấy thông tin sách từ cơ sở dữ liệu để hiển thị trong form sửa
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Nếu không có ID sách được cung cấp, chuyển hướng người dùng về trang quản lý sách
    header('Location: admin_book.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sách</title>
    <style>
        <?php include "styles.css" ?>
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>Trang admin</h1>
            <div class="user-options">
                <a href="admin.php">Trang chủ</a>
                <a href="admin_user.php">Quản lý người dùng</a>
                <a href="admin_borrow.php">Quản lý mượn sách</a>
                <a href="admin_booking.php">Quản lý đặt hẹn</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <h2>Chỉnh sửa sách</h2>
    <br>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
        <input type="text" name="title" value="<?php echo $book['title']; ?>" placeholder="Tiêu đề sách" required>
        <input type="text" name="author" value="<?php echo $book['author']; ?>" placeholder="Tác giả" required>
        <input type="number" name="quantity" value="<?php echo $book['quantity']; ?>" placeholder="Số lượng" required>
        <input type="file" name="image" accept="image/*">
        <button type="submit">Lưu</button>
    </form>
</body>
</html>
