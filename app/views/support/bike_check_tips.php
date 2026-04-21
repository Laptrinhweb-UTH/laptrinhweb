<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="container mt-5 mb-5" style="min-height: 70vh; max-width: 900px;">
    <article class="support-content bg-white p-4 p-md-5 rounded shadow-sm border">
        
        <div class="text-center mb-5">
            <h1 class="mb-3" style="color: #0a4d68; font-weight: 800;">
                <i class="fa-solid fa-screwdriver-wrench text-primary"></i> Mẹo Kiểm Tra Xe Đạp Cũ
            </h1>
            <p class="text-muted fs-5">Đừng để vẻ ngoài đánh lừa - Hãy kiểm tra như một chuyên gia</p>
            <hr style="width: 100px; margin: 0 auto; border-top: 3px solid #3b82f6; opacity: 1;">
        </div>

        <section class="mb-5">
            <h4 class="fw-bold" style="color: #1e293b;">
                <span class="badge bg-primary me-2">1</span> Kiểm tra Khung & Phuộc (Trái tim của xe)
            </h4>
            <div class="p-3 bg-light rounded mt-3">
                <ul>
                    <li class="mb-2"><strong>Vết nứt chân chim:</strong> Kiểm tra kỹ các mối hàn và khu vực quanh cốt yên. Với khung Carbon, dùng móng tay gõ nhẹ, nếu tiếng "cạch cạch" đanh là ổn, tiếng "bộp bộp" đục là có thể bị nứt trong.</li>
                    <li class="mb-2"><strong>Độ thẳng:</strong> Nhìn từ phía sau xem bánh sau và bánh trước có thẳng hàng không. Xe bị đâm đụng nặng thường sẽ bị lệch khung.</li>
                    <li><strong>Móp méo:</strong> Khung nhôm/thép bị móp sâu sẽ làm yếu cấu trúc sườn đáng kể.</li>
                </ul>
            </div>
        </section>

        <section class="mb-5">
            <h4 class="fw-bold" style="color: #1e293b;">
                <span class="badge bg-primary me-2">2</span> Hệ thống truyền động (Drivetrain)
            </h4>
            <div class="row mt-3">
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="fa-solid fa-link text-warning"></i> Độ mòn xích & líp</h6>
                            <p class="small">Nếu răng líp nhọn hoắt như răng cá mập, nghĩa là líp đã quá mòn. Thử kéo xích ra khỏi đĩa trước, nếu hở ra quá nửa răng là xích đã giãn.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="fa-solid fa-shuffle text-info"></i> Độ nhạy bộ đề</h6>
                            <p class="small">Bấm chuyển tất cả các số. Nếu chuyển số bị trễ, kêu lạch cạch hoặc nhảy xích, có thể do dây cáp rỉ sét hoặc cùi đề bị cong.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <h4 class="fw-bold" style="color: #1e293b;">
                <span class="badge bg-primary me-2">3</span> Kiểm tra độ rơ (Vòng bi/Bạc đạn)
            </h4>
            <p>Hãy thực hiện 3 thao tác "lắc" sau đây:</p>
            <div class="list-group">
                <div class="list-group-item">
                    <strong>1. Chén cổ:</strong> Bóp phanh trước, đẩy xe tới lui. Nếu thấy cổ xe bị sượng hoặc kêu "cục cục", bạc đạn chén cổ đã hỏng.
                </div>
                <div class="list-group-item">
                    <strong>2. Trục giữa (BB):</strong> Cầm 2 tay quay (giò đĩa) lắc mạnh sang 2 bên. Nếu có độ rơ (lỏng lẻo), trục giữa cần thay thế.
                </div>
                <div class="list-group-item">
                    <strong>3. May-ơ (Hub):</strong> Nhấc bánh xe lên và lắc ngang vành. Bánh xe chỉ được quay tròn, không được có độ rơ ngang.
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="p-4 rounded-3" style="background-color: #f0fdf4; border: 1px solid #dcfce7;">
                <h4 class="text-success fw-bold"><i class="fa-solid fa-bicycle"></i> Khi chạy thử (Test Ride)</h4>
                <ul class="mb-0 mt-3">
                    <li>Buông cả 2 tay (nếu bạn có kỹ năng) để xem xe có bị xỉa về một bên không.</li>
                    <li>Đứng lên đạp mạnh (Sprint) để kiểm tra xem trục giữa có tiếng kêu lạ khi chịu lực lớn không.</li>
                    <li>Bóp phanh đột ngột để kiểm tra lực phanh và độ ổn định của phuộc trước.</li>
                </ul>
            </div>
        </section>

        <div class="d-flex justify-content-between align-items-center mt-5">
            <a href="<?= route_url('support.safe_trading') ?>" class="text-decoration-none">
                <i class="fa-solid fa-chevron-left"></i> Xem hướng dẫn mua bán an toàn
            </a>
            <a href="<?= route_url('home') ?>" class="btn btn-primary px-4">Xong, quay về trang chủ</a>
        </div>
    </article>
</main>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>