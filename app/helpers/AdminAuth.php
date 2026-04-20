<?php

require_once __DIR__ . '/../../config/config.php';

if (!function_exists('admin_dashboard_url')) {
    function admin_dashboard_url(): string
    {
        return route_url('admin.dashboard');
    }
}

if (!function_exists('admin_listings_url')) {
    function admin_listings_url(string $query = ''): string
    {
        $url = route_url('admin.listings');
        return $query !== '' ? $url . '?' . ltrim($query, '?') : $url;
    }
}

if (!function_exists('admin_orders_url')) {
    function admin_orders_url(string $query = ''): string
    {
        $url = route_url('admin.orders');
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
            header('Location: ' . route_url('home'));
            exit;
        }
    }
}
?>
