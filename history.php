<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Lấy lịch sử mượn sách của người dùng từ cơ sở dữ liệu
$stmt = $pdo->prepare('SELECT borrow_history.borrow_date, borrow_history.return_date, books.title, books.author 
                       FROM borrow_history 
                       INNER JOIN books ON borrow_history.book_id = books.id
                       WHERE borrow_history.user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử mượn sách</title>
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
                <a href="edit_profile.php">Chỉnh sửa thông tin</a>
                <a href="booking_history.php">Xem lịch hẹn sách</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <h2 class="title-header">Lịch sử mượn sách</h2>
    <div class="container">
        <div class="history-list">
            <?php if (count($history) > 0) { ?>
                <table id="abc" width="100%" align="center" border="1 solid" cellpadding="5">
                    <tr bgcolor="lightblue">
                        <th class="history_table" width="20%" align="center">Tên sách</th>
                        <th class="history_table" width="15%" align="center">Tác giả</th>
                        <th class="history_table" width="5%" align="center">Ngày mượn</th>
                        <th class="history_table" width="5%" align="center">Ngày trả</th>
                    </tr>
                    <?php foreach ($history as $record) { ?>
                        <tr>
                            <td><?php echo $record['title']; ?></td>
                            <td align="center"><?php echo $record['author']; ?></td>
                            <td align="center"><?php echo $record['borrow_date']; ?></td>
                            <td align="center"><?php echo $record['return_date']; ?></td>
                        </tr>			
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>Không có lịch sử mượn sách.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
