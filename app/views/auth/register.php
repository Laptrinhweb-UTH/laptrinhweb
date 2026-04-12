<?php
session_start();
header('Content-Type: application/json');

// 1. Sửa lại đường dẫn lùi ra 2 cấp để gọi Database Helper
require_once __DIR__ . '/../../helpers/Database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu khớp
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit;
    }

    try {
        // 2. Khởi tạo kết nối DB chuẩn MVC
        $database = new Database();
        $conn = $database->getConnection();

        // Kiểm tra email tồn tại
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $checkEmail->bindParam(':email', $email);
        $checkEmail->execute();

        if ($checkEmail->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email này đã được sử dụng!']);
            exit;
        }

        // Mã hóa mật khẩu và insert
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'user')";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => '🎉 Chúc mừng! Bạn đã đăng ký tài khoản thành công.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi: Không thể tạo tài khoản. Vui lòng thử lại!']);
        }

    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi Hệ thống: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Truy cập không hợp lệ!']);
}
?>