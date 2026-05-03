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
      <aside class="sidebar marketplace-sidebar">
        <div class="sidebar-header">
          <div>
            <p class="sidebar-eyebrow">Bộ lọc</p>
            <h3 class="sidebar-title sidebar-title-compact">Tìm xe phù hợp</h3>
          </div>
          <button type="button" onclick="resetFilters()" class="sidebar-reset-btn" title="Đặt lại bộ lọc">
            <i class="fa-solid fa-rotate-right"></i>
          </button>
        </div>

        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-copyright"></i> Hãng xe</label>
          <select id="brandFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="">Tất cả hãng</option>
            <option value="Asama">Asama</option>
            <option value="Cannondale">Cannondale</option>
            <option value="Giant">Giant</option>
            <option value="Java">Java</option>
            <option value="Martin 107">Martin 107</option>
            <option value="Polygon">Polygon</option>
            <option value="Specialized">Specialized</option>
            <option value="Trek">Trek</option>
            <option value="Trinx">Trinx</option>
            <option value="Twitter">Twitter</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-bicycle"></i> Dòng xe</label>
          <select id="typeFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="">Tất cả dòng xe</option>
            <option value="Road">Road bike</option>
            <option value="MTB">Mountain bike</option>
            <option value="Gravel">Gravel</option>
            <option value="Touring">Touring</option>
            <option value="Fixed">Fixed gear</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label"><i class="fa-solid fa-money-bill-wave"></i> Khoảng giá</label>
          <div class="price-range-box">
            <input id="priceMin" type="number" min="0" placeholder="Từ" oninput="applyFilters()" class="price-input-modern" />
            <span class="price-separator">-</span>
            <input id="priceMax" type="number" min="0" placeholder="Đến" oninput="applyFilters()" class="price-input-modern" />
          </div>
          <p class="filter-help">Nhập giá theo VNĐ, ví dụ 10000000.</p>
        </div>

        <div class="filter-group filter-group-last">
          <label class="filter-label"><i class="fa-solid fa-arrow-down-a-z"></i> Sắp xếp</label>
          <select id="sortFilter" onchange="applyFilters()" class="filter-select-modern">
            <option value="newest">Tin mới nhất</option>
            <option value="price-low">Giá thấp đến cao</option>
            <option value="price-high">Giá cao đến thấp</option>
          </select>
        </div>
      </aside>

      <div class="products-section marketplace-results">
        <div class="products-header">
          <div>
            <h1>Xe đạp đang bán</h1>
          </div>
          <p id="resultCount"><?php echo count($products); ?> tin phù hợp</p>
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
                
                // 2.1 Xử lý địa chỉ: hiển thị Quận/Huyện + Tỉnh/Thành phố
                $location = 'Đang cập nhật';
                if (!empty($row['location'])) {
                    $locationParts = array_values(array_filter(array_map('trim', explode(',', $row['location']))));
                    $locationTail = array_slice($locationParts, -2);
                    $location = !empty($locationTail) ? implode(', ', $locationTail) : trim((string) $row['location']);
                }

                // 3. TÍNH TOÁN THỜI GIAN ĐĂNG BÀI
                $createdAt = !empty($row['created_at']) ? strtotime((string)$row['created_at']) : false;
                $now = time(); 
                $diff = $createdAt ? ($now - $createdAt) : null;

                if ($diff !== null && $diff < 60) {
                    $seconds = max(1, (int) $diff);
                    $timeAgo = $seconds . ' giây trước';
                } elseif ($diff !== null && $diff < 3600) {
                    $mins = floor($diff / 60);
                    $timeAgo = max(1, (int) $mins) . ' phút trước';
                } elseif ($diff !== null && $diff < 86400) {
                    $hours = floor($diff / 3600);
                    $timeAgo = max(1, (int) $hours) . ' giờ trước';
                } elseif ($diff !== null && $diff < 2592000) {
                    $days = floor($diff / 86400);
                    $timeAgo = max(1, (int) $days) . ' ngày trước';
                } elseif ($diff !== null && $diff < 31536000) {
                    $months = floor($diff / 2592000);
                    $timeAgo = max(1, (int) $months) . ' tháng trước';
                } elseif ($createdAt) {
                    $years = floor($diff / 31536000);
                    $timeAgo = max(1, (int) $years) . ' năm trước';
                } else {
                    $timeAgo = 'Vừa cập nhật';
                }

                // 4. LẤY SỐ LƯỢNG ẢNH
                $imgCount = isset($row['image_count']) && $row['image_count'] > 0 ? $row['image_count'] : 1;
              ?>
              
              <?php
                $brandValue = trim((string)($row['brand'] ?? ''));
                $typeValue = trim((string)($row['bike_type'] ?? ''));
                $locationSearch = trim((string)($row['location'] ?? ''));
                $createdSort = $createdAt ?: 0;
              ?>
              <article
                class="product-card"
                onclick="window.location.href='<?php echo route_url('listing', ['id' => (int) $row['id']]); ?>'"
                data-title="<?php echo htmlspecialchars($productTitle); ?>"
                data-brand="<?php echo htmlspecialchars($brandValue); ?>"
                data-type="<?php echo htmlspecialchars($typeValue); ?>"
                data-price="<?php echo is_numeric($priceValue) ? (float) $priceValue : 0; ?>"
                data-created="<?php echo (int) $createdSort; ?>"
                data-location="<?php echo htmlspecialchars($locationSearch); ?>"
              >
                <div class="product-image product-image-link" style="background-image: url('<?php echo htmlspecialchars($image); ?>');">
                    <button type="button" class="product-favorite-btn" aria-label="Lưu tin yêu thích" onclick="event.stopPropagation(); this.classList.toggle('is-active'); this.querySelector('i').classList.toggle('fa-solid'); this.querySelector('i').classList.toggle('fa-regular');">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                    <div class="product-time-badge">
                        <?php echo $timeAgo; ?>
                    </div>
                    <div class="product-image-count">
                        <i class="fa-regular fa-images"></i> <?php echo $imgCount; ?>
                    </div>
                </div>
                
                <div class="product-info">
                    <div class="product-meta-row">
                        <span><?php echo htmlspecialchars($brandValue !== '' ? $brandValue : 'Chưa rõ hãng'); ?></span>
                        <?php if ($typeValue !== ''): ?>
                        <span><?php echo htmlspecialchars($typeValue); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="product-title">
                        <?php echo htmlspecialchars($productTitle); ?>
                    </h3>
                    <div class="product-price"><?php echo $formattedPrice; ?></div>
                    
                    <div class="product-location product-location-spaced">
                        <i class="fa-solid fa-location-dot product-location-icon"></i> 
                        <span><?php echo htmlspecialchars($location); ?></span>
                    </div>
                    
                    <div class="product-spacer"></div>
                </div>
              </article>
            <?php endforeach; ?>
            <div id="noFilterResults" class="empty-state-card hidden">
                <i class="fa-solid fa-magnifying-glass empty-state-icon"></i>
                <p class="empty-state-text">Không có tin nào khớp với bộ lọc hiện tại. Hãy thử mở rộng khoảng giá hoặc đổi từ khóa tìm kiếm.</p>
            </div>
          <?php else: ?>
            <div class="empty-state-card">
                <i class="fa-solid fa-box-open empty-state-icon"></i>
                <p class="empty-state-text">Hiện chưa có tin nào ở trạng thái đang bán. Hãy tạo và chờ duyệt một tin mới!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

