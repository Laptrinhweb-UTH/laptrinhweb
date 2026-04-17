<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('app/views/auth/auth.php'));
    exit;
}

function format_order_status_label(string $status): string {
    return match ($status) {
        'pending' => 'Chờ thanh toán',
        'paid' => 'Đã thanh toán',
        'shipping' => 'Đang giao xe',
        'completed' => 'Hoàn tất',
        'cancelled' => 'Đã hủy',
        default => 'Đang cập nhật',
    };
}

function format_escrow_status_label(string $status): string {
    return match ($status) {
        'holding' => 'Đang giữ tiền',
        'released' => 'Đã giải phóng',
        'refunded' => 'Đã hoàn tiền',
        'disputed' => 'Đang khiếu nại',
        default => 'Đang cập nhật',
    };
}

function timeline_step_state(int $stepIndex, int $currentStep, bool $isCancelled): string {
    if ($isCancelled) {
        return $stepIndex <= 1 ? 'active' : '';
    }

    if ($stepIndex < $currentStep) {
        return 'active';
    }

    if ($stepIndex === $currentStep) {
        return 'current';
    }

    return '';
}

$currentUserId = (int) $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'] ?? 'user';
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$orderError = null;
$order = null;
$orderNotice = trim((string) ($_GET['message'] ?? ''));
$orderNoticeStatus = $_GET['status'] ?? '';
$noticeClass = $orderNoticeStatus === 'success' ? 'auth-message auth-message-success' : 'auth-message auth-message-error';

if ($orderId === false || $orderId === null) {
    $orderError = 'Không tìm thấy đơn hàng hợp lệ để hiển thị.';
}

$database = new Database();
$db = $orderError === null ? $database->getConnectionOrNull() : null;

if ($orderError === null && !$db) {
    $orderError = 'Chi tiết đơn hàng hiện chưa thể tải vì kết nối dữ liệu đang gặp sự cố.';
}

