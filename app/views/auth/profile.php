<?php
session_start();

// ==========================================
// 1. BẢO MẬT: KIỂM TRA ĐĂNG NHẬP
// ==========================================
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa đăng nhập, đá về trang auth.php
    header("Location: /spinbike/app/views/auth/auth.php");
    exit;
}

// ==========================================
// 2. KẾT NỐI DATABASE VÀ MODEL (Chuẩn bị sẵn)
// ==========================================
// Bạn sẽ cần mở comment các dòng này khi bắt đầu viết code cập nhật DB
// require_once __DIR__ . '/../../../config/config.php';
// require_once __DIR__ . '/../../helpers/Database.php';
// require_once __DIR__ . '/../../models/User.php'; // Giả sử bạn có model User


// ==========================================
// 3. XỬ LÝ DỮ LIỆU KHI SUBMIT FORM (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nhận dữ liệu từ form gửi lên
    // $fullname = $_POST['fullname'] ?? '';
    // $phone = $_POST['phone'] ?? '';
    // $address = $_POST['address'] ?? '';
    // $new_password = $_POST['new_password'] ?? '';
    // $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Xử lý File Upload (Avatar) nếu có
    // if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) { ... }

    // Logic kiểm tra và gọi Model để Update vào Database nằm ở đây
    
    // Nếu thành công, có thể redirect hoặc gán biến thông báo
    // header("Location: update_profile.php?status=success");
    // exit;
}

// ==========================================
// 4. LẤY DỮ LIỆU CŨ ĐỂ HIỂN THỊ LÊN FORM
// ==========================================
// $user_id = $_SESSION['user_id'];
// Gọi hàm lấy thông tin user từ Model để điền sẵn vào các thẻ <input value="...">


// ==========================================
// 5. NHÚNG GIAO DIỆN (HEADER -> HTML -> FOOTER)
// ==========================================

// Lùi 2 cấp để nhúng file header (từ app/views/auth/ về app/views/layouts/)
include __DIR__ . '/../layouts/header.php'; 
?>

<?php 
// Lùi 2 cấp để nhúng file footer
include __DIR__ . '/../layouts/footer.php'; 
?>