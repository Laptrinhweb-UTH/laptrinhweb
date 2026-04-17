<?php
session_start(); // Bắt buộc phải có session_start() để lấy $_SESSION['user_id']
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/models/Product.php';

// Kiểm tra ID xe hợp lệ
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnectionOrNull();
$detailError = null;
$product = null;
$images = ['https://via.placeholder.com/600x400?text=Chua+Co+Anh'];
$formattedPrice = 'Đang cập nhật';
$sellerId = '1';
$avatarUrl = "https://ui-avatars.com/api/?name=U+{$sellerId}&background=10b981&color=fff&rounded=true&bold=true";

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
            $avatarUrl = "https://ui-avatars.com/api/?name=U+{$sellerId}&background=10b981&color=fff&rounded=true&bold=true";
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

$productCondition = $product['condition'] ?? null;
$productConditionLabel = is_numeric($productCondition) ? rtrim(rtrim(number_format((float)$productCondition, 0, ',', '.'), '0'), ',') . '%' : 'Đang cập nhật';

$productGroupset = trim((string)($product['groupset'] ?? ''));
if ($productGroupset === '') {
    $productGroupset = 'Đang cập nhật';
}

$productDescription = trim((string)($product['description'] ?? ''));
if ($productDescription === '') {
    $productDescription = 'Người bán chưa bổ sung mô tả chi tiết cho sản phẩm này.';
}

$sellerLabel = is_numeric($sellerId) ? 'Người bán (ID: ' . $sellerId . ')' : 'Người bán đang cập nhật';

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="main-content detail-page-shell">
    <div class="container detail-page-container">
        <?php if ($detailError !== null): ?>
        <div class="empty-state-card">
            <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
            <p class="empty-state-text"><?php echo htmlspecialchars($detailError); ?></p>
            <a href="index.php" class="btn-detail product-detail-link">Quay lại trang chủ</a>
        </div>
        <?php else: ?>
        
        <div class="detail-breadcrumbs">
            <a href="index.php" class="detail-breadcrumb-link"><i class="fa-solid fa-house"></i> Trang chủ</a> 
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
                <h1 class="detail-page-title"><?php echo htmlspecialchars($productTitle); ?></h1>
                
               <div class="detail-page-price"><?php echo $formattedPrice; ?></div>
                
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
                        <button onclick="showBuyOptions()" class="detail-buy-btn" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fa-solid fa-cart-shopping"></i> MUA NGAY
                        </button>
                        
                        <a href="#" onclick="alert('Tính năng nhắn tin đang được phát triển!'); return false;" class="detail-chat-btn" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">
                            <i class="fa-solid fa-comment-dots detail-chat-icon"></i> Nhắn tin trao đổi
                        </a>
                    </div>

                </div>

                <h3 class="detail-section-title">Thông số kỹ thuật</h3>
                <div class="detail-specs-card">
                    <table class="detail-specs-table">
                        <tr class="detail-spec-row detail-spec-row-alt">
                            <td class="detail-spec-label">Hãng xe</td>
                            <td class="detail-spec-value"><?php echo htmlspecialchars($productBrand); ?></td>
                        </tr>
                        <tr class="detail-spec-row">
                            <td class="detail-spec-label">Size khung</td>
                            <td class="detail-spec-value"><?php echo htmlspecialchars($productSize); ?></td>
                        </tr>
                        <tr class="detail-spec-row detail-spec-row-alt">
                            <td class="detail-spec-label">Độ mới</td>
                            <td class="detail-spec-value detail-spec-value-danger"><?php echo htmlspecialchars($productConditionLabel); ?></td>
                        </tr>
                        <tr class="detail-spec-row">
                            <td class="detail-spec-label">Groupset</td>
                            <td class="detail-spec-value"><?php echo htmlspecialchars($productGroupset); ?></td>
                        </tr>
                    </table>
                </div>

                <h3 class="detail-section-title detail-description-title">Mô tả bài đăng</h3>
                <div class="detail-description-card">
<?php echo nl2br(htmlspecialchars($productDescription)); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($detailError === null): ?>
<div id="buyOptionsModal" class="modal hidden">
    <div class="modal-backdrop" onclick="hideBuyOptions()"></div>
    <div class="modal-content detail-buy-modal">
        
        <div class="detail-buy-modal-header">
            <h3 class="detail-buy-modal-title">Chọn phương thức mua hàng</h3>
            <button onclick="hideBuyOptions()" class="detail-buy-modal-close">&times;</button>
        </div>
        
        <div class="detail-buy-option detail-buy-option-primary" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" onclick="processEscrowCheckout()">
            <div class="detail-buy-option-content">
                <div class="detail-buy-option-icon detail-buy-option-icon-primary">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h4 class="detail-buy-option-heading detail-buy-option-heading-primary">Mua an toàn qua SpinBike</h4>
                    <span class="detail-buy-badge">Khuyên dùng</span>
                    <p class="detail-buy-option-text detail-buy-option-text-primary">SpinBike sẽ làm trung gian giữ tiền. Chỉ khi bạn kiểm tra và nhận xe đúng mô tả, tiền mới được chuyển cho người bán.</p>
                </div>
            </div>
        </div>

        <div class="detail-buy-option detail-buy-option-secondary" onmouseover="this.style.borderColor='var(--text-secondary)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'" onclick="processDirectCheckout()">
            <div class="detail-buy-option-content">
                <div class="detail-buy-option-icon detail-buy-option-icon-secondary">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <div>
                    <h4 class="detail-buy-option-heading">Tự giao dịch trực tiếp</h4>
                    <p class="detail-buy-option-text">Tự liên hệ và hẹn gặp người bán. SpinBike sẽ <b>không chịu trách nhiệm</b> bảo vệ tiền nếu bạn chọn phương thức này.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Logic Slider Ảnh
    const bikeImages = <?php echo json_encode($images); ?>;
    let currentIdx = 0;

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
            alert("Bạn cần đăng nhập để thực hiện chức năng mua hàng!");
            window.location.href = '<?php echo app_url('app/views/auth/auth.php'); ?>';
            return;
        <?php endif; ?>
        
        // Kiểm tra xem người bán có tự mua hàng của chính mình không
        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['seller_id']): ?>
            alert("Bạn không thể mua chiếc xe do chính mình đăng bán!");
            return;
        <?php endif; ?>
        
        document.getElementById('buyOptionsModal').classList.remove('hidden');
    }

    function hideBuyOptions() {
        document.getElementById('buyOptionsModal').classList.add('hidden');
    }

    function processEscrowCheckout() {
        window.location.href = '<?php echo app_url('app/views/orders/checkout.php'); ?>?product_id=<?php echo $id; ?>';
    }

    function processDirectCheckout() {
        hideBuyOptions();
        alert('Vui lòng sử dụng tính năng Nhắn tin trao đổi để thỏa thuận trực tiếp với người bán!');
    }
</script>
<?php endif; ?>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
