<?php
// Bắt buộc phải start session thì mới có cái mà xóa
session_start();

// Nhúng file config để lấy hằng số BASE_URL
require_once __DIR__ . '/../../config/config.php';

// Xóa sạch sẽ toàn bộ các biến trong session (user_id, user_name, role...)
session_unset();

// Hủy hoàn toàn phiên làm việc
session_destroy();

// Đẩy người dùng về đúng trang chủ một cách an toàn tuyệt đối
header("Location: " . BASE_URL . "/index.php");
exit;
?>