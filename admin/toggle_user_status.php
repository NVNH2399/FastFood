<?php
include '../connect.php';
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    http_response_code(403);
    die("Không có quyền truy cập");
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Kiểm tra xem có phải admin không
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user['username'] === 'admin') {
        http_response_code(400);
        die("Không thể khóa tài khoản admin");
    }

    // Cập nhật trạng thái
    $sql = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
    }
} else {
    http_response_code(400);
    echo "Invalid request";
}