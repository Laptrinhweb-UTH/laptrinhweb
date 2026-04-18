<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../helpers/ProjectFlow.php';
require_once __DIR__ . '/../../models/Product.php';

if (!isset($_SESSION['user_id']) || (string) ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: ' . asset_url('index.php'));
    exit;
}

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

if (!$db) {
    $pageError = 'Không thể tải khu vực duyệt tin lúc này.';
} else {
    try {
        $productModel = new Product($db);
        $listings = $productModel->getAdminListings($filter);
    } catch (Throwable $exception) {
        $pageError = 'Khu vực duyệt tin hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$currentUrl = app_url('app/views/products/review.php') . '?filter=' . urlencode($filter);
include __DIR__ . '/../layouts/header.php';
?>

<div class="container py-5" style="max-width: 1160px;">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div>
            <h2 class="fw-bold mb-1">Duyệt tin đăng</h2>
            <p class="text-muted mb-0">Admin kiểm tra, duyệt, từ chối hoặc ẩn các tin đăng trước khi hiển thị trên hệ thống.</p>
        </div>
        <span class="badge bg-dark rounded-pill px-4 py-3">Quyền quản trị viên</span>
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
            $url = app_url('app/views/products/review.php') . '?filter=' . urlencode($filterItem);
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
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
