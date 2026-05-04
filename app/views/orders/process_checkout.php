<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../helpers/VnpayHelper.php';
require_once __DIR__ . '/../../models/Product.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

function redirect_with_feedback(string $url, string $message, string $status = 'error'): never {
    $separator = str_contains($url, '?') ? '&' : '?';
    header('Location: ' . $url . $separator . 'status=' . rawurlencode($status) . '&message=' . rawurlencode($message));
    exit;
}

// Bảo vệ file: Phải đăng nhập và đi vào bằng nút "Submit" mới được
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . route_url('home'));
    exit;
}

$buyer_id = $_SESSION['user_id'];
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$payment_method = $_POST['payment_method'] ?? '';
$allowedPaymentMethods = ['vnpay'];
$checkoutUrl = route_url('checkout', ['product_id' => $product_id ?: '']);

if ($product_id === false || $product_id === null || !in_array($payment_method, $allowedPaymentMethods, true)) {
    redirect_with_feedback(route_url('home'), 'Dữ liệu thanh toán không hợp lệ. Vui lòng thử lại.');
}

if ($payment_method === 'vnpay' && !VnpayHelper::isConfigured()) {
    redirect_with_feedback(
        $checkoutUrl,
        'Chưa cấu hình VNPAY sandbox. Vui lòng điền VNPAY_TMN_CODE và VNPAY_HASH_SECRET trước khi demo thanh toán QR.'
    );
}

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

    $buyerPendingStmt = $db->prepare(
        "SELECT id FROM orders WHERE product_id = ? AND buyer_id = ? AND status = ? LIMIT 1"
    );
    $buyerPendingStmt->execute([$product_id, $buyer_id, ProjectFlow::ORDER_PENDING_PAYMENT]);
    $buyerPending = $buyerPendingStmt->fetch(PDO::FETCH_ASSOC);
    if ($buyerPending) {
        $db->rollBack();
        redirect_with_feedback(
            route_url('order', ['id' => (int) $buyerPending['id']]),
            'Bạn đang có đơn hàng chờ thanh toán cho sản phẩm này. Vui lòng hoàn tất hoặc liên hệ hỗ trợ để hủy đơn.',
            'error'
        );
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

    $order_id = (int) $db->lastInsertId();

    if ($payment_method === 'vnpay') {
        $db->commit();

        $paymentUrl = VnpayHelper::buildPaymentUrl([
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => VNPAY_TMN_CODE,
            'vnp_Amount' => (string) ((int) round((float) $amount) * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => (string) $order_id,
            'vnp_OrderInfo' => VnpayHelper::buildOrderInfo($order_id),
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => route_url('checkout.vnpay-return'),
            'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
            'vnp_BankCode' => VNPAY_DEFAULT_BANK_CODE,
        ]);

        header('Location: ' . $paymentUrl);
        exit;
    }

    throw new Exception('Phương thức thanh toán không được hỗ trợ.');

} catch (Exception $e) {
    // Nếu có lỗi CSDL, hủy bỏ lệnh
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    redirect_with_feedback($checkoutUrl, $e->getMessage());
}
?>
