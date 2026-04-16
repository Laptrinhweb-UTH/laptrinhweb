<?php
session_start(); // Bắt buộc phải có session_start() để lấy $_SESSION['user_id']
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/models/Product.php';

// Kiểm tra ID xe hợp lệ
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

$product = $productModel->getProductDetail($id);

if (!$product) {
    echo "<script>alert('Chiếc xe này không tồn tại!'); window.location.href='index.php';</script>";
    exit;
}

$formattedPrice = number_format($product['price'], 0, ',', '.') . ' đ';
$images = (!empty($product['images'])) ? $product['images'] : ['https://via.placeholder.com/600x400?text=Chua+Co+Anh'];
$sellerId = $product['seller_id'] ?? '1';
$avatarUrl = "https://ui-avatars.com/api/?name=U+{$sellerId}&background=10b981&color=fff&rounded=true&bold=true";

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="main-content" style="background-color: var(--bg); padding: 40px 0;">
    <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 24px;">
        
        <div style="margin-bottom: 24px; font-size: 14.5px; color: var(--text-secondary); display: flex; align-items: center; gap: 8px;">
            <a href="index.php" style="color: var(--primary); text-decoration: none;"><i class="fa-solid fa-house"></i> Trang chủ</a> 
            <i class="fa-solid fa-angle-right" style="font-size: 12px;"></i>
            <span><?php echo htmlspecialchars($product['brand'] ?? 'Khác'); ?></span>
            <i class="fa-solid fa-angle-right" style="font-size: 12px;"></i>
            <span style="color: var(--text-primary); font-weight: 600;"><?php echo htmlspecialchars($product['title']); ?></span>
        </div>

        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; background: #fff; padding: 32px; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--border);">
            
            <div class="detail-images">
                <div style="position: relative; width: 100%; height: 480px; border-radius: 20px; overflow: hidden; margin-bottom: 16px; border: 1px solid var(--border);">
                    
                    <img id="mainImage" src="<?php echo $images[0]; ?>" style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s ease;">

                    <button onclick="prevImage()" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.8); border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: var(--text-primary); transition: 0.2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.8)'">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <button onclick="nextImage()" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.8); border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: var(--text-primary); transition: 0.2s;" onmouseover="this.style.background='#fff'" onmouseout="this.style.background='rgba(255,255,255,0.8)'">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                
                <div style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px;">
                    <?php foreach ($images as $index => $img): ?>
                        <img src="<?php echo $img; ?>" 
                             class="thumb-item"
                             id="thumb-<?php echo $index; ?>"
                             onclick="showImage(<?php echo $index; ?>)" 
                             style="width: 80px; height: 80px; min-width: 80px; object-fit: cover; border-radius: 12px; cursor: pointer; border: 2px solid transparent; transition: 0.2s; <?php echo $index === 0 ? 'border-color: var(--primary-light); opacity: 1;' : 'opacity: 0.6;'; ?>">
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="detail-info">
                <h1 style="font-size: 26px; font-weight: 700; margin: 0 0 16px 0; color: var(--text-primary);"><?php echo htmlspecialchars($product['title']); ?></h1>
                
                <div style="font-size: 32px; font-weight: 800; color: #10b981; margin-bottom: 24px;"><?php echo $formattedPrice; ?></div>
                
                <div style="padding: 24px; background: #f8fafc; border-radius: 20px; margin-bottom: 32px; border: 1px solid var(--border);">
                    
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                        <img src="<?php echo $avatarUrl; ?>" alt="Avatar" style="width: 56px; height: 56px; border-radius: 50%;">
                        <div>
                            <div style="font-weight: 700; font-size: 17px; color: var(--text-primary);">Người bán (ID: <?php echo $sellerId; ?>)</div>
                            <div style="font-size: 13.5px; color: var(--text-secondary); margin-top: 4px;">
                                <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($product['location'] ?? 'Đang cập nhật'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <button onclick="showBuyOptions()" style="width: 100%; background: var(--primary); color: white; border: none; padding: 16px; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; box-shadow: 0 6px 20px rgba(10, 77, 104, 0.25);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                            <i class="fa-solid fa-cart-shopping"></i> MUA NGAY
                        </button>
                        
                        <a href="#" onclick="alert('Tính năng nhắn tin đang được phát triển!'); return false;" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: #ffffff; color: var(--text-primary); text-decoration: none; padding: 14px; border-radius: 12px; font-weight: 600; font-size: 15px; border: 1px solid var(--border); transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">
                            <i class="fa-solid fa-comment-dots" style="font-size: 18px; color: #10b981;"></i> Nhắn tin trao đổi
                        </a>
                    </div>

                </div>

                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 16px;">Thông số kỹ thuật</h3>
                <div style="background: white; border: 1px solid var(--border); border-radius: 16px; overflow: hidden; margin-bottom: 32px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                        <tr style="border-bottom: 1px solid var(--border); background: #f8fafc;">
                            <td style="padding: 14px 16px; color: var(--text-secondary);">Hãng xe</td>
                            <td style="padding: 14px 16px; font-weight: 600; text-align: right;"><?php echo htmlspecialchars($product['brand'] ?? 'Khác'); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 14px 16px; color: var(--text-secondary);">Size khung</td>
                            <td style="padding: 14px 16px; font-weight: 600; text-align: right;"><?php echo htmlspecialchars($product['size'] ?? 'Đang cập nhật'); ?></td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border); background: #f8fafc;">
                            <td style="padding: 14px 16px; color: var(--text-secondary);">Độ mới</td>
                            <td style="padding: 14px 16px; font-weight: 600; text-align: right; color: var(--danger);"><?php echo htmlspecialchars($product['condition'] ?? 'Đang cập nhật'); ?>%</td>
                        </tr>
                        <tr>
                            <td style="padding: 14px 16px; color: var(--text-secondary);">Groupset</td>
                            <td style="padding: 14px 16px; font-weight: 600; text-align: right;"><?php echo htmlspecialchars($product['groupset'] ?? 'Nguyên bản'); ?></td>
                        </tr>
                    </table>
                </div>

                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 12px;">Mô tả bài đăng</h3>
                <div style="color: #475569; line-height: 1.7; font-size: 15px; white-space: pre-wrap; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid var(--border);">
