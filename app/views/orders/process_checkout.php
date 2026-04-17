<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';

// Bảo vệ file: Phải đăng nhập và đi vào bằng nút "Submit" mới được
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . asset_url('index.php'));
    exit;
}

$buyer_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$payment_method = $_POST['payment_method'] ?? '';
$allowedPaymentMethods = ['vnpay', 'momo'];

if ($product_id === false || $product_id === null || !in_array($payment_method, $allowedPaymentMethods, true)) {
    echo "<script>alert('Dữ liệu thanh toán không hợp lệ. Vui lòng thử lại.'); window.location.href='" . asset_url('index.php') . "';</script>";
    exit;
}

// TẠI ĐÂY MÔ PHỎNG VIỆC GỌI API VNPAY/MOMO THÀNH CÔNG
// Nếu tích hợp thật, code VNPAY sẽ redirect người dùng sang app ngân hàng ở đây.
// Vì đang test, ta coi như thanh toán auto thành công.

$database = new Database();
$db = $database->getConnectionOrNull();

if (!$db) {
    echo "<script>alert('Không thể kết nối dữ liệu để xử lý thanh toán. Vui lòng thử lại sau.'); window.history.back();</script>";
    exit;
}

try {
    $productStmt = $db->prepare("SELECT id, seller_id, price FROM products WHERE id = ? LIMIT 1");
    $productStmt->execute([$product_id]);
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Sản phẩm không tồn tại hoặc đã bị xóa.");
    }

    if ((int)$product['seller_id'] === (int)$buyer_id) {
        throw new Exception("Bạn không thể tự mua xe của chính mình.");
    }

    $seller_id = (int)$product['seller_id'];
    $amount = $product['price'];

    if (!is_numeric($amount) || (float)$amount <= 0) {
        throw new Exception("Giá sản phẩm không hợp lệ để thanh toán.");
    }

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
            window.location.href = '" . app_url("app/views/orders/detail.php") . "?id=" . $order_id . "';
          </script>";
    exit;

} catch (Exception $e) {
    // Nếu có lỗi CSDL, hủy bỏ lệnh
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "<script>alert('Lỗi hệ thống: " . $e->getMessage() . "'); window.history.back();</script>";
}
?>
