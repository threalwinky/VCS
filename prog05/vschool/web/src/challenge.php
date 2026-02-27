<?php
require_once 'session.php';
require_once 'db.php';

$conn = get_conn();
$userId = current_user_id();
$isTeacher = is_teacher_or_admin();
$message = '';
$error = '';
$poemContent = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_challenge' && $isTeacher) {
        $hintText = trim($_POST['hint_text'] ?? '');

        if ($hintText === '' || empty($_FILES['challenge_file']['name'])) {
            $error = 'Can nhap goi y va chon file txt.';
        } else {
            $originalName = $_FILES['challenge_file']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if ($ext !== 'txt') {
                $error = 'Chi cho phep upload file .txt';
            } elseif (!preg_match('/^[A-Za-z0-9]+( [A-Za-z0-9]+)*\.txt$/', $originalName)) {
                $error = 'Ten file khong dau';
            } else {
                $uploadDir = __DIR__ . '/uploads/challenges';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $challengeFolder = 'challenge_' . time() . '_' . rand(1000, 9999);
                $challengeDir = $uploadDir . '/' . $challengeFolder;
                if (!mkdir($challengeDir, 0777, true) && !is_dir($challengeDir)) {
                    $error = 'Khong tao duoc thu muc challenge.';
                }

                if ($error === '') {
                    $target = $challengeDir . '/' . $originalName;
                    if (move_uploaded_file($_FILES['challenge_file']['tmp_name'], $target)) {
                        $relativePath = '/uploads/challenges/' . $challengeFolder;
                        $stmt = $conn->prepare('INSERT INTO challenges (hint_text, file_path, original_name, created_by) VALUES (?, ?, ?, ?)');
                        $emptyOriginalName = '';
                        $stmt->bind_param('sssi', $hintText, $relativePath, $emptyOriginalName, $userId);
                        if ($stmt->execute()) {
                            $message = 'Tao challenge thanh cong.';
                        } else {
                            $error = 'Khong luu duoc challenge vao DB.';
                        }
                        $stmt->close();
                    } else {
                        $error = 'Khong upload duoc file challenge.';
                    }
                }
            }
        }
    }

    if ($action === 'solve_challenge' && !$isTeacher) {
        $challengeId = (int)($_POST['challenge_id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');

        if ($challengeId <= 0 || $answer === '') {
            $error = 'Vui long nhap dap an.';
        } else {
            $stmt = $conn->prepare('SELECT c.file_path FROM challenges c JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = c.created_by AND cu_teacher.class_id = cu_student.class_id WHERE c.id = ? LIMIT 1');
            $stmt->bind_param('ii', $userId, $challengeId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $challenge = $result->fetch_assoc();

                $challengeDir = __DIR__ . $challenge['file_path'];
                $txtFiles = is_dir($challengeDir) ? glob($challengeDir . '/*.txt') : [];

                if (!$txtFiles || count($txtFiles) === 0) {
                    $error = 'Khong tim thay file dap an tren server.';
                } else {
                    $fileName = basename($txtFiles[0]);
                    $answerNoExt = pathinfo($fileName, PATHINFO_FILENAME);

                    $normalizedInput = strtolower(trim($answer));
                    $normalizedAnswerNoExt = strtolower(trim($answerNoExt));
                    $normalizedAnswerFull = strtolower(trim($fileName));

                    if ($normalizedInput === $normalizedAnswerNoExt || $normalizedInput === $normalizedAnswerFull) {
                        if (file_exists($txtFiles[0])) {
                            $poemContent = file_get_contents($txtFiles[0]);
                            $message = 'Chinh xac! Day la noi dung file dap an.';
                        } else {
                            $error = 'Khong tim thay file dap an tren server.';
                        }
                    } else {
                        $error = 'Dap an chua dung, hay thu lai.';
                    }
                }
            } else {
                $error = 'Challenge khong ton tai.';
            }
            $stmt->close();
        }
    }
}

if ($isTeacher) {
    $challengeRes = $conn->query('SELECT c.id, c.hint_text, c.created_at, u.full_name AS teacher_name FROM challenges c JOIN users u ON c.created_by = u.id ORDER BY c.id DESC LIMIT 1');
    $currentChallenge = $challengeRes->fetch_assoc();
} else {
    $challengeStmt = $conn->prepare('SELECT c.id, c.hint_text, c.created_at, u.full_name AS teacher_name FROM challenges c JOIN users u ON c.created_by = u.id JOIN class_user cu_student ON cu_student.user_id = ? JOIN class_user cu_teacher ON cu_teacher.user_id = c.created_by AND cu_teacher.class_id = cu_student.class_id ORDER BY c.id DESC LIMIT 1');
    $challengeStmt->bind_param('i', $userId);
    $challengeStmt->execute();
    $challengeRes = $challengeStmt->get_result();
    $currentChallenge = $challengeRes->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenge</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Challenge giai do</h2>
        <?php if ($message !== ''): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($isTeacher): ?>
            <h3>Tao challenge moi</h3>
            <form action="/challenge.php" method="post" enctype="multipart/form-data" class="card-form">
                <input type="hidden" name="action" value="create_challenge">

                <label>Goi y</label>
                <textarea name="hint_text" rows="3" required></textarea>

                <label>File txt (ten file khong dau la dap an)</label>
                <input type="file" name="challenge_file" accept=".txt" required>

                <input type="submit" value="Tao challenge">
            </form>
        <?php endif; ?>

        <?php if ($currentChallenge): ?>
            <h3>Challenge hien tai</h3>
            <div class="message-box">
                <p><strong>Goi y:</strong> <?= nl2br(htmlspecialchars($currentChallenge['hint_text'])) ?></p>
                <p>Tao boi: <?= htmlspecialchars($currentChallenge['teacher_name']) ?> | <?= htmlspecialchars($currentChallenge['created_at']) ?></p>
            </div>

            <?php if (!$isTeacher): ?>
                <form action="/challenge.php" method="post" class="card-form">
                    <input type="hidden" name="action" value="solve_challenge">
                    <input type="hidden" name="challenge_id" value="<?= (int)$currentChallenge['id'] ?>">

                    <label>Nhap dap an</label>
                    <input type="text" name="answer" placeholder="Dap an" required>

                    <input type="submit" value="Tra loi">
                </form>
            <?php endif; ?>
        <?php else: ?>
            <p>Chua co challenge nao duoc tao.</p>
        <?php endif; ?>

        <?php if ($poemContent !== ''): ?>
            <h3>Noi dung file dap an</h3>
            <pre class="poem-box"><?= htmlspecialchars($poemContent) ?></pre>
        <?php endif; ?>

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
<?php
$conn->close();