<?php echo htmlspecialchars($product['description'] ?? 'Không có mô tả.'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="buyOptionsModal" class="modal hidden">
    <div class="modal-backdrop" onclick="hideBuyOptions()"></div>
    <div class="modal-content" style="max-width: 500px; padding: 32px; border-radius: 24px; position: relative; z-index: 9999; background: white;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="font-size: 20px; font-weight: 700; margin: 0; color: var(--text-primary);">Chọn phương thức mua hàng</h3>
            <button onclick="hideBuyOptions()" style="background: none; border: none; font-size: 28px; cursor: pointer; color: var(--text-secondary); line-height: 1;">&times;</button>
        </div>
        
        <div style="border: 2px solid #10b981; border-radius: 16px; padding: 20px; margin-bottom: 16px; cursor: pointer; transition: 0.2s; background: #f0fdf4;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'" onclick="processEscrowCheckout()">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                <div style="width: 48px; height: 48px; background: #10b981; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 6px 0; font-size: 16px; color: #065f46; font-weight: 700;">Mua an toàn qua SpinBike</h4>
                    <span style="display: inline-block; background: #d1fae5; color: #047857; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 20px; margin-bottom: 8px;">Khuyên dùng</span>
                    <p style="margin: 0; font-size: 13.5px; color: #047857; line-height: 1.5;">SpinBike sẽ làm trung gian giữ tiền. Chỉ khi bạn kiểm tra và nhận xe đúng mô tả, tiền mới được chuyển cho người bán.</p>
                </div>
            </div>
        </div>

        <div style="border: 1px solid var(--border); border-radius: 16px; padding: 20px; cursor: pointer; transition: 0.2s; background: #fff;" onmouseover="this.style.borderColor='var(--text-secondary)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border)'; this.style.transform='translateY(0)'" onclick="processDirectCheckout()">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                <div style="width: 48px; height: 48px; background: #f1f5f9; color: #475569; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <div>
                    <h4 style="margin: 0 0 6px 0; font-size: 16px; color: var(--text-primary); font-weight: 700;">Tự giao dịch trực tiếp</h4>
                    <p style="margin: 0; font-size: 13.5px; color: var(--text-secondary); line-height: 1.5;">Tự liên hệ và hẹn gặp người bán. SpinBike sẽ <b>không chịu trách nhiệm</b> bảo vệ tiền nếu bạn chọn phương thức này.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Logic Slider Ảnh
    const bikeImages = <?php echo json_encode($images); ?>;
    let currentIdx = 0;

    function showImage(index) {
        currentIdx = index;
        const mainImg = document.getElementById('mainImage');
        
        mainImg.style.opacity = '0.5';
        setTimeout(() => {
            mainImg.src = bikeImages[currentIdx];
            mainImg.style.opacity = '1';
        }, 150);

        document.querySelectorAll('.thumb-item').forEach((thumb, i) => {
            if (i === currentIdx) {
                thumb.style.borderColor = 'var(--primary-light)';
                thumb.style.opacity = '1';
            } else {
                thumb.style.borderColor = 'transparent';
                thumb.style.opacity = '0.6';
            }
        });
    }

    function nextImage() {
        currentIdx = (currentIdx + 1) % bikeImages.length;
        showImage(currentIdx);
    }

    function prevImage() {
        currentIdx = (currentIdx - 1 + bikeImages.length) % bikeImages.length;
        showImage(currentIdx);
    }

    // ================= LOGIC NÚT MUA NGAY =================
    function showBuyOptions() {
        // Kiểm tra đăng nhập (PHP render logic)
        <?php if(!isset($_SESSION['user_id'])): ?>
            alert("Bạn cần đăng nhập để thực hiện chức năng mua hàng!");
            window.location.href = '<?php echo app_url('app/views/auth/auth.php'); ?>';
            return;
        <?php endif; ?>
        
        // Kiểm tra xem người bán có tự mua hàng của chính mình không
        <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['seller_id']): ?>
            alert("Bạn không thể mua chiếc xe do chính mình đăng bán!");
            return;
        <?php endif; ?>
        
        document.getElementById('buyOptionsModal').classList.remove('hidden');
    }

    function hideBuyOptions() {
        document.getElementById('buyOptionsModal').classList.add('hidden');
    }

    function processEscrowCheckout() {
        window.location.href = '<?php echo app_url('app/views/orders/checkout.php'); ?>?product_id=<?php echo $id; ?>';
    }

    function processDirectCheckout() {
        hideBuyOptions();
        alert('Vui lòng sử dụng tính năng Nhắn tin trao đổi để thỏa thuận trực tiếp với người bán!');
    }
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
