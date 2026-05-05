<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/AdminAuth.php';

$authUrl = route_url('auth');
$profileUrl = route_url('profile');
$settingsUrl = route_url('user.settings.profile');
$logoutUrl = route_url('logout');
$sellUrl = route_url('sell');
$myListingsUrl = route_url('my-listings');
$buyingOrdersUrl = route_url('orders.buying');
$reviewListingsUrl = admin_listings_url();
$adminOrdersUrl = admin_orders_url();
$adminDashboardUrl = admin_dashboard_url();
$isLoggedIn = isset($_SESSION['user_id'], $_SESSION['user_name']);
$isAdmin = (string) ($_SESSION['role'] ?? 'user') === 'admin';
$websiteUrl = route_url('home');
$currentRoute = current_route_name();
$isAdminArea = $isAdmin && is_string($currentRoute) && str_starts_with($currentRoute, 'admin.');
$isAdminDashboardPage = $currentRoute === 'admin.dashboard';
$isAdminListingsPage = $currentRoute === 'admin.listings';
$isAdminOrdersPage = $currentRoute === 'admin.orders';
$shouldShowLoginOverlay = !$isLoggedIn && !in_array($currentRoute, ['auth', 'auth.login', 'auth.register', 'auth.forgot_password', 'auth.reset_password'], true);
$homeUrl = $isAdminArea ? $adminDashboardUrl : route_url('home');
$displayUserName = htmlspecialchars($_SESSION['user_name'] ?? '');

// ==========================================
// LẤY AVATAR TỪ DATABASE CHO HEADER
// ==========================================
$header_avatar_url = '';
if ($isLoggedIn) {
    require_once __DIR__ . '/../../helpers/Database.php';
    $headerDatabase = new Database();
    $header_db = $headerDatabase->getConnectionOrNull();

    try {
        if ($header_db) {
        $stmt = $header_db->prepare("SELECT avatar, name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $header_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($header_user['avatar'])) {
            $header_avatar_url = $header_user['avatar'];
        } else {
            // Nếu chưa có avatar thì tạo ảnh mặc định chứa chữ cái tên
            $display_name = urlencode($header_user['name'] ?? $_SESSION['user_name'] ?? 'U');
            $header_avatar_url = "https://ui-avatars.com/api/?name={$display_name}&background=10b981&color=fff&rounded=true&bold=true";
        }
        }
    } catch (Exception $e) {
        $display_name = urlencode($_SESSION['user_name'] ?? 'U');
        $header_avatar_url = "https://ui-avatars.com/api/?name={$display_name}&background=10b981&color=fff&rounded=true&bold=true";
    }

    if ($header_avatar_url === '') {
        $display_name = urlencode($_SESSION['user_name'] ?? 'U');
        $header_avatar_url = "https://ui-avatars.com/api/?name={$display_name}&background=10b981&color=fff&rounded=true&bold=true";
    }
}
?>
<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SpinBike - Mua bán xe đạp thể thao cũ</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />

<link rel="stylesheet" href="<?php echo asset_url('assets/css/bootstrap.min.css'); ?>">

