<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h3 class="text-center">Quên mật khẩu</h3>
            <form action="index.php?action=send_reset_link" method="POST" class="mt-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Nhập email đăng ký của bạn</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Gửi mã khôi phục</button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php?action=login">Quay lại Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>