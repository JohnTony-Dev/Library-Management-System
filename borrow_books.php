<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');
// Khởi tạo biến lỗi
$error_message = '';

// Xử lý khi người dùng nhấn nút mượn sách
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['book_id'])) {
        // Xử lý mã sách được chọn
        $book_id = $_POST['book_id'];
          
        // Chuyển hướng người dùng đến trang đặt lịch hẹn và truyền ID của sách
        header('Location: booking.php?book_id=' . $book_id);
        exit();
    } else {
        // Hiển thị thông báo lỗi nếu không có mã sách được chọn
        $error_message = "Vui lòng chọn một cuốn sách để mượn.";
    }
}
// Lấy danh sách sách từ cơ sở dữ liệu
$stmt = $pdo->query('SELECT * FROM books');
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Chuyển danh sách sách sang dạng JSON để sử dụng trong JavaScript
$books_json = json_encode($books);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <style>
        <?php include "styles.css" ?>
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        // Lấy danh sách sách từ PHP và chuyển sang đối tượng JavaScript
        var books = <?php echo $books_json; ?>;

        // Hiển thị tất cả sách khi trang được tải
        displayBooks(books);

        // Tìm kiếm sách khi người dùng nhập từ khóa
        $('#searchInput').keyup(function(){
            searchBooks();
        });

        function searchBooks() {
            var keyword = $('#searchInput').val().trim().toLowerCase();

            // Xóa danh sách sách hiện tại
            $('#bookList').empty();

            // Hiển thị các sách phù hợp với từ khóa
            books.forEach(function(book) {
                if (book.title.toLowerCase().includes(keyword)) {
                    appendBookElement(book);
                }
            });
        }

        function displayBooks(books) {
            // Hiển thị tất cả các sách khi trang được tải
            books.forEach(function(book) {
                appendBookElement(book);
            });
        }

        function appendBookElement(book) {
            $('#bookList').append('<div class="book"><img src="' + book.image + '" alt="' + book.title + '"><div class="book-info"><h3>' + book.title + '</h3><p>Tác giả: ' + book.author + '</p><p>Số lượng: ' + book.quantity + '</p><form method="post"><input type="hidden" name="book_id" value="' + book.id + '"><button type="submit" class="borrow">Mượn sách</button></form></div></div>');
        }
    });
    </script>
</head>
<body>
<header>
    <div class="container">
        <h1>Trang mượn sách</h1>
        <div class="user-options">
            <a href="edit_profile.php">Chỉnh sửa thông tin</a>
            <a href="history.php">Xem lịch sử mượn sách</a>
			<a href="booking_history.php">Xem lịch hẹn</a>
			<a href="logout.php">Đăng xuất</a>
        </div>
    </div>
</header>
<div class="container">
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Tìm kiếm sách...">
    </div>
    <div class="book-list" id="bookList"></div>
</div>
</body>
</html>
