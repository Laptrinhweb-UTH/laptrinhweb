<?php 
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../models/Product.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . route_url('auth'));
    exit;
}

// 2. Kiểm tra ID sản phẩm truyền vào
$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$checkoutError = null;
$product = null;
$checkoutStatus = $_GET['status'] ?? '';
$checkoutMessage = trim((string)($_GET['message'] ?? ''));
$checkoutNoticeClass = $checkoutStatus === 'success' ? 'alert alert-success' : 'alert alert-danger';
$vnpayConfigured = defined('VNPAY_TMN_CODE')
    && defined('VNPAY_HASH_SECRET')
    && trim((string) VNPAY_TMN_CODE) !== ''
    && trim((string) VNPAY_HASH_SECRET) !== '';

if ($productId === false || $productId === null) {
    $checkoutError = 'Không tìm thấy sản phẩm hợp lệ để thanh toán.';
}

$database = new Database();
$db = $checkoutError === null ? $database->getConnectionOrNull() : null;

if ($checkoutError === null && !$db) {
    $checkoutError = 'Thanh toán hiện chưa sẵn sàng vì kết nối dữ liệu đang gặp sự cố.';
}

if ($checkoutError === null) {
    try {
        $productModel = new Product($db);
        $product = $productModel->getProductDetail($productId);

        if (!$product) {
            $checkoutError = 'Sản phẩm không tồn tại hoặc đã bị xóa.';
        } elseif (($product['seller_id'] ?? null) == $_SESSION['user_id']) {
            $checkoutError = 'Bạn không thể tự mua xe của chính mình.';
        } elseif (($product['listing_status'] ?? '') !== ProjectFlow::LISTING_APPROVED) {
            $checkoutError = 'Tin đăng này hiện không còn ở trạng thái có thể đặt mua an toàn.';
        }
    } catch (Throwable $exception) {
        $checkoutError = 'Không thể tải thông tin thanh toán lúc này. Vui lòng thử lại sau.';
    }
}

