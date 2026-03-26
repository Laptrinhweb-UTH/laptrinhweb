<?php
session_start();
header('Content-Type: application/json');

// Gọi file kết nối Database
require_once '../config/config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email và mật khẩu không được để trống!']);
        exit;
    }

    try {
        // Tìm user trong Database theo email
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra user tồn tại và mật khẩu khớp
        if ($user && password_verify($password, $user['password'])) {
            
            // Đăng nhập thành công -> Lưu session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // Lưu session vào file
            session_write_close();

            echo json_encode(['status' => 'success', 'message' => '✅ Đăng nhập thành công!']);
        } else {
            // Sai email hoặc sai mật khẩu
            echo json_encode(['status' => 'error', 'message' => '❌ Email hoặc mật khẩu không chính xác!']);
        }

    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi Hệ thống: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Truy cập không hợp lệ!']);
}
?>