<?php
require_once 'session.php';

require_once 'db.php';

$conn = get_conn();
$message = '';
$error = '';
$userId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $avatarUrl = trim($_POST['avatar_url'] ?? '');
    $newPassword = $_POST['password'] ?? '';
    $avatarPath = '';

    if (!empty($_FILES['avatar_file']['name'])) {
        $uploadDir = __DIR__ . '/static/images';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            $error = 'Avatar file khong hop le. Chi chap nhan jpg/jpeg/png/gif/webp.';
        } else {
            $newName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $target = $uploadDir . '/' . $newName;
            if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], $target)) {
                $avatarPath = '/static/images/' . $newName;
            } else {
                $error = 'Khong upload duoc avatar.';
            }
        }
    }

    if ($error === '' && $avatarPath === '' && $avatarUrl !== '') {
        $avatarPath = $avatarUrl;
    }

    if ($error === '') {
        if ($newPassword !== '') {
            if ($avatarPath !== '') {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET email = ?, phone_number = ?, avatar = ?, password = ? WHERE id = ?');
                $stmt->bind_param('ssssi', $email, $phone, $avatarPath, $hashedPassword, $userId);
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET email = ?, phone_number = ?, password = ? WHERE id = ?');
                $stmt->bind_param('sssi', $email, $phone, $hashedPassword, $userId);
            }
        } else {
            if ($avatarPath !== '') {
                $stmt = $conn->prepare('UPDATE users SET email = ?, phone_number = ?, avatar = ? WHERE id = ?');
                $stmt->bind_param('sssi', $email, $phone, $avatarPath, $userId);
            } else {
                $stmt = $conn->prepare('UPDATE users SET email = ?, phone_number = ? WHERE id = ?');
                $stmt->bind_param('ssi', $email, $phone, $userId);
            }
        }

        if ($stmt->execute()) {
            $message = 'Cap nhat thong tin thanh cong.';
        } else {
            $error = 'Cap nhat that bai.';
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT username, full_name, email, phone_number, avatar, role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    die("User not found.");
}

$stmt->close();

$classes = [];
$classStmt = $conn->prepare('SELECT c.class_name, c.class_code FROM class_user cu JOIN classes c ON c.id = cu.class_id WHERE cu.user_id = ? ORDER BY c.class_name ASC');
$classStmt->bind_param('i', $userId);
$classStmt->execute();
$classResult = $classStmt->get_result();
while ($classRow = $classResult->fetch_assoc()) {
    $classes[] = $classRow;
}
$classStmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Thong tin ca nhan</h2>
        <?php if ($message !== ''): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if (!empty($user['avatar'])): ?>
            <img class="avatar" src="<?= htmlspecialchars($user['avatar']) ?>" alt="avatar">
        <?php endif; ?>

        <form action="/profile.php" method="post" enctype="multipart/form-data" class="card-form">
            <label>Username</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>

            <label>Ho va ten</label>
            <input type="text" value="<?= htmlspecialchars($user['full_name']) ?>" disabled>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>So dien thoai</label>
            <input type="text" name="phone_number" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">

            <label>Doi mat khau</label>
            <input type="password" name="password" placeholder="Nhap neu muon doi">

            <label>Upload avatar tu file</label>
            <input type="file" name="avatar_file" accept="image/*">

            <label>Hoac avatar URL</label>
            <input type="url" name="avatar_url" placeholder="https://...">

            <input type="submit" value="Luu thay doi">
        </form>

        <p><strong>Vai tro:</strong> <?= htmlspecialchars($user['role']) ?></p>

        <h3>Danh sach lop cua toi</h3>
        <?php if (!empty($classes)): ?>
            <ul>
                <?php foreach ($classes as $class): ?>
                    <li>
                        <?= htmlspecialchars($class['class_name']) ?>
                        <?php if (!empty($class['class_code'])): ?>
                            (<?= htmlspecialchars($class['class_code']) ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Ban chua tham gia lop nao.</p>
        <?php endif; ?>

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>