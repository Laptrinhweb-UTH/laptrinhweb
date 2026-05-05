<?php
$adminSection = $adminSection ?? 'dashboard';
$adminSidebarName = trim((string) ($_SESSION['user_name'] ?? 'Quản trị viên'));
$adminSidebarInitial = strtoupper(substr($adminSidebarName, 0, 1));
?>

<aside class="admin-sidebar">
    <nav class="admin-side-nav">
        <a href="<?php echo admin_dashboard_url(); ?>" class="admin-side-link <?php echo $adminSection === 'dashboard' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo admin_listings_url(); ?>" class="admin-side-link <?php echo $adminSection === 'listings' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-shield-halved"></i>
            <span>Tin đăng</span>
        </a>
        <a href="<?php echo admin_orders_url(); ?>" class="admin-side-link <?php echo $adminSection === 'orders' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-receipt"></i>
            <span>Đơn hàng</span>
        </a>
    </nav>
</aside>
