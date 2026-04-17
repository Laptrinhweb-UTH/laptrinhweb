<?php
session_start();
require_once __DIR__ . '/../../../config/config.php';

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    header("Location: " . app_url('app/views/auth/auth.php'));
    exit;
}

// 2. KẾT NỐI DATABASE & BẬT CHẾ ĐỘ BÁO LỖI (Kỷ luật thép)
require_once __DIR__ . '/../../helpers/Database.php';
$database = new Database();
$db = $database->getConnectionOrNull();
$user_id = $_SESSION['user_id'];
$profileError = null;
$user = null;
$profileStatus = $_GET['status'] ?? '';
$profileMessage = trim((string)($_GET['message'] ?? ''));
$profileNoticeClass = $profileStatus === 'success' ? 'auth-message auth-message-success' : 'auth-message auth-message-error';

if (!$db) {
    $profileError = 'Thông tin tài khoản hiện chưa thể tải. Vui lòng kiểm tra kết nối dữ liệu và thử lại sau.';
} else {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// 3. XỬ LÝ LƯU DỮ LIỆU
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $db) {
    $name = trim($_POST['fullname'] ?? ''); 
    $phone = trim($_POST['phone'] ?? '');
    // Bỏ qua biến $address vì Database không có cột này
    
    if (empty($name)) {
        $profileError = 'Tên hiển thị không được để trống.';
    }

    if ($profileError === null) {
        // Lấy thông tin cũ
        $stmt_old = $db->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt_old->execute([$user_id]);
        $old_user = $stmt_old->fetch(PDO::FETCH_ASSOC);
        $avatar_url = $old_user['avatar'] ?? null;
    }

    // ==========================================
    // ĐẨY ẢNH LÊN CLOUDINARY
    // ==========================================
    if ($profileError === null && isset($_FILES['avatar']) && $_FILES['avatar']['name'] !== '') {
        if ($_FILES['avatar']['error'] !== 0) {
            $profileError = 'Tệp ảnh tải lên đang gặp sự cố. Vui lòng chọn lại ảnh khác.';
        }

        if ($profileError === null) {
            $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_exts)) {
                $cloud_name = CLD_CLOUD_NAME;
                $upload_preset = CLD_UPLOAD_PRESET;
                $file_tmp = $_FILES['avatar']['tmp_name'];

                $ch = curl_init('https://api.cloudinary.com/v1_1/' . $cloud_name . '/image/upload');
                $cfile = new CURLFile($file_tmp, $_FILES['avatar']['type'], $_FILES['avatar']['name']);
                
                $data = [
                    'file' => $cfile,
                    'upload_preset' => $upload_preset
                ];

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                
                if ($response === false) {
                    $profileError = 'Không thể tải ảnh đại diện lên lúc này. Vui lòng thử lại sau.';
                } else {
                    $result = json_decode($response, true);
                    if (isset($result['secure_url'])) {
                        $avatar_url = $result['secure_url'];
                    } else {
                        $profileError = $result['error']['message'] ?? 'Dịch vụ ảnh đại diện đang từ chối yêu cầu. Vui lòng thử lại sau.';
                    }
                }

                curl_close($ch);
            } else {
                $profileError = 'Ảnh đại diện chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.';
            }
        }
    }

    // ==========================================
    // CẬP NHẬT DATABASE (Đã xóa cột address)
    // ==========================================
    if ($profileError === null) {
    try {
        $query = "UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $phone, $avatar_url, $user_id]);
        
        header('Location: profile.php?status=success&message=' . rawurlencode('Cập nhật thông tin thành công.'));
        exit;
    } catch (Exception $e) {
        $profileError = 'Không thể lưu thay đổi lúc này. Vui lòng thử lại sau.';
    }
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !$db) {
    $profileError = 'Không thể lưu thay đổi lúc này vì kết nối dữ liệu đang gặp sự cố.';
}

// 4. LẤY DỮ LIỆU ĐỂ HIỂN THỊ
if ($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $profileError = 'Không tìm thấy thông tin tài khoản của bạn trong hệ thống.';
        }
    } catch (Throwable $exception) {
        $profileError = 'Thông tin tài khoản hiện chưa thể tải. Vui lòng thử lại sau.';
    }
}

