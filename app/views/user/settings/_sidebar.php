<?php
// Expects $currentSettingsRoute to be set by the including page
$currentSettingsRoute = $currentSettingsRoute ?? '';
$settingsNav = [
    [
        'route'  => 'user.settings.profile',
        'icon'   => 'fa-regular fa-user',
        'label'  => 'Thông tin cá nhân',
    ],
    [
        'route'  => 'user.settings.account',
        'icon'   => 'fa-solid fa-lock',
        'label'  => 'Cài đặt tài khoản',
    ],
];
?>
<div class="settings-sidebar">
    <div class="settings-sidebar-profile">
        <img src="<?php echo $displayAvatar ?? ''; ?>" alt="Avatar" class="settings-sidebar-avatar">
        <div>
            <p class="settings-sidebar-name"><?php echo htmlspecialchars($displayName ?? ''); ?></p>
            <a href="<?php echo route_url('profile'); ?>" class="settings-sidebar-view-link">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Xem trang cá nhân
            </a>
        </div>
    </div>
    <nav class="settings-sidebar-nav">
        <?php foreach ($settingsNav as $item): ?>
        <a href="<?php echo route_url($item['route']); ?>"
           class="settings-nav-link <?php echo $currentSettingsRoute === $item['route'] ? 'active' : ''; ?>">
            <i class="<?php echo $item['icon']; ?>"></i>
            <?php echo $item['label']; ?>
        </a>
        <?php endforeach; ?>
    </nav>
</div>
