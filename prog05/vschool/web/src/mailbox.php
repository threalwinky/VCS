<?php
require_once 'session.php';
require_once 'db.php';

$conn = get_conn();
$userId = current_user_id();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_message') {
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($receiverId <= 0 || $content === '') {
            $error = 'Thong tin gui tin nhan khong hop le.';
        } else {
            $checkStmt = $conn->prepare('SELECT id FROM users WHERE id = ? AND role IN ("teacher", "student")');
            $checkStmt->bind_param('i', $receiverId);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->num_rows === 1;
            $checkStmt->close();

            if (!$exists) {
                $error = 'Nguoi nhan khong hop le.';
            } else {
                $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)');
                $stmt->bind_param('iis', $userId, $receiverId, $content);
                if ($stmt->execute()) {
                    $message = 'Gui tin nhan thanh cong.';
                } else {
                    $error = 'Gui tin nhan that bai.';
                }
                $stmt->close();
            }
        }
    }

    if ($action === 'toggle_read') {
        $messageId = (int)($_POST['message_id'] ?? 0);
        $isRead = isset($_POST['is_read']) ? 1 : 0;

        if ($messageId > 0) {
            if ($isRead === 1) {
                $stmt = $conn->prepare('UPDATE messages SET is_read = 1, read_at = CURRENT_TIMESTAMP WHERE id = ? AND receiver_id = ?');
                $stmt->bind_param('ii', $messageId, $userId);
            } else {
                $stmt = $conn->prepare('UPDATE messages SET is_read = 0, read_at = NULL WHERE id = ? AND receiver_id = ?');
                $stmt->bind_param('ii', $messageId, $userId);
            }
            $stmt->execute();
            $stmt->close();
            $message = 'Cap nhat trang thai doc thanh cong.';
        }
    }

    if ($action === 'edit_message') {
        $messageId = (int)($_POST['message_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if ($messageId > 0 && $content !== '') {
            $stmt = $conn->prepare('UPDATE messages SET content = ?, is_read = 0, read_at = NULL WHERE id = ? AND sender_id = ?');
            $stmt->bind_param('sii', $content, $messageId, $userId);
            $stmt->execute();
            $stmt->close();
            $message = 'Sua tin nhan thanh cong.';
        } else {
            $error = 'Khong the sua tin nhan.';
        }
    }
}

if (isset($_GET['delete_message'])) {
    $messageId = (int)$_GET['delete_message'];
    if ($messageId > 0) {
        $stmt = $conn->prepare('DELETE FROM messages WHERE id = ? AND sender_id = ?');
        $stmt->bind_param('ii', $messageId, $userId);
        $stmt->execute();
        $stmt->close();
        header('Location: /mailbox.php');
        exit;
    }
}

$usersStmt = $conn->prepare('SELECT id, username, full_name, role FROM users WHERE id <> ? AND role IN ("teacher", "student") ORDER BY role, full_name');
$usersStmt->bind_param('i', $userId);
$usersStmt->execute();
$userList = $usersStmt->get_result();

$inboxStmt = $conn->prepare('SELECT m.id, m.content, m.is_read, m.read_at, m.created_at, u.full_name AS sender_name, u.username AS sender_username FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.receiver_id = ? ORDER BY m.created_at DESC');
$inboxStmt->bind_param('i', $userId);
$inboxStmt->execute();
$inbox = $inboxStmt->get_result();

$outboxStmt = $conn->prepare('SELECT m.id, m.content, m.is_read, m.created_at, u.full_name AS receiver_name, u.username AS receiver_username FROM messages m JOIN users u ON u.id = m.receiver_id WHERE m.sender_id = ? ORDER BY m.created_at DESC');
$outboxStmt->bind_param('i', $userId);
$outboxStmt->execute();
$outbox = $outboxStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hop thu</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Hop thu cua toi</h2>
        <?php if ($message !== ''): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <h3>Gui tin nhan moi</h3>
        <form action="/mailbox.php" method="post" class="card-form">
            <input type="hidden" name="action" value="send_message">

            <label>Nguoi nhan</label>
            <select name="receiver_id" required>
                <option value="">-- Chon nguoi nhan --</option>
                <?php while ($u = $userList->fetch_assoc()): ?>
                    <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?> - <?= htmlspecialchars($u['role']) ?>)</option>
                <?php endwhile; ?>
            </select>

            <label>Noi dung</label>
            <textarea name="content" rows="3" required></textarea>
            <input type="submit" value="Gui tin nhan">
        </form>

        <h3>Thu den</h3>
        <?php while ($row = $inbox->fetch_assoc()): ?>
            <div class="message-box">
                <p><strong><?= htmlspecialchars($row['sender_name']) ?></strong> (<?= htmlspecialchars($row['sender_username']) ?>) - <?= htmlspecialchars($row['created_at']) ?></p>
                <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                <form action="/mailbox.php" method="post" class="inline-form">
                    <input type="hidden" name="action" value="toggle_read">
                    <input type="hidden" name="message_id" value="<?= (int)$row['id'] ?>">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_read" value="1" <?= (int)$row['is_read'] === 1 ? 'checked' : '' ?>> Da doc
                    </label>
                    <button type="submit">Luu trang thai</button>
                </form>
                <p>Trang thai: <strong><?= (int)$row['is_read'] === 1 ? 'Da doc' : 'Chua doc' ?></strong>
                    <?php if ((int)$row['is_read'] === 1 && !empty($row['read_at'])): ?>
                        - luc <?= htmlspecialchars($row['read_at']) ?>
                    <?php endif; ?>
                </p>
            </div>
        <?php endwhile; ?>

        <h3>Thu da gui</h3>
        <?php while ($row = $outbox->fetch_assoc()): ?>
            <div class="message-box">
                <p>Toi <strong><?= htmlspecialchars($row['receiver_name']) ?></strong> (<?= htmlspecialchars($row['receiver_username']) ?>) - <?= htmlspecialchars($row['created_at']) ?></p>
                <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                <p>Nguoi nhan: <strong><?= (int)$row['is_read'] === 1 ? 'Da doc' : 'Chua doc' ?></strong></p>
                <form action="/mailbox.php" method="post" class="inline-form">
                    <input type="hidden" name="action" value="edit_message">
                    <input type="hidden" name="message_id" value="<?= (int)$row['id'] ?>">
                    <input type="text" name="content" value="<?= htmlspecialchars($row['content']) ?>" required>
                    <button type="submit">Sua</button>
                    <a class="danger-link" href="/mailbox.php?delete_message=<?= (int)$row['id'] ?>" onclick="return confirm('Xoa tin nhan da gui?')">Xoa</a>
                </form>
            </div>
        <?php endwhile; ?>

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
<?php
$usersStmt->close();
$inboxStmt->close();
$outboxStmt->close();
$conn->close();
