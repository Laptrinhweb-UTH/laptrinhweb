<?php 
session_start(); 
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

   <link rel="stylesheet" href="config/assets/style.css" />
  </head>
  <body>
    <!-- HEADER -->
    <header class="header">
      <div class="container header-content">

  <div class="logo">
    <i class="fa-solid fa-bicycle"></i>
    <span class="brand-name">SpinBike</span>
    <span class="brand-domain">.vn</span>
  </div>

  <div class="search-box">
    <input
      id="searchInput"
      type="text"
      placeholder="Tìm xe đạp..."
    />
    <i class="fa-solid fa-magnifying-glass"></i>
  </div>



  <!-- NÚT ĐĂNG BÁN -->
  <button class="btn-sell" onclick="showSellModal()">
    <i class="fa-solid fa-plus"></i>
    Đăng bán ngay
  </button>
  <!-- NÚT ĐĂNG NHẬP / ĐĂNG KÝ -->
<div class="auth-buttons">
    <?php if(isset($_SESSION['user_name'])): ?>
      <div style="display: flex; align-items: center; gap: 12px;">
        <span style="font-weight: 600; color: var(--primary); font-size: 16px;">
           <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </span>
     <a href="auth/logout.php" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất khỏi Spinbike không?');" class="btn-user-icon" title="Đăng xuất" style="color: var(--danger); border-color: var(--danger);">
  <i class="fa-solid fa-right-from-bracket"></i>
</a>
        
      </div>
    <?php else: ?>
      <a href="auth/auth.html" class="btn-user-icon" title="Đăng nhập / Đăng ký">
        <i class="fa-solid fa-circle-user"></i>
      </a>
    <?php endif; ?>
  </div>

