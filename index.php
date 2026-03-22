<?php
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
     
  </head>
  <body>
    <header class="site-header">
      <div class="header-container">
        <!-- Hamburger Menu (Mobile) -->
        <button class="mobile-toggle" id="mobile-toggle">
          <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Logo -->
        <a href="index.php" class="header-logo">
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
    <!-- HERO SLIDER BANNER -->
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

<section class="product-section">
  <div class="product-header">
    <h2>Xe đạp thể thao đường phố</h2>
    <a href="#" class="view-all">Xem tất cả</a>
  </div>

  <!-- BẮT ĐẦU LAYOUT CHIA 2 CỘT (Đã thêm thẻ này) -->
  <div class="product-layout">
    
    <!-- Ô TO CỐ ĐỊNH BÊN TRÁI -->
    <div class="featured-banner">
      <img src="config/assets/images/xedapduongpho.png" alt="GIANT Fastroad">
      
      <div class="featured-info">
        <p class="featured-name">Xe Đạp Đường Phố Touring GIANT Fastroad Advanced 2 – Phanh Đĩa, Bánh 700C – 2024</p>
        <span class="featured-price">39.490.000 VND</span>
      </div>
    </div>

    <!-- KHU VỰC LƯỚT SẢN PHẨM BÊN PHẢI -->
    <div class="product-slider">
      <button class="nav-btn prev">&#10094;</button>

      <div class="product-wrapper" id="productWrapper">
        <?php
          // 1. Cấu hình kết nối
          $servername = "localhost";
          $username = "root";
          $password = "";
          $dbname = "spinbike_db";

          $conn = new mysqli($servername, $username, $password, $dbname);

          if ($conn->connect_error) {
            die("Kết nối thất bại: " . $conn->connect_error);
          }

          // 2. Câu lệnh SQL JOIN để lấy dữ liệu
          $sql = "SELECT p.id, p.name, p.price, pi.image_url 
                  FROM products p
                  JOIN product_category pc ON p.id = pc.product_id
                  JOIN product_images pi ON p.id = pi.product_id
                  WHERE pc.category_id = 1 AND pi.is_primary = 1"; 
                  
          $result = $conn->query($sql);

          // 3. Hiển thị dữ liệu
          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              $formatted_price = number_format($row["price"], 0, ',', '.') . 'đ';
              
              // Sử dụng trực tiếp dữ liệu từ DB.
              $image_path = $row["image_url"];

              echo '<div class="product-card">';
              echo '  <img src="' . $image_path . '" alt="' . htmlspecialchars($row["name"]) . '">';
              echo '  <div class="product-info">';
              echo '    <p>' . htmlspecialchars($row["name"]) . '</p>';
              echo '    <span>' . $formatted_price . '</span>';
              echo '  </div>';
              echo '</div>';
            }
          } else {
            echo "<p>Chưa có sản phẩm nào.</p>";
          }
          $conn->close();
        ?>
      </div> 

      <button class="nav-btn next">&#10095;</button>
    </div> 
    
  <!-- KẾT THÚC LAYOUT CHIA 2 CỘT (Thẻ đóng này bảo vệ cấu trúc) -->
  </div> 

</section>
    <!-- Gọi file JS -->
    <script src="config/assets/script.js"></script>
  </body>
</html>
<?php
?>