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
    $db = (new Database())->getConnection();
    $escrowService = new EscrowService($db);
    
    // Gọi hàm ReleaseFunds, truyền ID đơn hàng và ID người đang đăng nhập (Người mua)
    $result = $escrowService->releaseFunds($_POST['order_id'], $_SESSION['user_id']);
    
    echo json_encode($result);
}
?>