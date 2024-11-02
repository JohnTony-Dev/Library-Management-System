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

// Lấy danh sách sách từ cơ sở dữ liệu
$stmt = $pdo->query('SELECT * FROM books');
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sách</title>
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

    <?php
        // Hiển thị thông báo lỗi nếu có
        if (isset($_GET['error']) && $_GET['error'] === 'delete') {
            echo '<p class="error-message">Không thể xóa sách này vì đang được mượn hoặc có lịch hẹn đặt.</p>';
        }
    ?>
    <h2>Quản lý sách</h2>
    <br>
    <div class="container">
        <a href="add_book.php" class="add-book-btn">Thêm sách</a>
        <div class="book-list">
            <?php if (count($books) > 0) { ?>
                <table width="100%" align="center" border="1 solid" cellpadding="5">
                    <tr bgcolor="lightblue">
                        <th width="20%">Tiêu đề sách</th>
                        <th width="10%">Tác giả</th>
                        <th width="5%">Số lượng</th>
                        <th width="5%">Hành động</th>
                    </tr>
                    <?php foreach ($books as $book) { ?>
                        <tr>
                            <td><?php echo $book['title']; ?></td>
                            <td align="center"><?php echo $book['author']; ?></td>
                            <td align="center"><?php echo $book['quantity']; ?></td>
                            <td align="center">
                                <a href="edit_book.php?id=<?php echo $book['id']; ?>">Chỉnh sửa</a> |
                                <a href="delete_book.php?id=<?php echo $book['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xoá sách này?')">Xoá</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>Không có sách nào trong cơ sở dữ liệu.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
