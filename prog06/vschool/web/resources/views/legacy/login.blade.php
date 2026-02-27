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
        @if ($error !== '')
            <div class="error">{{ $error }}</div>
        @endif
        <form action="/login.php" method="post" class="card-form">
            @csrf
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
