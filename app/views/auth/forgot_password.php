<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="auth-page-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <i class="fa-solid fa-bicycle"></i>
            <h2 class="auth-brand-title">SpinBike</h2>
        </div>

        <div class="auth-title" style="text-align: center; margin-bottom: 10px;">
            <h3 style="color: #0f172a; font-size: 1.25rem; font-weight: 700;">Khôi phục mật khẩu</h3>
        </div>

        <p style="text-align: center; color: #64748b; font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5;">
            Đừng lo lắng! Hãy nhập email bạn đã đăng ký, chúng tôi sẽ gửi cho bạn một đường dẫn để đặt lại mật khẩu an toàn.
        </p>

        <form action="<?= route_url('auth.send_reset_link') ?>" method="POST" class="auth-form active">
            <div class="input-group">
                <label>Email đăng ký</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="VD: spinbike@gmail.com" required style="width: 100%; border: none; outline: none; background: transparent; padding: 10px 0;"/>
                </div>
            </div>

            <button type="submit" class="btn-submit auth-submit-btn" style="margin-top: 15px;">
                Gửi đường dẫn khôi phục
            </button>
        </form>

        <a href="<?= route_url('auth.login') ?>" class="back-home" style="display: block; text-align: center; margin-top: 25px;">
            <i class="fa-solid fa-arrow-left"></i> Quay lại trang Đăng nhập
        </a>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>