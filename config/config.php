<?php
// Database local defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'spinbike_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// App URL defaults for local development
define('APP_URL', rtrim(getenv('APP_URL') ?: 'http://localhost', '/')); // LƯU Ý: Nếu XAMPP bạn có port thì sửa thành 'http://localhost:8080'
define('APP_BASE_PATH', trim(getenv('APP_BASE_PATH') ?: 'spinbike', '/'));

// Shared filesystem paths
define('PROJECT_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', PROJECT_ROOT . '/public');

// Cloudinary config
define('CLD_CLOUD_NAME', getenv('CLD_CLOUD_NAME') ?: 'dge3u1dzk');
define('CLD_UPLOAD_PRESET', getenv('CLD_UPLOAD_PRESET') ?: 'spinbike');

// ==========================================
// ĐỊNH NGHĨA CÁC HÀM (Bọc chống lỗi Redeclare)
// ==========================================

if (!function_exists('app_base_path')) {
    function app_base_path(): string {
        return APP_BASE_PATH !== '' ? '/' . APP_BASE_PATH : '';
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string {
        $cleanPath = ltrim($path, '/');
        $basePath = app_base_path();
        return $cleanPath !== '' ? $basePath . '/' . $cleanPath : $basePath;
    }
}

if (!function_exists('resolve_app_route_path')) {
    function resolve_app_route_path(string $path = ''): string {
        $parsedUrl = parse_url($path);
        $rawPath = $parsedUrl['path'] ?? '';
        $cleanPath = ltrim($rawPath, '/');

        $routeMap = [
            '' => '',
            'public/index.php' => '',
            'public/detail.php' => 'listing',
            'public/process_sell.php' => 'sell/process',
            'app/views/auth/auth.php' => 'auth',
            'app/views/auth/profile.php' => 'profile',
            'app/views/auth/logout.php' => 'logout',
            'app/views/products/sell.php' => 'sell',
            'app/views/products/manage.php' => 'my-listings',
            'app/views/orders/index.php' => 'orders',
            'app/views/orders/detail.php' => 'order',
            'app/views/orders/checkout.php' => 'checkout',
            'app/views/orders/process_checkout.php' => 'checkout/process',
            'app/views/admin/dashboard.php' => 'admin',
            'app/views/admin/listings.php' => 'admin/listings',
            'app/views/admin/orders.php' => 'admin/orders',
            'app/controllers/ListingStatusController.php' => 'listing/action',
            'app/controllers/ConfirmOrderController.php' => 'order/confirm',
            'app/controllers/SellerConfirmOrderController.php' => 'order/seller-confirm',
            'app/controllers/ShipOrderController.php' => 'order/ship',
            'app/controllers/DisputeOrderController.php' => 'order/dispute',
            'app/controllers/RefundOrderController.php' => 'order/refund',
        ];

        $resolvedPath = $routeMap[$cleanPath] ?? $cleanPath;
        $query = isset($parsedUrl['query']) && $parsedUrl['query'] !== '' ? '?' . $parsedUrl['query'] : '';

        return $resolvedPath . $query;
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string {
        return APP_URL . app_path(resolve_app_route_path($path));
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path = ''): string {
        return app_url('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('public_file_path')) {
    function public_file_path(string $path = ''): string {
        return PUBLIC_ROOT . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

// ==========================================
// CÁC CONSTANT PHỤ THUỘC VÀO HÀM (Phải để dưới cùng)
// ==========================================
define('BASE_URL', app_url('public'));
?>
