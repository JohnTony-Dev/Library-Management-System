<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kiểm tra nếu không có ID sách được chuyển đến
if (!isset($_GET['book_id'])) {
    header('Location: borrow_books.php');
    exit();
}

// Kiểm tra nếu người dùng thuộc blacklisted
if ($_SESSION['blacklisted'] === true) {
    $blacklist_message = "Bạn thuộc danh sách đen nên không được mượn sách.";
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Xử lý khi người dùng nhấn nút đặt lịch hẹn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['book_id']) && isset($_SESSION['user_id'])) {
        $book_id = $_POST['book_id'];
        $user_id = $_SESSION['user_id'];
        $booking_date = $_POST['booking_date'];

        // Kiểm tra nếu số lượng sách còn lại là 0
        $stmt = $pdo->prepare('SELECT quantity FROM books WHERE id = ?');
        $stmt->execute([$book_id]);
        $quantity = $stmt->fetchColumn();

        if ($quantity > 0 && $booking_date >= date('Y-m-d')) {
            // Thực hiện đặt lịch hẹn bằng cách thêm vào bảng booking
            $stmt = $pdo->prepare('INSERT INTO booking (user_id, book_id, booking_date) VALUES (?, ?, ?)');
            $stmt->execute([$user_id, $book_id, $booking_date]);

            // Cập nhật số lượng sách trong bảng books
            $stmt = $pdo->prepare('UPDATE books SET quantity = quantity - 1 WHERE id = ?');
            $stmt->execute([$book_id]);

            // Chuyển hướng người dùng đến trang lịch sử mượn sách
            header('Location: booking_history.php');
            exit();
        } else {
            // Hiển thị thông báo lỗi nếu số lượng sách là 0 hoặc ngày hẹn không hợp lệ
            $error_message = "Không thể đặt lịch hẹn. Vui lòng kiểm tra lại số lượng sách và ngày hẹn.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch hẹn</title>
    <style>
        <?php include "styles.css" ?>
    </style>
</head>

<body>
    <header>
        <div class="container">
            <h1>Trang mượn sách</h1>
            <div class="user-options">
                <a href="borrow_books.php">Trang chủ</a>
                <a href="edit_profile.php">Chỉnh sửa thông tin</a>
                <a href="history.php">Xem lịch sử mượn sách</a>
                <a href="booking_history.php">Xem lịch hẹn</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>

    <div class="booking-form">
        <h2>Đặt lịch hẹn</h2>
        <br>
        <?php if (isset($blacklist_message)) { ?>
            <p><?php echo $blacklist_message; ?></p>
            <a href="borrow_books.php">Quay lại</a>
        <?php } else { ?>
            <?php if (isset($error_message)) { ?>
                <p><?php echo $error_message; ?></p>
            <?php } ?>
            <form method="post">
                <input type="hidden" name="book_id" value="<?php echo $_GET['book_id']; ?>">
                <label for="booking_date">Nhập ngày hẹn:</label><br>
                <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>" required><br>
                <br>
                <button type="submit">Xác nhận đặt lịch hẹn</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>
