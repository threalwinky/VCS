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
        @if ($message !== '')<div class="success">{{ $message }}</div>@endif
        @if ($error !== '')<div class="error">{{ $error }}</div>@endif

        @if ($isTeacher)
            <h3>Giao bai moi</h3>
            <form action="/assignments.php" method="post" enctype="multipart/form-data" class="card-form">
                @csrf
                <input type="hidden" name="action" value="create_assignment">

                <label>Tieu de bai tap</label>
                <input type="text" name="title" required>

                <label>Mo ta</label>
                <textarea name="description" rows="3"></textarea>

                <label>File bai tap</label>
                <input type="file" name="assignment_file" required>

                <input type="submit" value="Dang bai tap">
            </form>
        @endif

        <h3>Danh sach bai tap</h3>
        @foreach ($assignments as $assignment)
            <div class="message-box">
                <p><strong>{{ $assignment->title }}</strong></p>
                <p>{!! nl2br(e($assignment->description ?? '')) !!}</p>
                <p>Giao boi: {{ $assignment->teacher_name }} | {{ $assignment->created_at }}</p>
                <p><a href="{{ $assignment->file_path }}" download="{{ $assignment->original_name }}">Tai file bai tap</a></p>

                @if (!$isTeacher)
                    <form action="/assignments.php" method="post" enctype="multipart/form-data" class="inline-form">
                        @csrf
                        <input type="hidden" name="action" value="submit_assignment">
                        <input type="hidden" name="assignment_id" value="{{ (int) $assignment->id }}">
                        <input type="file" name="submission_file" required>
                        <button type="submit">Nop bai</button>
                    </form>
                @endif
            </div>
        @endforeach

        @if ($isTeacher)
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
                    @foreach ($submissionRows as $row)
                        <tr>
                            <td>{{ $row->assignment_title }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td><a href="{{ $row->file_path }}" download="{{ $row->original_name }}">Tai bai nop</a></td>
                            <td>{{ $row->submitted_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
