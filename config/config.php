<?php
// Database local defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'spinbike_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Shared filesystem paths
define('PROJECT_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', PROJECT_ROOT . '/public');

// Cloudinary config
define('CLD_CLOUD_NAME', getenv('CLD_CLOUD_NAME') ?: 'dge3u1dzk');
define('CLD_UPLOAD_PRESET', getenv('CLD_UPLOAD_PRESET') ?: 'spinbike');

// ==========================================
// ĐỊNH NGHĨA CÁC HÀM (Bọc chống lỗi Redeclare)
// ==========================================

if (!function_exists('app_origin')) {
    function app_origin(): string {
        static $origin = null;

        if ($origin !== null) {
            return $origin;
        }

        $configuredOrigin = trim((string) (getenv('APP_URL') ?: ''));
        if ($configuredOrigin !== '') {
            $origin = rtrim($configuredOrigin, '/');
            return $origin;
        }

        if (PHP_SAPI !== 'cli') {
            $https = $_SERVER['HTTPS'] ?? '';
            $isHttps = !empty($https) && strtolower((string) $https) !== 'off';
            $scheme = $isHttps ? 'https' : 'http';
            $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));

            if ($host !== '') {
                $origin = $scheme . '://' . $host;
                return $origin;
            }
        }

        $origin = 'http://localhost';
        return $origin;
    }
}

