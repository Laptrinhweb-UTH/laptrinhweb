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
$homeUrl = route_url('home');
$profileUrl = route_url('profile');
$adminSection = 'dashboard';
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
        // 1. Thống kê trạng thái tin đăng
        $listingStatusStmt = $db->query("
            SELECT listing_status, COUNT(*) AS cnt
            FROM products
            GROUP BY listing_status
        ");
        $listingStatusRows = $listingStatusStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($listingStatusRows as $row) {
            if ($row['listing_status'] === ProjectFlow::LISTING_PENDING) {
                $stats['pending_listings'] = (int) $row['cnt'];
            } elseif ($row['listing_status'] === ProjectFlow::LISTING_APPROVED) {
                $stats['approved_listings'] = (int) $row['cnt'];
            }
        }

        // 2. Thống kê tranh chấp và hoàn tiền
        $orderStatsStmt = $db->query("
            SELECT
                SUM(CASE WHEN e.status = '" . ProjectFlow::ESCROW_DISPUTED . "' THEN 1 ELSE 0 END) AS disputed_orders,
                SUM(CASE WHEN e.status = '" . ProjectFlow::ESCROW_REFUNDED . "' THEN 1 ELSE 0 END) AS refunded_orders
            FROM orders o
            LEFT JOIN escrows e ON e.order_id = o.id
        ");
        $orderStats = $orderStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $stats['disputed_orders'] = (int) ($orderStats['disputed_orders'] ?? 0);
        $stats['refunded_orders'] = (int) ($orderStats['refunded_orders'] ?? 0);

        // 3. Lấy tin đăng chờ duyệt mới nhất
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

        // 4. Lấy đơn hàng tranh chấp mới nhất
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

        // 5. Thống kê doanh thu 6 tháng
        $revenueStmt = $db->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') AS month,
                SUM(amount) AS revenue
            FROM orders
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              AND status != 'cancelled'
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        $revenueRows = $revenueStmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Thống kê tất cả trạng thái đơn hàng
        $orderStatusStmt = $db->query("
            SELECT status, COUNT(*) AS cnt
            FROM orders
            GROUP BY status
        ");
        $orderStatusRows = $orderStatusStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Throwable $exception) {
        error_log("Dashboard Error: " . $exception->getMessage());
        $dashboardError = 'Dashboard hiện chưa thể tải đủ số liệu. Vui lòng thử lại sau.';
    }
}

// Chuẩn bị dữ liệu cho biểu đồ Doanh thu
$revenueLabels = [];
$revenueData = [];
foreach ($revenueRows ?? [] as $row) {
    [$year, $month] = explode('-', $row['month']);
    $revenueLabels[] = "T{$month}/{$year}";
    $revenueData[] = (float) $row['revenue'];
}

// Chuẩn bị dữ liệu cho biểu đồ Trạng thái đơn hàng
$statusLabelsMap = [
    'pending_payment'  => 'Chờ thanh toán',
    'paid'             => 'Đã thanh toán',
    'seller_confirmed' => 'Người bán xác nhận',
    'shipping'         => 'Đang giao',
    'completed'        => 'Hoàn thành',
    'cancelled'        => 'Đã hủy',
];
$statusColorsMap = [
    'pending_payment'  => '#fbbf24',
    'paid'             => '#60a5fa',
    'seller_confirmed' => '#a78bfa',
    'shipping'         => '#34d399',
    'completed'        => '#10b981',
    'cancelled'        => '#f87171',
];
$donutLabels = [];
$donutData = [];
$donutColors = [];
foreach ($orderStatusRows ?? [] as $row) {
    $key = $row['status'];
    $donutLabels[] = $statusLabelsMap[$key] ?? $key;
    $donutData[] = (int) $row['cnt'];
    $donutColors[] = $statusColorsMap[$key] ?? '#94a3b8';
}

// Chuẩn bị dữ liệu cho biểu đồ Phân bổ tin đăng
$listingStatusLabelsMap = [
    'pending'  => 'Chờ duyệt',
    'approved' => 'Đang hiển thị',
    'rejected' => 'Bị từ chối',
    'sold'     => 'Đã bán',
    'hidden'   => 'Ẩn',
];
$listingStatusColorsMap = [
    'pending'  => '#fbbf24',
    'approved' => '#10b981',
    'rejected' => '#f87171',
    'sold'     => '#60a5fa',
    'hidden'   => '#94a3b8',
];
$listingDonutLabels = [];
$listingDonutData = [];
$listingDonutColors = [];
foreach ($listingStatusRows ?? [] as $row) {
    $key = $row['listing_status'];
    $listingDonutLabels[] = $listingStatusLabelsMap[$key] ?? $key;
    $listingDonutData[] = (int) $row['cnt'];
    $listingDonutColors[] = $listingStatusColorsMap[$key] ?? '#94a3b8';
}

include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5 admin-layout-shell">
    <div class="admin-layout-grid">
        <?php include __DIR__ . '/_sidebar.php'; ?>

        <main class="admin-main-content">
            <?php if ($dashboardError !== null): ?>
            <div class="empty-state-card mb-4">
                <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
                <p class="empty-state-text mb-0"><?php echo htmlspecialchars($dashboardError); ?></p>
            </div>
            <?php endif; ?>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="profile-card h-100">
                        <h5 class="fw-bold mb-1">Trạng thái tin đăng</h5>
                        <?php if (empty($listingDonutData)): ?>
                        <p class="text-muted small mb-0">Chưa có dữ liệu tin đăng.</p>
                        <?php else: ?>
                        <canvas id="listingChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="profile-card h-100">
                        <h5 class="fw-bold mb-1">Trạng thái đơn hàng</h5>
                        <?php if (empty($donutData)): ?>
                        <p class="text-muted small mb-0">Chưa có dữ liệu đơn hàng.</p>
                        <?php else: ?>
                        <canvas id="statusChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="profile-card h-100">
                        <h5 class="fw-bold mb-1">Doanh thu theo tháng</h5>
                        <p class="text-muted small mb-3">6 tháng gần nhất</p>
                        <?php if (empty($revenueLabels)): ?>
                        <p class="text-muted small mb-0">Chưa có dữ liệu doanh thu.</p>
                        <?php else: ?>
                        <canvas id="revenueChart" height="110"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="profile-card h-100">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">Việc cần xử lý ngay</h5>
                                <p class="text-muted mb-0 small">Tin chờ duyệt và đơn đang tranh chấp.</p>
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
                                                <div class="fw-semibold text-dark text-truncate"><?php echo htmlspecialchars((string) ($listing['title'] ?? 'Tin đăng')); ?></div>
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
                                        <a href="<?php echo route_url('order', ['id' => (int) $order['id']]); ?>" class="text-decoration-none">
                                            <div class="border rounded-3 px-3 py-2 bg-white">
                                                <div class="fw-semibold text-dark">Đơn #<?php echo (int) $order['id']; ?></div>
                                                <div class="small text-muted text-truncate">
                                                    <?php echo htmlspecialchars((string) ($order['product_title'] ?? 'Sản phẩm')); ?>
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
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Cấu hình chung cho biểu đồ tròn/vòng
const donutOptions = {
    responsive: true,
    plugins: {
        legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
    },
    cutout: '65%',
};

// 1. Biểu đồ Doanh thu (Bar Chart)
<?php if (!empty($revenueLabels)): ?>
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($revenueLabels); ?>,
        datasets: [{
            label: 'Doanh thu (đ)',
            data: <?php echo json_encode($revenueData); ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.2)',
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(ctx.raw)
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(val) + ' đ'
                }
            }
        }
    }
});
<?php endif; ?>

// 2. Biểu đồ Phân bổ tin đăng (Doughnut Chart)
<?php if (!empty($listingDonutData)): ?>
new Chart(document.getElementById('listingChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($listingDonutLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($listingDonutData); ?>,
            backgroundColor: <?php echo json_encode($listingDonutColors); ?>,
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: donutOptions
});
<?php endif; ?>

// 3. Biểu đồ Trạng thái đơn hàng (Doughnut Chart)
<?php if (!empty($donutData)): ?>
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($donutLabels); ?>,
        datasets: [{
            data: <?php echo json_encode($donutData); ?>,
            backgroundColor: <?php echo json_encode($donutColors); ?>,
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: donutOptions
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>