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
        @if ($message !== '')<div class="success">{{ $message }}</div>@endif
        @if ($error !== '')<div class="error">{{ $error }}</div>@endif

        @if ($isTeacher)
            <h3>Tao challenge moi</h3>
            <form action="/challenge.php" method="post" enctype="multipart/form-data" class="card-form">
                @csrf
                <input type="hidden" name="action" value="create_challenge">

                <label>Goi y</label>
                <textarea name="hint_text" rows="3" required></textarea>

                <label>File txt (ten file khong dau la dap an)</label>
                <input type="file" name="challenge_file" accept=".txt" required>

                <input type="submit" value="Tao challenge">
            </form>
        @endif

        @if ($currentChallenge)
            <h3>Challenge hien tai</h3>
            <div class="message-box">
                <p><strong>Goi y:</strong> {!! nl2br(e($currentChallenge->hint_text)) !!}</p>
                <p>Tao boi: {{ $currentChallenge->teacher_name }} | {{ $currentChallenge->created_at }}</p>
            </div>

            @if (!$isTeacher)
                <form action="/challenge.php" method="post" class="card-form">
                    @csrf
                    <input type="hidden" name="action" value="solve_challenge">
                    <input type="hidden" name="challenge_id" value="{{ (int) $currentChallenge->id }}">

                    <label>Nhap dap an</label>
                    <input type="text" name="answer" placeholder="Dap an" required>

                    <input type="submit" value="Tra loi">
                </form>
            @endif
        @else
            <p>Chua co challenge nao duoc tao.</p>
        @endif

        @if ($poemContent !== '')
            <h3>Noi dung file dap an</h3>
            <pre class="poem-box">{{ $poemContent }}</pre>
        @endif

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
