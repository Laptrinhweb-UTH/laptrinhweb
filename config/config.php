<?php
// Thông tin cấu hình Database (Dành cho XAMPP / Laragon mặc định)
$host = 'localhost';
$dbname = 'spinbike_db'; // Bạn có thể đổi tên này thành tên Database của bạn trong phpMyAdmin
$username = 'root';      // User mặc định ở localhost thường là root
$password = '';          // Mật khẩu mặc định thường để trống

try {
    // Khởi tạo kết nối PDO với charset utf8mb4 (để không bị lỗi font tiếng Việt)
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Thiết lập chế độ báo lỗi (ném ra Exception nếu có lỗi)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Nếu bạn muốn test xem kết nối được chưa, hãy bỏ dấu // ở dòng dưới đây:
    // echo "Kết nối cơ sở dữ liệu thành công!"; 
    
} catch(PDOException $e) {
    // Nếu kết nối thất bại, báo lỗi và dừng chạy file
    echo "Kết nối Database thất bại: " . $e->getMessage();
    die();
}

?>