<?php
require_once 'session.php';
require_once 'db.php';

$conn = get_conn();
$query = "SELECT id, username, full_name, email, phone_number, role FROM users WHERE role IN ('teacher', 'student') ORDER BY role, full_name";
$result = $conn->query($query);
?>
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
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone_number'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td><a href="/user_detail.php?id=<?= (int)$row['id'] ?>">Xem</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <p><a href="/dashboard.php">&larr; Ve dashboard</a></p>
    </div>
</body>
</html>
<?php
$conn->close();
