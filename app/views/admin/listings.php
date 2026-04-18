<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/AdminAuth.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../models/Product.php';

require_admin_session();

$filter = $_GET['filter'] ?? 'pending';
$allowedFilters = ['pending', 'approved', 'rejected', 'hidden', 'sold', 'all'];
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'pending';
}

$status = $_GET['status'] ?? '';
$message = trim((string) ($_GET['message'] ?? ''));
$noticeClass = $status === 'success' ? 'auth-message auth-message-success' : 'auth-message auth-message-error';

$database = new Database();
$db = $database->getConnectionOrNull();
$pageError = null;
$listings = [];
$summary = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'hidden' => 0,
];

if (!$db) {
    $pageError = 'Không thể tải khu vực duyệt tin lúc này.';
} else {
    try {
        $productModel = new Product($db);
        $listings = $productModel->getAdminListings($filter);

        $summaryStmt = $db->query("
            SELECT
                SUM(CASE WHEN listing_status = '" . ProjectFlow::LISTING_PENDING . "' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN listing_status = '" . ProjectFlow::LISTING_APPROVED . "' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN listing_status = '" . ProjectFlow::LISTING_REJECTED . "' THEN 1 ELSE 0 END) AS rejected_count,
                SUM(CASE WHEN listing_status = '" . ProjectFlow::LISTING_HIDDEN . "' THEN 1 ELSE 0 END) AS hidden_count
            FROM products
        ");
        $summaryData = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $summary['pending'] = (int) ($summaryData['pending_count'] ?? 0);
        $summary['approved'] = (int) ($summaryData['approved_count'] ?? 0);
        $summary['rejected'] = (int) ($summaryData['rejected_count'] ?? 0);
        $summary['hidden'] = (int) ($summaryData['hidden_count'] ?? 0);
    } catch (Throwable $exception) {
        $pageError = 'Khu vực duyệt tin hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$dashboardUrl = admin_dashboard_url();
$currentUrl = admin_listings_url('filter=' . urlencode($filter));
$adminSection = 'listings';
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
                        <h2 class="fw-bold mb-1">Quản lý tin đăng</h2>
                        <p class="text-muted mb-0">Admin kiểm tra, duyệt, từ chối hoặc ẩn các tin đăng trước khi hiển thị trên hệ thống.</p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo $dashboardUrl; ?>" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fa-solid fa-gauge-high me-2"></i>Dashboard Admin
                        </a>
                        <span class="badge bg-dark rounded-pill px-4 py-3">Quyền quản trị viên</span>
                    </div>
                </div>
            </div>

            <?php if ($message !== ''): ?>
            <div class="<?php echo $noticeClass; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="<?php echo admin_listings_url('filter=' . ProjectFlow::LISTING_PENDING); ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-warning-emphasis">Chờ duyệt</div>
                    <div class="order-summary-value"><?php echo $summary['pending']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?php echo admin_listings_url('filter=' . ProjectFlow::LISTING_APPROVED); ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-success-emphasis">Đang hiển thị</div>
                    <div class="order-summary-value"><?php echo $summary['approved']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?php echo admin_listings_url('filter=' . ProjectFlow::LISTING_REJECTED); ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-danger-emphasis">Bị từ chối</div>
                    <div class="order-summary-value"><?php echo $summary['rejected']; ?></div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?php echo admin_listings_url('filter=' . ProjectFlow::LISTING_HIDDEN); ?>" class="text-decoration-none">
                <div class="profile-card order-summary-card h-100">
                    <div class="order-summary-label text-secondary-emphasis">Đã ẩn</div>
                    <div class="order-summary-value"><?php echo $summary['hidden']; ?></div>
                </div>
            </a>
        </div>
            </div>

            <div class="order-filter-bar mb-4">
        <?php foreach ($allowedFilters as $filterItem): ?>
            <?php
            $label = $filterItem === 'all' ? 'Tất cả' : ProjectFlow::listingLabel($filterItem);
            $url = admin_listings_url('filter=' . urlencode($filterItem));
            ?>
            <a href="<?php echo $url; ?>" class="order-filter-chip <?php echo $filter === $filterItem ? 'is-active' : ''; ?>">
                <?php echo htmlspecialchars($label); ?>
            </a>
        <?php endforeach; ?>
            </div>

            <?php if ($pageError !== null): ?>
            <div class="empty-state-card">
                <i class="fa-solid fa-circle-exclamation empty-state-icon"></i>
                <p class="empty-state-text"><?php echo htmlspecialchars($pageError); ?></p>
            </div>
            <?php elseif (empty($listings)): ?>
            <div class="empty-state-card">
                <i class="fa-solid fa-inbox empty-state-icon"></i>
                <p class="empty-state-text">Hiện không có tin đăng nào trong nhóm trạng thái này.</p>
            </div>
            <?php else: ?>
            <div class="row g-4">
        <?php foreach ($listings as $listing): ?>
            <?php
            $image = $listing['main_image'] ?: 'https://via.placeholder.com/600x400?text=SpinBike';
            $listingStatus = (string) ($listing['listing_status'] ?? '');
            $allowedActions = ProjectFlow::adminAllowedListingActions($listingStatus);
            $listingTitle = trim((string) ($listing['title'] ?? 'Xe đạp đang cập nhật tên'));
            $listingPrice = is_numeric($listing['price'] ?? null) ? number_format((float) $listing['price'], 0, ',', '.') . ' đ' : 'Đang cập nhật';
            $descriptionPreview = trim((string) ($listing['description'] ?? 'Chưa có mô tả.'));
            if (strlen($descriptionPreview) > 220) {
                $descriptionPreview = substr($descriptionPreview, 0, 217) . '...';
            }
            ?>
            <div class="col-lg-6">
                <div class="profile-card h-100">
                    <div class="d-flex gap-3 flex-wrap">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Listing image" style="width: 150px; height: 110px; object-fit: cover; border-radius: 16px; border: 1px solid var(--border);">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-3 flex-wrap mb-2">
                                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($listingTitle); ?></h5>
                                <span class="badge <?php echo ProjectFlow::listingBadgeClass($listingStatus); ?> rounded-pill px-3 py-2">
                                    <?php echo htmlspecialchars(ProjectFlow::listingLabel($listingStatus)); ?>
                                </span>
                            </div>
                            <div class="text-muted small mb-2"><?php echo htmlspecialchars(ProjectFlow::listingDescription($listingStatus)); ?></div>
                            <div class="small text-muted mb-1"><i class="fa-solid fa-user me-2"></i><?php echo htmlspecialchars((string) ($listing['seller_name'] ?? 'Người bán đang cập nhật')); ?></div>
                            <div class="small text-muted mb-1"><i class="fa-solid fa-location-dot me-2"></i><?php echo htmlspecialchars((string) ($listing['location'] ?? 'Đang cập nhật vị trí')); ?></div>
                            <div class="fw-semibold text-success"><?php echo htmlspecialchars($listingPrice); ?></div>
                        </div>
                    </div>

                    <div class="order-status-note mt-3 mb-0">
                        <p class="mb-1"><strong>Mô tả:</strong> <?php echo htmlspecialchars($descriptionPreview); ?></p>
                        <?php if (!empty($listing['approval_note'])): ?>
                        <p class="mb-0"><strong>Ghi chú hiện tại:</strong> <?php echo htmlspecialchars((string) $listing['approval_note']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-4">
                        <a href="<?php echo asset_url('detail.php?id=' . (int) $listing['id']); ?>" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="fa-regular fa-eye me-2"></i>Xem chi tiết
                        </a>
                        <?php foreach ($allowedActions as $action): ?>
                        <form action="<?php echo app_url('app/controllers/ListingStatusController.php'); ?>" method="POST" class="d-inline">
                            <input type="hidden" name="listing_id" value="<?php echo (int) $listing['id']; ?>">
                            <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
                            <button type="submit" class="btn <?php echo $action === 'approve' ? 'btn-success' : 'btn-outline-danger'; ?> rounded-pill px-3">
                                <?php echo htmlspecialchars(ProjectFlow::listingActionLabel($action)); ?>
                            </button>
                        </form>
                        <?php endforeach; ?>
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
