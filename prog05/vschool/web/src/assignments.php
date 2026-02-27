<?php
require_once 'session.php';
require_once 'db.php';

$conn = get_conn();
$message = '';
$error = '';
$userId = current_user_id();
$isTeacher = is_teacher_or_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_assignment' && $isTeacher) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || empty($_FILES['assignment_file']['name'])) {
            $error = 'Vui long nhap tieu de va chon file bai tap.';
        } else {
            $uploadDir = __DIR__ . '/uploads/assignments';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = $_FILES['assignment_file']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $savedName = 'assignment_' . time() . '_' . rand(1000, 9999) . ($ext ? '.' . $ext : '');
            $target = $uploadDir . '/' . $savedName;

            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target)) {
                $relativePath = '/uploads/assignments/' . $savedName;
                $stmt = $conn->prepare('INSERT INTO assignments (title, description, file_path, original_name, created_by) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssi', $title, $description, $relativePath, $originalName, $userId);
                if ($stmt->execute()) {
                    $message = 'Dang bai tap thanh cong.';
                } else {
                    $error = 'Khong luu duoc bai tap vao DB.';
                }
                $stmt->close();
            } else {
                $error = 'Khong upload duoc file bai tap.';
            }
        }
    }

    if ($action === 'submit_assignment' && !$isTeacher) {
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        if ($assignmentId <= 0 || empty($_FILES['submission_file']['name'])) {
            $error = 'Thong tin nop bai khong hop le.';
        } else {
            $accessStmt = $conn->prepare('SELECT a.id FROM assignments a JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = a.created_by AND cu_teacher.class_id = cu_student.class_id WHERE a.id = ? LIMIT 1');
            $accessStmt->bind_param('ii', $userId, $assignmentId);
            $accessStmt->execute();
            $accessResult = $accessStmt->get_result();
            if ($accessResult->num_rows !== 1) {
                $error = 'Ban khong co quyen nop bai cho bai tap nay.';
            }
            $accessStmt->close();
        }

        if ($error === '') {
            $uploadDir = __DIR__ . '/uploads/submissions';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $originalName = $_FILES['submission_file']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $savedName = 'submission_' . $userId . '_' . $assignmentId . '_' . time() . ($ext ? '.' . $ext : '');
            $target = $uploadDir . '/' . $savedName;

            if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target)) {
                $relativePath = '/uploads/submissions/' . $savedName;

                $checkStmt = $conn->prepare('SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1');
                $checkStmt->bind_param('ii', $assignmentId, $userId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if ($checkResult->num_rows === 1) {
                    $old = $checkResult->fetch_assoc();
                    $updateStmt = $conn->prepare('UPDATE submissions SET file_path = ?, original_name = ?, submitted_at = CURRENT_TIMESTAMP WHERE id = ?');
                    $updateStmt->bind_param('ssi', $relativePath, $originalName, $old['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    $message = 'Da cap nhat bai nop thanh cong.';
                } else {
                    $stmt = $conn->prepare('INSERT INTO submissions (assignment_id, student_id, file_path, original_name) VALUES (?, ?, ?, ?)');
                    $stmt->bind_param('iiss', $assignmentId, $userId, $relativePath, $originalName);
                    if ($stmt->execute()) {
                        $message = 'Nop bai thanh cong.';
                    } else {
                        $error = 'Khong luu duoc bai nop vao DB.';
                    }
                    $stmt->close();
                }
                $checkStmt->close();
            } else {
                $error = 'Khong upload duoc bai lam.';
            }
        }
    }
}

if ($isTeacher) {
    $assignments = $conn->query('SELECT a.id, a.title, a.description, a.file_path, a.original_name, a.created_at, u.full_name AS teacher_name FROM assignments a JOIN users u ON a.created_by = u.id ORDER BY a.id DESC');
} else {
    $stmt = $conn->prepare('SELECT DISTINCT a.id, a.title, a.description, a.file_path, a.original_name, a.created_at, u.full_name AS teacher_name FROM assignments a JOIN users u ON a.created_by = u.id JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = a.created_by AND cu_teacher.class_id = cu_student.class_id ORDER BY a.id DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $assignments = $stmt->get_result();
}

$submissionRows = null;
if ($isTeacher) {
    $submissionRows = $conn->query('SELECT s.id, s.assignment_id, s.file_path, s.original_name, s.submitted_at, u.full_name AS student_name, a.title AS assignment_title FROM submissions s JOIN users u ON s.student_id = u.id JOIN assignments a ON s.assignment_id = a.id ORDER BY s.submitted_at DESC');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bai tap</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Quan ly bai tap</h2>
        <?php if ($message !== ''): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($isTeacher): ?>
            <h3>Giao bai moi</h3>
            <form action="/assignments.php" method="post" enctype="multipart/form-data" class="card-form">
                <input type="hidden" name="action" value="create_assignment">

                <label>Tieu de bai tap</label>
                <input type="text" name="title" required>

                <label>Mo ta</label>
                <textarea name="description" rows="3"></textarea>

                <label>File bai tap</label>
                <input type="file" name="assignment_file" required>

                <input type="submit" value="Dang bai tap">
            </form>
        <?php endif; ?>

        <h3>Danh sach bai tap</h3>
        <?php while ($assignment = $assignments->fetch_assoc()): ?>
            <div class="message-box">
                <p><strong><?= htmlspecialchars($assignment['title']) ?></strong></p>
                <p><?= nl2br(htmlspecialchars($assignment['description'] ?? '')) ?></p>
                <p>Giao boi: <?= htmlspecialchars($assignment['teacher_name']) ?> | <?= htmlspecialchars($assignment['created_at']) ?></p>
                <p><a href="<?= htmlspecialchars($assignment['file_path']) ?>" download="<?= htmlspecialchars($assignment['original_name']) ?>">Tai file bai tap</a></p>

                <?php if (!$isTeacher): ?>
                    <form action="/assignments.php" method="post" enctype="multipart/form-data" class="inline-form">
                        <input type="hidden" name="action" value="submit_assignment">
                        <input type="hidden" name="assignment_id" value="<?= (int)$assignment['id'] ?>">
                        <input type="file" name="submission_file" required>
                        <button type="submit">Nop bai</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <?php if ($isTeacher): ?>
            <h3>Danh sach bai nop</h3>
            <table>
                <thead>
                    <tr>
                        <th>Bai tap</th>
                        <th>Sinh vien</th>
                        <th>File nop</th>
                        <th>Thoi gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $submissionRows->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['assignment_title']) ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><a href="<?= htmlspecialchars($row['file_path']) ?>" download="<?= htmlspecialchars($row['original_name']) ?>">Tai bai nop</a></td>
                            <td><?= htmlspecialchars($row['submitted_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
<?php
$conn->close();
