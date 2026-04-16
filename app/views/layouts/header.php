<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

require_once __DIR__ . '/../../../config/config.php';
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

<a href="<?php echo asset_url('index.php'); ?>" class="logo" style="text-decoration: none;">
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

<?php if(isset($_SESSION['user_id'])): ?>
    <a href="<?php echo app_url('app/views/products/sell.php'); ?>" class="btn-sell" style="text-decoration: none;">
        <i class="fa-solid fa-plus"></i> Đăng bán ngay
    </a>
<?php else: ?>
    <a href="<?php echo app_url('app/views/auth/auth.php'); ?>" class="btn-sell" style="text-decoration: none;">
        <i class="fa-solid fa-plus"></i> Đăng bán ngay
    </a>
<?php endif; ?>
  <div class="auth-buttons">
    <?php if(isset($_SESSION['user_name'])): ?>
      
      <div style="display: flex; align-items: center; gap: 12px;">
        
        <span style="font-weight: 600; color: var(--primary); font-size: 16px;">
           <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </span>

        <div class="user-dropdown-wrapper">
          <a href="javascript:void(0)" class="btn-user-icon" title="Tài khoản">
            <i class="fa-solid fa-circle-user"></i>
          </a>
          
          <div class="user-dropdown-menu">
         <a href="<?php echo app_url('app/views/auth/profile.php'); ?>" class="dropdown-item" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
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
      <a href="<?php echo app_url('app/views/auth/auth.php'); ?>" class="btn-user-icon" title="Đăng nhập / Đăng ký">
        <i class="fa-solid fa-circle-user"></i>
      </a>
    <?php endif; ?>
  </div>

</div>
    </header>
    <div id="customLogoutModal" class="modal hidden">
    <div class="modal-backdrop" onclick="hideLogoutModal()"></div>
    <div class="modal-content" style="max-width: 380px; padding: 32px; text-align: center; border-radius: 24px; position: relative; z-index: 9999;">
        
        <div style="width: 64px; height: 64px; background: #fee2e2; color: var(--danger); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px;">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        
        <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);">Xác nhận đăng xuất</h3>
        <p style="color: var(--text-secondary); margin-bottom: 24px; font-size: 15px; line-height: 1.5;">Bạn có chắc chắn muốn đăng xuất khỏi SpinBike không?</p>
        
        <div style="display: flex; gap: 12px;">
            <button onclick="hideLogoutModal()" style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--white); color: var(--text-primary); font-weight: 600; font-size: 15px; cursor: pointer; transition: 0.2s;">
                Hủy
            </button>
            <a href="<?php echo app_url('app/views/auth/logout.php'); ?>" style="flex: 1; padding: 12px; border-radius: 12px; border: none; background: var(--danger); color: white; font-weight: 600; font-size: 15px; text-decoration: none; display: flex; justify-content: center; align-items: center; transition: 0.2s; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);">
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
