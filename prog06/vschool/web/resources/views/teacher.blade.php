<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quan ly sinh vien</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Quan ly sinh vien</h2>
        @if ($message !== '')<div class="success">{{ $message }}</div>@endif
        @if ($error !== '')<div class="error">{{ $error }}</div>@endif

        <h3>{{ $editStudent ? 'Sua sinh vien' : 'Them sinh vien moi' }}</h3>
        <form action="/teacher.php" method="post" class="card-form">
            @csrf
            <input type="hidden" name="action" value="{{ $editStudent ? 'edit_student' : 'add_student' }}">
            @if ($editStudent)
                <input type="hidden" name="id" value="{{ (int) $editStudent->id }}">
            @endif

            <label>Username</label>
            <input type="text" name="username" value="{{ $editStudent->username ?? '' }}" required>

            <label>Mat khau {{ $editStudent ? '(de trong neu khong doi)' : '' }}</label>
            <input type="password" name="password" {{ $editStudent ? '' : 'required' }}>

            <label>Ho va ten</label>
            <input type="text" name="full_name" value="{{ $editStudent->full_name ?? '' }}" required>

            <label>Email</label>
            <input type="email" name="email" value="{{ $editStudent->email ?? '' }}" required>

            <label>So dien thoai</label>
            <input type="text" name="phone_number" value="{{ $editStudent->phone_number ?? '' }}">

            <input type="submit" value="{{ $editStudent ? 'Cap nhat' : 'Them sinh vien' }}">
        </form>

        <h3>Danh sach sinh vien</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Ho ten</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Ngay tao</th>
                    <th>Thao tac</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                <tr>
                    <td>{{ (int) $student->id }}</td>
                    <td>{{ $student->username }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->phone_number ?? '' }}</td>
                    <td>{{ $student->created_at }}</td>
                    <td>
                        <a href="/teacher.php?edit_id={{ (int) $student->id }}">Sua</a> |
                        <a class="danger-link" href="/teacher.php?delete_id={{ (int) $student->id }}" onclick="return confirm('Xoa sinh vien nay?')">Xoa</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
