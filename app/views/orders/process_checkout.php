<?php
session_start();
require_once __DIR__ . '/../../helpers/Database.php';

// Bảo vệ file: Phải đăng nhập và đi vào bằng nút "Submit" mới được
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /spinbike/index.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$seller_id = $_POST['seller_id'];
$amount = $_POST['amount'];
$payment_method = $_POST['payment_method'];

// TẠI ĐÂY MÔ PHỎNG VIỆC GỌI API VNPAY/MOMO THÀNH CÔNG
// Nếu tích hợp thật, code VNPAY sẽ redirect người dùng sang app ngân hàng ở đây.
// Vì đang test, ta coi như thanh toán auto thành công.

$db = (new Database())->getConnection();

try {
    // Mở Transaction: Đảm bảo viết vào Orders và Escrows cùng lúc, nếu lỗi thì hủy tất cả
    $db->beginTransaction();

    // 1. Tạo Đơn hàng (orders) với trạng thái 'paid' (Đã thanh toán)
    $queryOrder = "INSERT INTO orders (buyer_id, seller_id, product_id, amount, status) VALUES (?, ?, ?, ?, 'paid')";
    $stmtOrder = $db->prepare($queryOrder);
    $stmtOrder->execute([$buyer_id, $seller_id, $product_id, $amount]);
    
    // Lấy ID đơn hàng vừa được tạo
    $order_id = $db->lastInsertId();

    // 2. Tạo Két sắt giữ tiền (escrows) với trạng thái 'holding' (Đang giữ)
    $queryEscrow = "INSERT INTO escrows (order_id, amount, status) VALUES (?, ?, 'holding')";
    $stmtEscrow = $db->prepare($queryEscrow);
    $stmtEscrow->execute([$order_id, $amount]);

    // LƯU Ý MỞ RỘNG: Chỗ này có thể Update trạng thái của product thành 'sold' để không ai mua trùng.
    
    // Xác nhận lưu vào Database
    $db->commit();

    // Đẩy người dùng sang trang Theo dõi đơn hàng (Kèm theo thông báo xịn)
    echo "<script>
            alert('Thanh toán thành công! Tiền của bạn đang được SpinBike giữ an toàn.');
            window.location.href = '/spinbike/app/views/orders/detail.php?id=" . $order_id . "';
          </script>";
    exit;

} catch (Exception $e) {
    // Nếu có lỗi CSDL, hủy bỏ lệnh
    $db->rollBack();
    echo "<script>alert('Lỗi hệ thống: " . $e->getMessage() . "'); window.history.back();</script>";
}
?>