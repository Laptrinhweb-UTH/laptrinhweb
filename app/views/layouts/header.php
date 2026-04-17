<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

require_once __DIR__ . '/../../../config/config.php';

$homeUrl = asset_url('index.php');
$authUrl = app_url('app/views/auth/auth.php');
$profileUrl = app_url('app/views/auth/profile.php');
$logoutUrl = app_url('app/views/auth/logout.php');
$sellUrl = app_url('app/views/products/sell.php');
$isLoggedIn = isset($_SESSION['user_id'], $_SESSION['user_name']);
$displayUserName = htmlspecialchars($_SESSION['user_name'] ?? '');

// ==========================================
// LẤY AVATAR TỪ DATABASE CHO HEADER
// ==========================================
$header_avatar_url = '';
if ($isLoggedIn) {
    require_once __DIR__ . '/../../helpers/Database.php';
    try {
        // Dùng biến $header_db để không đụng chạm với biến $db ở các file khác
        $header_db = (new Database())->getConnection();
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
    } catch (Exception $e) {
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
        <a href="<?php echo $homeUrl; ?>" class="logo" style="text-decoration: none;">
          <i class="fa-solid fa-bicycle"></i>
          <span class="brand-name">SpinBike</span>
          <span class="brand-domain">.vn</span>
        </a>

        <div class="search-box">
          <input
            id="searchInput"
            type="text"
            placeholder="Tìm xe đạp..."
          />
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>

        <div class="header-actions">
          <a href="<?php echo $isLoggedIn ? $sellUrl : $authUrl; ?>" class="btn-sell" style="text-decoration: none;">
            <i class="fa-solid fa-plus"></i> Đăng bán ngay
          </a>

          <div class="auth-buttons">
            <?php if ($isLoggedIn): ?>
              <div class="header-user-info" style="display: flex; align-items: center; gap: 12px;">
                <span class="header-user-name" style="font-weight: 600; color: var(--primary); font-size: 16px;">
                  <?php echo $displayUserName; ?>
                </span>

                <div class="user-dropdown-wrapper">
                  <a
                    href="javascript:void(0)"
                    class="btn-user-icon btn-user-avatar"
                    title="Tài khoản"
                    style="display: flex; align-items: center; justify-content: center; padding: 0; border: none; background: transparent;"
                  >
                    <img
                      src="<?php echo $header_avatar_url; ?>"
                      alt="Avatar"
                      style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                    >
                  </a>
                  
                  <div class="user-dropdown-menu">
                    <a href="<?php echo $profileUrl; ?>" class="dropdown-item" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                      <i class="fa-solid fa-user-pen"></i> Cập nhật thông tin
                    </a>
                    
                    <hr class="dropdown-divider">
                    
                    <a href="#" class="dropdown-item text-danger" onclick="showLogoutModal(event)">
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
      <div class="modal-content logout-modal-card" style="max-width: 380px; padding: 32px; text-align: center; border-radius: 24px; position: relative; z-index: 9999;">
        <div style="width: 64px; height: 64px; background: #fee2e2; color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        
        <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);">Xác nhận đăng xuất</h3>
        <p style="color: var(--text-secondary); margin-bottom: 24px; font-size: 15px; line-height: 1.5;">Bạn có chắc chắn muốn đăng xuất khỏi SpinBike không?</p>
        
        <div style="display: flex; gap: 12px;">
            <button onclick="hideLogoutModal()" style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--white); color: var(--text-primary); font-weight: 600; font-size: 15px; cursor: pointer; transition: 0.2s;">
                Hủy
            </button>
            <a href="<?php echo $logoutUrl; ?>" style="flex: 1; padding: 12px; border-radius: 12px; border: none; background: var(--danger); color: white; font-weight: 600; font-size: 15px; text-decoration: none; display: flex; justify-content: center; align-items: center; transition: 0.2s; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);">
                Đăng xuất
            </a>
        </div>
      </div>
    </div>

<script>
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
