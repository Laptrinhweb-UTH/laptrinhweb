<?php
$adminSection = $adminSection ?? 'dashboard';
$adminSidebarName = trim((string) ($_SESSION['user_name'] ?? 'Quản trị viên'));
$adminSidebarInitial = strtoupper(substr($adminSidebarName, 0, 1));
$adminSidebarWebsiteUrl = route_url('home');
?>

<aside class="admin-sidebar">
    <div class="admin-side-card">
        <div class="admin-side-badge">Backoffice</div>
        <div class="admin-side-avatar"><?php echo htmlspecialchars($adminSidebarInitial); ?></div>
        <div class="admin-side-name"><?php echo htmlspecialchars($adminSidebarName); ?></div>
        <p class="admin-side-text">Quản lý hoạt động nội bộ của SpinBike từ một khu vực tập trung.</p>
    </div>

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
        <a href="<?php echo route_url('profile'); ?>" class="admin-side-link">
            <i class="fa-solid fa-user-shield"></i>
            <span>Hồ sơ admin</span>
        </a>
        <a href="<?php echo $adminSidebarWebsiteUrl; ?>" class="admin-side-link">
            <i class="fa-solid fa-globe"></i>
            <span>Xem website</span>
        </a>
    </nav>
</aside>
