<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/AdminAuth.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';

require_admin_session();

$adminName = trim((string) ($_SESSION['user_name'] ?? 'Quản trị viên'));
$reviewListingsUrl = admin_listings_url();
$pendingListingsUrl = $reviewListingsUrl . '?filter=' . urlencode(ProjectFlow::LISTING_PENDING);
$allListingsUrl = $reviewListingsUrl . '?filter=all';
$adminOrdersUrl = admin_orders_url();
$disputedOrdersUrl = admin_orders_url('filter=disputed');
$refundedOrdersUrl = admin_orders_url('filter=refunded');
$homeUrl = asset_url('index.php');
$profileUrl = app_url('app/views/auth/profile.php');
$dashboardError = null;
$stats = [
    'pending_listings' => 0,
    'approved_listings' => 0,
    'disputed_orders' => 0,
    'refunded_orders' => 0,
];
$recentPendingListings = [];
$recentDisputedOrders = [];

$database = new Database();
$db = $database->getConnectionOrNull();

if (!$db) {
    $dashboardError = 'Không thể tải số liệu dashboard lúc này vì kết nối dữ liệu đang gặp sự cố.';
} else {
    try {
        $listingStatsStmt = $db->query("
            SELECT
                SUM(CASE WHEN listing_status = '" . ProjectFlow::LISTING_PENDING . "' THEN 1 ELSE 0 END) AS pending_listings,
                SUM(CASE WHEN listing_status = '" . ProjectFlow::LISTING_APPROVED . "' THEN 1 ELSE 0 END) AS approved_listings
            FROM products
        ");
        $listingStats = $listingStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $orderStatsStmt = $db->query("
            SELECT
                SUM(CASE WHEN e.status = '" . ProjectFlow::ESCROW_DISPUTED . "' THEN 1 ELSE 0 END) AS disputed_orders,
                SUM(CASE WHEN e.status = '" . ProjectFlow::ESCROW_REFUNDED . "' THEN 1 ELSE 0 END) AS refunded_orders
            FROM orders o
            LEFT JOIN escrows e ON e.order_id = o.id
        ");
        $orderStats = $orderStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $stats['pending_listings'] = (int) ($listingStats['pending_listings'] ?? 0);
        $stats['approved_listings'] = (int) ($listingStats['approved_listings'] ?? 0);
        $stats['disputed_orders'] = (int) ($orderStats['disputed_orders'] ?? 0);
        $stats['refunded_orders'] = (int) ($orderStats['refunded_orders'] ?? 0);

        $pendingStmt = $db->prepare("
            SELECT p.id, p.title, p.price, p.created_at, u.name AS seller_name
            FROM products p
            LEFT JOIN users u ON u.id = p.seller_id
            WHERE p.listing_status = ?
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT 5
        ");
        $pendingStmt->execute([ProjectFlow::LISTING_PENDING]);
        $recentPendingListings = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

        $disputeStmt = $db->prepare("
            SELECT
                o.id,
                o.amount,
                o.created_at,
                p.title AS product_title,
                buyer.name AS buyer_name,
                seller.name AS seller_name
            FROM orders o
            LEFT JOIN escrows e ON e.order_id = o.id
            LEFT JOIN products p ON p.id = o.product_id
            LEFT JOIN users buyer ON buyer.id = o.buyer_id
            LEFT JOIN users seller ON seller.id = o.seller_id
            WHERE e.status = ?
            ORDER BY o.created_at DESC, o.id DESC
            LIMIT 5
        ");
        $disputeStmt->execute([ProjectFlow::ESCROW_DISPUTED]);
        $recentDisputedOrders = $disputeStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        $dashboardError = 'Dashboard hiện chưa thể tải đủ số liệu. Vui lòng thử lại sau.';
    }
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5" style="max-width: 1120px;">
    <div class="profile-card mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <span class="badge bg-dark rounded-pill px-3 py-2 mb-3">Khu vực quản trị</span>
                <h1 class="fw-bold mb-2">Dashboard Admin</h1>
                <p class="text-muted mb-0">
                    Xin chào <?php echo htmlspecialchars($adminName); ?>. Đây là điểm vào dành riêng cho quản trị viên để theo dõi và xử lý các nghiệp vụ nội bộ của SpinBike.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo $reviewListingsUrl; ?>" class="btn btn-success rounded-pill px-4">
                    <i class="fa-solid fa-shield-halved me-2"></i>Duyệt tin đăng
                </a>
                <a href="<?php echo $adminOrdersUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fa-solid fa-receipt me-2"></i>Quản lý đơn hàng
                </a>
                <a href="<?php echo $homeUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fa-solid fa-globe me-2"></i>Xem website
                </a>
            </div>
        </div>
    </div>

    <?php if ($dashboardError !== null): ?>
    <div class="empty-state-card mb-4">
        <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
        <p class="empty-state-text mb-0"><?php echo htmlspecialchars($dashboardError); ?></p>
    </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <a href="<?php echo $pendingListingsUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-warning-emphasis">Tin chờ duyệt</div>
                    <div class="order-summary-value"><?php echo $stats['pending_listings']; ?></div>
                    <div class="small text-muted mt-2">Cần admin kiểm tra trước khi hiển thị</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="<?php echo $allListingsUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-success-emphasis">Tin đang hiển thị</div>
                    <div class="order-summary-value"><?php echo $stats['approved_listings']; ?></div>
                    <div class="small text-muted mt-2">Những listing đang mở bán công khai</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="<?php echo $disputedOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-danger-emphasis">Đơn đang tranh chấp</div>
                    <div class="order-summary-value"><?php echo $stats['disputed_orders']; ?></div>
                    <div class="small text-muted mt-2">Mở nhanh khu admin xử lý dispute</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="<?php echo $refundedOrdersUrl; ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-primary-emphasis">Đơn đã hoàn tiền</div>
                    <div class="order-summary-value"><?php echo $stats['refunded_orders']; ?></div>
                    <div class="small text-muted mt-2">Theo dõi các case đã khép lại theo hướng refund</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="profile-card h-100">
                <h5 class="fw-bold mb-3">Thao tác nhanh</h5>
                <div class="d-grid gap-2">
                    <a href="<?php echo $pendingListingsUrl; ?>" class="btn btn-success rounded-pill px-4">
                        <i class="fa-solid fa-shield-halved me-2"></i>Mở tin chờ duyệt
                    </a>
                    <a href="<?php echo $allListingsUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-list-check me-2"></i>Xem toàn bộ tin đăng
                    </a>
                    <a href="<?php echo $adminOrdersUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-receipt me-2"></i>Quản lý đơn hàng
                    </a>
                    <a href="<?php echo $profileUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-user-shield me-2"></i>Hồ sơ quản trị viên
                    </a>
                    <a href="<?php echo $homeUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-globe me-2"></i>Quay lại website
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="profile-card h-100">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                    <div>
                        <h5 class="fw-bold mb-1">Việc cần xử lý ngay</h5>
                        <p class="text-muted mb-0">Danh sách ưu tiên để admin không phải đi tìm lại từng khu vực xử lý.</p>
                    </div>
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                        <?php echo $stats['pending_listings'] + $stats['disputed_orders']; ?> mục cần chú ý
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="order-status-note h-100 mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Tin chờ duyệt</strong>
                                <span class="badge bg-warning text-dark rounded-pill"><?php echo $stats['pending_listings']; ?></span>
                            </div>
                            <?php if (empty($recentPendingListings)): ?>
                            <p class="mb-0 text-muted">Hiện không có tin nào đang chờ admin duyệt.</p>
                            <?php else: ?>
                            <div class="d-grid gap-2">
                                <?php foreach ($recentPendingListings as $listing): ?>
                                <a href="<?php echo $pendingListingsUrl; ?>" class="text-decoration-none">
                                    <div class="border rounded-3 px-3 py-2 bg-white">
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars((string) ($listing['title'] ?? 'Tin đăng')); ?></div>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars((string) ($listing['seller_name'] ?? 'Người bán')); ?> ·
                                            <?php echo is_numeric($listing['price'] ?? null) ? number_format((float) $listing['price'], 0, ',', '.') . ' đ' : 'Đang cập nhật'; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="order-status-note h-100 mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Đơn đang tranh chấp</strong>
                                <span class="badge bg-danger rounded-pill"><?php echo $stats['disputed_orders']; ?></span>
                            </div>
                            <?php if (empty($recentDisputedOrders)): ?>
                            <p class="mb-0 text-muted">Hiện không có đơn nào đang tranh chấp.</p>
                            <?php else: ?>
                            <div class="d-grid gap-2">
                                <?php foreach ($recentDisputedOrders as $order): ?>
                                <a href="<?php echo app_url('app/views/orders/detail.php'); ?>?id=<?php echo (int) $order['id']; ?>" class="text-decoration-none">
                                    <div class="border rounded-3 px-3 py-2 bg-white">
                                        <div class="fw-semibold text-dark">Đơn #<?php echo (int) $order['id']; ?> · <?php echo htmlspecialchars((string) ($order['product_title'] ?? 'Xe đạp')); ?></div>
                                        <div class="small text-muted">
                                            Buyer: <?php echo htmlspecialchars((string) ($order['buyer_name'] ?? 'Đang cập nhật')); ?> ·
                                            Seller: <?php echo htmlspecialchars((string) ($order['seller_name'] ?? 'Đang cập nhật')); ?>
                                        </div>
                                        <div class="small text-danger fw-semibold">
                                            <?php echo is_numeric($order['amount'] ?? null) ? number_format((float) $order['amount'], 0, ',', '.') . ' đ' : 'Đang cập nhật'; ?>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-card">
        <h5 class="fw-bold mb-3">Phạm vi phase hiện tại</h5>
        <div class="order-status-note mb-0">
            <p class="mb-2"><strong>Đã có:</strong> entry point riêng cho admin, redirect sau login theo role, guard chặn user thường, dashboard tổng quan với số liệu thật từ database, khu quản lý tin đăng và màn quản lý order/dispute riêng cho admin.</p>
            <p class="mb-0"><strong>Sắp làm tiếp:</strong> hoàn thiện navigation admin và polish lại trải nghiệm khu quản trị cho đồng nhất hơn.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
