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
        @if ($message !== '')<div class="success">{{ $message }}</div>@endif
        @if ($error !== '')<div class="error">{{ $error }}</div>@endif

        <img class="avatar" src="{{ !empty($user->avatar) ? $user->avatar : '/static/images/default.jpg' }}" alt="avatar">

        <p><strong>Username:</strong> {{ $user->username }}</p>
        <p><strong>Ho ten:</strong> {{ $user->full_name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>So dien thoai:</strong> {{ $user->phone_number ?? '' }}</p>
        <p><strong>Role:</strong> {{ $user->role }}</p>

        @if ($currentUserId !== (int) $user->id)
            <h3>Gui tin nhan</h3>
            <form action="/user_detail.php?id={{ (int) $user->id }}" method="post" class="card-form">
                @csrf
                <input type="hidden" name="action" value="send_message">
                <textarea name="content" rows="3" required placeholder="Nhap noi dung..."></textarea>
                <input type="submit" value="Gui tin nhan">
            </form>
        @endif

        @if ($canReadMessages)
            <h3>Tin nhan gui toi ban</h3>
            @foreach ($messages as $msg)
                <div class="message-box">
                    <p><strong>{{ $msg->sender_username }}</strong> - {{ $msg->created_at }}</p>
                    <p>{!! nl2br(e($msg->content)) !!}</p>
                </div>
            @endforeach
        @endif

        <p><a href="/users.php">&larr; Ve danh sach nguoi dung</a></p>
    </div>
</body>
</html>
