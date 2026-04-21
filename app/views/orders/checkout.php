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

    /* Timeline Styles */
    .timeline-step { position: relative; padding-bottom: 1.5rem; }
    .timeline-step:last-child { padding-bottom: 0; }
    .timeline-icon {
        width: 36px; height: 36px;
        background: #f1f5f9; color: #475569;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; font-weight: bold; z-index: 2; position: relative;
    }
    .timeline-step:not(:last-child)::after {
        content: ''; position: absolute;
        left: 17px; top: 36px; bottom: 0;
        width: 2px; background: #e2e8f0; z-index: 1;
    }

    /* Escrow Banner */
    .escrow-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white; border-radius: 16px; position: relative; overflow: hidden;
    }
    .escrow-banner::before {
        content: ''; position: absolute; top: -50%; right: -10%;
        width: 200px; height: 200px; background: rgba(16, 185, 129, 0.2);
        filter: blur(40px); border-radius: 50%;
    }
    
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
    
    <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
        <a href="javascript:history.back()" class="text-muted text-decoration-none me-3 fs-5"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="fw-bold mb-0 text-dark">Thanh toán & Đặt hàng</h2>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            
 <div class="escrow-banner p-4 mb-4 shadow-sm">
                <div class="d-flex gap-3 align-items-start">
                    <div class="bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-shield-halved text-success fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-white mb-2">Giao dịch được bảo vệ 100%</h5>
                        <p class="text-white-50 mb-0 small" style="line-height: 1.6;">
                            SpinBike sẽ <b>giữ an toàn số tiền này</b>. Người bán chỉ nhận được tiền sau khi bạn xác nhận đã nhận xe, kiểm tra đúng mô tả và không có khiếu nại.
                        </p>
                    </div>
                </div>
            </div>

            <form action="<?php echo route_url('checkout.process'); ?>" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-wallet text-primary me-2"></i>Chọn phương thức thanh toán</h5>
                    
                    <label class="d-block mb-3 position-relative">
                        <input type="radio" name="payment_method" value="vnpay" class="payment-option-input d-none" checked>
                        <div class="payment-option-card rounded-3 p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418189687.png" height="32" alt="VNPay">
                                <div>
                                    <div class="fw-bold text-dark">Thẻ ATM / Internet Banking / VNPAY-QR</div>
                                    <div class="text-muted small">Thanh toán an toàn qua cổng VNPay</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-circle-check fs-4 check-icon"></i>
                        </div>
                    </label>

                    <label class="d-block mb-2 position-relative">
                        <input type="radio" name="payment_method" value="momo" class="payment-option-input d-none">
                        <div class="payment-option-card rounded-3 p-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" height="32" alt="MoMo">
                                <div>
                                    <div class="fw-bold text-dark">Ví điện tử MoMo</div>
                                    <div class="text-muted small">Quét mã QR qua ứng dụng MoMo</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-circle-check fs-4 check-icon"></i>
                        </div>
                    </label>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" value="" id="termsCheck" required checked>
                        <label class="form-check-label small text-muted ms-1" for="termsCheck">
                            Tôi đã xem kỹ tình trạng xe và đồng ý với <a href="<?= route_url('support.safe_trading') ?>" target="_blank" class="text-primary text-decoration-none">Chính sách mua bán an toàn</a> của SpinBike.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-5 shadow">
                        Thanh toán <?php echo $formattedPrice; ?>
                    </button>
                    <p class="text-center text-muted small mt-3 mb-0"><i class="fa-solid fa-lock text-success me-1"></i> Thông tin thanh toán của bạn được mã hóa bảo mật tuyệt đối.</p>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="summary-card shadow-sm p-4 sticky-top" style="top: 24px;">
                <h5 class="fw-bold mb-4">Thông tin xe</h5>
                
                <div class="d-flex gap-3 mb-4 pb-4 border-bottom">
                    <img src="<?php echo $mainImage; ?>" class="rounded-3" style="width: 90px; height: 90px; object-fit: cover;">
                    <div class="d-flex flex-column justify-content-center">
                        <h6 class="fw-bold text-dark mb-2" style="line-height: 1.4;"><?php echo htmlspecialchars($productTitle); ?></h6>
                        <div class="d-inline-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border text-truncate" style="max-width: 150px;"><i class="fa-solid fa-user text-muted me-1"></i> <?php echo htmlspecialchars($sellerName); ?></span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"><?php echo htmlspecialchars($productCondition); ?> mới</span>
                        </div>
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
                        <div class="text-muted mb-1">Khu vực giao dịch</div>
                        <div class="fw-semibold text-dark text-truncate"><?php echo htmlspecialchars($productLocation); ?></div>
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
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark fs-5">Tổng cộng</span>
                    <span class="fw-bold fs-3 text-primary"><?php echo $formattedPrice; ?></span>
                </div>

                <div class="bg-light rounded-3 p-3 mt-2 border">
                    <h6 class="fw-bold text-dark mb-3 text-center" style="font-size: 0.9rem;">Hành trình đơn hàng của bạn</h6>
                    <div class="timeline-step d-flex gap-3">
                        <div class="timeline-icon text-primary bg-primary bg-opacity-10"><i class="fa-solid fa-check"></i></div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">Bạn thanh toán</div>
                            <div class="text-muted" style="font-size: 0.75rem;">SpinBike giữ tiền an toàn</div>
                        </div>
                    </div>
                    <div class="timeline-step d-flex gap-3">
                        <div class="timeline-icon text-muted"><i class="fa-solid fa-truck"></i></div>
                        <div>
                            <div class="fw-bold text-muted" style="font-size: 0.85rem;">Người bán giao xe</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Bạn nhận và kiểm tra thực tế</div>
                        </div>
                    </div>
                    <div class="timeline-step d-flex gap-3">
                        <div class="timeline-icon text-muted"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                        <div>
                            <div class="fw-bold text-muted" style="font-size: 0.85rem;">SpinBike giải ngân</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Chỉ khi bạn bấm xác nhận OK</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>