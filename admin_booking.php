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

// Xác nhận mượn sách khi người dùng nhấn nút
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking_id'])) {
    $confirm_booking_id = $_POST['confirm_booking_id'];
    $borrow_date = date('Y-m-d');

    // Lấy thông tin đặt hẹn từ bảng booking
    $stmt = $pdo->prepare('SELECT user_id, book_id FROM booking WHERE id = ?');
    $stmt->execute([$confirm_booking_id]);
    $booking_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Chèn thông tin vào bảng borrow_history
    $stmt = $pdo->prepare('INSERT INTO borrow_history (user_id, book_id, borrow_date) VALUES (?, ?, ?)');
    $stmt->execute([$booking_info['user_id'], $booking_info['book_id'], $borrow_date]);

    // Xoá dòng đặt hẹn khỏi bảng booking
    $stmt = $pdo->prepare('DELETE FROM booking WHERE id = ?');
    $stmt->execute([$confirm_booking_id]);
}

// Lấy danh sách đặt hẹn từ cơ sở dữ liệu
$stmt = $pdo->query('SELECT booking.id, users.username, books.title, booking.booking_date
                     FROM booking
                     INNER JOIN users ON booking.user_id = users.id
                     INNER JOIN books ON booking.book_id = books.id');
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đặt hẹn</title>
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
                <a href="admin_book.php">Quản lý sách</a>
                <a href="admin_borrow.php">Quản lý mượn sách</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <h2>Quản lý đặt hẹn</h2>
    <br>
    <div class="container">
        <div class="booking-list">
            <?php if (count($bookings) > 0) { ?>
                <table width="100%" border="1 solid" cellpadding="5">
                    <tr bgcolor="lightblue">
                        <th width="5%">Người đặt</th>
                        <th width="20%">Tên sách</th>
                        <th width="5%">Ngày đặt</th>
                        <th width="5%">Hành động</th>
                    </tr>
                    <?php foreach ($bookings as $booking) { ?>
                        <tr>
                            <td><?php echo $booking['username']; ?></td>
                            <td><?php echo $booking['title']; ?></td>
                            <td align="center"><?php echo $booking['booking_date']; ?></td>
                            <td align="center">
                                <form action="" method="POST">
                                    <input type="hidden" name="confirm_booking_id" value="<?php echo $booking['id']; ?>">
                                    <button type="submit" align="center" >Xác nhận mượn</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>Không có đặt hẹn nào trong cơ sở dữ liệu.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
