<?php
require_once __DIR__ . '/config/config.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = app_base_path();
$relativePath = $requestUri;

if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
    $relativePath = substr($requestUri, strlen($basePath));
}

$route = trim((string) $relativePath, '/');

$routeMap = [
    '' => __DIR__ . '/public/index.php',
    'detail.php' => __DIR__ . '/public/detail.php',
    'process_sell.php' => __DIR__ . '/public/process_sell.php',
    'auth' => __DIR__ . '/app/views/auth/auth.php',
    'profile' => __DIR__ . '/app/views/auth/profile.php',
    'logout' => __DIR__ . '/app/views/auth/logout.php',
    'sell' => __DIR__ . '/app/views/products/sell.php',
    'sell/process' => __DIR__ . '/public/process_sell.php',
    'listing' => __DIR__ . '/public/detail.php',
    'listing/action' => __DIR__ . '/app/controllers/ListingStatusController.php',
    'my-listings' => __DIR__ . '/app/views/products/manage.php',
    'orders' => __DIR__ . '/app/views/orders/index.php',
    'order' => __DIR__ . '/app/views/orders/detail.php',
    'order/confirm' => __DIR__ . '/app/controllers/ConfirmOrderController.php',
    'order/seller-confirm' => __DIR__ . '/app/controllers/SellerConfirmOrderController.php',
    'order/ship' => __DIR__ . '/app/controllers/ShipOrderController.php',
    'order/dispute' => __DIR__ . '/app/controllers/DisputeOrderController.php',
    'order/refund' => __DIR__ . '/app/controllers/RefundOrderController.php',
    'checkout' => __DIR__ . '/app/views/orders/checkout.php',
    'checkout/process' => __DIR__ . '/app/views/orders/process_checkout.php',
    'admin' => __DIR__ . '/app/views/admin/dashboard.php',
    'admin/listings' => __DIR__ . '/app/views/admin/listings.php',
    'admin/orders' => __DIR__ . '/app/views/admin/orders.php',
];

$targetFile = $routeMap[$route] ?? null;

if ($targetFile === null || !is_file($targetFile)) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="vi"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>404 - Không tìm thấy trang</title></head><body style="font-family: Arial, sans-serif; background:#f8fafc; color:#0f172a; display:grid; min-height:100vh; place-items:center; margin:0;"><div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:32px; max-width:520px; box-shadow:0 20px 45px -30px rgba(15,23,42,.35);"><h1 style="margin:0 0 12px; font-size:28px;">Không tìm thấy trang</h1><p style="margin:0 0 20px; line-height:1.6;">Đường dẫn bạn vừa truy cập không tồn tại hoặc đã được thay đổi.</p><a href="' . htmlspecialchars(app_url(''), ENT_QUOTES, 'UTF-8') . '" style="display:inline-block; background:#10b981; color:#fff; text-decoration:none; padding:12px 18px; border-radius:999px; font-weight:700;">Quay về trang chủ</a></div></body></html>';
    exit;
}

require $targetFile;
