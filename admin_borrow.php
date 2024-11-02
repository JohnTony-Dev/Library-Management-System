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

// Xử lý khi người dùng trả sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_book_id'])) {
    $return_book_id = $_POST['return_book_id'];
    $return_date = date('Y-m-d');

    // Lấy thông tin sách được trả
    $stmt = $pdo->prepare('SELECT book_id FROM borrow_history WHERE id = ?');
    $stmt->execute([$return_book_id]);
    $book_id = $stmt->fetchColumn();

    // Cập nhật ngày trả sách là ngày hôm nay
    $stmt = $pdo->prepare('UPDATE borrow_history SET return_date = ? WHERE id = ?');
    $stmt->execute([$return_date, $return_book_id]);

    // Tăng lại số lượng sách trong bảng books
    $stmt = $pdo->prepare('UPDATE books SET quantity = quantity + 1 WHERE id = ?');
    $stmt->execute([$book_id]);
}

// Xử lý khi người dùng tạo hồ sơ mượn sách mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['book_id'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $borrow_date = date('Y-m-d');

    // Thêm hồ sơ mượn sách mới vào cơ sở dữ liệu
    $stmt = $pdo->prepare('INSERT INTO borrow_history (user_id, book_id, borrow_date) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $book_id, $borrow_date]);

    // Giảm số lượng sách trong bảng books
    $stmt = $pdo->prepare('UPDATE books SET quantity = quantity - 1 WHERE id = ?');
    $stmt->execute([$book_id]);
}

// Lấy danh sách lịch sử mượn sách từ cơ sở dữ liệu
$stmt = $pdo->query('SELECT borrow_history.id, users.username, books.title, borrow_history.borrow_date, borrow_history.return_date
                     FROM borrow_history
                     INNER JOIN users ON borrow_history.user_id = users.id
                     INNER JOIN books ON borrow_history.book_id = books.id');
$borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý mượn sách</title>
    <style>
        <?php include "styles.css" ?>
        /* CSS cho overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Màu nền semi-transparent */
            display: none; /* Mặc định ẩn */
            justify-content: center;
            align-items: center;
        }

        /* CSS cho cửa sổ pop-up */
        .popup {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
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
    <h2>Quản lý mượn sách</h2>
    <br>
    <div class="container">
        <a href="javascript:void(0);" class="add-borrow" onclick="openPopup()">Thêm hồ sơ mượn sách mới</a>
        <div class="borrow-list">
            <?php if (count($borrows) > 0) { ?>
                <table width="100%" border="1 solid" cellpadding="5">
                    <tr bgcolor="lightblue">
                        <th width="5%">Người mượn</th>
                        <th width="20%">Tên sách</th>
                        <th width="5%">Ngày mượn</th>
                        <th width="5%">Ngày trả</th>
                        <th width="5%">Hành động</th>
                    </tr>
                    <?php foreach ($borrows as $borrow) { ?>
                        <tr>
                            <td><?php echo $borrow['username']; ?></td>
                            <td><?php echo $borrow['title']; ?></td>
                            <td align="center"><?php echo $borrow['borrow_date']; ?></td>
                            <td align="center"><?php echo $borrow['return_date'] ? $borrow['return_date'] : 'Chưa trả'; ?></td>
                            <td align="center">
                                <form action="edit_borrow.php" method="POST">
                                    <input type="hidden" name="borrow_id" value="<?php echo $borrow['id']; ?>">
                                    <button type="submit">Chỉnh sửa</button>
                                </form>
                                <br>
                                <?php if (!$borrow['return_date']) { ?>
                                    <form action="" method="POST">
                                        <input type="hidden" name="return_book_id" value="<?php echo $borrow['id']; ?>">
                                        <button type="submit">Trả sách</button>
                                    </form>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>Không có lịch sử mượn sách.</p>
            <?php } ?>
        </div>
    </div>
    <!-- Overlay -->
    <div class="overlay" id="overlay">
        <!-- Cửa sổ pop-up -->
        <div class="popup" id="popup">
            <h2>Thêm hồ sơ mượn sách mới</h2>
            <br>
            <form action="" method="POST">
                <label for="user_id">Người mượn:</label><br>
                <select name="user_id" id="user_id">
                    <?php
                    // Lấy danh sách người dùng có role là user từ cơ sở dữ liệu
                    $stmt = $pdo->prepare('SELECT id, username FROM users WHERE id  IN (SELECT user_id FROM user_roles WHERE role_id = (SELECT id FROM roles WHERE name = "user"))');
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($users as $user) {
                        echo '<option value="' . $user['id'] . '">' . $user['username'] . '</option>';
                    }
                    ?>
                </select><br>
                <div class="book-list">
                    <!-- Danh sách sách hiện có -->
                </div>
                <label for="book_id">Tên sách:</label><br>
                <select name="book_id" id="book_id">
                    <?php
                    // Lấy danh sách sách có số lượng > 0 từ cơ sở dữ liệu
                    $stmt = $pdo->query('SELECT id, title FROM books WHERE quantity > 0 AND id NOT IN (SELECT book_id FROM borrow_history WHERE return_date IS NULL)');
                    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($books as $book) {
                        echo '<option value="' . $book['id'] . '">' . $book['title'] . '</option>';
                    }
                    ?>
                </select><br>
                <div class="book-list">
                    <!-- Danh sách sách hiện có -->
                </div>
                <button type="submit">Xác nhận mượn sách</button>
                <!-- Nút "Đóng" để ẩn pop-up -->
                <button type="button" onclick="closePopup()">Đóng</button>
            </form>
        </div>
    </div>
    <script>
        // JavaScript để hiển thị pop-up khi nhấn vào đường dẫn
        function openPopup() {
            var popup = document.getElementById("popup");
            var overlay = document.getElementById("overlay");
            popup.style.display = "block";
            overlay.style.display = "flex";
        }

        // JavaScript để ẩn pop-up khi nhấn nút "Đóng"
        function closePopup() {
            var popup = document.getElementById("popup");
            var overlay = document.getElementById("overlay");
            popup.style.display = "none";
            overlay.style.display = "none";
        }
    </script>
</body>
</html>
