<?php

require_once 'db.php';

session_start();

if (empty($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['user_id']) && !empty($_SESSION['username'])) {
    $conn = get_conn();
    $stmt = $conn->prepare("SELECT id, full_name, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
    }
    $stmt->close();
    $conn->close();
}

function current_user_id() {
    return (int)($_SESSION['user_id'] ?? 0);
}

function current_username() {
    return $_SESSION['username'] ?? '';
}

function current_role() {
    return $_SESSION['role'] ?? '';
}

function is_teacher_or_admin() {
    $role = current_role();
    return $role === 'teacher' || $role === 'admin';
}

function require_teacher_or_admin() {
    if (!is_teacher_or_admin()) {
        http_response_code(403);
        die('Ban khong co quyen truy cap trang nay.');
    }
}