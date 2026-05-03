<?php
session_start(); // Bắt buộc phải có session_start() để lấy $_SESSION['user_id']
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/helpers/ProjectFlow.php';
require_once __DIR__ . '/../app/models/Product.php';

// Kiểm tra ID xe hợp lệ
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . route_url('home'));
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    header('Location: ' . route_url('home'));
    exit;
}

$database = new Database();
$db = $database->getConnectionOrNull();
$detailError = null;
$product = null;
$images = ['https://via.placeholder.com/600x400?text=Chua+Co+Anh'];
$formattedPrice = 'Đang cập nhật';
$sellerId = '1';
$sellerName = '';
$avatarUrl = "https://ui-avatars.com/api/?name=U+{$sellerId}&background=10b981&color=fff&rounded=true&bold=true";
$canPurchase = false;

if (!$db) {
    $detailError = 'Dữ liệu sản phẩm hiện chưa sẵn sàng. Vui lòng kiểm tra kết nối dữ liệu và thử lại sau.';
} else {
    try {
        $productModel = new Product($db);
        $product = $productModel->getProductDetail($id);

        if (!$product) {
            $detailError = 'Không tìm thấy chiếc xe bạn đang xem hoặc dữ liệu đã được cập nhật.';
        } else {
            $formattedPrice = number_format($product['price'], 0, ',', '.') . ' đ';
            $images = !empty($product['images']) ? $product['images'] : $images;
            $sellerId = $product['seller_id'] ?? $sellerId;
            $sellerName = trim((string) ($product['seller_name'] ?? ''));
            if ($sellerName !== '') {
                $avatarUrl = !empty($product['seller_avatar'])
                    ? $product['seller_avatar']
                    : "https://ui-avatars.com/api/?name=" . urlencode($sellerName) . "&background=10b981&color=fff&rounded=true&bold=true";
            } else {
                $avatarUrl = "https://ui-avatars.com/api/?name=U+{$sellerId}&background=10b981&color=fff&rounded=true&bold=true";
            }
            $canPurchase = ($product['listing_status'] ?? '') === ProjectFlow::LISTING_APPROVED;
        }
    } catch (Throwable $exception) {
        $detailError = 'Thông tin chi tiết sản phẩm tạm thời chưa thể tải. Vui lòng thử lại sau.';
    }
}

$productTitle = trim((string)($product['title'] ?? ''));
if ($productTitle === '') {
    $productTitle = 'Xe đạp đang cập nhật tên';
}

$productBrand = trim((string)($product['brand'] ?? ''));
if ($productBrand === '') {
    $productBrand = 'Chưa cập nhật hãng xe';
}

$productLocation = trim((string)($product['location'] ?? ''));
if ($productLocation === '') {
    $productLocation = 'Đang cập nhật vị trí';
}

$productSize = trim((string)($product['size'] ?? ''));
if ($productSize === '') {
    $productSize = 'Đang cập nhật';
}

$productType = trim((string)($product['bike_type'] ?? ''));
if ($productType === '') {
    $productType = 'Đang cập nhật';
}

$productCondition = $product['condition'] ?? null;
$productConditionLabel = is_numeric($productCondition) ? rtrim(rtrim(number_format((float)$productCondition, 0, ',', '.'), '0'), ',') . '%' : 'Đang cập nhật';


$productDescription = trim((string)($product['description'] ?? ''));
if ($productDescription === '') {
    $productDescription = 'Người bán chưa bổ sung mô tả chi tiết cho sản phẩm này.';
}

