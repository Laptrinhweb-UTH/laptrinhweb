<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/AdminAuth.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';

require_admin_session();

function admin_order_status_label(string $status): string {
    return ProjectFlow::orderLabel($status);
}

function admin_escrow_status_label(string $status): string {
    return ProjectFlow::escrowLabel($status);
}

function admin_order_badge_class(string $status): string {
    return ProjectFlow::orderBadgeClass($status);
}

$filter = $_GET['filter'] ?? 'all';
$allowedFilters = ['all', 'holding', 'disputed', 'refunded', 'completed', 'cancelled'];
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$database = new Database();
$db = $database->getConnectionOrNull();
$pageError = null;
$orders = [];
$summary = [
    'total' => 0,
    'holding' => 0,
    'disputed' => 0,
    'refunded' => 0,
    'completed' => 0,
    'cancelled' => 0,
];

if (!$db) {
    $pageError = 'Danh sách đơn hàng admin hiện chưa thể tải vì kết nối dữ liệu đang gặp sự cố.';
} else {
    try {
        $extraCondition = '';
        if ($filter === 'holding') {
            $extraCondition = " AND e.status = '" . ProjectFlow::ESCROW_HOLDING . "'";
        } elseif ($filter === 'disputed') {
            $extraCondition = " AND e.status = '" . ProjectFlow::ESCROW_DISPUTED . "'";
        } elseif ($filter === 'refunded') {
            $extraCondition = " AND e.status = '" . ProjectFlow::ESCROW_REFUNDED . "'";
        } elseif ($filter === 'completed') {
            $extraCondition = " AND o.status = '" . ProjectFlow::ORDER_COMPLETED . "'";
        } elseif ($filter === 'cancelled') {
            $extraCondition = " AND o.status = '" . ProjectFlow::ORDER_CANCELLED . "'";
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
            WHERE 1 = 1
            {$extraCondition}
            ORDER BY
                CASE
                    WHEN e.status = '" . ProjectFlow::ESCROW_DISPUTED . "' THEN 0
                    WHEN e.status = '" . ProjectFlow::ESCROW_HOLDING . "' THEN 1
                    WHEN o.status = '" . ProjectFlow::ORDER_COMPLETED . "' THEN 2
                    WHEN e.status = '" . ProjectFlow::ESCROW_REFUNDED . "' THEN 3
                    ELSE 4
                END,
                o.created_at DESC,
                o.id DESC
        ";

        $stmt = $db->query($query);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summaryStmt = $db->query("
            SELECT
                COUNT(*) AS total_orders,
                SUM(CASE WHEN e.status = '" . ProjectFlow::ESCROW_HOLDING . "' THEN 1 ELSE 0 END) AS holding_orders,
                SUM(CASE WHEN e.status = '" . ProjectFlow::ESCROW_DISPUTED . "' THEN 1 ELSE 0 END) AS disputed_orders,
                SUM(CASE WHEN e.status = '" . ProjectFlow::ESCROW_REFUNDED . "' THEN 1 ELSE 0 END) AS refunded_orders,
                SUM(CASE WHEN o.status = '" . ProjectFlow::ORDER_COMPLETED . "' THEN 1 ELSE 0 END) AS completed_orders,
                SUM(CASE WHEN o.status = '" . ProjectFlow::ORDER_CANCELLED . "' THEN 1 ELSE 0 END) AS cancelled_orders
            FROM orders o
            LEFT JOIN escrows e ON e.order_id = o.id
        ");
        $summaryData = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $summary['total'] = (int) ($summaryData['total_orders'] ?? 0);
        $summary['holding'] = (int) ($summaryData['holding_orders'] ?? 0);
        $summary['disputed'] = (int) ($summaryData['disputed_orders'] ?? 0);
        $summary['refunded'] = (int) ($summaryData['refunded_orders'] ?? 0);
        $summary['completed'] = (int) ($summaryData['completed_orders'] ?? 0);
        $summary['cancelled'] = (int) ($summaryData['cancelled_orders'] ?? 0);
    } catch (Throwable $exception) {
        $pageError = 'Khu vực quản lý đơn hàng admin hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$dashboardUrl = admin_dashboard_url();
$listingsUrl = admin_listings_url();
$allOrdersUrl = admin_orders_url('filter=all');
$holdingOrdersUrl = admin_orders_url('filter=holding');
$disputedOrdersUrl = admin_orders_url('filter=disputed');
$refundedOrdersUrl = admin_orders_url('filter=refunded');
$completedOrdersUrl = admin_orders_url('filter=completed');
$cancelledOrdersUrl = admin_orders_url('filter=cancelled');
$filterLabel = match ($filter) {
    'holding' => 'đơn đang giữ tiền',
    'disputed' => 'đơn đang tranh chấp',
    'refunded' => 'đơn đã hoàn tiền',
    'completed' => 'đơn hoàn tất',
    'cancelled' => 'đơn đã hủy',
    default => 'tất cả đơn hàng',
};
$adminSection = 'orders';

include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5 admin-layout-shell">
    <div class="admin-layout-grid">
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <main class="admin-main-content">
            <div class="profile-card mb-4">
                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                    <div>
                        <span class="badge bg-dark rounded-pill px-3 py-2 mb-3">Khu vực quản trị</span>
                        <h2 class="fw-bold mb-1">Quản lý đơn hàng & tranh chấp</h2>
                        <p class="text-muted mb-0">Admin theo dõi toàn bộ giao dịch giữ tiền, đơn đang tranh chấp và các case hoàn tiền trên hệ thống.</p>
                        <p class="order-filter-subtitle mb-0">Đang xem: <?php echo htmlspecialchars($filterLabel); ?></p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fa-solid fa-gauge-high me-2"></i>Dashboard Admin
                        </a>
                        <a href="<?php echo $listingsUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fa-solid fa-list-check me-2"></i>Quản lý tin đăng
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2">
            <a href="<?php echo $allOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label">Tổng đơn</div>
                    <div class="order-summary-value"><?php echo $summary['total']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-xl-2">
            <a href="<?php echo $holdingOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-warning-emphasis">Đang giữ tiền</div>
                    <div class="order-summary-value"><?php echo $summary['holding']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-xl-2">
            <a href="<?php echo $disputedOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-danger-emphasis">Tranh chấp</div>
                    <div class="order-summary-value"><?php echo $summary['disputed']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-xl-2">
            <a href="<?php echo $refundedOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-primary-emphasis">Hoàn tiền</div>
                    <div class="order-summary-value"><?php echo $summary['refunded']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-xl-2">
            <a href="<?php echo $completedOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-success-emphasis">Hoàn tất</div>
                    <div class="order-summary-value"><?php echo $summary['completed']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-xl-2">
            <a href="<?php echo $cancelledOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-secondary-emphasis">Đã hủy</div>
                    <div class="order-summary-value"><?php echo $summary['cancelled']; ?></div>
                </div>
            </a>
        </div>
            </div>

            <div class="order-filter-bar mb-4">
        <a href="<?php echo $allOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'all' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-list-ul"></i> Tất cả
        </a>
        <a href="<?php echo $holdingOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'holding' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-lock"></i> Đang giữ tiền
        </a>
        <a href="<?php echo $disputedOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'disputed' ? 'is-active is-danger' : ''; ?>">
            <i class="fa-solid fa-triangle-exclamation"></i> Đang tranh chấp
        </a>
        <a href="<?php echo $refundedOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'refunded' ? 'is-active is-success' : ''; ?>">
            <i class="fa-solid fa-rotate-left"></i> Đã hoàn tiền
        </a>
        <a href="<?php echo $completedOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'completed' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-circle-check"></i> Hoàn tất
        </a>
        <a href="<?php echo $cancelledOrdersUrl; ?>" class="order-filter-chip <?php echo $filter === 'cancelled' ? 'is-active' : ''; ?>">
            <i class="fa-solid fa-ban"></i> Đã hủy
        </a>
            </div>

            <?php if ($pageError !== null): ?>
            <div class="empty-state-card">
                <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
                <p class="empty-state-text"><?php echo htmlspecialchars($pageError); ?></p>
            </div>
            <?php elseif (empty($orders)): ?>
            <div class="empty-state-card">
                <i class="fa-solid fa-box-open empty-state-icon"></i>
                <p class="empty-state-text">Hiện không có đơn hàng nào khớp với bộ lọc admin đang xem.</p>
                <a href="<?php echo $allOrdersUrl; ?>" class="btn-detail product-detail-link">Xem tất cả đơn hàng</a>
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

            $buyerName = trim((string) ($order['buyer_name'] ?? ''));
            if ($buyerName === '') {
                $buyerName = 'Người mua đang cập nhật';
            }

            $sellerName = trim((string) ($order['seller_name'] ?? ''));
            if ($sellerName === '') {
                $sellerName = 'Người bán đang cập nhật';
            }

            $formattedAmount = is_numeric($order['amount'] ?? null)
                ? number_format((float) $order['amount'], 0, ',', '.') . ' đ'
                : 'Đang cập nhật';
            $formattedCreatedAt = !empty($order['order_created_at'])
                ? date('d/m/Y H:i', strtotime((string) $order['order_created_at']))
                : 'Đang cập nhật';
            $productImage = $order['product_image'] ?? 'https://via.placeholder.com/96x96?text=SpinBike';
            $statusGuide = ProjectFlow::orderListGuide(
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
                                <div class="text-muted small"><?php echo htmlspecialchars($formattedCreatedAt); ?></div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <div class="order-status-group">
                                <span class="order-status-label">Đơn hàng</span>
                                <span class="badge <?php echo admin_order_badge_class((string) ($order['order_status'] ?? '')); ?> rounded-pill px-3 py-2">
                                    <?php echo htmlspecialchars(admin_order_status_label((string) ($order['order_status'] ?? ''))); ?>
                                </span>
                            </div>
                            <div class="order-status-group">
                                <span class="order-status-label">Giữ tiền</span>
                                <span class="badge <?php echo admin_order_badge_class((string) ($order['escrow_status'] ?? '')); ?> rounded-pill px-3 py-2">
                                    <?php echo htmlspecialchars(admin_escrow_status_label((string) ($order['escrow_status'] ?? ''))); ?>
                                </span>
                            </div>
                        </div>

                        <p class="<?php echo $statusGuideClass; ?> mb-3"><?php echo htmlspecialchars($statusGuide); ?></p>

                        <div class="order-list-meta">
                            <span>
                                <i class="fa-solid fa-user"></i>
                                Buyer: <?php echo htmlspecialchars($buyerName); ?>
                            </span>
                            <span>
                                <i class="fa-solid fa-shop"></i>
                                Seller: <?php echo htmlspecialchars($sellerName); ?>
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
        </main>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