</div>
    </header>

    <div class="main-content">
      <!-- FILTER SIDEBAR -->
      <aside class="sidebar">
        <h3 class="sidebar-title">
          <i class="fa-solid fa-sliders"></i> Bộ lọc nâng cao
        </h3>

        <!-- Loại xe -->
        <div class="filter-group">
          <label class="filter-label">Loại xe</label>
          <div class="filter-buttons" id="typeFilters">
            <button
              onclick="toggleFilter(this)"
              data-type="Road"
              class="filter-btn"
            >
              Road Bike
            </button>
            <button
              onclick="toggleFilter(this)"
              data-type="MTB"
              class="filter-btn"
            >
              MTB
            </button>
            <button
              onclick="toggleFilter(this)"
              data-type="Gravel"
              class="filter-btn"
            >
              Gravel
            </button>
            <button
              onclick="toggleFilter(this)"
              data-type="Fixed"
              class="filter-btn"
            >
              Fixed Gear
            </button>
          </div>
        </div>

        <!-- Size -->
        <div class="filter-group">
          <label class="filter-label">Size khung</label>
          <select
            id="sizeFilter"
            onchange="applyFilters()"
            class="filter-select"
          >
            <option value="">Tất cả size</option>
            <option value="S">S</option>
            <option value="M">M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>
          </select>
        </div>

        <!-- Giá -->
        <div class="filter-group">
          <label class="filter-label">Giá (triệu VND)</label>
          <div class="price-inputs">
            <input
              id="priceMin"
              type="number"
              value="5"
              onchange="applyFilters()"
              class="price-input"
            />
            <input
              id="priceMax"
              type="number"
              value="100"
              onchange="applyFilters()"
              class="price-input"
            />
          </div>
        </div>

        <!-- Groupset -->
        <div class="filter-group">
          <label class="filter-label">Groupset</label>
          <select
            id="groupsetFilter"
            onchange="applyFilters()"
            class="filter-select"
          >
            <option value="">Tất cả</option>
            <option value="105">Shimano 105</option>
            <option value="Ultegra">Ultegra</option>
            <option value="Dura-Ace">Dura-Ace</option>
            <option value="Rival">SRAM Rival</option>
          </select>
        </div>

        <!-- Tình trạng -->
        <div class="filter-group">
          <label class="filter-label">Tình trạng</label>
          <div class="filter-buttons" id="conditionFilters">
            <button
              onclick="toggleCondition(this)"
              data-cond="8/10"
              class="condition-btn"
            >
              8/10
            </button>
            <button
              onclick="toggleCondition(this)"
              data-cond="9/10"
              class="condition-btn"
            >
              9/10
            </button>
            <button
              onclick="toggleCondition(this)"
              data-cond="10/10"
              class="condition-btn"
            >
              10/10
            </button>
          </div>
        </div>

        <!-- Sắp xếp -->
        <div class="filter-group">
          <label class="filter-label">Sắp xếp theo</label>
          <select
            id="sortFilter"
            onchange="applyFilters()"
            class="filter-select"
          >
            <option value="price-low">Giá thấp → cao</option>
            <option value="price-high">Giá cao → thấp</option>
          </select>
        </div>
      </aside>

      <!-- PRODUCT GRID -->
      <div class="products-section">
        <div class="products-header">
          <h1>Danh sách xe đạp</h1>
          <p id="resultCount">8 sản phẩm</p>
        </div>

        <div id="productGrid" class="product-grid">
          <!-- JS render ở đây -->
        </div>
      </div>
    </div>

    <!-- MODAL CHI TIẾT -->
    <div id="detailModal" class="modal hidden">
      <div class="modal-backdrop" onclick="hideDetailModal()"></div>
      <div class="modal-content detail-modal">
        <div class="modal-body">
          <div class="modal-images">
            <div id="modalMainImage" class="modal-main-image"></div>
            <div class="modal-thumbnails">
              <div
                onclick="changeModalImage(0)"
                id="thumb0"
                class="modal-thumb"
              ></div>
              <div
                onclick="changeModalImage(1)"
                id="thumb1"
                class="modal-thumb"
              ></div>
            </div>
          </div>

          <div class="modal-info">
            <button onclick="hideDetailModal()" class="modal-close">×</button>

            <div id="modalTitle" class="modal-title"></div>
            <div id="modalSubtitle" class="modal-subtitle"></div>

            <div class="modal-price-section">
              <div id="modalPrice" class="modal-price"></div>
              <div id="modalVerified" class="modal-verified"></div>
            </div>

            <table class="modal-specs">
              <tr>
                <td class="spec-label">Size</td>
                <td id="modalSize" class="spec-value"></td>
              </tr>
              <tr>
                <td class="spec-label">Groupset</td>
                <td id="modalGroupset" class="spec-value"></td>
              </tr>
              <tr>
                <td class="spec-label">Tình trạng</td>
                <td id="modalCondition" class="spec-value"></td>
              </tr>
              <tr>
                <td class="spec-label">Năm</td>
                <td id="modalYear" class="spec-value"></td>
              </tr>
              <tr>
                <td class="spec-label">Vị trí</td>
                <td id="modalLocation" class="spec-value"></td>
              </tr>
            </table>

            <p id="modalDesc" class="modal-description"></p>

            <div class="modal-actions">
              <button
                onclick="toggleWishlistFromModal()"
                class="btn btn-wishlist"
              >
                <i class="fa-solid fa-heart heart"></i>
                <span>Yêu thích</span>
              </button>
              <button onclick="fakeChat()" class="btn btn-chat">
                <i class="fa-solid fa-message"></i> Chat ngay
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL ĐĂNG BÁN -->
    <div id="sellModal" class="modal hidden">
      <div class="modal-backdrop" onclick="hideSellModal()"></div>
      <div class="modal-content sell-modal">
        <h2 class="sell-title">Đăng bán xe của bạn</h2>
        <p class="sell-subtitle">
          Thông tin càng đầy đủ, tin càng được tin tưởng
        </p>
        <div class="sell-form">
          <input
            type="text"
            id="sellTitle"
            placeholder="Tên xe (ví dụ: Trek Domane SL 5)"
            class="form-input"
          />
          <div class="form-row">
            <input type="text" placeholder="Size khung" class="form-input" />
            <input
              type="number"
              placeholder="Giá (triệu VND)"
              class="form-input"
            />
          </div>
        </div>
        <button onclick="hideSellModal()" class="btn btn-submit">
          Đăng tin ngay
        </button>
      </div>
    </div>

    <script src="config/assets/script.js"></script>
  </body>
</html>

<?php
?>