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
    $name    = trim($_POST['fullname'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

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
            $db->prepare("UPDATE users SET name = ?, phone = ?, address = ?, avatar = ? WHERE id = ?")
               ->execute([$name, $phone, $address ?: null, $avatar_url, $user_id]);
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
$profileName    = $user['name'] ?? '';
$profilePhone   = $user['phone'] ?? '';
$profileAddress = $user['address'] ?? '';
$profileEmail   = $user['email'] ?? '';
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
                                <label class="profile-field-label">Địa chỉ</label>
                                <?php if ($profileAddress !== ''): ?>
                                <div class="d-flex align-items-center gap-2 p-3 rounded-3 mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:14px;">
                                    <i class="fa-solid fa-location-dot text-muted"></i>
                                    <span class="text-dark"><?php echo htmlspecialchars($profileAddress); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <select id="addrProvince" class="form-control profile-field-input">
                                            <option value="">-- Tỉnh/Thành phố --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="addrWard" class="form-control profile-field-input" disabled>
                                            <option value="">-- Phường/Xã --</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" id="addrStreet" class="form-control profile-field-input" placeholder="Số nhà, tên đường">
                                    </div>
                                </div>
                                <input type="hidden" name="address" id="profileAddressHidden" value="<?php echo htmlspecialchars($profileAddress); ?>">
                                <p class="profile-upload-hint mt-2">Chọn tỉnh/thành và phường/xã rồi nhập số nhà, tên đường để cập nhật địa chỉ.</p>
                            </div>
                            <div class="col-md-12">
                                <label class="profile-field-label">Email</label>
                                <input type="email" class="form-control profile-field-input profile-field-readonly"
                                    value="<?php echo htmlspecialchars($profileEmail); ?>" readonly>
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

// ==========================================
// ADDRESS PICKER
// ==========================================
const addrHost = "https://provinces.open-api.vn/api/v2/";
const addrProvince = document.getElementById('addrProvince');
const addrWard     = document.getElementById('addrWard');
const addrStreet   = document.getElementById('addrStreet');
const addrHidden   = document.getElementById('profileAddressHidden');

function buildAddrOptions(select, placeholder, items) {
    select.innerHTML = placeholder;
    items.forEach(item => {
        select.innerHTML += `<option value="${item.code}" data-name="${item.name}">${item.name}</option>`;
    });
}

fetch(addrHost + "?depth=1")
    .then(r => r.json())
    .then(data => buildAddrOptions(addrProvince, '<option value="">-- Tỉnh/Thành phố --</option>', Array.isArray(data) ? data : []))
    .catch(() => { addrProvince.innerHTML = '<option value="">Không tải được danh sách</option>'; });

addrProvince.addEventListener('change', function () {
    addrWard.innerHTML = '<option value="">-- Phường/Xã --</option>';
    addrWard.disabled = true;
    if (!this.value) return;
    fetch(addrHost + "p/" + this.value + "?depth=2")
        .then(r => r.json())
        .then(data => {
            const wards = Array.isArray(data.wards) ? data.wards : (Array.isArray(data.districts) ? data.districts : []);
            buildAddrOptions(addrWard, '<option value="">-- Phường/Xã --</option>', wards);
            addrWard.disabled = wards.length === 0;
        })
        .catch(() => { addrWard.innerHTML = '<option value="">Không tải được phường/xã</option>'; });
});

// Compose address into hidden field whenever any part changes
function composeAddress() {
    const provinceName = addrProvince.options[addrProvince.selectedIndex]?.getAttribute('data-name') || '';
    const wardName     = addrWard.options[addrWard.selectedIndex]?.getAttribute('data-name') || '';
    const street       = addrStreet.value.trim();
    const parts        = [street, wardName, provinceName].filter(Boolean);
    if (parts.length > 0) addrHidden.value = parts.join(', ');
}
addrProvince.addEventListener('change', composeAddress);
addrWard.addEventListener('change', composeAddress);
addrStreet.addEventListener('input', composeAddress);
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
