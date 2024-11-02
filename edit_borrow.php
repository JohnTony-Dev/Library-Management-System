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

// Kiểm tra xem có tham số borrow_id được truyền không
if (!isset($_POST['borrow_id'])) {
    header('Location: admin_booking.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Lấy borrow_id từ biến POST
$borrow_id = $_POST['borrow_id'];

// Lấy thông tin về mượn sách từ cơ sở dữ liệu
$stmt = $pdo->prepare('SELECT borrow_history.id, users.id AS user_id, users.username, books.id AS book_id, books.title, borrow_history.borrow_date, borrow_history.return_date
                     FROM borrow_history
                     INNER JOIN users ON borrow_history.user_id = users.id
                     INNER JOIN books ON borrow_history.book_id = books.id
                     WHERE borrow_history.id = ?');
$stmt->execute([$borrow_id]);
$borrow = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra xem borrow_id có hợp lệ không
if (!$borrow) {
    header('Location: admin_booking.php');
    exit();
}

// Lấy danh sách người dùng từ cơ sở dữ liệu
$stmt_users = $pdo->query('SELECT id, username FROM users WHERE id  IN (SELECT user_id FROM user_roles WHERE role_id = (SELECT id FROM roles WHERE name = "user"))');
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sách từ cơ sở dữ liệu
$stmt_books = $pdo->query('SELECT id, title FROM books');
$books = $stmt_books->fetchAll(PDO::FETCH_ASSOC);

// Kiểm tra xem sách đã được trả hay chưa
$book_returned = $borrow['return_date'] !== null;

// Xử lý khi người dùng cập nhật thông tin về việc mượn sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['book_id'], $_POST['borrow_date'], $_POST['return_date'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $borrow_date = $_POST['borrow_date'];
    $return_date = $_POST['return_date'];

    // Nếu sách chưa được trả, chỉ cập nhật ngày mượn
    if (!$book_returned) {
        // Cập nhật thông tin mượn sách trong cơ sở dữ liệu
        $stmt_update = $pdo->prepare('UPDATE borrow_history SET user_id = ?, book_id = ?, borrow_date = ? WHERE id = ?');
        $stmt_update->execute([$user_id, $book_id, $borrow_date, $borrow_id]);
    } else {
        // Cập nhật thông tin mượn sách trong cơ sở dữ liệu
        $stmt_update = $pdo->prepare('UPDATE borrow_history SET user_id = ?, book_id = ?, borrow_date = ?, return_date = ? WHERE id = ?');
        $stmt_update->execute([$user_id, $book_id, $borrow_date, $return_date, $borrow_id]);
    }

    // Chuyển hướng người dùng đến trang danh sách mượn sách sau khi cập nhật
    header('Location: admin_booking.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa mượn sách</title>
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
                <a href="admin_booking.php">Quản lý đặt hẹn</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <h2>Chỉnh sửa mượn sách</h2>
    <br>
    <div class="borrow-info">
        <form action="" method="POST">
            <input type="hidden" name="borrow_id" value="<?php echo $borrow['id']; ?>">
            <label for="user_id">Người mượn:</label>
            <select id="user_id" name="user_id" required>
                <?php foreach ($users as $user) { ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $borrow['user_id']) ? 'selected' : ''; ?>><?php echo $user['username']; ?></option>
                <?php } ?>
            </select><br>
            <br>
            <label for="book_id">Tên sách:</label>
            <select id="book_id" name="book_id" required>
                <?php foreach ($books as $book) { ?>
                    <option value="<?php echo $book['id']; ?>" <?php echo ($book['id'] == $borrow['book_id']) ? 'selected' : ''; ?>><?php echo $book['title']; ?></option>
                <?php } ?>
            </select><br>
            <br>
            <label for="borrow_date">Ngày mượn:</label>
            <input type="date" id="borrow_date" name="borrow_date" value="<?php echo $borrow['borrow_date']; ?>" required><br>
            <br>
            <?php if ($book_returned) { ?>
                <label for="return_date">Ngày trả:</label>
                <input type="date" id="return_date" name="return_date" value="<?php echo $borrow['return_date']; ?>" required><br>
            <?php } ?>
            <br>
            <button type="submit">Lưu</button>
        </form>
    </div>
</body>
</html>
