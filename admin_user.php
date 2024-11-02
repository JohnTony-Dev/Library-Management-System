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

// Xử lý tìm kiếm
$search_keyword = '';
if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
}

// Lấy danh sách người dùng từ cơ sở dữ liệu và thông tin về trạng thái blacklisted
$stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u INNER JOIN user_roles ur ON u.id = ur.user_id INNER JOIN roles r ON ur.role_id = r.id WHERE u.name LIKE ? AND r.name != "admin" ORDER BY u.name');
$stmt->execute(["%$search_keyword%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
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
                <a href="admin_book.php">Quản lý sách</a>
                <a href="admin_borrow.php">Quản lý mượn sách</a>
                <a href="admin_booking.php">Quản lý đặt hẹn</a>
                <a href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </header>
    <h2>Quản lý người dùng</h2>
    <div class="search-bar">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm theo tên" value="<?php echo htmlspecialchars($search_keyword); ?>">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>
    <button class="blacklist" onclick="showBlacklistedUsers()">Xem danh sách đen</button>
    <div class="user-list">
        <?php if (count($users) > 0) { ?>
            <table id="abc" width=80% align="center" border="1 solid" cellpadding="5">
                <tr bgcolor="lightblue">
                    <th class="user_table" width="15%" align="center">Tài khoản</th>
                    <th class="user_table" width="15%" align="center">Mật khẩu</th>
                    <th class="user_table" width="15%" align="center">Tên</th>
                    <th class="user_table" width="15%" align="center">Email</th>
                    <th class="user_table" width="7%" align="center">Quyền</th>
                    <th class="user_table" width="7%" align="center">Danh sách đen</th>
                    <th class="user_table" width="7%" align="center">Hành động</th>
                </tr>
                <?php foreach ($users as $user) { ?>
                    <tr>
                        <td><?php echo $user['username']; ?></td>
                        <td align="center"><?php echo $user['password']; ?></td>
                        <td align="center"><?php echo $user['name']; ?></td>
                        <td align="center"><?php echo $user['email']; ?></td>
                        <td align="center"><?php echo $user['role_name']; ?></td>
                        <td align="center"><?php echo $user['blacklisted'] ? 'Có' : 'Không'; ?></td>
                        <td align="center">
                            <?php if ($user['blacklisted']) { ?>
                                <button onclick="confirmUnblock(<?php echo $user['id']; ?>)">Loại khỏi danh sách đen</button>
                            <?php } else { ?>
                                <button onclick="confirmBlock(<?php echo $user['id']; ?>)">Đưa vào danh sách đen</button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p>Không có người dùng nào phù hợp.</p>
        <?php } ?>
    </div>

    <script>
        function confirmBlock(userId) {
            if (confirm("Bạn có chắc chắn muốn đưa người dùng này vào danh sách đen không?")) {
                // Xử lý khi người dùng xác nhận
                window.location.href = "block_user.php?user_id=" + userId;
            }
        }
        function confirmUnblock(userId) {
            if (confirm("Bạn có chắc chắn muốn loại người dùng này khỏi danh sách đen không?")) {
                // Xử lý khi người dùng xác nhận
                window.location.href = "unblock_user.php?user_id=" + userId;
            }
        }

        function showBlacklistedUsers() {
            var table = document.getElementById("abc");
            var rows = table.getElementsByTagName("tr");

            // Lặp qua từng hàng trong bảng
            for (var i = 1; i < rows.length; i++) {
                var cells = rows[i].getElementsByTagName("td");
                var isBlacklisted = cells[5].innerText.trim(); // Giả sử cột blacklisted là cột thứ 6 (index 5)

                // Nếu người dùng đã vào danh sách đen, hiển thị hàng đsó
                if (isBlacklisted === "Có") {
                    rows[i].style.display = "";
                } else {
                    // Nếu không, ẩn đi hàng đó
                    rows[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>