if ($orderError === null) {
    try {
        $query = "
            SELECT
                o.id,
                o.buyer_id,
                o.seller_id,
                o.product_id,
                o.amount,
                o.status AS order_status,
                o.created_at AS order_created_at,
                e.status AS escrow_status,
                e.amount AS escrow_amount,
                e.created_at AS escrow_created_at,
                e.released_at,
                p.title AS product_title,
                p.brand AS product_brand,
                p.location AS product_location,
                buyer.name AS buyer_name,
                buyer.email AS buyer_email,
                buyer.phone AS buyer_phone,
                seller.name AS seller_name,
                seller.email AS seller_email,
                seller.phone AS seller_phone,
                (
                    SELECT image_url
                    FROM product_images
                    WHERE product_id = o.product_id
                    ORDER BY id ASC
                    LIMIT 1
                ) AS product_image
            FROM orders o
            LEFT JOIN escrows e ON e.order_id = o.id
            LEFT JOIN products p ON p.id = o.product_id
            LEFT JOIN users buyer ON buyer.id = o.buyer_id
            LEFT JOIN users seller ON seller.id = o.seller_id
            WHERE o.id = ?
            LIMIT 1
        ";

        $stmt = $db->prepare($query);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $orderError = 'Không tìm thấy đơn hàng bạn đang tìm hoặc dữ liệu đã thay đổi.';
        } else {
            $canViewOrder = $currentUserRole === 'admin'
                || (int) $order['buyer_id'] === $currentUserId
                || (int) $order['seller_id'] === $currentUserId;

            if (!$canViewOrder) {
                $orderError = 'Bạn không có quyền xem chi tiết đơn hàng này.';
                $order = null;
            }
        }
    } catch (Throwable $exception) {
        $orderError = 'Chi tiết đơn hàng hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$orderStatus = $order['order_status'] ?? 'pending';
$escrowStatus = $order['escrow_status'] ?? 'holding';
$productTitle = trim((string) ($order['product_title'] ?? ''));
if ($productTitle === '') {
    $productTitle = 'Xe đạp đang cập nhật tên';
}

$productBrand = trim((string) ($order['product_brand'] ?? ''));
if ($productBrand === '') {
    $productBrand = 'Đang cập nhật hãng xe';
}

$productLocation = trim((string) ($order['product_location'] ?? ''));
if ($productLocation === '') {
    $productLocation = 'Đang cập nhật vị trí';
}

$productImage = $order['product_image'] ?? 'https://via.placeholder.com/120x120?text=SpinBike';
$buyerName = trim((string) ($order['buyer_name'] ?? ''));
if ($buyerName === '') {
    $buyerName = 'Người mua đang cập nhật';
}

$sellerName = trim((string) ($order['seller_name'] ?? ''));
if ($sellerName === '') {
    $sellerName = 'Người bán đang cập nhật';
}

$buyerPhone = trim((string) ($order['buyer_phone'] ?? ''));
if ($buyerPhone === '') {
    $buyerPhone = 'Chưa cập nhật số điện thoại';
}

$sellerPhone = trim((string) ($order['seller_phone'] ?? ''));
if ($sellerPhone === '') {
    $sellerPhone = 'Chưa cập nhật số điện thoại';
}

$buyerEmail = trim((string) ($order['buyer_email'] ?? ''));
if ($buyerEmail === '') {
    $buyerEmail = 'Chưa cập nhật email';
}

$sellerEmail = trim((string) ($order['seller_email'] ?? ''));
if ($sellerEmail === '') {
    $sellerEmail = 'Chưa cập nhật email';
}

$paidAmount = is_numeric($order['amount'] ?? null) ? (float) $order['amount'] : 0.0;
$feeAmount = $paidAmount * 0.05;
$sellerReceives = max($paidAmount - $feeAmount, 0);
$formattedPaidAmount = number_format($paidAmount, 0, ',', '.') . ' đ';
$formattedFeeAmount = number_format($feeAmount, 0, ',', '.') . ' đ';
$formattedSellerReceives = number_format($sellerReceives, 0, ',', '.') . ' đ';
$formattedOrderDate = !empty($order['order_created_at']) ? date('d/m/Y H:i', strtotime((string) $order['order_created_at'])) : 'Đang cập nhật';
$formattedReleaseDate = !empty($order['released_at']) ? date('d/m/Y H:i', strtotime((string) $order['released_at'])) : 'Chưa giải phóng';

$statusBadgeClass = match ($orderStatus) {
    'completed' => 'bg-success',
    'shipping', 'paid' => 'bg-primary',
    'cancelled' => 'bg-danger',
    default => 'bg-secondary',
};

$escrowBadgeClass = match ($escrowStatus) {
    'released' => 'bg-success',
    'holding' => 'bg-warning text-dark',
    'refunded', 'disputed' => 'bg-danger',
    default => 'bg-secondary',
};

$timelineCurrentStep = match ($orderStatus) {
    'pending' => 0,
    'paid' => 1,
    'shipping' => 2,
    'completed' => 3,
    default => 1,
};

$isCancelledOrder = $orderStatus === 'cancelled' || $escrowStatus === 'refunded';
$isBuyerView = $order !== null && (int) $order['buyer_id'] === $currentUserId;
$canConfirmReceipt = $isBuyerView && in_array($orderStatus, ['paid', 'shipping'], true) && $escrowStatus === 'holding';

include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5" style="max-width: 960px;">
    <?php if ($orderNotice !== ''): ?>
    <div class="<?php echo $noticeClass; ?>">
        <?php echo htmlspecialchars($orderNotice); ?>
    </div>
    <?php endif; ?>

    <?php if ($orderError !== null): ?>
    <div class="empty-state-card">
        <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
        <p class="empty-state-text"><?php echo htmlspecialchars($orderError); ?></p>
        <a href="<?php echo asset_url('index.php'); ?>" class="btn-detail product-detail-link">Quay lại trang chủ</a>
    </div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold m-0">Chi tiết đơn hàng #<?php echo (int) $order['id']; ?></h3>
            <p class="text-muted mb-0">Tạo lúc <?php echo htmlspecialchars($formattedOrderDate); ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge <?php echo $statusBadgeClass; ?> text-white p-2 px-3 rounded-pill fs-6">
                <?php echo htmlspecialchars(format_order_status_label($orderStatus)); ?>
            </span>
            <span class="badge <?php echo $escrowBadgeClass; ?> p-2 px-3 rounded-pill fs-6">
                <?php echo htmlspecialchars(format_escrow_status_label($escrowStatus)); ?>
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
        <div class="order-timeline">
            <?php
            $timelineSteps = [
                ['icon' => 'fa-solid fa-check', 'text' => 'Đã đặt hàng'],
                ['icon' => 'fa-solid fa-wallet', 'text' => 'Đã thanh toán'],
                ['icon' => 'fa-solid fa-truck-fast', 'text' => 'Đang giao xe'],
                ['icon' => 'fa-solid fa-box-open', 'text' => 'Hoàn tất'],
            ];
            ?>
            <?php foreach ($timelineSteps as $stepIndex => $step): ?>
                <?php $stepClass = timeline_step_state($stepIndex, $timelineCurrentStep, $isCancelledOrder); ?>
                <div class="timeline-step <?php echo $stepClass; ?>">
                    <div class="timeline-icon"><i class="<?php echo $step['icon']; ?>"></i></div>
                    <div class="timeline-text"><?php echo $step['text']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($isCancelledOrder): ?>
        <p class="text-danger fw-semibold mb-0 text-center">Đơn hàng này đã bị hủy hoặc hoàn tiền. Dòng thời gian được dừng tại bước thanh toán.</p>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <div class="escrow-icon-wrapper flex-shrink-0">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-primary mb-1">SpinBike đang quản lý khoản tiền <?php echo htmlspecialchars($formattedPaidAmount); ?></h5>
                    <p class="text-muted mb-3" style="font-size: 14px;">
                        <?php if ($escrowStatus === 'holding'): ?>
                        Khoản tiền này sẽ được giữ an toàn cho đến khi người mua xác nhận đã nhận xe đúng mô tả.
                        <?php elseif ($escrowStatus === 'released'): ?>
                        Khoản tiền này đã được giải phóng cho người bán vào <?php echo htmlspecialchars($formattedReleaseDate); ?>.
                        <?php elseif ($escrowStatus === 'refunded'): ?>
                        Khoản tiền này đã được hoàn về cho người mua.
                        <?php else: ?>
                        Khoản tiền này đang được hệ thống theo dõi và xử lý.
                        <?php endif; ?>
                    </p>

                    <div class="bg-white p-3 rounded-3 border">
                        <div class="d-flex justify-content-between mb-2" style="font-size: 14px;">
                            <span class="text-muted">Tổng tiền đơn hàng</span>
                            <span class="fw-bold"><?php echo htmlspecialchars($formattedPaidAmount); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" style="font-size: 14px;">
                            <span class="text-danger">Phí nền tảng (5%)</span>
                            <span>- <?php echo htmlspecialchars($formattedFeeAmount); ?></span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between" style="font-size: 15px;">
                            <span class="text-success fw-bold">Người bán dự kiến nhận</span>
                            <span class="fw-bold text-success"><?php echo htmlspecialchars($formattedSellerReceives); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Sản phẩm trong đơn</h5>
                    <div class="d-flex gap-3">
                        <img src="<?php echo htmlspecialchars($productImage); ?>" alt="Sản phẩm" class="rounded-3" style="width: 120px; height: 120px; object-fit: cover; border: 1px solid var(--border);">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($productTitle); ?></h6>
                            <div class="text-muted mb-2"><?php echo htmlspecialchars($productBrand); ?></div>
                            <div class="text-muted mb-2"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($productLocation); ?></div>
                            <div class="fw-bold text-primary fs-5"><?php echo htmlspecialchars($formattedPaidAmount); ?></div>
                            <a href="<?php echo asset_url('detail.php'); ?>?id=<?php echo (int) $order['product_id']; ?>" class="btn-detail product-detail-link mt-3">Xem lại sản phẩm</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Thông tin giao dịch</h5>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Người mua</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($buyerName); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($buyerEmail); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($buyerPhone); ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Người bán</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($sellerName); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($sellerEmail); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($sellerPhone); ?></div>
                    </div>
                    <div class="pt-3 border-top">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Mã đơn hàng</span>
                            <span class="fw-semibold">#<?php echo (int) $order['id']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Mã sản phẩm</span>
                            <span class="fw-semibold">#<?php echo (int) $order['product_id']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Ngày giải phóng</span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($formattedReleaseDate); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-3 mb-5 flex-wrap">
        <?php if ($canConfirmReceipt): ?>
        <button class="btn btn-success flex-grow-1 py-3 rounded-3 fw-bold fs-6 shadow-sm" disabled>
            <i class="fa-solid fa-check-circle me-2"></i> Tôi đã nhận được xe
        </button>
        <?php else: ?>
        <button class="btn btn-outline-secondary flex-grow-1 py-3 rounded-3 fw-bold fs-6 shadow-sm" disabled>
            <i class="fa-solid fa-check-circle me-2"></i> Chưa có thao tác xác nhận ở trạng thái này
        </button>
        <?php endif; ?>

        <button class="btn btn-outline-danger py-3 px-4 rounded-3 fw-bold shadow-sm" disabled>
            <i class="fa-solid fa-triangle-exclamation"></i> Khiếu nại sẽ mở ở phase tiếp theo
        </button>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
