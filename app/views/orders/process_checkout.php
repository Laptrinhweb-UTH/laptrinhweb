<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';

function redirect_with_feedback(string $url, string $message, string $status = 'error'): never {
    $separator = str_contains($url, '?') ? '&' : '?';
    header('Location: ' . $url . $separator . 'status=' . rawurlencode($status) . '&message=' . rawurlencode($message));
    exit;
}

// Bảo vệ file: Phải đăng nhập và đi vào bằng nút "Submit" mới được
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . asset_url('index.php'));
    exit;
}

$buyer_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$payment_method = $_POST['payment_method'] ?? '';
$allowedPaymentMethods = ['vnpay', 'momo'];
$checkoutUrl = app_url('app/views/orders/checkout.php') . '?product_id=' . ($product_id ?: '');

if ($product_id === false || $product_id === null || !in_array($payment_method, $allowedPaymentMethods, true)) {
    redirect_with_feedback(asset_url('index.php'), 'Dữ liệu thanh toán không hợp lệ. Vui lòng thử lại.');
}

// TẠI ĐÂY MÔ PHỎNG VIỆC GỌI API VNPAY/MOMO THÀNH CÔNG
// Nếu tích hợp thật, code VNPAY sẽ redirect người dùng sang app ngân hàng ở đây.
// Vì đang test, ta coi như thanh toán auto thành công.

$database = new Database();
$db = $database->getConnectionOrNull();

if (!$db) {
    redirect_with_feedback($checkoutUrl, 'Không thể kết nối dữ liệu để xử lý thanh toán. Vui lòng thử lại sau.');
}

try {
    $productStmt = $db->prepare("SELECT id, seller_id, price, listing_status FROM products WHERE id = ? LIMIT 1");
    $productStmt->execute([$product_id]);
    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        throw new Exception("Sản phẩm không tồn tại hoặc đã bị xóa.");
    }

    if ((int)$product['seller_id'] === (int)$buyer_id) {
        throw new Exception("Bạn không thể tự mua xe của chính mình.");
    }

    if (($product['listing_status'] ?? '') !== ProjectFlow::LISTING_APPROVED) {
        throw new Exception("Tin đăng này hiện không còn ở trạng thái có thể thanh toán.");
    }

    $seller_id = (int)$product['seller_id'];
    $amount = $product['price'];

    if (!is_numeric($amount) || (float)$amount <= 0) {
        throw new Exception("Giá sản phẩm không hợp lệ để thanh toán.");
    }

    // Mở Transaction: Đảm bảo viết vào Orders và Escrows cùng lúc, nếu lỗi thì hủy tất cả
    $db->beginTransaction();

    // 1. Tạo Đơn hàng (orders) với trạng thái 'paid' (Đã thanh toán)
    $queryOrder = "INSERT INTO orders (buyer_id, seller_id, product_id, amount, status) VALUES (?, ?, ?, ?, ?)";
    $stmtOrder = $db->prepare($queryOrder);
    $stmtOrder->execute([$buyer_id, $seller_id, $product_id, $amount, ProjectFlow::ORDER_PAID]);
    
    // Lấy ID đơn hàng vừa được tạo
    $order_id = $db->lastInsertId();

    // 2. Tạo Két sắt giữ tiền (escrows) với trạng thái 'holding' (Đang giữ)
    $queryEscrow = "INSERT INTO escrows (order_id, amount, status) VALUES (?, ?, ?)";
    $stmtEscrow = $db->prepare($queryEscrow);
    $stmtEscrow->execute([$order_id, $amount, ProjectFlow::ESCROW_HOLDING]);

    $productModel = new Product($db);
    if (!$productModel->markAsSold((int) $product_id)) {
        throw new Exception("Không thể khóa tin đăng cho đơn hàng này. Vui lòng thử lại.");
    }
    
    // Xác nhận lưu vào Database
    $db->commit();

    redirect_with_feedback(
        app_url("app/views/orders/detail.php") . "?id=" . $order_id,
        'Thanh toán thành công. SpinBike đang giữ tiền an toàn cho đơn hàng của bạn.',
        'success'
    );

} catch (Exception $e) {
    // Nếu có lỗi CSDL, hủy bỏ lệnh
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    redirect_with_feedback($checkoutUrl, $e->getMessage());
}
?>
