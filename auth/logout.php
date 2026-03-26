<?php
session_start();
session_destroy(); // Xóa sạch dữ liệu đăng nhập
header("Location: ../index.php");
exit;
?>