<?php 
// Lùi ra 1 cấp để tìm header
include __DIR__ . '/../app/views/layouts/header.php'; 

// Lùi ra 1 cấp để nhúng Database và Model
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/models/Product.php';
$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

// Lấy danh sách sản phẩm để biến $products có giá trị
$products = $productModel->getAll();
?>

    <div class="main-content">
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

      <div class="products-section">
        <div class="products-header">
          <h1>Danh sách xe đạp</h1>
          <p id="resultCount"><?php echo count($products); ?> sản phẩm</p>
        </div>

        <div id="productGrid" class="product-grid">
  <?php if (!empty($products) && count($products) > 0): ?>
    <?php foreach ($products as $row): ?>
      <?php
        // 1. Định dạng giá tiền cho đẹp (VD: 15.000.000 VNĐ)
        $formattedPrice = number_format($row['price'], 0, ',', '.') . ' đ';
        
        // 2. Lấy link ảnh. Nếu xe chưa có ảnh thì dùng ảnh mặc định tránh bị lỗi giao diện
        $image = !empty($row['main_image']) ? $row['main_image'] : 'https://via.placeholder.com/300x200?text=No+Image';
        
        // 3. Xử lý trường hợp thiếu hãng xe hoặc địa chỉ
        $brand = !empty($row['brand']) ? $row['brand'] : 'Khác';
        $location = !empty($row['location']) ? $row['location'] : 'Đang cập nhật';
      ?>
      
      <div class="product-card">
        <div class="product-image" style="background-image: url('<?php echo htmlspecialchars($image); ?>'); background-size: cover; background-position: center;">
            <span class="product-tag"><?php echo htmlspecialchars($brand); ?></span>
        </div>
        <div class="product-info" style="padding: 16px;">
            <h3 class="product-title" style="margin: 0 0 8px 0; font-size: 18px; font-weight: 600;"><?php echo htmlspecialchars($row['title']); ?></h3>
            <div class="product-location" style="color: var(--text-secondary); font-size: 13px; margin-bottom: 8px;">
                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($location); ?>
            </div>
            <div class="product-price" style="color: var(--primary); font-size: 18px; font-weight: 700; margin-bottom: 12px;">
                <?php echo $formattedPrice; ?>
            </div>
            <button class="btn-detail" onclick="showDetail(<?php echo $row['id']; ?>)" style="width: 100%; padding: 10px; background: var(--primary-light); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                Xem chi tiết
            </button>
        </div>
      </div>
      
    <?php endforeach; ?>
  <?php else: ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 16px; border: 1px dashed var(--border);">
        <i class="fa-solid fa-box-open" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px;"></i>
        <p style="color: var(--text-secondary); font-size: 16px;">Chưa có chiếc xe nào được đăng bán. Hãy là người đầu tiên!</p>
    </div>
  <?php endif; ?>
</div>
      </div>
    </div>

    <div id="detailModal" class="modal hidden">
      <div class="modal-backdrop" onclick="hideDetailModal()"></div>
      <div class="modal-content detail-modal">
        <div class="modal-body">
          <div class="modal-images">
            <div id="modalMainImage" class="modal-main-image"></div>
            <div class="modal-thumbnails">
              <div onclick="changeModalImage(0)" id="thumb0" class="modal-thumb"></div>
              <div onclick="changeModalImage(1)" id="thumb1" class="modal-thumb"></div>
            </div>
          </div>

          <div class="modal-info">
            <button type="button" onclick="hideDetailModal()" class="modal-close" style="position: static;">×</button>

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
              <button onclick="toggleWishlistFromModal()" class="btn btn-wishlist">
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

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
    
    <script src="config/assets/js/script.js"></script>
  </body>
</html>