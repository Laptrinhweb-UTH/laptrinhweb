<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/Database.php';

$viewerId = $_SESSION['user_id'] ?? null;

// Cho phép xem profile của người khác qua ?id=, mặc định là chính mình
$targetId = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($viewerId ?? 0);

if ($targetId === 0) {
    header('Location: ' . route_url('auth'));
    exit;
}

$database = new Database();
$db = $database->getConnectionOrNull();
$pageError = null;
$user = null;
$activeListings = [];
$stats = ['active' => 0, 'sold' => 0];

if (!$db) {
    $pageError = 'Không thể tải thông tin trang cá nhân lúc này.';
} else {
    try {
        // Thông tin user
        $stmt = $db->prepare("SELECT id, name, avatar, phone, created_at FROM users WHERE id = ?");
        $stmt->execute([$targetId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $pageError = 'Không tìm thấy người dùng này.';
        } else {
            // Stats
            $stmt = $db->prepare("SELECT
                SUM(CASE WHEN listing_status = 'approved' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN listing_status = 'sold' THEN 1 ELSE 0 END) AS sold
                FROM products WHERE seller_id = ?");
            $stmt->execute([$targetId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active'] = (int) ($row['active'] ?? 0);
            $stats['sold']   = (int) ($row['sold'] ?? 0);

            // Tin đang hoạt động
            $stmt = $db->prepare("
                SELECT p.id, p.title, p.price, p.brand, p.created_at,
                    (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY id ASC LIMIT 1) AS image_url
                FROM products p
                WHERE p.seller_id = ? AND p.listing_status = 'approved'
                ORDER BY p.created_at DESC
                LIMIT 12
            ");
            $stmt->execute([$targetId]);
            $activeListings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        $pageError = 'Không thể tải thông tin trang cá nhân lúc này.';
    }
}

$isOwnProfile = $viewerId !== null && (int) $viewerId === $targetId;
$displayName  = htmlspecialchars($user['name'] ?? 'Người dùng SpinBike');
$displayAvatar = !empty($user['avatar'])
    ? $user['avatar']
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'U') . '&background=10b981&color=fff&size=160&rounded=true&bold=true';
$memberSince = !empty($user['created_at'])
    ? date('m/Y', strtotime($user['created_at']))
    : null;

include __DIR__ . '/../layouts/header.php';
?>

<div class="pub-profile-shell">
    <?php if ($pageError !== null): ?>
    <div class="container py-5 text-center">
        <p class="text-muted"><?php echo htmlspecialchars($pageError); ?></p>
        <a href="<?php echo route_url('home'); ?>" class="btn-detail product-detail-link mt-3">Quay về trang chủ</a>
    </div>
    <?php else: ?>

    <!-- Hero -->
    <div class="pub-profile-hero">
        <div class="pub-profile-hero-inner container">
            <img src="<?php echo $displayAvatar; ?>" alt="Avatar" class="pub-profile-avatar">
            <div class="pub-profile-info">
                <h1 class="pub-profile-name"><?php echo $displayName; ?></h1>
                <?php if ($memberSince): ?>
                <p class="pub-profile-since">Thành viên từ <?php echo $memberSince; ?></p>
                <?php endif; ?>
                <div class="pub-profile-stats">
                    <div class="pub-profile-stat">
                        <span class="pub-profile-stat-value"><?php echo $stats['active']; ?></span>
                        <span class="pub-profile-stat-label">Tin đang bán</span>
                    </div>
                    <div class="pub-profile-stat-divider"></div>
                    <div class="pub-profile-stat">
                        <span class="pub-profile-stat-value"><?php echo $stats['sold']; ?></span>
                        <span class="pub-profile-stat-label">Đã bán</span>
                    </div>
                </div>
            </div>
            <?php if ($isOwnProfile): ?>
            <a href="<?php echo route_url('user.settings.profile'); ?>" class="pub-profile-edit-btn">
                <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa trang cá nhân
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Listings -->
    <div class="container pub-profile-body">
        <h2 class="pub-profile-section-title">
            <?php echo $isOwnProfile ? 'Tin đăng của bạn' : "Tin đăng của {$displayName}"; ?>
        </h2>

        <?php if (empty($activeListings)): ?>
        <div class="empty-state-card">
            <i class="fa-solid fa-box-open empty-state-icon"></i>
            <p class="empty-state-text">
                <?php echo $isOwnProfile
                    ? 'Bạn chưa có tin đăng nào đang hoạt động.'
                    : 'Người dùng này chưa có tin đăng nào đang hoạt động.'; ?>
            </p>
            <?php if ($isOwnProfile): ?>
            <a href="<?php echo route_url('sell'); ?>" class="btn-detail product-detail-link">Đăng bán ngay</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($activeListings as $listing): ?>
            <?php
            $img = $listing['image_url'] ?? 'https://via.placeholder.com/300x200?text=SpinBike';
            $price = is_numeric($listing['price'] ?? null)
                ? number_format((float) $listing['price'], 0, ',', '.') . ' đ'
                : 'Liên hệ';
            ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?php echo route_url('listing', ['id' => (int) $listing['id']]); ?>" class="pub-profile-listing-card">
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>" class="pub-profile-listing-img">
                    <div class="pub-profile-listing-body">
                        <p class="pub-profile-listing-title"><?php echo htmlspecialchars($listing['title']); ?></p>
                        <p class="pub-profile-listing-price"><?php echo $price; ?></p>
                        <p class="pub-profile-listing-brand text-muted small"><?php echo htmlspecialchars($listing['brand'] ?? ''); ?></p>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
