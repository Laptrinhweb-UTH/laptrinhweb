<?php
session_start();
require_once __DIR__ . '/../../../../config/config.php';
require_once __DIR__ . '/../../../helpers/Database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . route_url('auth'));
    exit;
}

$database = new Database();
$db = $database->getConnectionOrNull();
$user_id = (int) $_SESSION['user_id'];
$pageError = null;
$user = null;
$noticeStatus  = $_GET['status'] ?? '';
$noticeMessage = trim((string) ($_GET['message'] ?? ''));
$noticeClass   = $noticeStatus === 'success' ? 'auth-message auth-message-success' : 'auth-message auth-message-error';

if (!$db) {
    $pageError = 'Kết nối dữ liệu đang gặp sự cố. Vui lòng thử lại sau.';
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $pageError = 'Vui lòng điền đầy đủ tất cả các trường.';
    } elseif (strlen($newPassword) < 6) {
        $pageError = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } elseif ($newPassword !== $confirmPassword) {
        $pageError = 'Mật khẩu mới và xác nhận không khớp.';
    } else {
        try {
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($currentPassword, $row['password'])) {
                $pageError = 'Mật khẩu hiện tại không đúng.';
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
                header('Location: ' . route_url('user.settings.account', ['status' => 'success', 'message' => 'Đổi mật khẩu thành công.']));
                exit;
            }
        } catch (Throwable $e) {
            $pageError = 'Không thể đổi mật khẩu lúc này. Vui lòng thử lại sau.';
        }
    }
}

// Lấy thông tin user để hiển thị sidebar + email
if ($db) {
    try {
        $stmt = $db->prepare("SELECT name, email, avatar FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        // không block trang
    }
}

$displayName   = htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? 'Người dùng');
$displayAvatar = !empty($user['avatar'])
    ? $user['avatar']
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'U') . '&background=10b981&color=fff&size=160&rounded=true&bold=true';
$profileEmail = htmlspecialchars($user['email'] ?? '');
$currentSettingsRoute = 'user.settings.account';

include __DIR__ . '/../../layouts/header.php';
?>

<div class="settings-shell">
    <div class="container settings-container">
        <div class="settings-layout">

            <?php include __DIR__ . '/_sidebar.php'; ?>

            <main class="settings-main">

                <!-- Email (readonly) -->
                <div class="settings-card mb-4">
                    <h2 class="settings-card-title">Địa chỉ Email</h2>
                    <p class="settings-card-subtitle">Email được dùng để đăng nhập và nhận thông báo từ SpinBike.</p>
                    <div class="settings-email-row">
                        <input type="email" class="form-control profile-field-input profile-field-readonly"
                            value="<?php echo $profileEmail; ?>" readonly>
                        <span class="settings-email-badge"><i class="fa-solid fa-lock"></i> Không thể thay đổi</span>
                    </div>
                </div>

                <!-- Đổi mật khẩu -->
                <div class="settings-card">
                    <h2 class="settings-card-title">Đổi mật khẩu</h2>
                    <p class="settings-card-subtitle">Sử dụng mật khẩu mạnh, ít nhất 6 ký tự, không dùng lại mật khẩu cũ.</p>

                    <?php if ($noticeMessage !== ''): ?>
                    <div class="<?php echo $noticeClass; ?>"><?php echo htmlspecialchars($noticeMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($pageError !== null): ?>
                    <div class="auth-message auth-message-error"><?php echo htmlspecialchars($pageError); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" autocomplete="off">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="profile-field-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control profile-field-input"
                                    placeholder="Nhập mật khẩu hiện tại" required autocomplete="current-password">
                            </div>
                            <div class="col-md-6">
                                <label class="profile-field-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" class="form-control profile-field-input"
                                    placeholder="Tối thiểu 6 ký tự" required autocomplete="new-password">
                            </div>
                            <div class="col-md-6">
                                <label class="profile-field-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control profile-field-input"
                                    placeholder="Nhập lại mật khẩu mới" required autocomplete="new-password">
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn-save">Đổi mật khẩu</button>
                        </div>
                    </form>
                </div>

            </main>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
