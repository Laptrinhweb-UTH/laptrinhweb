<?php include 'config/includes/header.php'; ?>

<main class="main-content" style="max-width: 1000px; margin: 40px auto;">
    <div class="checkout-header">
        <h2><i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> Thanh Toán Đảm Bảo</h2>
        <p>Tiền của bạn sẽ được SpinBike giữ an toàn và chỉ chuyển cho người bán khi bạn xác nhận đã nhận đúng xe.</p>
    </div>

    <div class="checkout-grid">
        <div class="checkout-left">
            <div class="payment-box">
                <h3>Chuyển khoản qua Ngân hàng</h3>
                <div class="alert-info">
                    <i class="fa-solid fa-circle-info"></i> Vui lòng chuyển khoản đúng số tiền và nội dung để hệ thống duyệt tự động.
                </div>
                
                <div class="bank-details-wrapper">
                    <div class="qr-code-box">
                        <img src="https://api.vietqr.io/image/970436-0123456789-Zq0wP3b.jpg?accountName=SPINBIKE%20ESCROW&amount=15000000&addInfo=SPINBIKE%20DH1002" alt="QR Code">
                        <p>Quét mã QR để thanh toán</p>
                    </div>
                    
                    <div class="bank-info">
                        <div class="info-row">
                            <span>Ngân hàng:</span>
                            <strong>Vietcombank</strong>
                        </div>
                        <div class="info-row">
                            <span>Chủ tài khoản:</span>
                            <strong>CTY TNHH SPINBIKE (TRUNG GIAN)</strong>
                        </div>
                        <div class="info-row">
                            <span>Số tài khoản:</span>
                            <strong class="copy-text">0123456789 <i class="fa-regular fa-copy"></i></strong>
                        </div>
                        <div class="info-row">
                            <span>Nội dung CK:</span>
                            <strong class="copy-text text-danger">SPINBIKE DH1002 <i class="fa-regular fa-copy"></i></strong>
                        </div>
                    </div>
                </div>

                <button class="btn-confirm-payment">
                    <i class="fa-solid fa-check-double"></i> Tôi đã chuyển khoản
                </button>
            </div>
        </div>

        <div class="checkout-right">
            <div class="order-summary">
                <h3>Tóm tắt đơn hàng</h3>
                <div class="order-product">
                    <img src="https://picsum.photos/id/1015/200/200" alt="Xe đạp">
                    <div class="product-brief">
                        <h4>Trek Domane SL 5 2022</h4>
                        <p>Người bán: <strong>Trần Văn A</strong></p>
                    </div>
                </div>
                
                <hr class="summary-divider">

                <div class="summary-row">
                    <span>Giá xe:</span>
                    <span>15,000,000 đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span>Thỏa thuận khi giao</span>
                </div>
                <div class="summary-row">
                    <span>Phí giao dịch (5%):</span>
                    <span style="color: var(--text-secondary); font-style: italic;">Người bán chịu</span>
                </div>

                <hr class="summary-divider">

                <div class="summary-row total-row">
                    <span>Tổng thanh toán:</span>
                    <span class="total-price">15,000,000 đ</span>
                </div>
                
                <div class="escrow-badge">
                    <i class="fa-solid fa-lock"></i> Giao dịch được bảo vệ 100% bởi SpinBike
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'config/includes/footer.php'; ?>