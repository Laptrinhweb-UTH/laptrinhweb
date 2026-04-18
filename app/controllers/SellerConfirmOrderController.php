<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../services/EscrowService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($orderId === false || $orderId === null) {
        echo json_encode(['status' => 'error', 'message' => 'Mã đơn hàng không hợp lệ.']);
        exit;
    }

    $database = new Database();
    $db = $database->getConnectionOrNull();
    if (!$db) {
        echo json_encode(['status' => 'error', 'message' => 'Không thể kết nối dữ liệu để xử lý yêu cầu.']);
        exit;
    }

    $escrowService = new EscrowService($db);
    echo json_encode($escrowService->sellerConfirmOrder($orderId, $_SESSION['user_id']));
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Yêu cầu không hợp lệ.']);
