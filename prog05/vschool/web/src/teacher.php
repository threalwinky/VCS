<?php
require_once 'session.php';
require_once 'db.php';

require_teacher_or_admin();

$conn = get_conn();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';

	if ($action === 'add_student') {
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		$full_name = trim($_POST['full_name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$phone = trim($_POST['phone_number'] ?? '');

		if ($username === '' || $password === '' || $full_name === '' || $email === '') {
			$error = 'Vui long nhap day du thong tin sinh vien.';
		} else {
			$role = 'student';
			$avatar = '/static/images/default.jpg';
			$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
			$stmt = $conn->prepare('INSERT INTO users (username, password, full_name, email, phone_number, role, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)');
			$stmt->bind_param('sssssss', $username, $hashedPassword, $full_name, $email, $phone, $role, $avatar);
			if ($stmt->execute()) {
				$message = 'Them sinh vien thanh cong.';
			} else {
				$error = 'Khong the them sinh vien. Co the trung username/email.';
			}
			$stmt->close();
		}
	}

	if ($action === 'edit_student') {
		$id = (int)($_POST['id'] ?? 0);
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';
		$full_name = trim($_POST['full_name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$phone = trim($_POST['phone_number'] ?? '');

		if ($id <= 0 || $username === '' || $full_name === '' || $email === '') {
			$error = 'Du lieu cap nhat khong hop le.';
		} else {
			if ($password !== '') {
				$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
				$stmt = $conn->prepare('UPDATE users SET username = ?, password = ?, full_name = ?, email = ?, phone_number = ? WHERE id = ? AND role = "student"');
				$stmt->bind_param('sssssi', $username, $hashedPassword, $full_name, $email, $phone, $id);
			} else {
				$stmt = $conn->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, phone_number = ? WHERE id = ? AND role = "student"');
				$stmt->bind_param('ssssi', $username, $full_name, $email, $phone, $id);
			}

			if ($stmt->execute()) {
				$message = 'Cap nhat sinh vien thanh cong.';
			} else {
				$error = 'Cap nhat that bai.';
			}
			$stmt->close();
		}
	}
}

if (isset($_GET['delete_id'])) {
	$deleteId = (int)$_GET['delete_id'];
	if ($deleteId > 0) {
		$stmt = $conn->prepare('DELETE FROM users WHERE id = ? AND role = "student"');
		$stmt->bind_param('i', $deleteId);
		$stmt->execute();
		$stmt->close();
		header('Location: /teacher.php');
		exit;
	}
}

$editStudent = null;
if (isset($_GET['edit_id'])) {
	$editId = (int)$_GET['edit_id'];
	$stmt = $conn->prepare('SELECT id, username, full_name, email, phone_number FROM users WHERE id = ? AND role = "student"');
	$stmt->bind_param('i', $editId);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows === 1) {
		$editStudent = $result->fetch_assoc();
	}
	$stmt->close();
}

$students = $conn->query('SELECT id, username, full_name, email, phone_number, created_at FROM users WHERE role = "student" ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Quan ly sinh vien</title>
	<link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
	<div class="container">
		<h2>Quan ly sinh vien</h2>
		<?php if ($message !== ''): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
		<?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

		<h3><?= $editStudent ? 'Sua sinh vien' : 'Them sinh vien moi' ?></h3>
		<form action="/teacher.php" method="post" class="card-form">
			<input type="hidden" name="action" value="<?= $editStudent ? 'edit_student' : 'add_student' ?>">
			<?php if ($editStudent): ?>
				<input type="hidden" name="id" value="<?= (int)$editStudent['id'] ?>">
			<?php endif; ?>

			<label>Username</label>
			<input type="text" name="username" value="<?= htmlspecialchars($editStudent['username'] ?? '') ?>" required>

			<label>Mat khau <?= $editStudent ? '(de trong neu khong doi)' : '' ?></label>
			<input type="password" name="password" <?= $editStudent ? '' : 'required' ?>>

			<label>Ho va ten</label>
			<input type="text" name="full_name" value="<?= htmlspecialchars($editStudent['full_name'] ?? '') ?>" required>

			<label>Email</label>
			<input type="email" name="email" value="<?= htmlspecialchars($editStudent['email'] ?? '') ?>" required>

			<label>So dien thoai</label>
			<input type="text" name="phone_number" value="<?= htmlspecialchars($editStudent['phone_number'] ?? '') ?>">

			<input type="submit" value="<?= $editStudent ? 'Cap nhat' : 'Them sinh vien' ?>">
		</form>

		<h3>Danh sach sinh vien</h3>
		<table>
			<thead>
				<tr>
					<th>ID</th>
					<th>Username</th>
					<th>Ho ten</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Ngay tao</th>
					<th>Thao tac</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($student = $students->fetch_assoc()): ?>
				<tr>
					<td><?= (int)$student['id'] ?></td>
					<td><?= htmlspecialchars($student['username']) ?></td>
					<td><?= htmlspecialchars($student['full_name']) ?></td>
					<td><?= htmlspecialchars($student['email']) ?></td>
					<td><?= htmlspecialchars($student['phone_number'] ?? '') ?></td>
					<td><?= htmlspecialchars($student['created_at']) ?></td>
					<td>
						<a href="/teacher.php?edit_id=<?= (int)$student['id'] ?>">Sua</a> |
						<a class="danger-link" href="/teacher.php?delete_id=<?= (int)$student['id'] ?>" onclick="return confirm('Xoa sinh vien nay?')">Xoa</a>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>

		<p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
	</div>
</body>
</html>
<?php
$conn->close();

