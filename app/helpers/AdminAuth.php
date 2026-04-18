<?php

require_once __DIR__ . '/../../config/config.php';

if (!function_exists('admin_dashboard_url')) {
    function admin_dashboard_url(): string
    {
        return app_url('app/views/admin/dashboard.php');
    }
}

if (!function_exists('admin_listings_url')) {
    function admin_listings_url(string $query = ''): string
    {
        $url = app_url('app/views/admin/listings.php');
        return $query !== '' ? $url . '?' . ltrim($query, '?') : $url;
    }
}

if (!function_exists('admin_orders_url')) {
    function admin_orders_url(string $query = ''): string
    {
        $url = app_url('app/views/admin/orders.php');
        return $query !== '' ? $url . '?' . ltrim($query, '?') : $url;
    }
}

if (!function_exists('is_admin_session')) {
    function is_admin_session(): bool
    {
        return isset($_SESSION['user_id']) && (string) ($_SESSION['role'] ?? 'user') === 'admin';
    }
}

if (!function_exists('require_admin_session')) {
    function require_admin_session(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!is_admin_session()) {
            header('Location: ' . asset_url('index.php'));
            exit;
        }
    }
}
?>
