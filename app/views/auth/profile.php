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
$db = (new Database())->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ép Database phải báo lỗi nếu có
$user_id = $_SESSION['user_id'];

// 3. XỬ LÝ LƯU DỮ LIỆU
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['fullname'] ?? ''); 
    $phone = trim($_POST['phone'] ?? '');
    // Bỏ qua biến $address vì Database không có cột này
    
    if (empty($name)) {
        die("<script>alert('Lỗi: Tên không được để trống!'); window.history.back();</script>");
    }

    // Lấy thông tin cũ
    $stmt_old = $db->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt_old->execute([$user_id]);
    $old_user = $stmt_old->fetch(PDO::FETCH_ASSOC);
    $avatar_url = $old_user['avatar'] ?? null;

    // ==========================================
    // ĐẨY ẢNH LÊN CLOUDINARY
    // ==========================================
    if (isset($_FILES['avatar']) && $_FILES['avatar']['name'] !== '') {
        if ($_FILES['avatar']['error'] !== 0) {
            die("<script>alert('Lỗi file từ máy tính (Mã: " . $_FILES['avatar']['error'] . ")'); window.history.back();</script>");
        }

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
                die("<script>alert('Lỗi XAMPP cURL: " . addslashes(curl_error($ch)) . "'); window.history.back();</script>");
            } 
            
            $result = json_decode($response, true);
            if (isset($result['secure_url'])) {
                $avatar_url = $result['secure_url']; // Lấy link thành công
            } else {
                $err_msg = $result['error']['message'] ?? 'Lỗi từ Cloudinary';
                die("<script>alert('Cloudinary từ chối: " . addslashes($err_msg) . "'); window.history.back();</script>");
            }
            curl_close($ch);
        } else {
            die("<script>alert('Định dạng ảnh không hợp lệ!'); window.history.back();</script>");
        }
    }

    // ==========================================
    // CẬP NHẬT DATABASE (Đã xóa cột address)
    // ==========================================
    try {
        $query = "UPDATE users SET name = ?, phone = ?, avatar = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $phone, $avatar_url, $user_id]);
        
        echo "<script>alert('Cập nhật thông tin thành công!'); window.location.href='profile.php';</script>";
        exit;
    } catch (Exception $e) {
        // Nếu có bất kỳ lỗi nào từ DB, nó sẽ in ra màn hình ngay lập tức!
        die("<script>alert('Lỗi CSDL: " . addslashes($e->getMessage()) . "'); window.history.back();</script>");
    }
}

// 4. LẤY DỮ LIỆU ĐỂ HIỂN THỊ
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$display_name = htmlspecialchars($user['name'] ?? 'U');
$display_avatar = !empty($user['avatar']) ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($display_name) . "&background=10b981&color=fff&size=120&rounded=true&bold=true";

include __DIR__ . '/../layouts/header.php'; 
?>

<div class="main-content" style="background-color: #f8fafc; min-height: calc(100vh - 100px); padding-bottom: 40px;">
    <div class="profile-container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px;">
        <div class="row g-5">
            
            <div class="col-lg-3">
                <div class="profile-sidebar" style="background: #fff; border-radius: 16px; border: 1px solid var(--border); overflow: hidden;">
                    <a href="profile.php" class="profile-nav-link active" style="display: flex; align-items: center; padding: 16px 20px; color: #10b981; background: #ecfdf5; text-decoration: none; font-weight: 600; border-left: 3px solid #10b981;">
                        <i class="fa-regular fa-user" style="width: 24px; margin-right: 12px;"></i> Hồ sơ của tôi
                    </a>
                    <a href="#" class="profile-nav-link" style="display: flex; align-items: center; padding: 16px 20px; color: #475569; text-decoration: none; font-weight: 600;">
                        <i class="fa-solid fa-lock" style="width: 24px; margin-right: 12px;"></i> Đổi mật khẩu
                    </a>
                    <a href="#" class="profile-nav-link" style="display: flex; align-items: center; padding: 16px 20px; color: #475569; text-decoration: none; font-weight: 600;">
                        <i class="fa-solid fa-box" style="width: 24px; margin-right: 12px;"></i> Đơn hàng mua
                    </a>
                    <a href="#" class="profile-nav-link" style="display: flex; align-items: center; padding: 16px 20px; color: #475569; text-decoration: none; font-weight: 600;">
                        <i class="fa-solid fa-shop" style="width: 24px; margin-right: 12px;"></i> Quản lý bán hàng
                    </a>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="profile-card" style="background: #fff; border-radius: 16px; border: 1px solid var(--border); padding: 40px;">
                    <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">Thông tin cá nhân</h2>
                    <p style="color: #64748b; font-size: 15px; margin-bottom: 32px;">Cập nhật thông tin cá nhân và ảnh đại diện của bạn.</p>

                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div style="display: flex; align-items: center; gap: 24px; margin-bottom: 32px;">
                            <img src="<?= $display_avatar ?>" alt="Avatar" id="avatarPreview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            
                            <div>
                                <label for="avatarInput" style="background: #fff; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-weight: 600; color: #334155; cursor: pointer; transition: 0.2s; display: inline-block; margin-bottom: 8px;">
                                    Thay đổi ảnh đại diện
                                </label>
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(event)">
                                <p style="font-size: 13px; color: #94a3b8; margin: 0;">Định dạng: JPG, PNG. Tối đa 2MB.</p>
                            </div>
                        </div>

                        <hr style="border-color: #e2e8f0; margin-bottom: 32px;">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px;">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required style="padding: 12px 16px; border-radius: 10px;">
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px;">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" style="padding: 12px 16px; border-radius: 10px;">
                            </div>
                            <div class="col-md-12">
                                <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px;">Địa chỉ Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly style="padding: 12px 16px; border-radius: 10px; background-color: #f1f5f9; color: #64748b; cursor: not-allowed;">
                            </div>
                            <div class="col-md-12">
                                <label style="font-weight: 600; font-size: 14px; margin-bottom: 8px;">Địa chỉ (Tỉnh/Thành phố)</label>
                                <input type="text" name="address" class="form-control" value="Chưa hỗ trợ lưu địa chỉ" readonly style="padding: 12px 16px; border-radius: 10px; background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed;">
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" style="background: #10b981; color: white; padding: 12px 32px; border: none; border-radius: 10px; font-weight: 600;">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
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