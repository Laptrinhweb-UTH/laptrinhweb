<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kiểm tra 2 mật khẩu có khớp nhau không
    if ($password !== $confirm_password) {
        echo "<script>alert('Mật khẩu không khớp!'); window.history.back();</script>";
        exit;
    }

    $userModel = new User();
    
    // Tìm user đang sở hữu token này
    $user = $userModel->findUserByToken($token);

    if ($user) {
        // Nếu tìm thấy và token còn hạn thì tiến hành đổi pass
        if ($userModel->updatePassword($user['id'], $password)) {
            // Lấy link trang đăng nhập dựa vào hệ thống route của bạn
            $loginUrl = route_url('auth.login');
            
            // Thông báo thành công và chuyển hướng
            echo "<script>
                    alert('Đổi mật khẩu thành công! Vui lòng đăng nhập lại.');
                    window.location.href = '{$loginUrl}';
                  </script>";
            exit;
        }
    } else {
        // Token sai hoặc đã quá hạn 1 tiếng
        echo "<script>alert('Đường dẫn đổi mật khẩu không hợp lệ hoặc đã hết hạn!'); window.location.href='" . route_url('home') . "';</script>";
    }
}
?>