// Lấy thông tin liên lạc của buyer
$buyerPhone   = '';
$buyerAddress = '';
$buyerName    = trim((string) ($_SESSION['user_name'] ?? ''));
if ($db) {
    try {
        $stmt = $db->prepare("SELECT phone, address FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $buyerPhone   = trim((string) ($row['phone'] ?? ''));
        $buyerAddress = trim((string) ($row['address'] ?? ''));
    } catch (Throwable $e) {}
}

// Xử lý dữ liệu hiển thị
$formattedPrice = is_numeric($product['price'] ?? null) ? number_format((float)$product['price'], 0, ',', '.') . ' đ' : 'Liên hệ để báo giá';
$mainImage = (!empty($product['images'])) ? $product['images'][0] : 'https://via.placeholder.com/80';
$productTitle = trim((string)($product['title'] ?? ''));
$sellerName = trim((string)($product['seller_name'] ?? ''));
$productBrand = trim((string)($product['brand'] ?? ''));
$productLocation = trim((string)($product['location'] ?? ''));
$productBikeType = trim((string)($product['bike_type'] ?? ''));
$productFrameSize = trim((string)($product['frame_size'] ?? ''));
$productCondition = is_numeric($product['condition_percent'] ?? null) ? (int) $product['condition_percent'] . '%' : 'Đang cập nhật';
if ($productTitle === '') {
    $productTitle = 'Xe đạp đang cập nhật tên';
}
if ($sellerName === '') {
    $sellerName = 'Người bán đang cập nhật';
}
if ($productBrand === '') {
    $productBrand = 'Đang cập nhật hãng xe';
}
if ($productLocation === '') {
    $productLocation = 'Đang cập nhật vị trí';
}
if ($productBikeType === '') {
    $productBikeType = 'Đang cập nhật loại xe';
}
if ($productFrameSize === '') {
    $productFrameSize = 'Đang cập nhật size';
}

include __DIR__ . '/../layouts/header.php'; 
?>

<style>
    body { background-color: #f8fafc; }
    .checkout-wrapper { max-width: 1050px; }
    
    /* Payment Radio Cards */
    .payment-option-input:checked + .payment-option-card {
        border-color: #10b981 !important;
        background-color: #ecfdf5;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1);
    }
    .payment-option-input:checked + .payment-option-card .check-icon {
        opacity: 1;
        transform: scale(1);
    }
    .payment-option-card {
        cursor: pointer;
        border: 2px solid #e2e8f0;
        transition: all 0.25s ease;
    }
    .payment-option-card:hover { border-color: #cbd5e1; }
    .check-icon {
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.25s ease;
        color: #10b981;
    }

    /* Trust Card */
    .trust-card {
        background: #fff;
        border: 1px solid #d1fae5;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    }
    .trust-card-header {
        background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        border-bottom: 1px solid #d1fae5;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .trust-card-icon {
        width: 44px; height: 44px; border-radius: 50%;
        background: #10b981; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .trust-card-badges {
        display: flex;
        gap: 8px;
        padding: 14px 24px;
        border-bottom: 1px solid #f1f5f9;
        flex-wrap: wrap;
    }
    .trust-badge {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; color: #475569;
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 999px; padding: 5px 12px;
    }
    .trust-badge i { color: #10b981; font-size: 11px; }

    /* Steps */
    .trust-steps {
        display: flex;
        padding: 20px 24px;
        gap: 0;
        position: relative;
    }
    .trust-step {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
    }
    .trust-step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 18px;
        left: calc(50% + 20px);
        right: calc(-50% + 20px);
        height: 2px;
        background: #e2e8f0;
    }
    .trust-step-icon {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; margin-bottom: 10px; position: relative; z-index: 1;
    }
    .trust-step-title { font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
    .trust-step-desc { font-size: 11px; color: #94a3b8; line-height: 1.4; }

    /* Summary Card */
    .summary-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; }
</style>

<div class="container py-5 checkout-wrapper">
    <?php if ($checkoutMessage !== ''): ?>
    <div class="<?php echo $checkoutNoticeClass; ?> mb-4 shadow-sm rounded-3">
        <?php echo htmlspecialchars($checkoutMessage); ?>
    </div>
    <?php endif; ?>

    <?php if ($checkoutError !== null): ?>
    <div class="card border-0 shadow-sm rounded-4 text-center p-5 mt-4">
        <i class="fa-solid fa-circle-exclamation text-danger mb-3" style="font-size: 3rem;"></i>
        <h4 class="text-dark fw-bold mb-3">Rất tiếc!</h4>
        <p class="text-muted fs-5 mb-4"><?php echo htmlspecialchars($checkoutError); ?></p>
        <div>
            <a href="<?php echo route_url('home'); ?>" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">Quay lại trang chủ</a>
        </div>
    </div>
    <?php else: ?>
    
    <h2 class="fw-bold mb-4 text-dark">Thanh toán & Đặt hàng</h2>

    <div class="row g-4">
        <div class="col-lg-7">
            
            <div class="trust-card mb-4">
                <!-- Header -->
                <div class="trust-card-header">
                    <div class="trust-card-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:15px;">Giao dịch được bảo vệ 100%</div>
                        <div class="text-muted small mt-1" style="line-height:1.5;">
                            Tiền của bạn được giữ an toàn. Người bán chỉ nhận tiền sau khi bạn xác nhận đã nhận xe đúng mô tả.
                        </div>
                    </div>
                </div>
                <!-- Steps -->
                <div class="trust-steps">
                    <div class="trust-step">
                        <div class="trust-step-icon" style="background:#dcfce7;color:#166534;">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div class="trust-step-title">Thanh toán</div>
                        <div class="trust-step-desc">Tiền được giữ an toàn ngay lập tức</div>
                    </div>
                    <div class="trust-step">
                        <div class="trust-step-icon" style="background:#f1f5f9;color:#64748b;">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div class="trust-step-title">Nhận xe</div>
                        <div class="trust-step-desc">Người bán giao xe, bạn kiểm tra</div>
                    </div>
                    <div class="trust-step">
                        <div class="trust-step-icon" style="background:#f1f5f9;color:#64748b;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="trust-step-title">Xác nhận</div>
                        <div class="trust-step-desc">Bấm OK, giao dịch hoàn tất</div>
                    </div>
                </div>
            </div>

            <form id="checkoutForm" action="<?php echo route_url('checkout.process'); ?>" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0">Địa chỉ nhận xe</h5>
                        <a href="<?php echo route_url('user.settings.profile'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:13px;">
                            <i class="fa-solid fa-pen-to-square me-1"></i>Chỉnh sửa
                        </a>
                    </div>

                    <input type="hidden" name="buyer_address" id="buyerAddress" value="<?php echo htmlspecialchars($buyerAddress); ?>">

                    <?php
                    $missingInfo  = $buyerPhone === '' || $buyerAddress === '';
                    $displayPhone = $buyerPhone !== '' ? '(+84) ' . ltrim($buyerPhone, '0') : '';
                    ?>
                    <div id="buyerContactCard" class="d-flex align-items-center gap-3 rounded-3"
                         style="padding:14px 16px;border:1px solid <?php echo $missingInfo ? '#fed7aa' : '#e2e8f0'; ?>;background:<?php echo $missingInfo ? '#fff7ed' : '#f8fafc'; ?>;">
                        <!-- Icon định vị -->
                        <div style="width:44px;height:44px;border-radius:50%;background:<?php echo $missingInfo ? '#fed7aa' : '#dcfce7'; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa-solid fa-location-dot" style="font-size:18px;color:<?php echo $missingInfo ? '#f97316' : '#16a34a'; ?>;"></i>
                        </div>

                        <!-- Nội dung -->
                        <div class="flex-grow-1 min-w-0">
                            <?php if ($missingInfo): ?>
                            <div style="font-size:14px;color:#9a3412;font-weight:600;">
                                <?php if ($buyerPhone === ''): ?>Chưa có số điện thoại<?php endif; ?>
                                <?php if ($buyerPhone === '' && $buyerAddress === ''): ?> &amp; <?php endif; ?>
                                <?php if ($buyerAddress === ''): ?>Chưa có địa chỉ nhận xe<?php endif; ?>
                            </div>
                            <div style="font-size:12px;color:#c2410c;margin-top:3px;">Nhấn <strong>Chỉnh sửa</strong> để bổ sung thông tin trước khi thanh toán.</div>
                            <?php else: ?>
                            <!-- Row 1: Tên + SĐT -->
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <span class="fw-semibold text-dark" style="font-size:14px;"><?php echo htmlspecialchars($buyerName); ?></span>
                                <span style="color:#94a3b8;font-size:12px;"><?php echo htmlspecialchars($displayPhone); ?></span>
                            </div>
                            <!-- Row 2: Địa chỉ -->
                            <div style="font-size:13px;color:#475569;margin-top:3px;"><?php echo htmlspecialchars($buyerAddress); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Anchor cho JS scroll khi thiếu thông tin -->
                    <div id="phoneWarning"></div>
                    <div id="addressWarning"></div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold small text-dark">Ghi chú cho người bán <span class="text-muted fw-normal">(tuỳ chọn)</span></label>
                        <textarea name="buyer_note" rows="2" class="form-control" style="border-radius:10px;font-size:14px;resize:none;"></textarea>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4">Chọn phương thức thanh toán</h5>

                    <label class="d-block mb-2 position-relative">
                        <input type="radio" name="payment_method" value="vnpay" class="payment-option-input d-none" checked>
                        <div class="payment-option-card rounded-3 p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <img src="/spinbike/public/assets/images/vnpay-logo.jpg" height="36" alt="VNPAY" class="rounded-2">
                                <div>
                                    <div class="fw-bold text-dark">Thanh toán qua VNPAY</div>
                                    <div class="text-muted small">Thẻ ATM nội địa, thẻ quốc tế, QR ngân hàng</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-circle-check fs-4 check-icon"></i>
                        </div>
                    </label>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" value="" id="termsCheck">
                        <label class="form-check-label small text-muted ms-1" for="termsCheck">
                            Tôi đồng ý với <a href="<?= route_url('support.safe_trading') ?>" target="_blank" class="text-primary text-decoration-none">Chính sách mua bán an toàn</a> của SpinBike và xác nhận đã xem kỹ mô tả xe.
                        </label>
                    </div>
                    <p id="termsError" class="small text-danger mb-3 ms-4 ps-1" style="display:none;">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>Vui lòng đồng ý với điều khoản trước khi tiếp tục.
                    </p>

                    <button type="button" id="submitBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-5 shadow" onclick="showCheckoutConfirm()">
                        <i class="fa-solid fa-lock me-2"></i>Thanh toán <?php echo $formattedPrice; ?>
                    </button>
                    <p class="text-center text-muted small mt-3 mb-0"><i class="fa-solid fa-lock text-success me-1"></i> Thông tin thanh toán của bạn được mã hóa bảo mật tuyệt đối.</p>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="summary-card shadow-sm p-4">
                <h5 class="fw-bold mb-4">Thông tin xe</h5>
                
                <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                    <img src="<?php echo $mainImage; ?>" class="rounded-3 flex-shrink-0" style="width: 90px; height: 90px; object-fit: cover;">
                    <div class="d-flex flex-column justify-content-center min-w-0">
                        <h6 class="fw-bold text-dark mb-2" style="line-height: 1.4;"><?php echo htmlspecialchars($productTitle); ?></h6>
                        <p class="text-muted small mb-0">
                            <i class="fa-regular fa-user me-1"></i><?php echo htmlspecialchars($sellerName); ?>
                        </p>
                    </div>
                </div>

                <div class="row g-3 mb-4 pb-4 border-bottom small">
                    <div class="col-6">
                        <div class="text-muted mb-1">Thương hiệu</div>
                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($productBrand); ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted mb-1">Loại xe</div>
                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($productBikeType); ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted mb-1">Kích cỡ (Size)</div>
                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($productFrameSize); ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted mb-1">Tình trạng</div>
                        <div class="fw-semibold" style="color:#166534;"><?php echo htmlspecialchars($productCondition); ?> mới</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted mb-1">Khu vực giao dịch</div>
                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($productLocation); ?></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Giá xe</span>
                    <span class="fw-bold text-dark"><?php echo $formattedPrice; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                    <span class="text-muted">Phí xử lý giao dịch</span>
                    <span class="text-success fw-bold">Miễn phí</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-0">
                    <span class="fw-bold text-dark fs-5">Tổng cộng</span>
                    <span class="fw-bold fs-3 text-primary"><?php echo $formattedPrice; ?></span>
                </div>

            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div id="checkoutConfirmModal" class="modal hidden">
    <div class="modal-backdrop" onclick="hideCheckoutConfirm()"></div>
    <div class="modal-content detail-buy-modal" style="max-width: 500px;">
        <div class="detail-buy-modal-header">
            <h3 class="detail-buy-modal-title">Xác nhận thanh toán</h3>
            <button type="button" class="detail-buy-modal-close" onclick="hideCheckoutConfirm()">&times;</button>
        </div>
        <div class="p-4">
            <div class="d-flex gap-3 align-items-center mb-4 p-3 bg-light rounded-3">
                <img src="<?php echo htmlspecialchars($mainImage); ?>" class="rounded-2" style="width:64px;height:64px;object-fit:cover;flex-shrink:0;">
                <div>
                    <div class="fw-bold text-dark" style="font-size:15px;"><?php echo htmlspecialchars($productTitle); ?></div>
                    <div class="text-primary fw-bold fs-5 mt-1"><?php echo $formattedPrice; ?></div>
                </div>
            </div>
            <div class="d-flex align-items-start gap-2 mb-4 p-3 rounded-3" style="background:#ecfdf5;border:1px solid #bbf7d0;">
                <i class="fa-solid fa-shield-halved text-success mt-1"></i>
                <p class="mb-0 small text-success" style="line-height:1.6;">
                    Số tiền sẽ được <strong>SpinBike giữ an toàn</strong>. Người bán chỉ nhận tiền khi bạn xác nhận đã nhận xe đúng mô tả.
                </p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 flex-grow-1" onclick="hideCheckoutConfirm()">Quay lại</button>
                <button type="button" id="confirmPayBtn" class="btn btn-primary rounded-pill px-4 flex-grow-1 fw-bold" onclick="submitCheckout()">
                    Xác nhận thanh toán
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const buyerHasPhone   = <?php echo $buyerPhone !== '' ? 'true' : 'false'; ?>;
const buyerHasAddress = <?php echo $buyerAddress !== '' ? 'true' : 'false'; ?>;

function showCheckoutConfirm() {
    let blocked = false;

    if (!buyerHasPhone || !buyerHasAddress) {
        document.getElementById('buyerContactCard')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        blocked = true;
    }

    if (blocked) return;

    const terms = document.getElementById('termsCheck');
    const termsError = document.getElementById('termsError');
    if (!terms.checked) {
        termsError.style.display = 'block';
        terms.closest('.form-check').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    termsError.style.display = 'none';
    document.getElementById('checkoutConfirmModal').classList.remove('hidden');
}

document.getElementById('termsCheck').addEventListener('change', function () {
    document.getElementById('termsError').style.display = this.checked ? 'none' : 'block';
});

function hideCheckoutConfirm() {
    document.getElementById('checkoutConfirmModal').classList.add('hidden');
}

function submitCheckout() {
    const btn = document.getElementById('submitBtn');
    const confirmBtn = document.getElementById('confirmPayBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang chuyển hướng...';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang chuyển sang cổng thanh toán...';
    hideCheckoutConfirm();
    document.getElementById('checkoutForm').submit();
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
