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
          <div class="filter-section-heading">
            <span>Hãng</span>
          </div>
          <div class="filter-chip-grid" data-filter-group="brand">
            <button type="button" class="filter-chip is-active" data-value="">Tất cả</button>
            <button type="button" class="filter-chip" data-value="Giant">Giant</button>
            <button type="button" class="filter-chip" data-value="Trek">Trek</button>
            <button type="button" class="filter-chip" data-value="Specialized">Specialized</button>
            <button type="button" class="filter-chip" data-value="Cannondale">Cannondale</button>
            <button type="button" class="filter-chip" data-value="Trinx">Trinx</button>
            <button type="button" class="filter-chip" data-value="Asama">Asama</button>
            <button type="button" class="filter-chip" data-value="Java">Java</button>
            <button type="button" class="filter-chip" data-value="Polygon">Polygon</button>
          </div>
        </div>

        <div class="filter-group">
          <div class="filter-section-heading">
            <span>Dòng xe</span>
          </div>
          <div class="filter-chip-grid" data-filter-group="type">
            <button type="button" class="filter-chip is-active" data-value="">Tất cả</button>
            <button type="button" class="filter-chip" data-value="Road">Road</button>
            <button type="button" class="filter-chip" data-value="MTB">MTB</button>
            <button type="button" class="filter-chip" data-value="Gravel">Gravel</button>
            <button type="button" class="filter-chip" data-value="Touring">Touring</button>
            <button type="button" class="filter-chip" data-value="Fixed">Fixed</button>
          </div>
        </div>

        <div class="filter-group">
          <div class="filter-section-heading">
            <span>Khoảng giá</span>
          </div>
          <div class="filter-chip-grid filter-price-presets" data-filter-group="price">
            <button type="button" class="filter-chip is-active" data-min="" data-max="">Tất cả</button>
            <button type="button" class="filter-chip" data-min="0" data-max="10000000">Dưới 10tr</button>
            <button type="button" class="filter-chip" data-min="10000000" data-max="20000000">10-20tr</button>
            <button type="button" class="filter-chip" data-min="20000000" data-max="40000000">20-40tr</button>
            <button type="button" class="filter-chip" data-min="40000000" data-max="">Trên 40tr</button>
          </div>
          <div class="price-range-box">
            <input id="priceMin" type="number" min="0" placeholder="Từ" oninput="applyFilters()" class="price-input-modern" />
            <span class="price-separator">-</span>
            <input id="priceMax" type="number" min="0" placeholder="Đến" oninput="applyFilters()" class="price-input-modern" />
          </div>
        </div>
      </aside>

      <div class="products-section marketplace-results">
        <div class="products-header">
          <div>
            <h1>Tin xe mới nhất</h1>
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

  function getActiveChipValue(groupName) {
    return document.querySelector(`[data-filter-group="${groupName}"] .filter-chip.is-active`)?.dataset.value || '';
  }

  function syncPricePresetState() {
    const priceMin = document.getElementById('priceMin')?.value || '';
    const priceMax = document.getElementById('priceMax')?.value || '';
    const priceChips = document.querySelectorAll('[data-filter-group="price"] .filter-chip');

    priceChips.forEach((chip) => {
      const matches = (chip.dataset.min || '') === priceMin && (chip.dataset.max || '') === priceMax;
      chip.classList.toggle('is-active', matches);
    });
  }

  function applyFilters() {
    const grid = document.getElementById('productGrid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.product-card'));
    const brand = getActiveChipValue('brand');
    const type = getActiveChipValue('type');
    const min = Number(document.getElementById('priceMin')?.value || 0);
    const maxInput = document.getElementById('priceMax')?.value || '';
    const max = maxInput !== '' ? Number(maxInput) : Infinity;
    const sort = 'newest';
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

    noResults?.classList.toggle('hidden', visibleCount !== 0);
  }

  function resetFilters() {
    ['priceMin', 'priceMax'].forEach((id) => {
      const element = document.getElementById(id);
      if (!element) return;
      element.value = '';
    });

    document.querySelectorAll('.filter-chip-grid').forEach((group) => {
      group.querySelectorAll('.filter-chip').forEach((chip, index) => {
        chip.classList.toggle('is-active', index === 0);
      });
    });

    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    applyFilters();
  }

  document.querySelectorAll('[data-filter-group="brand"] .filter-chip, [data-filter-group="type"] .filter-chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      const group = chip.closest('.filter-chip-grid');
      group.querySelectorAll('.filter-chip').forEach((item) => item.classList.remove('is-active'));
      chip.classList.add('is-active');
      applyFilters();
    });
  });

  document.querySelectorAll('[data-filter-group="price"] .filter-chip').forEach((chip) => {
    chip.addEventListener('click', () => {
      const group = chip.closest('.filter-chip-grid');
      group.querySelectorAll('.filter-chip').forEach((item) => item.classList.remove('is-active'));
      chip.classList.add('is-active');
      document.getElementById('priceMin').value = chip.dataset.min || '';
      document.getElementById('priceMax').value = chip.dataset.max || '';
      applyFilters();
    });
  });

  document.getElementById('priceMin')?.addEventListener('input', syncPricePresetState);
  document.getElementById('priceMax')?.addEventListener('input', syncPricePresetState);
  document.getElementById('searchInput')?.addEventListener('input', applyFilters);
  window.addEventListener('DOMContentLoaded', applyFilters);
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
    
  </body>
</html>
