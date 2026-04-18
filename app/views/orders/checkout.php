<?php 
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../models/Product.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . app_url('app/views/auth/auth.php'));
    exit;
}

// 2. Kiểm tra ID sản phẩm truyền vào
$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$checkoutError = null;
$product = null;
$checkoutStatus = $_GET['status'] ?? '';
$checkoutMessage = trim((string)($_GET['message'] ?? ''));
$checkoutNoticeClass = $checkoutStatus === 'success' ? 'auth-message auth-message-success' : 'auth-message auth-message-error';

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

<div class="container py-5" style="max-width: 1000px;">
    <?php if ($checkoutMessage !== ''): ?>
    <div class="<?php echo $checkoutNoticeClass; ?>">
        <?php echo htmlspecialchars($checkoutMessage); ?>
    </div>
    <?php endif; ?>

    <?php if ($checkoutError !== null): ?>
    <div class="empty-state-card">
        <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
        <p class="empty-state-text"><?php echo htmlspecialchars($checkoutError); ?></p>
        <a href="<?php echo asset_url('index.php'); ?>" class="btn-detail product-detail-link">Quay lại trang chủ</a>
    </div>
    <?php else: ?>
    <h2 class="fw-bold mb-4">Đặt mua và thanh toán an toàn</h2>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="escrow-card mb-4">
                <div class="d-flex gap-3">
                    <div class="escrow-icon-wrapper flex-shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-success mb-2">Được bảo vệ bởi SpinBike Escrow</h5>
                        <p class="text-muted mb-0" style="font-size: 14px; line-height: 1.6;">
                            Sau khi bạn xác nhận đặt mua và thanh toán, SpinBike sẽ tạo đơn hàng và giữ tiền an toàn. Người bán <b>chỉ nhận được tiền</b> sau khi bạn đã nhận xe, kiểm tra đúng mô tả và nhấn xác nhận.
                        </p>
                    </div>
                </div>
            </div>

            <div class="profile-card mb-4">
                <h5 class="fw-bold mb-3">Quy trình giao dịch</h5>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <div class="badge bg-dark rounded-pill px-3 py-2">1</div>
                        <div>
                            <div class="fw-semibold">Đặt mua chiếc xe này</div>
                            <div class="text-muted small">Bạn xác nhận thông tin xe, giá bán và người bán trước khi thanh toán.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="badge bg-dark rounded-pill px-3 py-2">2</div>
                        <div>
                            <div class="fw-semibold">Thanh toán an toàn qua hệ thống</div>
                            <div class="text-muted small">Đơn hàng được tạo và tiền chuyển sang trạng thái chờ giữ an toàn.</div>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="badge bg-dark rounded-pill px-3 py-2">3</div>
                        <div>
                            <div class="fw-semibold">Hệ thống giữ tiền cho đến khi bạn hài lòng</div>
                            <div class="text-muted small">Người bán giao xe, bạn kiểm tra và xác nhận hoặc gửi khiếu nại nếu có vấn đề.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h5 class="fw-bold mb-2">Xác nhận đặt mua</h5>
                <p class="text-muted mb-4">Bạn đang ở bước cuối để tạo đơn hàng an toàn cho chiếc xe này.</p>
                
                <form action="process_checkout.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="alert alert-light border rounded-4 mb-4">
                        <div class="fw-semibold mb-2">Tôi xác nhận rằng:</div>
                        <ul class="mb-0 ps-3 small text-muted">
                            <li>Tôi đã xem kỹ thông tin chiếc xe và giá bán.</li>
                            <li>Tôi hiểu rằng SpinBike sẽ giữ tiền cho đến khi tôi xác nhận nhận xe.</li>
                            <li>Nếu xe có vấn đề, tôi có thể gửi khiếu nại để hệ thống tiếp tục khóa tiền.</li>
                        </ul>
                    </div>
                    
                    <label class="d-block mb-3">
                        <input type="radio" name="payment_method" value="vnpay" class="form-check-input d-none" checked>
                        <div class="payment-method-card d-flex align-items-center gap-3">
                            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418189687.png" height="30" alt="VNPay">
                            <span class="fw-bold text-dark">Thanh toán qua VNPay</span>
                        </div>
                    </label>

                    <label class="d-block mb-4">
                        <input type="radio" name="payment_method" value="momo" class="form-check-input d-none">
                        <div class="payment-method-card d-flex align-items-center gap-3">
                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" height="30" alt="MoMo">
                            <span class="fw-bold text-dark">Thanh toán qua Ví MoMo</span>
                        </div>
                    </label>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-5 shadow-sm">
                        <i class="fa-solid fa-lock"></i> Đặt mua và thanh toán <?php echo $formattedPrice; ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 p-4 sticky-top" style="top: 20px;">
                <h5 class="fw-bold mb-4">Tóm tắt yêu cầu mua</h5>
                <div class="d-flex gap-3 mb-4">
                    <img src="<?php echo $mainImage; ?>" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid var(--border);">
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="line-height: 1.4;"><?php echo htmlspecialchars($productTitle); ?></h6>
                        <span class="badge bg-light text-dark border mt-1"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($sellerName); ?></span>
                    </div>
                </div>
                <div class="small text-muted mb-2"><strong>Hãng xe:</strong> <?php echo htmlspecialchars($productBrand); ?></div>
                <div class="small text-muted mb-2"><strong>Loại xe:</strong> <?php echo htmlspecialchars($productBikeType); ?></div>
                <div class="small text-muted mb-2"><strong>Size khung:</strong> <?php echo htmlspecialchars($productFrameSize); ?></div>
                <div class="small text-muted mb-2"><strong>Độ mới:</strong> <?php echo htmlspecialchars($productCondition); ?></div>
                <div class="small text-muted mb-4"><strong>Khu vực giao dịch:</strong> <?php echo htmlspecialchars($productLocation); ?></div>
                <hr class="text-muted">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tạm tính</span>
                    <span class="fw-bold"><?php echo $formattedPrice; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Phí giữ tiền an toàn</span>
                    <span class="text-success fw-bold">Miễn phí cho người mua</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="fw-bold text-dark">Tổng thanh toán</span>
                    <span class="fw-bold fs-4 text-primary"><?php echo $formattedPrice; ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
