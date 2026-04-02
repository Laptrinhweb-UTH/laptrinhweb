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

    <div class="main-content">
      <!-- FILTER SIDEBAR -->
      <aside class="sidebar">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
          <h3 class="sidebar-title" style="margin: 0;">
            <i class="fa-solid fa-filter"></i> Bộ lọc xe
          </h3>
          <button onclick="resetFilters()" style="background: none; border: none; color: var(--danger); cursor: pointer; font-size: 13px; font-weight: 600; padding: 0;">
            <i class="fa-solid fa-rotate-right"></i> Đặt lại
          </button>
        </div>
<div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-bicycle"></i> Loại xe</label>
          <select id="typeFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="">Tất cả loại xe</option>
            <option value="Road">Road Bike (Xe cuộc)</option>
            <option value="MTB">MTB (Xe địa hình)</option>
            <option value="Gravel">Gravel Bike</option>
            <option value="Fixed">Fixed Gear</option>
            <option value="Touring">Touring</option>
            <option value="Other">Khác</option>
          </select>
        </div>
        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-ruler"></i> Size khung</label>
          <select id="sizeFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="">Tất cả kích cỡ</option>
            <option value="XS">XS (Dưới 1m60)</option>
            <option value="S">S (1m60 - 1m70)</option>
            <option value="M">M (1m70 - 1m80)</option>
            <option value="L">L (1m80 - 1m90)</option>
            <option value="XL">XL (1m90 - 1m95)</option>
            <option value="XXL">XXL (Trên 1m95)</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-money-bill-wave"></i> Mức giá (Triệu VNĐ)</label>
          <div class="price-range-box">
            <input id="priceMin" type="number" placeholder="Từ..." onchange="applyFilters()" class="price-input-modern" />
            <span class="price-separator">-</span>
            <input id="priceMax" type="number" placeholder="Đến..." onchange="applyFilters()" class="price-input-modern" />
          </div>
        </div>

        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-star"></i> Tình trạng</label>
          <div class="filter-tags" id="conditionFilters">
            <button onclick="toggleCondition(this)" data-cond="99" class="tag-btn">Mới 99%</button>
            <button onclick="toggleCondition(this)" data-cond="95" class="tag-btn">95%</button>
            <button onclick="toggleCondition(this)" data-cond="90" class="tag-btn">90%</button>
            <button onclick="toggleCondition(this)" data-cond="80" class="tag-btn">Dưới 90%</button>
          </div>
        </div>

        <div class="filter-group" style="margin-bottom: 0;">
          <label class="filter-label"><i class="fa-solid fa-arrow-down-a-z"></i> Sắp xếp kết quả</label>
          <select id="sortFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="newest">Tin mới nhất</option>
            <option value="price-low">Giá: Thấp đến cao</option>
            <option value="price-high">Giá: Cao đến thấp</option>
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
            <button type="button" onclick="hideSellModal()" class="modal-close" style="position: static;">×</button>

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
      <div class="modal-content sell-modal" style="max-width: 650px; max-height: 90vh; overflow-y: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
          <h2 class="sell-title" style="margin: 0;">Đăng bán xe đạp</h2>
          <button onclick="hideSellModal()" class="modal-close" style="position: static;">×</button>
        </div>
        <p class="sell-subtitle">Vui lòng điền đầy đủ thông tin để tin đăng uy tín và dễ bán hơn.</p>

        <form id="sellBikeForm" class="sell-form" onsubmit="handleSellSubmit(event)">
          
          <div class="form-group">
            <label class="form-label">Tên xe <span class="text-danger">*</span></label>
            <input type="text" placeholder="VD: Trek Domane SL 5 2022" class="form-input" required />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Loại xe <span class="text-danger">*</span></label>
              <select class="form-input" required>
                <option value="">-- Chọn loại xe --</option>
                <option value="Road">Road Bike (Xe cuộc)</option>
                <option value="MTB">MTB (Xe địa hình)</option>
                <option value="Gravel">Gravel Bike</option>
                <option value="Fixed">Fixed Gear</option>
                <option value="Touring">Touring</option>
                <option value="Other">Khác</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Size khung <span class="text-danger">*</span></label>
              <select class="form-input" required>
                <option value="">-- Chọn Size --</option>
                <option value="XS">XS (Dưới 1m60)</option>
                <option value="S">S (1m60 - 1m70)</option>
                <option value="M">M (1m70 - 1m80)</option>
                <option value="L">L (1m80 - 1m90)</option>
                <option value="XL">XL (1m90 - 1m95)</option>
                <option value="XXL">XXL (Trên 1m95)</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Tình trạng khung <span class="text-danger">*</span></label>
              <select class="form-input" required>
                <option value="">-- Đánh giá --</option>
                <option value="99">Mới 99% (Như mới, không xước)</option>
                <option value="95">95% (Có xước dăm rất nhẹ)</option>
                <option value="90">90% (Xước thấy rõ, không móp méo)</option>
                <option value="80">80% (Cũ theo thời gian, tróc sơn)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Tình trạng phụ tùng</label>
              <input type="text" placeholder="VD: Groupset nguyên bản 105, sên mới..." class="form-input" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Mức giá (VNĐ) <span class="text-danger">*</span></label>
              <input type="number" placeholder="VD: 15000000" class="form-input" required />
            </div>
            <div class="form-group">
              <label class="form-label">Hình thức bán</label>
              <select class="form-input">
                <option value="fixed">Giá cố định (Không bớt)</option>
                <option value="negotiable">Có thương lượng</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Địa chỉ xem xe <span class="text-danger">*</span></label>
            <input type="text" placeholder="VD: Phường 12, Quận 10, TP.HCM" class="form-input" required />
          </div>

          <div class="form-group">
            <label class="form-label">Hình ảnh thực tế (Tối thiểu 1 ảnh) <span class="text-danger">*</span></label>
            <div class="upload-area" onclick="document.getElementById('bikeImages').click()">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              <p style="font-weight: 600; margin-bottom: 4px;">Nhấn vào đây để tải ảnh lên</p>
              <span class="upload-hint">(Nên chụp rõ: Toàn cảnh, khung, groupset, lốp, đồng hồ...)</span>
            </div>
            <input type="file" id="bikeImages" multiple accept="image/*" style="display: none;" required onchange="previewImages(event)">
            
            <div id="imagePreviewContainer" class="image-preview-container"></div>
          </div>

          <button type="submit" class="btn-submit" style="margin-top: 16px;">
            <i class="fa-solid fa-paper-plane"></i> Đăng tin bán xe
          </button>
        </form>
      </div>
    </div>

    <script src="config/assets/script.js"></script>
  </body>
</html>

<?php
?>