if ($sellerName === '') {
    $sellerName = is_numeric($sellerId) ? 'Người bán (ID: ' . $sellerId . ')' : 'Người bán đang cập nhật';
}
$sellerLabel = $sellerName;
$listingStatus = (string) ($product['listing_status'] ?? '');
$listingStatusLabel = match ($listingStatus) {
    ProjectFlow::LISTING_APPROVED => 'Đang bán',
    ProjectFlow::LISTING_SOLD => 'Đã bán',
    ProjectFlow::LISTING_PENDING => 'Chờ duyệt',
    ProjectFlow::LISTING_REJECTED => 'Tạm ngưng',
    ProjectFlow::LISTING_HIDDEN => 'Đã ẩn',
    default => 'Đang cập nhật',
};
$listingStatusMessage = match ($listingStatus) {
    ProjectFlow::LISTING_SOLD => 'Chiếc xe này đã được giữ chỗ hoặc đã chốt giao dịch nên hiện không thể mua thêm.',
    ProjectFlow::LISTING_PENDING => 'Tin đăng này đang chờ duyệt và chưa mở bán công khai.',
    ProjectFlow::LISTING_REJECTED => 'Tin đăng này đang được người bán chỉnh sửa lại nên chưa thể giao dịch.',
    ProjectFlow::LISTING_HIDDEN => 'Tin đăng hiện đang được ẩn tạm thời khỏi hệ thống.',
    default => '',
};

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="main-content detail-page-shell">
    <div class="container detail-page-container">
        <?php if ($detailError !== null): ?>
        <div class="empty-state-card">
            <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
            <p class="empty-state-text"><?php echo htmlspecialchars($detailError); ?></p>
            <a href="<?php echo route_url('home'); ?>" class="btn-detail product-detail-link">Quay lại trang chủ</a>
        </div>
        <?php else: ?>
        
        <div class="detail-breadcrumbs">
            <a href="<?php echo route_url('home'); ?>" class="detail-breadcrumb-link"><i class="fa-solid fa-house"></i> Trang chủ</a> 
            <i class="fa-solid fa-angle-right detail-breadcrumb-separator"></i>
            <span><?php echo htmlspecialchars($productBrand); ?></span>
            <i class="fa-solid fa-angle-right detail-breadcrumb-separator"></i>
            <span class="detail-breadcrumb-current"><?php echo htmlspecialchars($productTitle); ?></span>
        </div>

        <div class="detail-page-card">
            
            <div class="detail-images">
                <div class="detail-main-image-frame">
                    
                    <img id="mainImage" src="<?php echo $images[0]; ?>" class="detail-main-image" style="transition: opacity 0.3s ease;">

                    <button onclick="prevImage()" class="detail-image-nav detail-image-nav-prev" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.8)'">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <button onclick="nextImage()" class="detail-image-nav detail-image-nav-next" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.8)'">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                
                <div class="detail-thumbnail-row">
                    <?php foreach ($images as $index => $img): ?>
                        <img src="<?php echo $img; ?>" 
                             class="thumb-item"
                             id="thumb-<?php echo $index; ?>"
                             onclick="showImage(<?php echo $index; ?>)" 
                             style="<?php echo $index === 0 ? 'border-color: var(--primary-light); opacity: 1;' : 'opacity: 0.6;'; ?>">
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="detail-info">
                <div class="detail-topline">
                    <span class="detail-status-badge"><?php echo htmlspecialchars($listingStatusLabel); ?></span>
                    <span class="detail-brand-chip"><?php echo htmlspecialchars($productBrand); ?></span>
                </div>

                <h1 class="detail-page-title"><?php echo htmlspecialchars($productTitle); ?></h1>
                
                <div class="detail-page-price"><?php echo $formattedPrice; ?></div>

                <div class="detail-quick-specs">
                    <div>
                        <span>Dòng xe</span>
                        <strong><?php echo htmlspecialchars($productType); ?></strong>
                    </div>
                    <div>
                        <span>Size</span>
                        <strong><?php echo htmlspecialchars($productSize); ?></strong>
                    </div>
                    <div>
                        <span>Độ mới</span>
                        <strong><?php echo htmlspecialchars($productConditionLabel); ?></strong>
                    </div>
                </div>
                
                <div class="detail-seller-card">
                    
                    <div class="detail-seller-header">
                        <img src="<?php echo $avatarUrl; ?>" alt="Avatar" class="detail-seller-avatar">
                        <div>
                            <div class="detail-seller-name"><?php echo htmlspecialchars($sellerLabel); ?></div>
                            <div class="detail-seller-location">
                                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($productLocation); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-action-stack">
                        <?php if ($canPurchase): ?>
                        <button onclick="showBuyOptions()" class="detail-buy-btn" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fa-solid fa-cart-shopping"></i> Đặt mua
                        </button>
                        <?php else: ?>
                        <div class="auth-message auth-message-error">
                            <?php echo htmlspecialchars($listingStatusMessage !== '' ? $listingStatusMessage : 'Tin đăng này hiện chưa thể giao dịch.'); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>

                <h3 class="detail-section-title detail-description-title">Mô tả bài đăng</h3>
                <div class="detail-description-card">
<?php echo nl2br(htmlspecialchars($productDescription)); ?>
                </div>

                <h3 class="detail-section-title">Thông tin xe</h3>
                <div class="detail-specs-card">
                    <div class="detail-spec-row">
                        <span class="detail-spec-label">Hãng xe</span>
                        <strong class="detail-spec-value"><?php echo htmlspecialchars($productBrand); ?></strong>
                    </div>
                    <div class="detail-spec-row">
                        <span class="detail-spec-label">Dòng xe</span>
                        <strong class="detail-spec-value"><?php echo htmlspecialchars($productType); ?></strong>
                    </div>
                    <div class="detail-spec-row">
                        <span class="detail-spec-label">Size khung</span>
                        <strong class="detail-spec-value"><?php echo htmlspecialchars($productSize); ?></strong>
                    </div>
                    <div class="detail-spec-row">
                        <span class="detail-spec-label">Độ mới</span>
                        <strong class="detail-spec-value"><?php echo htmlspecialchars($productConditionLabel); ?></strong>
                    </div>
                    <div class="detail-spec-row">
                        <span class="detail-spec-label">Khu vực</span>
                        <strong class="detail-spec-value"><?php echo htmlspecialchars($productLocation); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($detailError === null): ?>
