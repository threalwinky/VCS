<?php
require_once 'session.php';
require_once 'db.php';

$targetUserId = (int)($_GET['id'] ?? 0);
if ($targetUserId <= 0) {
    die('User khong hop le.');
}

$conn = get_conn();
$message = '';
$error = '';
$currentUserId = current_user_id();
$canReadMessages = ($currentUserId === $targetUserId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_message') {
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $error = 'Noi dung tin nhan khong duoc rong.';
        } else {
            $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)');
            $stmt->bind_param('iis', $currentUserId, $targetUserId, $content);
            if ($stmt->execute()) {
                $message = 'Gui tin nhan thanh cong.';
            } else {
                $error = 'Khong gui duoc tin nhan.';
            }
            $stmt->close();
        }
    }

}

$stmt = $conn->prepare('SELECT id, username, full_name, email, phone_number, avatar, role FROM users WHERE id = ? AND role IN ("teacher", "student")');
$stmt->bind_param('i', $targetUserId);
$stmt->execute();
$userResult = $stmt->get_result();
if ($userResult->num_rows !== 1) {
    die('Khong tim thay nguoi dung.');
}
$user = $userResult->fetch_assoc();
$stmt->close();

$msgStmt = null;
$messages = null;
if ($canReadMessages) {
    $msgStmt = $conn->prepare('SELECT m.id, m.sender_id, m.content, m.created_at, u.username AS sender_username FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.receiver_id = ? ORDER BY m.created_at DESC');
    $msgStmt->bind_param('i', $targetUserId);
    $msgStmt->execute();
    $messages = $msgStmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiet nguoi dung</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Chi tiet nguoi dung</h2>
        <?php if ($message !== ''): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <img class="avatar" src="<?= htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : '/static/images/default.jpg') ?>" alt="avatar">

        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Ho ten:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>So dien thoai:</strong> <?= htmlspecialchars($user['phone_number'] ?? '') ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role']) ?></p>

        <?php if ($currentUserId !== (int)$user['id']): ?>
            <h3>Gui tin nhan</h3>
            <form action="/user_detail.php?id=<?= (int)$user['id'] ?>" method="post" class="card-form">
                <input type="hidden" name="action" value="send_message">
                <textarea name="content" rows="3" required placeholder="Nhap noi dung..."></textarea>
                <input type="submit" value="Gui tin nhan">
            </form>
        <?php endif; ?>

        <?php if ($canReadMessages): ?>
            <h3>Tin nhan gui toi ban</h3>
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <div class="message-box">
                    <p><strong><?= htmlspecialchars($msg['sender_username']) ?></strong> - <?= htmlspecialchars($msg['created_at']) ?></p>
                    <p><?= nl2br(htmlspecialchars($msg['content'])) ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
        <?php endif; ?>

        <p><a href="/users.php">&larr; Ve danh sach nguoi dung</a></p>
    </div>
</body>
</html>
<?php
if ($msgStmt !== null) {
    $msgStmt->close();
}
$conn->close();
