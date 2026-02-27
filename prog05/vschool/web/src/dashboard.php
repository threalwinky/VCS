<?php

require_once 'session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Dashboard</h2>
        <p>Xin chao <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></strong> (<?= htmlspecialchars($_SESSION['role']) ?>)</p>

        <div class="actions">
            <a class="btn" href="/profile.php">Thong tin cua toi</a>
            <a class="btn" href="/users.php">Danh sach nguoi dung</a>
            <a class="btn" href="/mailbox.php">Hop thu</a>
            <a class="btn" href="/assignments.php">Bai tap</a>
            <a class="btn" href="/challenge.php">Challenge</a>
            <?php if (is_teacher_or_admin()): ?>
                <a class="btn" href="/teacher.php">Quan ly sinh vien</a>
            <?php endif; ?>
            <a class="btn btn-danger" href="/logout.php">Dang xuat</a>
        </div>
    </div>
</body>
</html>