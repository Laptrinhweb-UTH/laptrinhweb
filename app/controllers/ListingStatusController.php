<?php
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/ProjectFlow.php';
require_once __DIR__ . '/../models/Product.php';

function redirect_listing_feedback(string $url, string $message, string $status = 'error'): never
{
    $separator = str_contains($url, '?') ? '&' : '?';
    header('Location: ' . $url . $separator . 'status=' . rawurlencode($status) . '&message=' . rawurlencode($message));
    exit;
}

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . asset_url('index.php'));
    exit;
}

$listingId = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$action = trim((string) ($_POST['action'] ?? ''));
$returnUrl = trim((string) ($_POST['return_url'] ?? ''));

$fallbackUrl = app_url('app/views/auth/profile.php');
$safeReturnUrl = $returnUrl !== '' && str_starts_with($returnUrl, APP_URL) ? $returnUrl : $fallbackUrl;

if ($listingId === false || $listingId === null || $action === '') {
    redirect_listing_feedback($safeReturnUrl, 'Thiếu dữ liệu để cập nhật trạng thái tin đăng.');
}

$database = new Database();
$db = $database->getConnectionOrNull();

if (!$db) {
    redirect_listing_feedback($safeReturnUrl, 'Không thể kết nối dữ liệu để cập nhật tin đăng.');
}

$productModel = new Product($db);
$listing = $productModel->findListingById((int) $listingId);

if (!$listing) {
    redirect_listing_feedback($safeReturnUrl, 'Không tìm thấy tin đăng cần thao tác.');
}

$currentUserId = (int) $_SESSION['user_id'];
$currentUserRole = (string) ($_SESSION['role'] ?? 'user');
$isAdmin = $currentUserRole === 'admin';
$isOwner = (int) ($listing['seller_id'] ?? 0) === $currentUserId;
$approvalNote = null;
$nextStatus = null;

if ($isAdmin) {
    $allowedActions = ProjectFlow::adminAllowedListingActions((string) ($listing['listing_status'] ?? ''));
    if (!in_array($action, $allowedActions, true)) {
        redirect_listing_feedback($safeReturnUrl, 'Bạn không thể thực hiện thao tác này với tin đăng hiện tại.');
    }

    $nextStatus = match ($action) {
        'approve' => ProjectFlow::LISTING_APPROVED,
        'reject' => ProjectFlow::LISTING_REJECTED,
        'hide' => ProjectFlow::LISTING_HIDDEN,
        default => null,
    };

    if ($action === 'approve') {
        $approvalNote = 'Tin đã được admin duyệt hiển thị.';
    } elseif ($action === 'reject') {
        $approvalNote = 'Tin bị từ chối và cần người bán chỉnh sửa lại nội dung.';
    } elseif ($action === 'hide') {
        $approvalNote = 'Tin được admin tạm ẩn khỏi khu vực mua bán.';
    }
} elseif ($isOwner) {
    $allowedActions = ProjectFlow::sellerAllowedListingActions((string) ($listing['listing_status'] ?? ''));
    if (!in_array($action, $allowedActions, true)) {
        redirect_listing_feedback($safeReturnUrl, 'Bạn không thể thực hiện thao tác này với tin đăng của mình.');
    }

    $nextStatus = match ($action) {
        'hide' => ProjectFlow::LISTING_HIDDEN,
        'show' => ProjectFlow::LISTING_APPROVED,
        'mark_sold' => ProjectFlow::LISTING_SOLD,
        default => null,
    };

    if ($action === 'hide') {
        $approvalNote = 'Tin được người bán tạm ẩn khỏi danh sách hiển thị.';
    } elseif ($action === 'show') {
        $approvalNote = 'Tin được người bán mở lại để tiếp tục giao dịch.';
    } elseif ($action === 'mark_sold') {
        $approvalNote = 'Người bán đã xác nhận chiếc xe đã bán xong.';
    }
} else {
    redirect_listing_feedback($safeReturnUrl, 'Bạn không có quyền thao tác với tin đăng này.');
}

if ($nextStatus === null) {
    redirect_listing_feedback($safeReturnUrl, 'Không xác định được trạng thái mới cho tin đăng.');
}

if (!$productModel->updateListingStatus((int) $listingId, $nextStatus, $approvalNote)) {
    redirect_listing_feedback($safeReturnUrl, 'Không thể cập nhật trạng thái tin đăng lúc này.');
}

$successMessage = ProjectFlow::listingActionLabel($action) . ' thành công cho tin "' . trim((string) ($listing['title'] ?? '')) . '".';
redirect_listing_feedback($safeReturnUrl, $successMessage, 'success');
