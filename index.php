<?php
// Kết nối database
require_once 'config/config.php';

// Lấy dữ liệu sản phẩm
try {
    $query = "
        SELECT 
            p.id,
            p.name,
            p.brand,
            p.price,
            p.old_price,
            p.discount,
            p.is_featured,
            pi.image_url
        FROM products p
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        ORDER BY p.id ASC
        LIMIT 47
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Lỗi khi lấy dữ liệu: " . $e->getMessage();
    $products = [];
}
?>

<!doctype html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Spinbike Header - Green Theme</title>
    <!-- Google Fonts & FontAwesome -->
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <!-- Gọi file CSS -->
    <link rel="stylesheet" href="config/assets/style.css" />
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body>
    <header class="site-header">
      <div class="header-container">
        <!-- Hamburger Menu (Mobile) -->
        <button class="mobile-toggle" id="mobile-toggle">
          <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Logo -->
        <a href="#" class="header-logo">
          <i class="fa-solid fa-bicycle"></i>
          Spinbike
        </a>

        <!-- Center: Menu & Search -->
        <div class="header-center" id="header-center">
          <nav class="nav-menu">
            <div class="dropdown">
              <a href="#" class="dropdown-toggle" id="dropdown-toggle-mobile">
                Sản phẩm
                <i class="fa-solid fa-chevron-down" style="font-size: 12px"></i>
              </a>
              <ul class="dropdown-menu" id="dropdown-menu-mobile">
                <li><a href="#">Xe đạp thể thao đường phố</a></li>
                <li><a href="#">Xe đạp địa hình</a></li>
                <li><a href="#">Xe đạp đua</a></li>
                <li><a href="#">Xe đạp gấp</a></li>
                <li><a href="#">Xe đạp nữ</a></li>
                <li><a href="#">Xe đạp trẻ em</a></li>
                <li><a href="#">Khung sườn</a></li>
                <li><a href="#">Xe đạp fixed gear</a></li>
              </ul>
            </div>
          </nav>

          <div class="search-container">
            <input type="text" class="search-input" placeholder="Tìm kiếm..." />
            <button class="search-btn">
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </div>
        </div>

        <!-- Right: Icons -->
        <div class="header-right">
          <a href="#" class="icon-action">
            <i class="fa-regular fa-user"></i>
            <span class="user-text">Đăng nhập</span>
          </a>
          <a href="#" class="icon-action">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cart-badge">3</span>
          </a>
        </div>
      </div>
    </header>

    <!-- ============================================== -->
    <!-- HERO SLIDER BANNER -->
    <!-- ============================================== -->
    <section class="hero-slider">
      <div class="slider-wrapper" id="slider-wrapper">
        <!-- Slide 1 -->
        <div class="slide">
          <a href="link-trang-đích-1.html">
            <img src="config/assets/images/sale50.jpg" alt="Sale xả kho 50%" />
          </a>
        </div>

        <!-- Slide 2 -->
        <div class="slide">
          <a href="link-trang-đích-2.html">
            <img src="config/assets/images/anh2.jpg" alt="Xe đạp địa hình" />
          </a>
        </div>

        <!-- Slide 3 -->
        <div class="slide">
          <a href="link-trang-đích-3.html">
            <img src="config/assets/images/anh3.jpg" alt="Xe đạp đua" />
          </a>
        </div>

        <div class="slide">
          <a href="link-trang-đích-4.html">
            <img src="config/assets/images/xenu.jpg" alt="Xe đạp nữ" />
          </a>
        </div>

        <div class="slide">
          <a href="link-trang-đích-5.html">
            <img src="config/assets/images/xedapbetrai.jpg" alt="Xe đạp bé trai" />
          </a>
        </div>
      </div>

      <!-- Dấu chấm điều hướng (Dots) -->
      <div class="slider-dots" id="slider-dots">
        <span class="dot active" data-index="0"></span>
        <span class="dot" data-index="1"></span>
        <span class="dot" data-index="2"></span>
        <span class="dot" data-index="3"></span>
        <span class="dot" data-index="4"></span>
      </div>
    </section>
    <!-- ============================================== -->
    <!-- KẾT THÚC HERO SLIDER -->
    <!-- ============================================== -->
<!-- ============================================== -->
    <!-- BẮT ĐẦU: DANH SÁCH SẢN PHẨM -->
    <!-- ============================================== -->
    <section class="custom-slider-section">
        <!-- Header -->
        <div class="cs-header">
            <h2 class="cs-title">Xe đạp thể thao đường phố</h2>
            <a href="products.php" class="cs-view-all">Xem tất cả <i class="fa-solid fa-chevron-right" style="font-size: 12px; margin-left:4px;"></i></a>
        </div>

        <!-- Khung Layout -->
        <div class="cs-layout">
            
      
<!-- CỘT TRÁI: Placeholder -->
<div class="cs-left-placeholder">
    <img src="config/assets/images/xedapduongpho.png" alt="Xe đạp đường phố nổi bật" class="cs-featured-img">
    <h3>Xe Đạp Đường Phố Touring GIANT Fastroad Advanced 2</h3>
    <p>39.490.000 đ</p>
</div>
            <!-- CỘT PHẢI: Khung Trượt (Slider) -->
            <div class="cs-right-slider">
                
                <!-- Nút Trái -->
                <button class="cs-nav-btn cs-prev" id="btnPrev" disabled>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <!-- Wrapper bọc thẻ track -->
                <div class="cs-track-wrapper">
                    <div class="cs-track" id="sliderTrack">
                        
                        <!-- Sản phẩm được render từ database -->
                        <?php foreach ($products as $product): ?>
                            <div class="cs-card">
                                
                                <?php if (!empty($product['discount'])): ?>
                                    <div class="cs-badge"><?= htmlspecialchars($product['discount']) ?></div>
                                <?php endif; ?>
                                
                                <div class="cs-img-box">
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/300x300?text=No+Image" alt="No image">
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="cs-name"><?= htmlspecialchars($product['name']) ?></h3>
                                
                                <div class="cs-price-box">
                                    <p class="cs-price"><?= number_format($product['price'], 0, ',', '.') ?> đ</p>
                                    <div class="cs-old-price">
                                        <?php if (!empty($product['old_price'])): ?>
                                            <?= number_format($product['old_price'], 0, ',', '.') ?> đ
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                            </div>
                        <?php endforeach; ?>
                        
                    </div>
                </div>

                <!-- Nút Phải -->
                <button class="cs-nav-btn cs-next" id="btnNext">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

            </div>
        </div>
    </section>
    <!-- ============================================== -->
    <!-- KẾT THÚC DANH SÁCH SẢN PHẨM -->
    <!-- ============================================== -->
    <!-- Gọi file JS -->
    <script src="config/assets/script.js"></script>
  </body>
</html>