<div id="detailInfoModal" class="modal hidden">
    <div class="modal-backdrop" onclick="hideInfoDialog()"></div>
    <div class="modal-content detail-buy-modal" style="max-width: 520px;">
        <div class="detail-buy-modal-header">
            <h3 id="detailInfoModalTitle" class="detail-buy-modal-title">Thông báo</h3>
            <button type="button" class="detail-buy-modal-close" onclick="hideInfoDialog()">&times;</button>
        </div>
        <div class="p-4">
            <p id="detailInfoModalMessage" class="mb-4 text-muted" style="line-height: 1.7;"></p>
            <div class="d-flex justify-content-end gap-2 flex-wrap">
                <button type="button" id="detailInfoModalCancel" class="btn btn-outline-secondary rounded-pill px-4 hidden" onclick="hideInfoDialog()">Để sau</button>
                <button type="button" id="detailInfoModalConfirm" class="btn btn-primary rounded-pill px-4" onclick="confirmInfoDialog()">Đã hiểu</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($detailError === null): ?>
<script>
    // Logic Slider Ảnh
    const bikeImages = <?php echo json_encode($images); ?>;
    let currentIdx = 0;
    let detailInfoDialogAction = null;

    function showInfoDialog(options = {}) {
        const modal = document.getElementById('detailInfoModal');
        if (!modal) {
            if (typeof options.onConfirm === 'function') {
                options.onConfirm();
            }
            return;
        }

        const title = options.title || 'Thông báo';
        const message = options.message || 'Thông tin đang được cập nhật.';
        const confirmText = options.confirmText || 'Đã hiểu';
        const cancelText = options.cancelText || 'Để sau';
        const showCancel = Boolean(options.showCancel);
        const confirmButtonClass = options.confirmButtonClass || 'btn btn-primary rounded-pill px-4';

        document.getElementById('detailInfoModalTitle').textContent = title;
        document.getElementById('detailInfoModalMessage').textContent = message;

        const cancelButton = document.getElementById('detailInfoModalCancel');
        cancelButton.textContent = cancelText;
        cancelButton.classList.toggle('hidden', !showCancel);

        const confirmButton = document.getElementById('detailInfoModalConfirm');
        confirmButton.textContent = confirmText;
        confirmButton.className = confirmButtonClass;

        detailInfoDialogAction = typeof options.onConfirm === 'function' ? options.onConfirm : null;
        modal.classList.remove('hidden');
    }

    function hideInfoDialog() {
        const modal = document.getElementById('detailInfoModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        detailInfoDialogAction = null;
    }

    function confirmInfoDialog() {
        const action = detailInfoDialogAction;
        hideInfoDialog();
        if (typeof action === 'function') {
            action();
        }
    }

    function showImage(index) {
        currentIdx = index;
        const mainImg = document.getElementById('mainImage');
        
        mainImg.style.opacity = '0.5';
        setTimeout(() => {
            mainImg.src = bikeImages[currentIdx];
            mainImg.style.opacity = '1';
        }, 150);

        document.querySelectorAll('.thumb-item').forEach((thumb, i) => {
            if (i === currentIdx) {
                thumb.style.borderColor = 'var(--primary-light)';
                thumb.style.opacity = '1';
            } else {
                thumb.style.borderColor = 'transparent';
                thumb.style.opacity = '0.6';
            }
        });
    }

    function nextImage() {
        currentIdx = (currentIdx + 1) % bikeImages.length;
        showImage(currentIdx);
    }

    function prevImage() {
        currentIdx = (currentIdx - 1 + bikeImages.length) % bikeImages.length;
        showImage(currentIdx);
    }

    // ================= LOGIC NÚT MUA NGAY =================
    function showBuyOptions() {
        // Kiểm tra đăng nhập (PHP render logic)
        <?php if(!isset($_SESSION['user_id'])): ?>
            showInfoDialog({
                title: 'Cần đăng nhập để tiếp tục',
                message: 'Bạn cần đăng nhập trước khi đặt mua an toàn qua SpinBike.',
                confirmText: 'Đăng nhập ngay',
                showCancel: true,
                onConfirm: () => {
                    window.location.href = '<?php echo route_url('auth'); ?>';
                }
            });
            return;
        <?php endif; ?>
        
        // Kiểm tra xem người bán có tự mua hàng của chính mình không
        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['seller_id']): ?>
            showInfoDialog({
                title: 'Không thể tự mua sản phẩm của bạn',
                message: 'Đây là chiếc xe do chính bạn đăng bán, nên hệ thống không cho phép tạo đơn mua cho sản phẩm này.'
            });
            return;
        <?php endif; ?>
        
        window.location.href = '<?php echo route_url('checkout'); ?>?product_id=<?php echo $id; ?>';
    }
</script>
<?php endif; ?>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
