<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="container mt-5 mb-5" style="min-height: 70vh; max-width: 900px;">
    <article class="support-content bg-white p-4 p-md-5 rounded shadow-sm border">
        
        <div class="text-center mb-5">
            <h1 class="mb-3" style="color: #0a4d68; font-weight: 800;">
                <i class="fa-solid fa-clipboard-check text-primary"></i> Quy Định Đăng Tin & Kiểm Duyệt
            </h1>
            <p class="text-muted fs-5">Môi trường giao dịch minh bạch, an toàn và chất lượng</p>
            <hr style="width: 100px; margin: 0 auto; border-top: 3px solid #10b981; opacity: 1;">
        </div>

        <p class="mb-4">Để đảm bảo quyền lợi cho cả người mua và người bán, mọi tin đăng trên nền tảng <strong>SpinBike</strong> đều phải trải qua quá trình kiểm duyệt tự động kết hợp thủ công. Dưới đây là các tiêu chuẩn bắt buộc:</p>

        <section class="mb-5">
            <h4 class="fw-bold" style="color: #1e293b;">
                <span class="badge bg-primary me-2">1</span> Yêu cầu về Hình ảnh
            </h4>
            <div class="row mt-3">
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-success bg-success bg-opacity-10">
                        <div class="card-body">
                            <h6 class="text-success fw-bold"><i class="fa-solid fa-check-circle"></i> ĐƯỢC PHÉP (Nên làm)</h6>
                            <ul class="mb-0 small">
                                <li>Hình chụp thực tế của xe ở thời điểm hiện tại.</li>
                                <li>Chụp rõ toàn cảnh xe và cận cảnh các bộ phận quan trọng (bộ đề, lốp, phanh).</li>
                                <li>Chụp rõ các vết xước, móp méo (nếu có) để minh bạch tình trạng.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-danger bg-danger bg-opacity-10">
                        <div class="card-body">
                            <h6 class="text-danger fw-bold"><i class="fa-solid fa-ban"></i> KHÔNG ĐƯỢC PHÉP</h6>
                            <ul class="mb-0 small">
                                <li>Sử dụng ảnh mạng, ảnh catalogue của nhà sản xuất.</li>
                                <li>Ảnh quá mờ, tối, hoặc qua chỉnh sửa che đậy khuyết điểm.</li>
                                <li>Chèn logo, số điện thoại, link website khác đè lên hình ảnh sản phẩm.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

<section class="mb-5">
            <h4 class="fw-bold" style="color: #1e293b;">
                <span class="badge bg-primary me-2">2</span> Yêu cầu về Nội dung mô tả
            </h4>
            <div class="p-4 bg-light rounded mt-3 border">
                <p>Nội dung cần miêu tả trung thực và chi tiết về cấu hình xe. Bắt buộc phải có các thông tin sau:</p>
                <ul class="mb-0">
                    <li class="mb-2"><strong>Thương hiệu & Model:</strong> (Ví dụ: Giant Escape 2, Trek Marlin 5...)</li>
                    <li class="mb-2"><strong>Tình trạng xe:</strong> Mức độ hao mòn, các phụ tùng đã thay thế hoặc nâng cấp.</li>
                    <li class="mb-2"><strong>Size xe (Kích cỡ):</strong> (Ví dụ: Size S, M hoặc cho người cao 1m60 - 1m70).</li>
                    <li><strong class="text-danger">Lưu ý:</strong> Không giao dịch bên ngoài hệ thống. SpinBike sẽ không chịu trách nhiệm đối với mọi rủi ro phát sinh từ các giao dịch ngoài.</li>
                </ul>
            </div>
        </section>

        <section class="mb-5">
            <h4 class="fw-bold" style="color: #1e293b;">
                <span class="badge bg-primary me-2">3</span> Các loại xe & Hàng hóa TỪ CHỐI DUYỆT
            </h4>
            <ul class="list-group list-group-flush mt-3 border-start border-3 border-danger">
                <li class="list-group-item border-0"><strong>Xe đạp giả/nhái (Fake/Replica):</strong> Đăng xe nhái nhưng cố tình ghi tên thương hiệu thật để lừa đảo. Nếu là xe rep 1:1, phải ghi rõ chữ "Fake" hoặc "Replica" trên tiêu đề.</li>
                <li class="list-group-item border-0"><strong>Xe không rõ nguồn gốc/Xe gian:</strong> Các trường hợp bị cộng đồng report là xe ăn cắp sẽ bị khóa tài khoản vĩnh viễn và cung cấp thông tin cho cơ quan chức năng.</li>
                <li class="list-group-item border-0"><strong>Sản phẩm sai danh mục:</strong> Đăng bán xe máy, xe điện (không có bàn đạp), linh kiện máy tính, mỹ phẩm... trên sàn SpinBike.</li>
            </ul>
        </section>

        <section class="mb-4">
            <div class="alert alert-info border-0 bg-info bg-opacity-10 d-flex align-items-center">
                <i class="fa-solid fa-clock-rotate-left fs-1 text-info me-3"></i>
                <div>
                    <h5 class="fw-bold text-info-emphasis mb-1">Thời gian kiểm duyệt</h5>
                    <p class="mb-0 small text-info-emphasis">Tin đăng của bạn sẽ ở trạng thái <strong>"Chờ duyệt"</strong>. Đội ngũ SpinBike sẽ tiến hành kiểm tra và duyệt tin trong khoảng từ <strong>1 đến 4 giờ làm việc</strong> (8h00 - 22h00 hàng ngày).</p>
                </div>
            </div>
        </section>

        <div class="d-flex justify-content-between align-items-center mt-5">
            <a href="<?= route_url('support.bike_check_tips') ?>" class="text-decoration-none">
                <i class="fa-solid fa-chevron-left"></i> Xem Mẹo kiểm tra xe
            </a>
            <a href="<?= route_url('home') ?>" class="btn btn-primary px-4">Xong, quay về trang chủ</a>
        </div>
    </article>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>