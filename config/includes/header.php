<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
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
   <link rel="stylesheet" href="/spinbike/assets/style.css" />
  </head>
  <body>
    <header class="header">
      <div class="container header-content">

<a href="/spinbike/index.php" class="logo" style="text-decoration: none;">
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

<a href="/spinbike/pages/sell.php" class="btn-sell" style="text-decoration: none;">
    <i class="fa-solid fa-plus"></i>
    Đăng bán ngay
</a>
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
            <a href="update_profile.php" class="dropdown-item" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
              <i class="fa-solid fa-user-pen"></i> Cập nhật thông tin
            </a>
            
            <hr class="dropdown-divider">
            
            <a href="auth/logout.php" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất khỏi SpinBike không?');" class="dropdown-item text-danger">
              <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
            </a>
          </div>
        </div>
        
      </div>

    <?php else: ?>
      <a href="auth/auth.html" class="btn-user-icon" title="Đăng nhập / Đăng ký">
        <i class="fa-solid fa-circle-user"></i>
      </a>
    <?php endif; ?>
  </div>

</div>
    </header>