<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Xử lý lỗi ở đây, ví dụ: chuyển hướng người dùng đến trang đăng nhập
    header('Location: login.php');
    exit();
}
// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Lấy thông tin người dùng từ cơ sở dữ liệu
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý chỉnh sửa thông tin người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Cập nhật thông tin người dùng vào cơ sở dữ liệu
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    $stmt->execute([$name, $email, $_SESSION['user_id']]);

    // Chuyển hướng người dùng về trang chính sau khi cập nhật thành công
    header('Location: borrow_books.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin</title>
    <style>
        <?php include "styles.css" ?>
    </style>
</head>

<body>
    <header>
        <div class="header-box">
            <h2 class="site-name">Trang mượn sách</h2>
            <div class="user-options">
                <a href="borrow_books.php">Trang chủ</a>
                <a href="history.php">Xem lịch sử mượn sách</a>
                <a href="booking_history.php">Xem lịch hẹn</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <h2 class="title-header">Chỉnh sửa thông tin</h2>
    <br>
    <div class="container">
        <div class="user-info">
            <form method="post" class="user-info-form">
                <div class="row">
                    <div class="col">
                        <label for="name">Họ và tên:</label><br>
                    </div>
                    <div class="col">
                        <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required><br>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="email">Email:</label><br>
                    </div>
                    <div class="col">
                        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required><br>
                    </div>
                </div>
                <div class="save">
                    <button type="submit">Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
