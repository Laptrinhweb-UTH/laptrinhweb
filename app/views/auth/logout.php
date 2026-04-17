<?php
require_once __DIR__ . '/../../../config/config.php';

// Bắt buộc phải start session thì mới có cái mà xóa
session_start();

// Xóa sạch sẽ toàn bộ các biến trong session (user_id, user_name, role...)
session_unset();

// Hủy hoàn toàn phiên làm việc
session_destroy();

// Đẩy người dùng về trang chủ theo cấu hình local hiện tại
header("Location: " . asset_url('index.php'));
exit;
?>