<link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
  </head>
  <body>
    <header class="header">
      <div class="container header-content">
        <a href="<?php echo $homeUrl; ?>" class="logo logo-link">
          <i class="fa-solid fa-bicycle"></i>
          <span class="brand-name">SpinBike</span>
          <span class="brand-domain">.vn</span>
        </a>

        <?php if ($isAdminArea): ?>
        <div class="admin-header-nav">
          <a href="<?php echo $adminDashboardUrl; ?>" class="admin-header-link <?php echo $isAdminDashboardPage ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
          </a>
          <a href="<?php echo $reviewListingsUrl; ?>" class="admin-header-link <?php echo $isAdminListingsPage ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-shield-halved"></i> Tin đăng
          </a>
          <a href="<?php echo $adminOrdersUrl; ?>" class="admin-header-link <?php echo $isAdminOrdersPage ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-receipt"></i> Đơn hàng
          </a>
        </div>
        <?php else: ?>
        <div class="search-box">
          <input
            id="searchInput"
            type="text"
            placeholder="Tìm xe đạp..."
          />
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <?php endif; ?>

        <div class="header-actions">
          <?php if ($isAdminArea): ?>
          <a href="<?php echo $websiteUrl; ?>" class="btn-sell header-sell-link admin-back-to-site">
            <i class="fa-solid fa-globe"></i> Xem website
          </a>
          <?php else: ?>
          <a href="<?php echo $isLoggedIn ? $sellUrl : $authUrl; ?>" class="btn-sell header-sell-link">
            <i class="fa-solid fa-plus"></i> Đăng bán ngay
          </a>
          <?php endif; ?>

          <div class="auth-buttons">
            <?php if ($isLoggedIn): ?>
              <div class="header-user-info">
                <span class="header-user-name">
                  <?php echo $displayUserName; ?>
                </span>

                <div class="user-dropdown-wrapper">
                  <a
                    href="javascript:void(0)"
                    class="btn-user-icon btn-user-avatar"
                    title="Tài khoản"
                  >
                    <img
                      src="<?php echo $header_avatar_url; ?>"
                      alt="Avatar"
                      class="header-avatar-image"
                    >
                  </a>
                  
                  <div class="user-dropdown-menu">
                    <a href="<?php echo $profileUrl; ?>" class="dropdown-item dropdown-item-first">
                      <i class="fa-regular fa-user"></i> Trang cá nhân
                    </a>
                    <a href="<?php echo $myListingsUrl; ?>" class="dropdown-item">
                      <i class="fa-solid fa-list-check"></i> Tin đăng của tôi
                    </a>
                    <a href="<?php echo $buyingOrdersUrl; ?>" class="dropdown-item">
                      <i class="fa-solid fa-box"></i> Đơn hàng
                    </a>
                    <a href="<?php echo $settingsUrl; ?>" class="dropdown-item">
                      <i class="fa-solid fa-gear"></i> Cài đặt tài khoản
                    </a>
                    <?php if ($isAdmin): ?>
                    <hr class="dropdown-divider">
                    <a href="<?php echo $adminDashboardUrl; ?>" class="dropdown-item">
                      <i class="fa-solid fa-gauge-high"></i> Dashboard Admin
                    </a>
                    <a href="<?php echo $reviewListingsUrl; ?>" class="dropdown-item">
                      <i class="fa-solid fa-shield-halved"></i> Duyệt tin đăng
                    </a>
                    <a href="<?php echo $adminOrdersUrl; ?>" class="dropdown-item">
                      <i class="fa-solid fa-receipt"></i> Đơn hàng & tranh chấp
                    </a>
                    <?php endif; ?>

                    <hr class="dropdown-divider">

                    <a href="javascript:void(0)" class="dropdown-item text-danger" onclick="showLogoutModal(event)">
                      <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </a>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <a href="<?php echo $authUrl; ?>" class="btn-user-icon" title="Đăng nhập / Đăng ký">
                <i class="fa-solid fa-circle-user"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </header>

    <div id="customLogoutModal" class="modal hidden">
      <div class="modal-backdrop" onclick="hideLogoutModal()"></div>
      <div class="modal-content logout-modal-card">
        <div class="logout-modal-icon">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        
        <h3 class="logout-modal-title">Xác nhận đăng xuất</h3>
        <p class="logout-modal-text">Bạn có chắc chắn muốn đăng xuất khỏi SpinBike không?</p>
        
        <div class="logout-modal-actions">
            <button onclick="hideLogoutModal()" class="logout-modal-cancel">
                Hủy
            </button>
            <a href="<?php echo $logoutUrl; ?>" class="logout-modal-confirm">
                Đăng xuất
            </a>
        </div>
      </div>
    </div>

    <?php if ($shouldShowLoginOverlay): ?>
    <div id="guestLoginOverlay" class="guest-login-overlay" role="region" aria-label="Đăng nhập để nhận deal mới">
      <div class="guest-login-overlay-content">
        <div class="guest-login-overlay-icon">
          <i class="fa-solid fa-user"></i>
        </div>
        <div class="guest-login-overlay-copy">
          <strong>Đăng nhập ngay để không bỏ lỡ deal mới gần bạn.</strong>
        </div>
      </div>
      <div class="guest-login-overlay-actions">
        <a href="<?php echo $authUrl; ?>" class="guest-login-overlay-login">Đăng nhập</a>
      </div>
      <button type="button" class="guest-login-overlay-close" onclick="closeGuestLoginOverlay()" aria-label="Đóng thông báo">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <?php endif; ?>

<script>
    const guestLoginOverlay = document.getElementById('guestLoginOverlay');
    if (guestLoginOverlay && sessionStorage.getItem('spinbike_guest_overlay_closed') === '1') {
        guestLoginOverlay.classList.add('hidden');
    }

    function closeGuestLoginOverlay() {
        const overlay = document.getElementById('guestLoginOverlay');
        if (overlay) {
            overlay.classList.add('hidden');
            sessionStorage.setItem('spinbike_guest_overlay_closed', '1');
        }
    }

    // Hàm bật bảng Đăng xuất
    function showLogoutModal(e) {
        e.preventDefault(); // Chặn việc nhảy trang
        document.getElementById('customLogoutModal').classList.remove('hidden');
    }

    // Hàm tắt bảng Đăng xuất
    function hideLogoutModal() {
        document.getElementById('customLogoutModal').classList.add('hidden');
    }
</script>
