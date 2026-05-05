<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . route_url('auth'));
    exit;
}

$currentUserId = (int) $_SESSION['user_id'];
$view = 'seller';

$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'disputed', 'refunded'], true)) {
    $filter = 'all';
}

$database = new Database();
$db = $database->getConnectionOrNull();
$pageError = null;
$orders = [];
$summary = ['total' => 0, 'holding' => 0, 'completed' => 0, 'cancelled' => 0];

if (!$db) {
    $pageError = 'Danh sách đơn hàng hiện chưa thể tải vì kết nối dữ liệu đang gặp sự cố.';
} else {
    try {
        $extraCondition = match ($filter) {
            'disputed' => " AND e.status = '" . ProjectFlow::ESCROW_DISPUTED . "'",
            'refunded'  => " AND e.status = '" . ProjectFlow::ESCROW_REFUNDED . "'",
            default     => '',
        };

        $stmt = $db->prepare("
            SELECT
                o.id, o.product_id, o.amount,
                o.status AS order_status,
                o.created_at AS order_created_at,
                e.status AS escrow_status,
                p.title AS product_title,
                p.brand AS product_brand,
                buyer.name AS buyer_name,
                (SELECT image_url FROM product_images WHERE product_id = o.product_id ORDER BY id ASC LIMIT 1) AS product_image
            FROM orders o
            LEFT JOIN escrows e ON e.order_id = o.id
            LEFT JOIN products p ON p.id = o.product_id
            LEFT JOIN users buyer ON buyer.id = o.buyer_id
            WHERE o.seller_id = ?
            {$extraCondition}
            ORDER BY o.created_at DESC, o.id DESC
        ");
        $stmt->execute([$currentUserId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($orders as $order) {
            $summary['total']++;
            if (($order['escrow_status'] ?? '') === ProjectFlow::ESCROW_HOLDING) $summary['holding']++;
            if (($order['order_status'] ?? '') === ProjectFlow::ORDER_COMPLETED)  $summary['completed']++;
            if (
                in_array($order['order_status'] ?? '', [ProjectFlow::ORDER_CANCELLED], true) ||
                in_array($order['escrow_status'] ?? '', [ProjectFlow::ESCROW_REFUNDED, ProjectFlow::ESCROW_DISPUTED], true)
            ) $summary['cancelled']++;
        }
    } catch (Throwable $e) {
        $pageError = 'Danh sách đơn hàng hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$filterLabel = match ($filter) {
    'disputed' => 'đơn đang tranh chấp',
    'refunded'  => 'đơn đã hoàn tiền',
    default     => 'tất cả đơn hàng',
};

$allUrl      = route_url('orders.selling');
$disputedUrl = route_url('orders.selling', ['filter' => 'disputed']);
$refundedUrl = route_url('orders.selling', ['filter' => 'refunded']);
$buyingUrl   = route_url('orders.buying');

include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5" style="max-width: 1100px;">

    <!-- Page header -->
    <div class="orders-page-header">
        <div>
            <h2 class="fw-bold mb-1">Quản lý bán hàng</h2>
            <p class="text-muted mb-0">Theo dõi các đơn hàng người mua đã đặt, quá trình tiếp nhận và giao xe.</p>
            <p class="order-filter-subtitle mb-0">Đang xem: <?php echo htmlspecialchars($filterLabel); ?></p>
        </div>
        <a href="<?php echo $buyingUrl; ?>" class="orders-switch-btn">
            <i class="fa-solid fa-box"></i> Đơn hàng mua
        </a>
    </div>

    <!-- Filter chips -->
    <div class="order-filter-bar mb-4">
        <a href="<?php echo $allUrl; ?>" class="order-filter-chip <?php echo $filter === 'all' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-list-ul"></i> Tất cả
        </a>
        <a href="<?php echo $disputedUrl; ?>" class="order-filter-chip <?php echo $filter === 'disputed' ? 'is-active is-danger' : ''; ?>">
            <i class="fa-solid fa-triangle-exclamation"></i> Đang tranh chấp
        </a>
        <a href="<?php echo $refundedUrl; ?>" class="order-filter-chip <?php echo $filter === 'refunded' ? 'is-active is-success' : ''; ?>">
            <i class="fa-solid fa-rotate-left"></i> Đã hoàn tiền
        </a>
    </div>

    <?php if ($pageError !== null): ?>
    <div class="empty-state-card">
        <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
        <p class="empty-state-text"><?php echo htmlspecialchars($pageError); ?></p>
    </div>
    <?php else: ?>

    <!-- Summary cards -->
    <div class="row g-3 mb-4">
        <?php foreach ([
            ['Tổng đơn', $summary['total']],
            ['Đang giữ tiền', $summary['holding']],
            ['Hoàn tất', $summary['completed']],
            ['Có vấn đề / đã hủy', $summary['cancelled']],
        ] as [$label, $value]): ?>
        <div class="col-6 col-md-3">
            <div class="profile-card order-summary-card">
                <div class="order-summary-label"><?php echo $label; ?></div>
                <div class="order-summary-value"><?php echo (int) $value; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
    <div class="empty-state-card">
        <i class="fa-solid fa-box-open empty-state-icon"></i>
        <p class="empty-state-text">
            <?php echo $filter === 'all'
                ? 'Bạn chưa có đơn hàng bán nào. Khi có người mua, đơn hàng sẽ xuất hiện tại đây.'
                : 'Không có đơn nào khớp với bộ lọc bạn đang xem.'; ?>
        </p>
        <?php if ($filter !== 'all'): ?>
        <a href="<?php echo $allUrl; ?>" class="btn-detail product-detail-link">Xem tất cả đơn hàng</a>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <div class="order-list">
        <?php foreach ($orders as $order): ?>
        <?php
        $productTitle = trim((string) ($order['product_title'] ?? '')) ?: 'Xe đạp đang cập nhật tên';
        $productBrand = trim((string) ($order['product_brand'] ?? '')) ?: 'Đang cập nhật hãng xe';
        $buyerName    = trim((string) ($order['buyer_name'] ?? ''))    ?: 'Người mua đang cập nhật';
        $formattedAmount = is_numeric($order['amount'] ?? null)
            ? number_format((float) $order['amount'], 0, ',', '.') . ' đ' : 'Đang cập nhật';
        $formattedDate = !empty($order['order_created_at'])
            ? date('d/m/Y H:i', strtotime((string) $order['order_created_at'])) : '';
        $productImage = $order['product_image'] ?? 'https://via.placeholder.com/96x96?text=SpinBike';
        $statusGuide  = ProjectFlow::orderListGuide(
            (string) ($order['order_status'] ?? ''),
            (string) ($order['escrow_status'] ?? ''),
            'seller'
        );
        $statusGuideClass = match ((string) ($order['escrow_status'] ?? '')) {
            ProjectFlow::ESCROW_DISPUTED => 'order-list-guide is-danger',
            ProjectFlow::ESCROW_REFUNDED => 'order-list-guide is-success',
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
                            <div class="text-muted small"><?php echo htmlspecialchars($formattedDate); ?></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <div class="order-status-group">
                            <span class="order-status-label">Đơn hàng</span>
                            <span class="badge <?php echo ProjectFlow::orderBadgeClass((string) ($order['order_status'] ?? '')); ?> rounded-pill px-3 py-2">
                                <?php echo htmlspecialchars(ProjectFlow::orderLabel((string) ($order['order_status'] ?? ''))); ?>
                            </span>
                        </div>
                        <div class="order-status-group">
                            <span class="order-status-label">Giữ tiền</span>
                            <span class="badge <?php echo ProjectFlow::orderBadgeClass((string) ($order['escrow_status'] ?? '')); ?> rounded-pill px-3 py-2">
                                <?php echo htmlspecialchars(ProjectFlow::escrowLabel((string) ($order['escrow_status'] ?? ''))); ?>
                            </span>
                        </div>
                    </div>

                    <p class="<?php echo $statusGuideClass; ?> mb-3"><?php echo htmlspecialchars($statusGuide); ?></p>

                    <div class="order-list-meta">
                        <span><i class="fa-solid fa-user"></i> Người mua: <?php echo htmlspecialchars($buyerName); ?></span>
                        <span><i class="fa-solid fa-hashtag"></i> Sản phẩm #<?php echo (int) $order['product_id']; ?></span>
                    </div>

                    <div class="mt-3">
                        <a href="<?php echo route_url('order', ['id' => (int) $order['id']]); ?>" class="btn-detail product-detail-link order-list-action">
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
