<?php
$footerSupportLinks = [
    'Hướng dẫn mua bán an toàn',
    'Mẹo kiểm tra xe đạp cũ',
    'Quy định đăng tin & kiểm duyệt',
    'Chính sách giải quyết tranh chấp',
];

$footerCatalogLinks = [
    'Xe đạp Road (Cuộc) thanh lý',
    'Xe đạp MTB (Địa hình) cũ',
    'Phụ tùng & Phụ kiện qua sử dụng',
    'Cộng đồng SpinBike',
];

$footerSocialLinks = [
    ['title' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f'],
    ['title' => 'Instagram', 'icon' => 'fa-brands fa-instagram'],
    ['title' => 'YouTube', 'icon' => 'fa-brands fa-youtube'],
];
?>

<footer class="pro-footer">
    <div class="footer-main">
        <div class="container footer-grid">
            <section class="footer-widget brand-info">
                <h2 class="footer-logo">SPINBIKE<span>.</span></h2>
                <p>Nền tảng kết nối mua bán xe đạp thể thao cũ, kết hợp cơ chế giữ tiền an toàn để người mua và người bán giao dịch minh bạch hơn.</p>
                <div class="pro-socials">
                    <?php foreach ($footerSocialLinks as $social): ?>
                        <a href="#" title="<?php echo $social['title']; ?>">
                            <i class="<?php echo $social['icon']; ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <nav class="footer-widget" aria-label="Hỗ trợ người dùng">
                <h4>Hỗ Trợ Người Dùng</h4>
                <ul>
                    <?php foreach ($footerSupportLinks as $link): ?>
                        <li><a href="#"><?php echo $link; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <nav class="footer-widget" aria-label="Khám phá danh mục">
                <h4>Khám Phá & Danh Mục</h4>
                <ul>
                    <?php foreach ($footerCatalogLinks as $link): ?>
                        <li><a href="#"><?php echo $link; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <section class="footer-widget contact-widget">
                <h4>Liên Hệ Ban Quản Trị</h4>
                <ul>
                    <li><strong>Văn phòng:</strong> 123 Đường Giao Dịch, Quận 10, TP.HCM</li>
                    <li><strong>Hotline CSKH:</strong> 1900 1234 (8h - 20h)</li>
                    <li><strong>Email:</strong> hotro@spinbike.vn</li>
                </ul>
            </section>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container bottom-flex">
            <p>&copy; 2026 SPINBIKE. Đồ án Lập Trình Web.</p>
            <div class="payment-methods">
                <span>Mô phỏng thanh toán an toàn:</span>
                <strong>VNPay</strong> | <strong>MoMo</strong> | <strong>Escrow</strong>
            </div>
        </div>
    </div>
</footer>
