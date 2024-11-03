<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Kết nối đến cơ sở dữ liệu
include('connect.php');

// Lấy lịch sử mượn sách từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT booking.id,books.title, books.author, booking.booking_date 
                      FROM booking 
                      INNER JOIN books ON booking.book_id = books.id 
                      WHERE booking.user_id = ?');
$stmt->execute([$user_id]);
$booking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lịch hẹn</title>
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
				<a href="history.php">Xem lịch sử mượn sách</a>
				<a href="logout.php">Đăng xuất</a>
			</div>
		</div>
	</header>
	<h2 class="title-header">Lịch hẹn</h2>
	<div class="container">
		<div class="booking-history">
			<?php if (count($booking_history) > 0) { ?>
				<table id="abc" width="100%" align="center" border="1 solid" cellpadding="5">
				<tr bgcolor="lightblue">
					<th class="booking_table" width="2%" align="center">
						ID
					</th>
					<th class="booking_table" width="30%" align="center">
						Tiêu đề sách
					</th>
					<th class="booking_table" width="20%" align="center">
						Tác giả
					</th>
					<th class="booking_table" width="15%" align="center">
						Ngày hẹn
					</th>
					<th class="booking_table" width="10%" align="center">
						Chức năng
					</td>
				</tr>
				<?php foreach ($booking_history as $booking) { ?>
					<tr>
						<td align="center"><?php echo $booking['id'] ?></td>
						<td><?php echo $booking['title'] ?></td>
						<td align="center"><?php echo $booking['author']?></td>
						<td align="center"><?php echo $booking['booking_date'] ?></td>
						<?php $_POST['booking_id']=$booking['id'] ?>
						<td align="center">
							<form method="post" action="delete_booking.php">
								<input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
								<button type="submit" onclick="return confirm('Bạn có chắc chắn muốn xóa lịch hẹn này không?')">Xóa</button>
							</form>
						</td>
					</tr>			
					<?php 
				}
				?>
				<?php } else { ?>
					<p>Không có lịch hẹn sách.</p>
				<?php } ?>
		</div>
	</div>
	<script>
		function confDelete(){
			if(confirm("Bấm vào nút OK để tiếp tục"))
			{
				document.getElementById("demo").setAttribute('target','');
			}
			else
			{	
				document.getElementById("demo").setAttribute('href','list_qltourdl.php');
				alert("Xóa ko thành công!");
			}
		}
	</script>
</body>
</html>
