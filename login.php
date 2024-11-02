<?php
session_start();

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Xử lý khi người dùng gửi biểu mẫu đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin đăng nhập từ biểu mẫu
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Kiểm tra trong cơ sở dữ liệu xem thông tin đăng nhập có khớp không
    $stmt = $pdo->prepare('SELECT users.id, users.username, users.password, users.blacklisted, roles.name AS role 
                           FROM users 
                           INNER JOIN user_roles ON users.id = user_roles.user_id 
                           INNER JOIN roles ON user_roles.role_id = roles.id 
                           WHERE users.username = ? AND users.password = ?');
    $stmt->execute([$username,$password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra xem người dùng tồn tại và mật khẩu khớp không
    if ($user) {
        // Kiểm tra trạng thái blacklisted của người dùng
        if ($user['blacklisted']) {
            header('Location: index.php?error=blacklisted');
            exit();
        } else {
            // Đăng nhập thành công, lưu thông tin người dùng vào session
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['blacklisted'] = false; // Đặt blacklisted thành false cho người dùng không bị block

            // Kiểm tra vai trò của người dùng và chuyển hướng đến trang tương ứng
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
                exit();
            } else {
                header('Location: borrow_books.php');
                exit();
            }
        }
    } else {
        // Đăng nhập không thành công, hiển thị thông báo lỗi
        header('Location: index.php?error=incorrect_credentials');
        exit();
    }
}
?>
