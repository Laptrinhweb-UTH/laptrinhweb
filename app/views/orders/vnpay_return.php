<?php
session_start();

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../helpers/VnpayHelper.php';
require_once __DIR__ . '/../../models/Product.php';

function redirect_vnpay_feedback(string $url, string $message, string $status = 'error'): never
{
    $separator = str_contains($url, '?') ? '&' : '?';
    header('Location: ' . $url . $separator . 'status=' . rawurlencode($status) . '&message=' . rawurlencode($message));
    exit;
}

if (!VnpayHelper::isConfigured()) {
    redirect_vnpay_feedback(route_url('home'), 'Chưa cấu hình VNPAY sandbox để xác thực kết quả thanh toán.');
}

$orderId = filter_input(INPUT_GET, 'vnp_TxnRef', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$responseCode = (string) ($_GET['vnp_ResponseCode'] ?? '');
$transactionStatus = (string) ($_GET['vnp_TransactionStatus'] ?? '');
$vnpAmount = filter_input(INPUT_GET, 'vnp_Amount', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($orderId === false || $orderId === null) {
    redirect_vnpay_feedback(route_url('home'), 'VNPAY trả về mã đơn hàng không hợp lệ.');
}

$database = new Database();
$db = $database->getConnectionOrNull();

if (!$db) {
    redirect_vnpay_feedback(route_url('home'), 'Không thể kết nối dữ liệu để xác nhận thanh toán VNPAY.');
}

try {
    $db->beginTransaction();

    $orderStmt = $db->prepare(
        "SELECT
            o.id,
            o.buyer_id,
            o.seller_id,
            o.product_id,
            o.amount,
            o.status AS order_status,
            p.listing_status,
            e.id AS escrow_id
         FROM orders o
         INNER JOIN products p ON p.id = o.product_id
         LEFT JOIN escrows e ON e.order_id = o.id
         WHERE o.id = ?
         LIMIT 1
         FOR UPDATE"
    );
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Không tìm thấy đơn hàng cần xác nhận thanh toán.');
    }

    $checkoutUrl = route_url('checkout', ['product_id' => (int) $order['product_id']]);
    $orderUrl = route_url('order', ['id' => (int) $order['id']]);

    if (!VnpayHelper::validateReturn($_GET)) {
        if ((string) $order['order_status'] === ProjectFlow::ORDER_PENDING_PAYMENT) {
            $db->prepare("UPDATE orders SET status = ? WHERE id = ?")
                ->execute([ProjectFlow::ORDER_CANCELLED, (int) $order['id']]);
        }

        $db->commit();
        redirect_vnpay_feedback($checkoutUrl, 'Không thể xác thực chữ ký phản hồi từ VNPAY.');
    }

    if ((string) $order['order_status'] === ProjectFlow::ORDER_PAID) {
        $db->commit();
        redirect_vnpay_feedback($orderUrl, 'Đơn hàng này đã được xác nhận thanh toán trước đó.', 'success');
    }

    if ((string) $order['order_status'] !== ProjectFlow::ORDER_PENDING_PAYMENT) {
        throw new Exception('Đơn hàng không còn ở trạng thái chờ thanh toán.');
    }

    $expectedAmount = (int) round((float) $order['amount']) * 100;
    if ($vnpAmount === false || $vnpAmount === null || $vnpAmount !== $expectedAmount) {
        $db->prepare("UPDATE orders SET status = ? WHERE id = ?")
            ->execute([ProjectFlow::ORDER_CANCELLED, (int) $order['id']]);

        $db->commit();
        redirect_vnpay_feedback($checkoutUrl, 'Số tiền VNPAY trả về không khớp với đơn hàng.');
    }

    if ($responseCode !== '00' || ($transactionStatus !== '' && $transactionStatus !== '00')) {
        $db->prepare("UPDATE orders SET status = ? WHERE id = ?")
            ->execute([ProjectFlow::ORDER_CANCELLED, (int) $order['id']]);

        $db->commit();
        redirect_vnpay_feedback($checkoutUrl, VnpayHelper::responseMessage($responseCode, $transactionStatus));
    }

    if ((string) $order['listing_status'] !== ProjectFlow::LISTING_APPROVED) {
        $db->prepare("UPDATE orders SET status = ? WHERE id = ?")
            ->execute([ProjectFlow::ORDER_CANCELLED, (int) $order['id']]);

        $db->commit();
        redirect_vnpay_feedback($checkoutUrl, 'Tin đăng này vừa thay đổi trạng thái nên không thể hoàn tất thanh toán.');
    }

    $db->prepare("UPDATE orders SET status = ? WHERE id = ?")
        ->execute([ProjectFlow::ORDER_PAID, (int) $order['id']]);

    if (empty($order['escrow_id'])) {
        $db->prepare("INSERT INTO escrows (order_id, amount, status) VALUES (?, ?, ?)")
            ->execute([(int) $order['id'], (float) $order['amount'], ProjectFlow::ESCROW_HOLDING]);
    }

    $db->prepare("INSERT INTO transactions (user_id, order_id, amount, fee, type) VALUES (?, ?, ?, ?, 'payment')")
        ->execute([(int) $order['buyer_id'], (int) $order['id'], (float) $order['amount'], 0]);

    $productModel = new Product($db);
    if (!$productModel->markAsSold((int) $order['product_id'])) {
        throw new Exception('Không thể khóa tin đăng sau khi thanh toán thành công.');
    }

    $db->commit();

    redirect_vnpay_feedback(
        $orderUrl,
        VnpayHelper::responseMessage($responseCode, $transactionStatus) . ' SpinBike đã tạo escrow giữ tiền cho giao dịch.',
        'success'
    );
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    redirect_vnpay_feedback(route_url('home'), $exception->getMessage());
}
