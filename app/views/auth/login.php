<?php
session_start();
header('Content-Type: application/json');

// 1. Sửa lại đường dẫn lùi ra 2 cấp để gọi Database Helper
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/AdminAuth.php';
require_once __DIR__ . '/../../../config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email và mật khẩu không được để trống!']);
        exit;
    }

    try {
        // 2. Khởi tạo kết nối DB chuẩn MVC
        $database = new Database();
        $conn = $database->getConnection();

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

            $redirectUrl = (string) $user['role'] === 'admin'
                ? admin_dashboard_url()
                : route_url('home');

            echo json_encode([
                'status' => 'success',
                'message' => '✅ Đăng nhập thành công!',
                'redirect_url' => $redirectUrl,
            ]);
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
