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
        @if ($message !== '')<div class="success">{{ $message }}</div>@endif
        @if ($error !== '')<div class="error">{{ $error }}</div>@endif

        @if (!empty($user->avatar))
            <img class="avatar" src="{{ $user->avatar }}" alt="avatar">
        @endif

        <form action="/profile.php" method="post" enctype="multipart/form-data" class="card-form">
            @csrf
            <label>Username</label>
            <input type="text" value="{{ $user->username }}" disabled>

            <label>Ho va ten</label>
            <input type="text" value="{{ $user->full_name }}" disabled>

            <label>Email</label>
            <input type="email" name="email" value="{{ $user->email }}" required>

            <label>So dien thoai</label>
            <input type="text" name="phone_number" value="{{ $user->phone_number ?? '' }}">

            <label>Doi mat khau</label>
            <input type="password" name="password" placeholder="Nhap neu muon doi">

            <label>Upload avatar tu file</label>
            <input type="file" name="avatar_file" accept="image/*">

            <label>Hoac avatar URL</label>
            <input type="url" name="avatar_url" placeholder="https://...">

            <input type="submit" value="Luu thay doi">
        </form>

        <p><strong>Vai tro:</strong> {{ $user->role }}</p>

        <h3>Danh sach lop cua toi</h3>
        @if ($classes->count())
            <ul>
                @foreach ($classes as $class)
                    <li>
                        {{ $class->class_name }}
                        @if (!empty($class->class_code))
                            ({{ $class->class_code }})
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p>Ban chua tham gia lop nao.</p>
        @endif

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