<script>
  function getSearchTerm() {
    const searchInput = document.getElementById('searchInput');
    return searchInput ? searchInput.value.trim().toLowerCase() : '';
  }

  function applyFilters() {
    const grid = document.getElementById('productGrid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.product-card'));
    const brand = document.getElementById('brandFilter')?.value || '';
    const type = document.getElementById('typeFilter')?.value || '';
    const min = Number(document.getElementById('priceMin')?.value || 0);
    const maxInput = document.getElementById('priceMax')?.value || '';
    const max = maxInput !== '' ? Number(maxInput) : Infinity;
    const sort = document.getElementById('sortFilter')?.value || 'newest';
    const keyword = getSearchTerm();
    let visibleCount = 0;

    const sortedCards = cards.sort((a, b) => {
      if (sort === 'price-low') return Number(a.dataset.price) - Number(b.dataset.price);
      if (sort === 'price-high') return Number(b.dataset.price) - Number(a.dataset.price);
      return Number(b.dataset.created) - Number(a.dataset.created);
    });

    sortedCards.forEach((card) => grid.appendChild(card));
    const noResults = document.getElementById('noFilterResults');
    if (noResults) grid.appendChild(noResults);

    cards.forEach((card) => {
      const price = Number(card.dataset.price || 0);
      const text = `${card.dataset.title || ''} ${card.dataset.brand || ''} ${card.dataset.type || ''} ${card.dataset.location || ''}`.toLowerCase();
      const matched =
        (!brand || card.dataset.brand === brand) &&
        (!type || card.dataset.type === type) &&
        price >= min &&
        price <= max &&
        (!keyword || text.includes(keyword));

      card.classList.toggle('hidden', !matched);
      if (matched) visibleCount += 1;
    });

    const resultCount = document.getElementById('resultCount');
    if (resultCount) {
      resultCount.textContent = `${visibleCount} tin phù hợp`;
    }

    noResults?.classList.toggle('hidden', visibleCount !== 0);
  }

  function resetFilters() {
    ['brandFilter', 'typeFilter', 'sortFilter', 'priceMin', 'priceMax'].forEach((id) => {
      const element = document.getElementById(id);
      if (!element) return;
      element.value = id === 'sortFilter' ? 'newest' : '';
    });

    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    applyFilters();
  }

  document.getElementById('searchInput')?.addEventListener('input', applyFilters);
  window.addEventListener('DOMContentLoaded', applyFilters);
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
    
  </body>
</html>
