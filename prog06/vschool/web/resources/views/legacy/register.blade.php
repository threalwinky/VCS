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

        @if ($message !== '')
            <div class="success">{{ $message }}</div>
        @endif

        @if ($error !== '')
            <div class="error">{{ $error }}</div>
        @endif

        <form action="/register.php" method="post" class="card-form">
            @csrf
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
