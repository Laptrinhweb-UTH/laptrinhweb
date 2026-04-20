<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php'; // Đảm bảo bạn đã tạo model User như mình hướng dẫn trước đó

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $userModel = new User();
    $user = $userModel->findUserByEmail($email);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expire = date("Y-m-d H:i:s", time() + 3600); // Hạn 1 tiếng

        if ($userModel->saveResetToken($email, $token, $expire)) {
            // Tạo link reset dựa vào hàm route_url() của bạn
            $resetLink = route_url('auth.reset_password', ['token' => $token]);
            
            // TẠM THỜI: In thẳng ra màn hình để test. Sau này bạn thay bằng code gửi Email (PHPMailer)
            echo "<h3>Hãy click vào link sau để đổi mật khẩu:</h3>";
            echo "<a href='{$resetLink}'>{$resetLink}</a>";
            exit;
        }
    } else {
        echo "<script>alert('Email không tồn tại!'); window.history.back();</script>";
    }
}
?>