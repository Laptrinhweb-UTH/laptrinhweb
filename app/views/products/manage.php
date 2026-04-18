<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../models/Product.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('app/views/auth/auth.php'));
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$allowedFilters = ['all', 'pending', 'approved', 'rejected', 'hidden', 'sold'];
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$status = $_GET['status'] ?? '';
$message = trim((string) ($_GET['message'] ?? ''));
$noticeClass = $status === 'success' ? 'auth-message auth-message-success' : 'auth-message auth-message-error';

$database = new Database();
$db = $database->getConnectionOrNull();
$pageError = null;
$listings = [];

if (!$db) {
    $pageError = 'Không thể tải danh sách tin đăng lúc này.';
} else {
    try {
        $productModel = new Product($db);
        $listings = $productModel->getSellerListings((int) $_SESSION['user_id'], $filter);
    } catch (Throwable $exception) {
        $pageError = 'Danh sách tin đăng hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$currentUrl = app_url('app/views/products/manage.php') . '?filter=' . urlencode($filter);
include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5" style="max-width: 1100px;">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div>
            <h2 class="fw-bold mb-1">Tin đăng của tôi</h2>
            <p class="text-muted mb-0">Theo dõi trạng thái duyệt tin và quản lý những chiếc xe bạn đang rao bán.</p>
        </div>
        <a href="<?php echo app_url('app/views/products/sell.php'); ?>" class="btn btn-success rounded-pill px-4">
            <i class="fa-solid fa-plus me-2"></i>Tạo tin mới
        </a>
    </div>

    <?php if ($message !== ''): ?>
    <div class="<?php echo $noticeClass; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="order-filter-bar mb-4">
        <?php foreach ($allowedFilters as $filterItem): ?>
            <?php
            $label = $filterItem === 'all' ? 'Tất cả' : ProjectFlow::listingLabel($filterItem);
            $url = app_url('app/views/products/manage.php') . '?filter=' . urlencode($filterItem);
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
        <i class="fa-solid fa-box-open empty-state-icon"></i>
        <p class="empty-state-text">Bạn chưa có tin đăng nào khớp với bộ lọc hiện tại.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($listings as $listing): ?>
            <?php
            $image = $listing['main_image'] ?: 'https://via.placeholder.com/600x400?text=SpinBike';
            $listingStatus = (string) ($listing['listing_status'] ?? '');
            $allowedActions = ProjectFlow::sellerAllowedListingActions($listingStatus);
            $listingTitle = trim((string) ($listing['title'] ?? 'Xe đạp đang cập nhật tên'));
            $listingPrice = is_numeric($listing['price'] ?? null) ? number_format((float) $listing['price'], 0, ',', '.') . ' đ' : 'Đang cập nhật';
            ?>
            <div class="col-lg-6">
                <div class="profile-card h-100">
                    <div class="d-flex gap-3 flex-wrap">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Listing image" style="width: 140px; height: 105px; object-fit: cover; border-radius: 16px; border: 1px solid var(--border);">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-3 flex-wrap mb-2">
                                <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($listingTitle); ?></h5>
                                <span class="badge <?php echo ProjectFlow::listingBadgeClass($listingStatus); ?> rounded-pill px-3 py-2">
                                    <?php echo htmlspecialchars(ProjectFlow::listingLabel($listingStatus)); ?>
                                </span>
                            </div>
                            <div class="text-muted small mb-2"><?php echo htmlspecialchars(ProjectFlow::listingDescription($listingStatus)); ?></div>
                            <div class="fw-semibold text-success mb-2"><?php echo htmlspecialchars($listingPrice); ?></div>
                            <div class="small text-muted mb-1"><i class="fa-solid fa-location-dot me-2"></i><?php echo htmlspecialchars((string) ($listing['location'] ?? 'Đang cập nhật vị trí')); ?></div>
                            <div class="small text-muted"><i class="fa-regular fa-images me-2"></i><?php echo (int) ($listing['image_count'] ?? 0); ?> ảnh</div>
                        </div>
                    </div>

                    <?php if (!empty($listing['approval_note'])): ?>
                    <div class="order-status-note mt-3 mb-0">
                        <p class="mb-0"><strong>Ghi chú trạng thái:</strong> <?php echo htmlspecialchars((string) $listing['approval_note']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 flex-wrap mt-4">
                        <a href="<?php echo asset_url('detail.php?id=' . (int) $listing['id']); ?>" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="fa-regular fa-eye me-2"></i>Xem chi tiết
                        </a>
                        <?php foreach ($allowedActions as $action): ?>
                        <form action="<?php echo app_url('app/controllers/ListingStatusController.php'); ?>" method="POST" class="d-inline">
                            <input type="hidden" name="listing_id" value="<?php echo (int) $listing['id']; ?>">
                            <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($currentUrl); ?>">
                            <button type="submit" class="btn <?php echo in_array($action, ['hide', 'mark_sold'], true) ? 'btn-outline-danger' : 'btn-outline-success'; ?> rounded-pill px-3">
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
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
