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
        @if ($message !== '')<div class="success">{{ $message }}</div>@endif
        @if ($error !== '')<div class="error">{{ $error }}</div>@endif

        <h3>Gui tin nhan moi</h3>
        <form action="/mailbox.php" method="post" class="card-form">
            @csrf
            <input type="hidden" name="action" value="send_message">

            <label>Nguoi nhan</label>
            <select name="receiver_id" required>
                <option value="">-- Chon nguoi nhan --</option>
                @foreach ($userList as $u)
                    <option value="{{ (int) $u->id }}">{{ $u->full_name }} ({{ $u->username }} - {{ $u->role }})</option>
                @endforeach
            </select>

            <label>Noi dung</label>
            <textarea name="content" rows="3" required></textarea>
            <input type="submit" value="Gui tin nhan">
        </form>

        <h3>Thu den</h3>
        @foreach ($inbox as $row)
            <div class="message-box">
                <p><strong>{{ $row->sender_name }}</strong> ({{ $row->sender_username }}) - {{ $row->created_at }}</p>
                <p>{!! nl2br(e($row->content)) !!}</p>
                <form action="/mailbox.php" method="post" class="inline-form">
                    @csrf
                    <input type="hidden" name="action" value="toggle_read">
                    <input type="hidden" name="message_id" value="{{ (int) $row->id }}">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_read" value="1" {{ (int) $row->is_read === 1 ? 'checked' : '' }}> Da doc
                    </label>
                    <button type="submit">Luu trang thai</button>
                </form>
                <p>Trang thai: <strong>{{ (int) $row->is_read === 1 ? 'Da doc' : 'Chua doc' }}</strong>
                    @if ((int) $row->is_read === 1 && !empty($row->read_at))
                        - luc {{ $row->read_at }}
                    @endif
                </p>
            </div>
        @endforeach

        <h3>Thu da gui</h3>
        @foreach ($outbox as $row)
            <div class="message-box">
                <p>Toi <strong>{{ $row->receiver_name }}</strong> ({{ $row->receiver_username }}) - {{ $row->created_at }}</p>
                <p>{!! nl2br(e($row->content)) !!}</p>
                <p>Nguoi nhan: <strong>{{ (int) $row->is_read === 1 ? 'Da doc' : 'Chua doc' }}</strong></p>
                <form action="/mailbox.php" method="post" class="inline-form">
                    @csrf
                    <input type="hidden" name="action" value="edit_message">
                    <input type="hidden" name="message_id" value="{{ (int) $row->id }}">
                    <input type="text" name="content" value="{{ $row->content }}" required>
                    <button type="submit">Sua</button>
                    <a class="danger-link" href="/mailbox.php?delete_message={{ (int) $row->id }}" onclick="return confirm('Xoa tin nhan da gui?')">Xoa</a>
                </form>
            </div>
        @endforeach

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
