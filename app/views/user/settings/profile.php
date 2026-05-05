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

// Xử lý lưu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    $name = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $pageError = 'Tên hiển thị không được để trống.';
    }

    if ($pageError === null) {
        $stmt = $db->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        $avatar_url = $old['avatar'] ?? null;
    }

    // Upload avatar lên Cloudinary
    if ($pageError === null && !empty($_FILES['avatar']['name'])) {
        if ($_FILES['avatar']['error'] !== 0) {
            $pageError = 'Tệp ảnh tải lên đang gặp sự cố. Vui lòng chọn lại.';
        } else {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $pageError = 'Ảnh đại diện chỉ hỗ trợ JPG, PNG hoặc WEBP.';
            } else {
                $ch = curl_init('https://api.cloudinary.com/v1_1/' . CLD_CLOUD_NAME . '/image/upload');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => [
                        'file'          => new CURLFile($_FILES['avatar']['tmp_name'], $_FILES['avatar']['type'], $_FILES['avatar']['name']),
                        'upload_preset' => CLD_UPLOAD_PRESET,
                    ],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $response = curl_exec($ch);
                curl_close($ch);

                if ($response === false) {
                    $pageError = 'Không thể tải ảnh lên lúc này. Vui lòng thử lại sau.';
                } else {
                    $result = json_decode($response, true);
                    if (isset($result['secure_url'])) {
                        $avatar_url = $result['secure_url'];
                    } else {
                        $pageError = $result['error']['message'] ?? 'Dịch vụ ảnh đại diện từ chối yêu cầu. Vui lòng thử lại.';
                    }
                }
            }
        }
    }

    if ($pageError === null) {
        try {
            $db->prepare("UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?")
               ->execute([$name, $phone, $avatar_url, $user_id]);
            $_SESSION['user_name'] = $name;
            header('Location: ' . route_url('user.settings.profile', ['status' => 'success', 'message' => 'Cập nhật thông tin thành công.']));
            exit;
        } catch (Throwable $e) {
            $pageError = 'Không thể lưu thay đổi lúc này. Vui lòng thử lại sau.';
        }
    }
}

// Lấy dữ liệu hiển thị
if ($db && $user === null) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            $pageError = 'Không tìm thấy thông tin tài khoản.';
        }
    } catch (Throwable $e) {
        $pageError = 'Không thể tải thông tin tài khoản.';
    }
}

$displayName   = htmlspecialchars($user['name'] ?? $_SESSION['user_name'] ?? 'Người dùng');
$displayAvatar = !empty($user['avatar'])
    ? $user['avatar']
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['name'] ?? 'U') . '&background=10b981&color=fff&size=160&rounded=true&bold=true';
$profileName  = $user['name'] ?? '';
$profilePhone = $user['phone'] ?? '';
$profileEmail = $user['email'] ?? '';
$currentSettingsRoute = 'user.settings.profile';

include __DIR__ . '/../../layouts/header.php';
?>

<div class="settings-shell">
    <div class="container settings-container">
        <div class="settings-layout">

            <?php include __DIR__ . '/_sidebar.php'; ?>

            <main class="settings-main">
                <div class="settings-card">
                    <h2 class="settings-card-title">Thông tin cá nhân</h2>
                    <p class="settings-card-subtitle">Cập nhật ảnh đại diện và thông tin hiển thị trên trang cá nhân.</p>

                    <?php if ($noticeMessage !== ''): ?>
                    <div class="<?php echo $noticeClass; ?>"><?php echo htmlspecialchars($noticeMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($pageError !== null): ?>
                    <div class="auth-message auth-message-error"><?php echo htmlspecialchars($pageError); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">

                        <!-- Avatar -->
                        <div class="settings-avatar-section">
                            <img src="<?php echo $displayAvatar; ?>" alt="Avatar" id="avatarPreview" class="settings-avatar-img">
                            <div>
                                <label for="avatarInput" class="btn-upload profile-upload-label">Thay đổi ảnh</label>
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" class="visually-hidden-input" onchange="previewAvatar(event)">
                                <p class="profile-upload-hint">JPG, PNG, WEBP · Tối đa 2MB</p>
                            </div>
                        </div>

                        <hr class="profile-divider">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="profile-field-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="fullname" class="form-control profile-field-input"
                                    value="<?php echo htmlspecialchars($profileName); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="profile-field-label">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control profile-field-input"
                                    value="<?php echo htmlspecialchars($profilePhone); ?>"
                                    placeholder="Chưa cập nhật">
                            </div>
                            <div class="col-md-12">
                                <label class="profile-field-label">Email</label>
                                <input type="email" class="form-control profile-field-input profile-field-readonly"
                                    value="<?php echo htmlspecialchars($profileEmail); ?>" readonly>
                                <p class="profile-upload-hint mt-1">Email không thể thay đổi tại đây. Đổi email trong <a href="<?php echo route_url('user.settings.account'); ?>">Cài đặt tài khoản</a>.</p>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn-save">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </main>

        </div>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('avatarPreview');
        img.src = e.target.result;
        img.style.opacity = '0.5';
        setTimeout(() => { img.style.opacity = '1'; img.style.transition = 'opacity 0.3s'; }, 150);
    };
    reader.readAsDataURL(file);
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
