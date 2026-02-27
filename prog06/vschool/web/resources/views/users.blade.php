<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sach nguoi dung</title>
    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Danh sach nguoi dung</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Ho ten</th>
                    <th>Email</th>
                    <th>So dien thoai</th>
                    <th>Role</th>
                    <th>Chi tiet</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $row)
                    <tr>
                        <td>{{ $row->username }}</td>
                        <td>{{ $row->full_name }}</td>
                        <td>{{ $row->email }}</td>
                        <td>{{ $row->phone_number ?? '' }}</td>
                        <td>{{ $row->role }}</td>
                        <td><a href="/user_detail.php?id={{ (int) $row->id }}">Xem</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
