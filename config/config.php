<?php
// Thông tin cấu hình Database
$host = 'localhost';
$dbname = 'spinbike_db';
$username = 'root';
$password = '';

try {
    // Khởi tạo kết nối PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Thiết lập chế độ ném lỗi Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // Nếu kết nối thất bại, báo lỗi và dừng chạy
    die("Kết nối Database thất bại: " . $e->getMessage());
}
?>