if (!function_exists('detect_app_base_path')) {
    function detect_app_base_path(): string {
        $configuredBasePath = trim((string) (getenv('APP_BASE_PATH') ?: ''), '/');
        if ($configuredBasePath !== '') {
            return $configuredBasePath;
        }

        if (PHP_SAPI === 'cli') {
            return '';
        }

        $scriptName = trim((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptName === '') {
            return '';
        }

        $segments = explode('/', $scriptName);
        if (end($segments) === 'index.php') {
            array_pop($segments);
        }

        return trim(implode('/', $segments), '/');
    }
}

if (!function_exists('route_definitions')) {
    function route_definitions(): array {
        static $routes = null;

        if ($routes !== null) {
            return $routes;
        }

        $routes = [
            'home' => [
                'path' => '',
                'target' => PROJECT_ROOT . '/public/index.php',
                'aliases' => ['index.php'],
            ],
            'listing' => [
                'path' => 'listing',
                'target' => PROJECT_ROOT . '/public/detail.php',
                'aliases' => ['detail.php'],
            ],
            'sell.process' => [
                'path' => 'sell/process',
                'target' => PROJECT_ROOT . '/public/process_sell.php',
                'aliases' => ['process_sell.php'],
            ],
            'auth' => [
                'path' => 'auth',
                'target' => PROJECT_ROOT . '/app/views/auth/auth.php',
            ],
            'auth.login' => [
                'path' => 'auth/login',
                'target' => PROJECT_ROOT . '/app/views/auth/login.php',
                'aliases' => ['login.php'],
            ],
            'auth.register' => [
                'path' => 'auth/register',
                'target' => PROJECT_ROOT . '/app/views/auth/register.php',
                'aliases' => ['register.php'],
            ],
            'profile' => [
                'path' => 'profile',
                'target' => PROJECT_ROOT . '/app/views/auth/profile.php',
                'aliases' => ['profile.php'],
            ],
            'logout' => [
                'path' => 'logout',
                'target' => PROJECT_ROOT . '/app/views/auth/logout.php',
                'aliases' => ['logout.php'],
            ],
            'sell' => [
                'path' => 'sell',
                'target' => PROJECT_ROOT . '/app/views/products/sell.php',
            ],
            'my-listings' => [
                'path' => 'my-listings',
                'target' => PROJECT_ROOT . '/app/views/products/manage.php',
            ],
            'listing.action' => [
                'path' => 'listing/action',
                'target' => PROJECT_ROOT . '/app/controllers/ListingStatusController.php',
            ],
            'orders' => [
                'path' => 'orders',
                'target' => PROJECT_ROOT . '/app/views/orders/index.php',
            ],
            'order' => [
                'path' => 'order',
                'target' => PROJECT_ROOT . '/app/views/orders/detail.php',
            ],
            'order.confirm' => [
                'path' => 'order/confirm',
                'target' => PROJECT_ROOT . '/app/controllers/ConfirmOrderController.php',
            ],
            'order.seller-confirm' => [
                'path' => 'order/seller-confirm',
                'target' => PROJECT_ROOT . '/app/controllers/SellerConfirmOrderController.php',
            ],
            'order.ship' => [
                'path' => 'order/ship',
                'target' => PROJECT_ROOT . '/app/controllers/ShipOrderController.php',
            ],
            'order.dispute' => [
                'path' => 'order/dispute',
                'target' => PROJECT_ROOT . '/app/controllers/DisputeOrderController.php',
            ],
            'order.refund' => [
                'path' => 'order/refund',
                'target' => PROJECT_ROOT . '/app/controllers/RefundOrderController.php',
            ],
            'checkout' => [
                'path' => 'checkout',
                'target' => PROJECT_ROOT . '/app/views/orders/checkout.php',
            ],
            'checkout.process' => [
                'path' => 'checkout/process',
                'target' => PROJECT_ROOT . '/app/views/orders/process_checkout.php',
                'aliases' => ['process_checkout.php'],
            ],
            'admin.dashboard' => [
                'path' => 'admin',
                'target' => PROJECT_ROOT . '/app/views/admin/dashboard.php',
            ],
            'admin.listings' => [
                'path' => 'admin/listings',
                'target' => PROJECT_ROOT . '/app/views/admin/listings.php',
            ],
            'admin.orders' => [
                'path' => 'admin/orders',
                'target' => PROJECT_ROOT . '/app/views/admin/orders.php',
            ],
        ];

        return $routes;
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string {
        static $basePath = null;

        if ($basePath !== null) {
            return $basePath;
        }

        $detectedBasePath = detect_app_base_path();
        $basePath = $detectedBasePath !== '' ? '/' . $detectedBasePath : '';

        return $basePath;
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string {
        $cleanPath = ltrim($path, '/');
        $basePath = app_base_path();
        return $cleanPath !== '' ? $basePath . '/' . $cleanPath : $basePath;
    }
}

if (!function_exists('route_path')) {
    function route_path(string $name): string {
        $routes = route_definitions();
        if (!isset($routes[$name])) {
            throw new InvalidArgumentException('Undefined route: ' . $name);
        }

        return $routes[$name]['path'];
    }
}

if (!function_exists('route_target')) {
    function route_target(string $name): string {
        $routes = route_definitions();
        if (!isset($routes[$name])) {
            throw new InvalidArgumentException('Undefined route: ' . $name);
        }

        return $routes[$name]['target'];
    }
}

if (!function_exists('find_route_name_by_path')) {
    function find_route_name_by_path(string $path = ''): ?string {
        $normalizedPath = trim($path, '/');

        foreach (route_definitions() as $name => $route) {
            $candidatePaths = array_merge(
                [$route['path']],
                isset($route['aliases']) && is_array($route['aliases']) ? $route['aliases'] : []
            );

            foreach ($candidatePaths as $candidatePath) {
                if (trim((string) $candidatePath, '/') === $normalizedPath) {
                    return $name;
                }
            }
        }

        return null;
    }
}

if (!function_exists('find_route_name_by_target')) {
    function find_route_name_by_target(string $target): ?string {
        $normalizedTarget = str_replace('\\', '/', $target);

        foreach (route_definitions() as $name => $route) {
            if (str_replace('\\', '/', (string) $route['target']) === $normalizedTarget) {
                return $name;
            }
        }

        return null;
    }
}

if (!function_exists('route_url')) {
    function route_url(string $name, $query = []): string {
        $path = route_path($name);
        $url = app_origin() . app_path($path);

        if (is_string($query)) {
            $query = ltrim($query, '?');
            return $query !== '' ? $url . '?' . $query : $url;
        }

        if (is_array($query) && !empty($query)) {
            $queryString = http_build_query($query);
            return $queryString !== '' ? $url . '?' . $queryString : $url;
        }

        return $url;
    }
}

if (!function_exists('current_route_name')) {
    function current_route_name(): ?string {
        static $routeName = false;

        if ($routeName !== false) {
            return $routeName;
        }

        if (PHP_SAPI === 'cli') {
            $routeName = null;
            return $routeName;
        }

        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $basePath = app_base_path();
        $relativePath = $requestUri;

        if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
            $relativePath = substr($requestUri, strlen($basePath));
        }

        $routeName = find_route_name_by_path($relativePath);
        return $routeName;
    }
}

if (!function_exists('resolve_app_route_path')) {
    function resolve_app_route_path(string $path = ''): string {
        $parsedUrl = parse_url($path);
        $rawPath = $parsedUrl['path'] ?? '';
        $cleanPath = ltrim($rawPath, '/');
        $absolutePath = $cleanPath !== '' ? PROJECT_ROOT . '/' . $cleanPath : route_target('home');
        $routeName = find_route_name_by_target($absolutePath);
        $resolvedPath = $routeName !== null ? route_path($routeName) : $cleanPath;
        $query = isset($parsedUrl['query']) && $parsedUrl['query'] !== '' ? '?' . $parsedUrl['query'] : '';

        return $resolvedPath . $query;
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string {
        return app_origin() . app_path(resolve_app_route_path($path));
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
