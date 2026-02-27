<?php
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if ($username === '' || $password === '' || $full_name === '' || $email === '') {
        $error = 'Vui long nhap day du cac truong bat buoc.';
    } else {
        $conn = get_conn();

        $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $checkStmt->bind_param('ss', $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = 'Username hoac email da ton tai.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';
            $avatar = '/static/images/default.jpg';
            $stmt = $conn->prepare('INSERT INTO users (username, password, full_name, email, phone_number, role, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssss', $username, $hashedPassword, $full_name, $email, $phone_number, $role, $avatar);

            if ($stmt->execute()) {
                $message = 'Dang ky thanh cong. Ban co the dang nhap ngay bay gio.';
            } else {
                $error = 'Co loi xay ra khi dang ky.';
            }

            $stmt->close();
        }

        $checkStmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dang ky</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Dang ky tai khoan sinh vien</h2>

        <?php if ($message !== ''): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/register.php" method="post" class="card-form">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Mat khau</label>
            <input type="password" name="password" id="password" required>

            <label for="full_name">Ho va ten</label>
            <input type="text" name="full_name" id="full_name" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="phone_number">So dien thoai</label>
            <input type="text" name="phone_number" id="phone_number">

            <input type="submit" value="Dang ky">
        </form>

        <p>Da co tai khoan? <a href="/login.php">Dang nhap</a></p>
        <p><a href="/index.php">Ve trang chu</a></p>
    </div>
</body>
</html>