$display_name = htmlspecialchars($user['name'] ?? 'U');
$display_avatar = !empty($user['avatar']) ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($display_name) . "&background=10b981&color=fff&size=120&rounded=true&bold=true";
$profileName = trim((string)($user['name'] ?? ''));
$profilePhone = trim((string)($user['phone'] ?? ''));
$profileEmail = trim((string)($user['email'] ?? ''));
$profilePageUrl = app_url('app/views/auth/profile.php');
$buyerOrdersUrl = app_url('app/views/orders/index.php') . '?view=buyer';
$sellerOrdersUrl = app_url('app/views/orders/index.php') . '?view=seller';

if ($profileName === '') {
    $profileName = 'Người dùng SpinBike';
}

if ($profilePhone === '') {
    $profilePhone = 'Chưa cập nhật số điện thoại';
}

if ($profileEmail === '') {
    $profileEmail = 'Chưa cập nhật email';
}

include __DIR__ . '/../layouts/header.php'; 
?>

<div class="main-content profile-page-shell">
    <div class="profile-container profile-page-container">
        <div class="row g-5">
            
            <div class="col-lg-3">
                <div class="profile-sidebar">
                    <a href="<?php echo $profilePageUrl; ?>" class="profile-nav-link active">
                        <i class="fa-regular fa-user"></i> Hồ sơ của tôi
                    </a>
                    <a href="javascript:void(0)" class="profile-nav-link">
                        <i class="fa-solid fa-lock"></i> Đổi mật khẩu
                    </a>
                    <a href="<?php echo $buyerOrdersUrl; ?>" class="profile-nav-link">
                        <i class="fa-solid fa-box"></i> Đơn hàng mua
                    </a>
                    <a href="<?php echo $sellerOrdersUrl; ?>" class="profile-nav-link">
                        <i class="fa-solid fa-shop"></i> Quản lý bán hàng
                    </a>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="profile-card">
                    <h2 class="profile-page-title">Thông tin cá nhân</h2>
                    <?php if ($profileMessage !== ''): ?>
                    <div class="<?php echo $profileNoticeClass; ?>">
                        <?php echo htmlspecialchars($profileMessage); ?>
                    </div>
                    <?php endif; ?>

                    <p class="profile-page-subtitle">
                        <?php echo $profileError === null ? 'Cập nhật thông tin cá nhân và ảnh đại diện của bạn.' : htmlspecialchars($profileError); ?>
                    </p>

                    <?php if ($profileError === null): ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div class="profile-avatar-section">
                            <img src="<?= $display_avatar ?>" alt="Avatar" id="avatarPreview" class="profile-avatar-image">
                            
                            <div>
                                <label for="avatarInput" class="btn-upload profile-upload-label">
                                    Thay đổi ảnh đại diện
                                </label>
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" class="visually-hidden-input" onchange="previewAvatar(event)">
                                <p class="profile-upload-hint">Định dạng: JPG, PNG. Tối đa 2MB.</p>
                            </div>
                        </div>

                        <hr class="profile-divider">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="profile-field-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="fullname" class="form-control profile-field-input" value="<?= htmlspecialchars($profileName) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="profile-field-label">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control profile-field-input" value="<?= htmlspecialchars($profilePhone === 'Chưa cập nhật số điện thoại' ? '' : $profilePhone) ?>" placeholder="Chưa cập nhật số điện thoại">
                            </div>
                            <div class="col-md-12">
                                <label class="profile-field-label">Địa chỉ Email</label>
                                <input type="email" name="email" class="form-control profile-field-input profile-field-readonly" value="<?= htmlspecialchars($profileEmail) ?>" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="profile-field-label">Địa chỉ (Tỉnh/Thành phố)</label>
                                <input type="text" name="address" class="form-control profile-field-input profile-field-readonly profile-field-placeholder" value="Chưa hỗ trợ lưu địa chỉ" readonly>
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" class="btn-save">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="auth-message auth-message-error">
                        <?php echo htmlspecialchars($profileError); ?>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo asset_url('index.php'); ?>" class="btn-detail product-detail-link">Quay lại trang chủ</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    const previewImg = document.getElementById('avatarPreview');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.opacity = '0.5';
            setTimeout(() => {
                previewImg.style.opacity = '1';
                previewImg.style.transition = 'opacity 0.3s ease';
            }, 150);
        }
        reader.readAsDataURL(file);
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
