<?php
// Bắt buộc phải start session thì mới có cái mà xóa
session_start();

// Xóa sạch sẽ toàn bộ các biến trong session (user_id, user_name, role...)
session_unset();

// Hủy hoàn toàn phiên làm việc
session_destroy();

// Đẩy người dùng thẳng về trang chủ bằng đường dẫn tuyệt đối (Không cần gọi config)
header("Location: /spinbike/public/index.php");
exit;
?>