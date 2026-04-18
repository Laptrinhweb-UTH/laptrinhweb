<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../models/Product.php';

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
    $db->beginTransaction();

    $productStmt = $db->prepare("SELECT id, seller_id, price, listing_status FROM products WHERE id = ? LIMIT 1 FOR UPDATE");
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

    $activeOrderStmt = $db->prepare(
        "SELECT o.id
         FROM orders o
         LEFT JOIN escrows e ON e.order_id = o.id
         WHERE o.product_id = ?
           AND (
                o.status IN (?, ?, ?, ?)
                OR e.status IN (?, ?)
           )
         LIMIT 1"
    );
    $activeOrderStmt->execute([
        $product_id,
        ProjectFlow::ORDER_PENDING_PAYMENT,
        ProjectFlow::ORDER_PAID,
        ProjectFlow::ORDER_SELLER_CONFIRMED,
        ProjectFlow::ORDER_SHIPPING,
        ProjectFlow::ESCROW_HOLDING,
        ProjectFlow::ESCROW_DISPUTED,
    ]);

    if ($activeOrderStmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception("Chiếc xe này vừa phát sinh giao dịch khác. Vui lòng tải lại trang để xem trạng thái mới nhất.");
    }

    // 1. Tạo đơn hàng ở trạng thái chờ thanh toán để phản ánh đúng flow đặt mua.
    $queryDraftOrder = "INSERT INTO orders (buyer_id, seller_id, product_id, amount, status) VALUES (?, ?, ?, ?, ?)";
    $stmtDraftOrder = $db->prepare($queryDraftOrder);
    $stmtDraftOrder->execute([$buyer_id, $seller_id, $product_id, $amount, ProjectFlow::ORDER_PENDING_PAYMENT]);

    $order_id = $db->lastInsertId();

    // 2. Mô phỏng cổng thanh toán thành công và cập nhật đơn sang trạng thái đã thanh toán.
    $stmtOrder = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmtOrder->execute([ProjectFlow::ORDER_PAID, $order_id]);

    // 3. Tạo escrow ở trạng thái holding ngay sau khi thanh toán thành công.
    $queryEscrow = "INSERT INTO escrows (order_id, amount, status) VALUES (?, ?, ?)";
    $stmtEscrow = $db->prepare($queryEscrow);
    $stmtEscrow->execute([$order_id, $amount, ProjectFlow::ESCROW_HOLDING]);

    // 4. Ghi nhận dòng tiền người mua đã thanh toán vào hệ thống.
    $transactionStmt = $db->prepare(
        "INSERT INTO transactions (user_id, order_id, amount, fee, type) VALUES (?, ?, ?, ?, 'payment')"
    );
    $transactionStmt->execute([$buyer_id, $order_id, $amount, 0]);

    // 5. Khóa tin đăng để tránh phát sinh giao dịch trùng.
    $productModel = new Product($db);
    if (!$productModel->markAsSold((int) $product_id)) {
        throw new Exception("Không thể khóa tin đăng cho đơn hàng này. Vui lòng thử lại.");
    }
    
    // Xác nhận lưu vào Database
    $db->commit();

    redirect_with_feedback(
        app_url("app/views/orders/detail.php") . "?id=" . $order_id,
        'Đặt mua thành công. Hệ thống đã tạo đơn hàng và đang giữ tiền an toàn cho giao dịch của bạn.',
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
