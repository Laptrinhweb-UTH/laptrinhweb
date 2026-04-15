<?php
session_start();

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    header("Location: /spinbike/app/views/auth/auth.php");
    exit;
}

// 2. KẾT NỐI DATABASE
require_once __DIR__ . '/../../helpers/Database.php';
$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// 3. XỬ LÝ LƯU DỮ LIỆU KHI BẤM NÚT "LƯU THAY ĐỔI"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nhận dữ liệu từ form
    $name = trim($_POST['fullname'] ?? ''); // Form gửi lên là fullname, gán vào biến $name
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Lấy thông tin cũ để giữ lại avatar nếu không upload ảnh mới
    $stmt_old = $db->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt_old->execute([$user_id]);
    $old_user = $stmt_old->fetch(PDO::FETCH_ASSOC);
    $avatar_url = $old_user['avatar'] ?? null;

    // Xử lý Upload Avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $upload_dir = __DIR__ . '/../../../public/assets/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_exts)) {
            $new_file_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $avatar_url = '/spinbike/public/assets/uploads/' . $new_file_name;
            }
        }
    }

    // Cập nhật thông tin vào Database (DÙNG ĐÚNG CỘT `name`)
    try {
        $query = "UPDATE users SET name = ?, phone = ?, address = ?, avatar = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$name, $phone, $address, $avatar_url, $user_id]);
        
        echo "<script>alert('Cập nhật thông tin thành công!'); window.location.href='profile.php';</script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Lỗi cập nhật: " . $e->getMessage() . "');</script>";
    }
}

// 4. LẤY DỮ LIỆU ĐỂ HIỂN THỊ LÊN FORM
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý hiển thị Avatar (LẤY TỪ CỘT `name`)
$display_name = htmlspecialchars($user['name'] ?? 'U');
$display_avatar = !empty($user['avatar']) ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($display_name) . "&background=10b981&color=fff&size=100&rounded=true&bold=true";

// 5. NHÚNG GIAO DIỆN
include __DIR__ . '/../layouts/header.php'; 
?>

<style>
    .profile-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
    .profile-sidebar { background: #fff; border-radius: 16px; border: 1px solid var(--border, #e2e8f0); overflow: hidden; }
    .profile-nav-link { display: flex; align-items: center; padding: 16px 20px; color: #475569; text-decoration: none; font-weight: 600; transition: all 0.2s; border-left: 3px solid transparent; font-size: 15px; }
    .profile-nav-link:hover { background: #f8fafc; color: #10b981; }
    .profile-nav-link.active { background: #ecfdf5; color: #10b981; border-left-color: #10b981; }
    .profile-nav-link i { width: 24px; font-size: 18px; margin-right: 12px; color: inherit; }
    .profile-card { background: #fff; border-radius: 16px; border: 1px solid var(--border, #e2e8f0); padding: 40px; }
    .avatar-section { display: flex; align-items: center; gap: 24px; margin-bottom: 32px; }
    .avatar-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .form-label { font-weight: 600; font-size: 14px; color: #334155; margin-bottom: 8px; }
    .form-control-custom { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box; }
    .form-control-custom:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
    .btn-save { background: #10b981; color: white; padding: 12px 32px; border: none; border-radius: 10px; font-weight: 600; transition: 0.3s; }
    .btn-save:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
    .btn-upload { background: #fff; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-weight: 600; color: #334155; cursor: pointer; transition: 0.2s; font-size: 14px; }
    .btn-upload:hover { background: #f8fafc; border-color: #94a3b8; }
</style>

<div class="main-content" style="background-color: #f8fafc; min-height: calc(100vh - 100px);">
    <div class="profile-container">
        <div class="row g-5">
            
            <div class="col-lg-3">
                <div class="profile-sidebar">
                    <a href="profile.php" class="profile-nav-link active">
                        <i class="fa-regular fa-user"></i> Hồ sơ của tôi
                    </a>
                    <a href="#" class="profile-nav-link">
                        <i class="fa-solid fa-lock"></i> Đổi mật khẩu
                    </a>
                    <a href="/spinbike/app/views/account/orders.php" class="profile-nav-link">
                        <i class="fa-solid fa-box"></i> Đơn hàng mua
                    </a>
                    <a href="#" class="profile-nav-link">
                        <i class="fa-solid fa-shop"></i> Quản lý bán hàng
                    </a>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="profile-card">
                    <h2 style="font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Thông tin cá nhân</h2>
                    <p style="color: #64748b; font-size: 15px; margin-bottom: 32px;">Cập nhật thông tin cá nhân và ảnh đại diện của bạn.</p>

                    <form action="profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="avatar-section">
                            <img src="<?= $display_avatar ?>" alt="Avatar" class="avatar-img" id="avatarPreview">
                            <div>
                                <label for="avatarInput" class="btn-upload d-inline-block mb-2">
                                    Thay đổi ảnh đại diện
                                </label>
                                <input type="file" id="avatarInput" name="avatar" class="d-none" accept="image/jpeg, image/png">
                                <p style="font-size: 13px; color: #94a3b8; margin: 0;">Định dạng: JPG, PNG. Tối đa 2MB.</p>
                            </div>
                        </div>

                        <hr style="border-color: #e2e8f0; margin-bottom: 32px;">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Họ và tên</label>
                                <input type="text" name="fullname" class="form-control-custom" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="VD: Nguyễn Hoài Nam" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control-custom" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Địa chỉ Email</label>
                                <input type="email" name="email" class="form-control-custom" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly style="background-color: #f1f5f9; color: #64748b; cursor: not-allowed;">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Địa chỉ (Tỉnh/Thành phố)</label>
                                <input type="text" name="address" class="form-control-custom" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="VD: Quận 10, TP.HCM">
                            </div>
                        </div>

                        <div class="mt-5 text-end">
                            <button type="submit" class="btn-save">
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
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>