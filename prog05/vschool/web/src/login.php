<?php

require_once 'db.php';

function verify_user_password($inputPassword, $storedPassword) {
    if ($inputPassword === $storedPassword) {
        return true;
    }
    return password_verify($inputPassword, $storedPassword);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conn = get_conn();

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username va mat khau khong hop le.";
    }

    if ($error === '') {
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (verify_user_password($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Sai mat khau.";
            }
        } else {
            $error = "Khong tim thay tai khoan.";
        }

        $stmt->close();
    }
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Dang nhap</h2>
        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="/login.php" method="post" class="card-form">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="username">

            <label for="password">Mat khau</label>
            <input type="password" name="password" id="password" placeholder="password">

            <input type="submit" value="Dang nhap">
        </form>
        <p>Chua co tai khoan? <a href="/register.php">Dang ky</a></p>
        <p><a href="/index.php">Ve trang chu</a></p>
    </div>
</body>
</html>