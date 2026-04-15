<?php 
session_start();
require_once __DIR__ . '/../../helpers/Database.php';
require_once __DIR__ . '/../../models/Product.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /spinbike/app/views/auth/auth.php");
    exit;
}

// 2. Kiểm tra ID sản phẩm truyền vào
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo "<script>alert('Lỗi: Không tìm thấy sản phẩm!'); window.location.href='/spinbike/index.php';</script>";
    exit;
}

$db = (new Database())->getConnection();
$productModel = new Product($db);
$product = $productModel->getProductDetail($_GET['product_id']);

if (!$product) {
    echo "<script>alert('Sản phẩm không tồn tại hoặc đã bị xóa!'); window.location.href='/spinbike/index.php';</script>";
    exit;
}

// Chống gian lận: Không cho tự mua hàng của mình
if ($product['seller_id'] == $_SESSION['user_id']) {
    echo "<script>alert('Bạn không thể tự mua xe của chính mình!'); window.location.href='/spinbike/index.php';</script>";
    exit;
}

// Xử lý dữ liệu hiển thị
$formattedPrice = number_format($product['price'], 0, ',', '.') . ' đ';
$mainImage = (!empty($product['images'])) ? $product['images'][0] : 'https://via.placeholder.com/80';

include __DIR__ . '/../layouts/header.php'; 
?>

<div class="container py-5" style="max-width: 1000px;">
    <h2 class="fw-bold mb-4">Thanh toán an toàn</h2>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="escrow-card mb-4">
                <div class="d-flex gap-3">
                    <div class="escrow-icon-wrapper flex-shrink-0">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-success mb-2">Được bảo vệ bởi SpinBike Escrow</h5>
                        <p class="text-muted mb-0" style="font-size: 14px; line-height: 1.6;">
                            Tiền của bạn sẽ được hệ thống giữ lại. Người bán <b>chỉ nhận được tiền</b> sau khi bạn đã nhận xe, kiểm tra đúng mô tả và nhấn xác nhận.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h5 class="fw-bold mb-3">Chọn phương thức thanh toán</h5>
                
                <form action="process_checkout.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="seller_id" value="<?php echo $product['seller_id']; ?>">
                    <input type="hidden" name="amount" value="<?php echo $product['price']; ?>">
                    
                    <label class="d-block mb-3">
                        <input type="radio" name="payment_method" value="vnpay" class="form-check-input d-none" checked>
                        <div class="payment-method-card d-flex align-items-center gap-3">
                            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418189687.png" height="30" alt="VNPay">
                            <span class="fw-bold text-dark">Thanh toán qua VNPay</span>
                        </div>
                    </label>

                    <label class="d-block mb-4">
                        <input type="radio" name="payment_method" value="momo" class="form-check-input d-none">
                        <div class="payment-method-card d-flex align-items-center gap-3">
                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" height="30" alt="MoMo">
                            <span class="fw-bold text-dark">Thanh toán qua Ví MoMo</span>
                        </div>
                    </label>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-5 shadow-sm">
                        <i class="fa-solid fa-lock"></i> Thanh toán an toàn <?php echo $formattedPrice; ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 p-4 sticky-top" style="top: 20px;">
                <h5 class="fw-bold mb-4">Tóm tắt đơn hàng</h5>
                <div class="d-flex gap-3 mb-4">
                    <img src="<?php echo $mainImage; ?>" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid var(--border);">
                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="line-height: 1.4;"><?php echo htmlspecialchars($product['title']); ?></h6>
                        <span class="badge bg-light text-dark border mt-1"><i class="fa-solid fa-user"></i> Người bán: ID <?php echo $product['seller_id']; ?></span>
                    </div>
                </div>
                <hr class="text-muted">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tạm tính</span>
                    <span class="fw-bold"><?php echo $formattedPrice; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Phí giữ tiền an toàn</span>
                    <span class="text-success fw-bold">Miễn phí cho người mua</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="fw-bold text-dark">Tổng thanh toán</span>
                    <span class="fw-bold fs-4 text-primary"><?php echo $formattedPrice; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>