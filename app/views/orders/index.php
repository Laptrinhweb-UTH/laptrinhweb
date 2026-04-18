<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('app/views/auth/auth.php'));
    exit;
}

function order_list_status_label(string $status): string {
    return ProjectFlow::orderLabel($status);
}

function order_list_escrow_label(string $status): string {
    return ProjectFlow::escrowLabel($status);
}

function order_list_badge_class(string $status): string {
    return ProjectFlow::orderBadgeClass($status);
}

function order_list_status_guide(string $orderStatus, string $escrowStatus, string $view): string {
    return ProjectFlow::orderListGuide($orderStatus, $escrowStatus, $view);
}

$currentUserId = (int) $_SESSION['user_id'];
$view = $_GET['view'] ?? 'buyer';
if (!in_array($view, ['buyer', 'seller'], true)) {
    $view = 'buyer';
}
$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'disputed', 'refunded'], true)) {
    $filter = 'all';
}

$database = new Database();
$db = $database->getConnectionOrNull();
$pageError = null;
$orders = [];
$summary = [
    'total' => 0,
    'holding' => 0,
    'completed' => 0,
    'cancelled' => 0,
];

if (!$db) {
    $pageError = 'Danh sách đơn hàng hiện chưa thể tải vì kết nối dữ liệu đang gặp sự cố.';
} else {
    try {
        $filterColumn = $view === 'seller' ? 'o.seller_id' : 'o.buyer_id';
        $extraCondition = '';
        if ($filter === 'disputed') {
            $extraCondition = " AND e.status = '" . ProjectFlow::ESCROW_DISPUTED . "'";
        } elseif ($filter === 'refunded') {
            $extraCondition = " AND e.status = '" . ProjectFlow::ESCROW_REFUNDED . "'";
        }
        $query = "
            SELECT
                o.id,
                o.product_id,
                o.amount,
                o.status AS order_status,
                o.created_at AS order_created_at,
                e.status AS escrow_status,
                p.title AS product_title,
                p.brand AS product_brand,
                buyer.name AS buyer_name,
                seller.name AS seller_name,
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
            WHERE {$filterColumn} = ?
            {$extraCondition}
            ORDER BY o.created_at DESC, o.id DESC
        ";

        $stmt = $db->prepare($query);
        $stmt->execute([$currentUserId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as $order) {
            $summary['total']++;
            if (($order['escrow_status'] ?? '') === ProjectFlow::ESCROW_HOLDING) {
                $summary['holding']++;
            }
            if (($order['order_status'] ?? '') === ProjectFlow::ORDER_COMPLETED) {
                $summary['completed']++;
            }
            if (
                in_array(($order['order_status'] ?? ''), [ProjectFlow::ORDER_CANCELLED], true)
                || in_array(($order['escrow_status'] ?? ''), [ProjectFlow::ESCROW_REFUNDED, ProjectFlow::ESCROW_DISPUTED], true)
            ) {
                $summary['cancelled']++;
            }
        }
    } catch (Throwable $exception) {
        $pageError = 'Danh sách đơn hàng hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$pageTitle = $view === 'seller' ? 'Quản lý bán hàng' : 'Đơn hàng mua';
$pageSubtitle = $view === 'seller'
    ? 'Theo dõi các đơn hàng mà bạn đang bán và trạng thái giải phóng tiền.'
    : 'Theo dõi các đơn hàng bạn đã mua và tiến trình giữ tiền an toàn.';
$filterLabel = match ($filter) {
    'disputed' => 'đơn đang tranh chấp',
    'refunded' => 'đơn đã hoàn tiền',
    default => 'tất cả đơn hàng',
};
$otherView = $view === 'seller' ? 'buyer' : 'seller';
$otherViewLabel = $view === 'seller' ? 'Xem đơn hàng mua' : 'Xem quản lý bán hàng';
$profilePageUrl = app_url('app/views/auth/profile.php');
$buyerOrdersUrl = app_url('app/views/orders/index.php') . '?view=buyer';
$sellerOrdersUrl = app_url('app/views/orders/index.php') . '?view=seller';
$disputedOrdersUrl = app_url('app/views/orders/index.php') . '?view=' . urlencode($view) . '&filter=disputed';
$refundedOrdersUrl = app_url('app/views/orders/index.php') . '?view=' . urlencode($view) . '&filter=refunded';
$allOrdersUrl = app_url('app/views/orders/index.php') . '?view=' . urlencode($view) . '&filter=all';

include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5" style="max-width: 1100px;">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div>
            <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($pageSubtitle); ?></p>
            <p class="order-filter-subtitle mb-0">Đang xem: <?php echo htmlspecialchars($filterLabel); ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo $profilePageUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="fa-regular fa-user me-2"></i>Hồ sơ của tôi
            </a>
            <a href="<?php echo $buyerOrdersUrl; ?>" class="btn <?php echo $view === 'buyer' ? 'btn-success' : 'btn-outline-success'; ?> rounded-pill px-4">
                <i class="fa-solid fa-box me-2"></i>Đơn hàng mua
            </a>
            <a href="<?php echo $sellerOrdersUrl; ?>" class="btn <?php echo $view === 'seller' ? 'btn-success' : 'btn-outline-success'; ?> rounded-pill px-4">
                <i class="fa-solid fa-shop me-2"></i>Quản lý bán hàng
            </a>
        </div>
    </div>

    <div class="order-filter-bar mb-4">
        <a href="<?php echo $allOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'all' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-list-ul"></i> Tất cả
        </a>
        <a href="<?php echo $disputedOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'disputed' ? 'is-active is-danger' : ''; ?>">
            <i class="fa-solid fa-triangle-exclamation"></i> Đang tranh chấp
        </a>
        <a href="<?php echo $refundedOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'refunded' ? 'is-active is-success' : ''; ?>">
            <i class="fa-solid fa-rotate-left"></i> Đã hoàn tiền
        </a>
    </div>

    <?php if ($pageError !== null): ?>
    <div class="empty-state-card">
        <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
        <p class="empty-state-text"><?php echo htmlspecialchars($pageError); ?></p>
        <a href="<?php echo asset_url('index.php'); ?>" class="btn-detail product-detail-link">Quay lại trang chủ</a>
    </div>
    <?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="profile-card order-summary-card">
                <div class="order-summary-label">Tổng đơn</div>
                <div class="order-summary-value"><?php echo (int) $summary['total']; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="profile-card order-summary-card">
                <div class="order-summary-label">Đang giữ tiền</div>
                <div class="order-summary-value"><?php echo (int) $summary['holding']; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="profile-card order-summary-card">
                <div class="order-summary-label">Hoàn tất</div>
                <div class="order-summary-value"><?php echo (int) $summary['completed']; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="profile-card order-summary-card">
                <div class="order-summary-label">Có vấn đề / đã hủy</div>
                <div class="order-summary-value"><?php echo (int) $summary['cancelled']; ?></div>
            </div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
    <div class="empty-state-card">
        <i class="fa-solid fa-box-open empty-state-icon"></i>
        <p class="empty-state-text">
            <?php echo $view === 'seller'
                ? ($filter === 'all'
                    ? 'Bạn chưa có đơn hàng bán nào. Khi có người mua, đơn hàng sẽ xuất hiện tại đây.'
                    : 'Hiện chưa có đơn bán nào khớp với bộ lọc bạn đang xem.')
                : ($filter === 'all'
                    ? 'Bạn chưa có đơn hàng mua nào. Khi hoàn tất thanh toán, đơn hàng sẽ xuất hiện tại đây.'
                    : 'Hiện chưa có đơn mua nào khớp với bộ lọc bạn đang xem.'); ?>
        </p>
        <a href="<?php echo $filter === 'all' ? app_url('app/views/orders/index.php') . '?view=' . $otherView : $allOrdersUrl; ?>" class="btn-detail product-detail-link">
            <?php echo htmlspecialchars($filter === 'all' ? $otherViewLabel : 'Xem tất cả đơn hàng'); ?>
        </a>
    </div>
    <?php else: ?>
    <div class="order-list">
        <?php foreach ($orders as $order): ?>
            <?php
            $productTitle = trim((string) ($order['product_title'] ?? ''));
            if ($productTitle === '') {
                $productTitle = 'Xe đạp đang cập nhật tên';
            }

            $productBrand = trim((string) ($order['product_brand'] ?? ''));
            if ($productBrand === '') {
                $productBrand = 'Đang cập nhật hãng xe';
            }

            $counterpartName = $view === 'seller'
                ? trim((string) ($order['buyer_name'] ?? ''))
                : trim((string) ($order['seller_name'] ?? ''));
            if ($counterpartName === '') {
                $counterpartName = $view === 'seller' ? 'Người mua đang cập nhật' : 'Người bán đang cập nhật';
            }

            $formattedAmount = is_numeric($order['amount'] ?? null)
                ? number_format((float) $order['amount'], 0, ',', '.') . ' đ'
                : 'Đang cập nhật';
            $formattedCreatedAt = !empty($order['order_created_at'])
                ? date('d/m/Y H:i', strtotime((string) $order['order_created_at']))
                : 'Đang cập nhật';
            $productImage = $order['product_image'] ?? 'https://via.placeholder.com/96x96?text=SpinBike';
            $statusGuide = order_list_status_guide(
                (string) ($order['order_status'] ?? ''),
                (string) ($order['escrow_status'] ?? ''),
                $view
            );
            $statusGuideClass = match ((string) ($order['escrow_status'] ?? '')) {
                'disputed' => 'order-list-guide is-danger',
                'refunded' => 'order-list-guide is-success',
                default => 'order-list-guide',
            };
            ?>

            <div class="profile-card order-list-card">
                <div class="d-flex gap-3 flex-wrap">
                    <img src="<?php echo htmlspecialchars($productImage); ?>" alt="Sản phẩm" class="order-list-image">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                            <div>
                                <div class="order-list-id">Đơn hàng #<?php echo (int) $order['id']; ?></div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($productTitle); ?></h5>
                                <div class="text-muted"><?php echo htmlspecialchars($productBrand); ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary fs-5"><?php echo htmlspecialchars($formattedAmount); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($formattedCreatedAt); ?></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <div class="order-status-group">
                                <span class="order-status-label">Đơn hàng</span>
                                <span class="badge <?php echo order_list_badge_class((string) ($order['order_status'] ?? '')); ?> rounded-pill px-3 py-2">
                                    <?php echo htmlspecialchars(order_list_status_label((string) ($order['order_status'] ?? ''))); ?>
                                </span>
                            </div>
                            <div class="order-status-group">
                                <span class="order-status-label">Giữ tiền</span>
                                <span class="badge <?php echo order_list_badge_class((string) ($order['escrow_status'] ?? '')); ?> rounded-pill px-3 py-2">
                                    <?php echo htmlspecialchars(order_list_escrow_label((string) ($order['escrow_status'] ?? ''))); ?>
                                </span>
                            </div>
                        </div>

                        <p class="<?php echo $statusGuideClass; ?> mb-3"><?php echo htmlspecialchars($statusGuide); ?></p>

                        <div class="order-list-meta">
                            <span>
                                <i class="fa-solid fa-user"></i>
                                <?php echo $view === 'seller' ? 'Người mua: ' : 'Người bán: '; ?>
                                <?php echo htmlspecialchars($counterpartName); ?>
                            </span>
                            <span>
                                <i class="fa-solid fa-hashtag"></i>
                                Sản phẩm #<?php echo (int) $order['product_id']; ?>
                            </span>
                        </div>

                        <div class="mt-3">
                            <a href="<?php echo app_url('app/views/orders/detail.php'); ?>?id=<?php echo (int) $order['id']; ?>" class="btn-detail product-detail-link order-list-action">
                                Xem chi tiết đơn hàng
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
