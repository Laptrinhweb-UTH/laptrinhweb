<?php 
// Lùi ra 1 cấp để tìm header
include __DIR__ . '/../app/views/layouts/header.php'; 

// Lùi ra 1 cấp để nhúng Database và Model
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/models/Product.php';
$database = new Database();
$db = $database->getConnectionOrNull();
$products = [];
$pageError = null;

if (!$db) {
    $pageError = 'Danh sách sản phẩm hiện chưa sẵn sàng. Vui lòng kiểm tra lại kết nối dữ liệu và thử lại sau.';
} else {
    try {
        $productModel = new Product($db);
        $products = $productModel->getAll();
    } catch (Throwable $exception) {
        $pageError = 'Danh sách sản phẩm tạm thời chưa thể tải. Vui lòng thử lại sau.';
    }
}
?>

    <div class="main-content home-page-layout">
      <aside class="sidebar">
        <div class="sidebar-header">
          <h3 class="sidebar-title sidebar-title-compact">
            <i class="fa-solid fa-filter"></i> Lọc kết quả
          </h3>
          <button onclick="resetFilters()" class="sidebar-reset-btn">
            <i class="fa-solid fa-rotate-right"></i> Đặt lại
          </button>
        </div>
        
        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-copyright"></i> Hãng xe</label>
          <select id="brandFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="">Tất cả các hãng</option>
            <option value="Giant">Giant</option>
            <option value="Trek">Trek</option>
            <option value="Trinx">Trinx</option>
            <option value="Asama">Asama</option>
            <option value="Martin 107">Martin 107</option>
            <option value="Thống Nhất">Thống Nhất</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-money-bill-wave"></i> Mức giá (VNĐ)</label>
          <div class="price-range-box">
            <input id="priceMin" type="number" placeholder="Từ..." onchange="applyFilters()" class="price-input-modern" />
            <span class="price-separator">-</span>
            <input id="priceMax" type="number" placeholder="Đến..." onchange="applyFilters()" class="price-input-modern" />
          </div>
        </div>

        <div class="filter-group filter-group-last">
          <label class="filter-label"><i class="fa-solid fa-arrow-down-a-z"></i> Sắp xếp</label>
          <select id="sortFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="newest">Tin mới nhất</option>
            <option value="price-low">Giá: Thấp đến cao</option>
            <option value="price-high">Giá: Cao đến thấp</option>
          </select>
        </div>
      </aside>

      <div class="products-section">
        <div class="products-header">
          <h1>Xe Đang Mở Bán</h1>
          <p id="resultCount"><?php echo count($products); ?> tin đã được duyệt và đang hiển thị</p>
        </div>

        <div class="profile-card mb-4">
          <div class="d-flex flex-column gap-2">
            <div class="fw-bold">Quy trình giao dịch trên SpinBike</div>
            <div class="text-muted">Người mua đặt mua an toàn, hệ thống giữ tiền, người bán giao xe, sau đó buyer xác nhận nhận hàng hoặc gửi khiếu nại nếu có vấn đề.</div>
          </div>
        </div>

        <div id="productGrid" class="product-grid">
          <?php if ($pageError !== null): ?>
            <div class="empty-state-card">
                <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
                <p class="empty-state-text"><?php echo htmlspecialchars($pageError); ?></p>
            </div>
          <?php elseif (!empty($products) && count($products) > 0): ?>
            <?php foreach ($products as $row): ?>
              <?php
                $productTitle = trim((string)($row['title'] ?? ''));
                if ($productTitle === '') {
                    $productTitle = 'Xe đạp đang cập nhật tên';
                }

                // 1. Định dạng giá tiền cho đẹp
                $priceValue = $row['price'] ?? null;
                $formattedPrice = is_numeric($priceValue) ? number_format((float)$priceValue, 0, ',', '.') . ' đ' : 'Liên hệ để báo giá';
                
                // 2. Lấy link ảnh
                $image = !empty($row['main_image']) ? $row['main_image'] : 'https://via.placeholder.com/400x300?text=Chua+Co+Anh';
                
                // 2.1 Xử lý địa chỉ: Chỉ cắt lấy Tỉnh/Thành phố
                $location = 'Đang cập nhật';
                if (!empty($row['location'])) {
                    $locationParts = explode(',', $row['location']); 
                    $location = trim(end($locationParts)); 
                }

                // 3. TÍNH TOÁN THỜI GIAN ĐĂNG BÀI
                $createdAt = !empty($row['created_at']) ? strtotime((string)$row['created_at']) : false;
                $now = time(); 
                $diff = $createdAt ? ($now - $createdAt) : null;

                if ($diff !== null && $diff < 3600) {
                    $mins = floor($diff / 60);
                    $timeAgo = ($mins > 0 ? $mins : 1) . ' phút trước';
                } elseif ($diff !== null && $diff < 86400) {
                    $hours = floor($diff / 3600);
                    $timeAgo = $hours . ' giờ trước';
                } elseif ($createdAt) {
                    $timeAgo = date('d/m/Y', $createdAt);
                } else {
                    $timeAgo = 'Vừa cập nhật';
                }

                // 4. LẤY SỐ LƯỢNG ẢNH
                $imgCount = isset($row['image_count']) && $row['image_count'] > 0 ? $row['image_count'] : 1;
              ?>
              
              <div class="product-card">
                <div class="product-image" style="background-image: url('<?php echo htmlspecialchars($image); ?>'); position: relative;">
                    <div class="product-time-badge">
                        <i class="fa-regular fa-clock"></i> <?php echo $timeAgo; ?>
                    </div>
                    <div class="product-image-count">
                        <i class="fa-regular fa-images"></i> <?php echo $imgCount; ?>
                    </div>
                </div>
                
                <div class="product-info">
                    <h3 class="product-title"><?php echo htmlspecialchars($productTitle); ?></h3>
                    <div class="product-price"><?php echo $formattedPrice; ?></div>
                    
                    <div class="product-location product-location-spaced">
                        <i class="fa-solid fa-location-dot product-location-icon"></i> 
                        <span><?php echo htmlspecialchars($location); ?></span>
                    </div>
                    
                    <div class="product-spacer"></div>
                    
                    <a href="<?php echo asset_url('detail.php?id=' . (int) $row['id']); ?>" class="btn-detail product-detail-link">
                        Xem chi tiết
                    </a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state-card">
                <i class="fa-solid fa-box-open empty-state-icon"></i>
                <p class="empty-state-text">Hiện chưa có tin nào ở trạng thái đang bán. Hãy tạo và chờ duyệt một tin mới!</p>
            </div>
          <?php endif; ?>
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
    
    <script src="<?php echo asset_url('assets/js/script.js'); ?>"></script>
  </body>
</html>
