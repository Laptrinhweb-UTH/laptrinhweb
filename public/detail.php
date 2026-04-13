<?php
require_once __DIR__ . '/../app/helpers/Database.php';
require_once __DIR__ . '/../app/models/Product.php';

// Kiểm tra xem trên thanh địa chỉ có ID không (Ví dụ: detail.php?id=5)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$database = new Database();
$db = $database->getConnection();
$productModel = new Product($db);

// Gọi hàm lấy dữ liệu xe
$product = $productModel->getProductDetail($id);

// Nếu không tìm thấy xe (bị xóa hoặc gõ sai ID), đẩy về trang chủ
if (!$product) {
    echo "<script>alert('Chiếc xe này không tồn tại hoặc đã bị xóa!'); window.location.href='index.php';</script>";
    exit;
}

// Xử lý dữ liệu trước khi hiển thị
$formattedPrice = number_format($product['price'], 0, ',', '.') . ' đ';
$images = (!empty($product['images'])) ? $product['images'] : ['https://via.placeholder.com/600x400?text=Chua+Co+Anh'];
$mainImage = $images[0]; // Ảnh to nhất
$sellerId = $product['seller_id'] ?? '1';
$avatarUrl = "https://ui-avatars.com/api/?name=U+{$sellerId}&background=6b21a8&color=fff&rounded=true&bold=true";

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="container" style="padding: 40px 24px; max-width: 1200px; margin: 0 auto;">
    
    <div style="margin-bottom: 24px; font-size: 14px; color: var(--text-secondary);">
        <a href="index.php" style="color: var(--primary); text-decoration: none;"><i class="fa-solid fa-house"></i> Trang chủ</a> 
        <span style="margin: 0 8px;">/</span> 
        <span><?php echo htmlspecialchars($product['brand'] ?? 'Khác'); ?></span>
        <span style="margin: 0 8px;">/</span> 
        <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($product['title']); ?></strong>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: #fff; padding: 32px; border-radius: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid var(--border);">
        
        <div class="detail-images">
            <div id="mainImageDisplay" style="width: 100%; height: 450px; border-radius: 16px; overflow: hidden; background: #f3f4f6; margin-bottom: 16px;">
                <img src="<?php echo $mainImage; ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Hình ảnh xe">
            </div>
            
            <div style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px;">
                <?php foreach ($images as $img): ?>
                    <img src="<?php echo $img; ?>" onclick="document.getElementById('mainImageDisplay').innerHTML = '<img src=\'<?php echo $img; ?>\' style=\'width: 100%; height: 100%; object-fit: cover;\'>'" style="width: 80px; height: 80px; object-fit: cover; border-radius: 12px; cursor: pointer; border: 2px solid transparent; transition: 0.3s;" onmouseover="this.style.borderColor='var(--primary-light)'" onmouseout="this.style.borderColor='transparent'">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="detail-info">
            <h1 style="font-size: 28px; font-weight: 700; margin: 0 0 16px 0;"><?php echo htmlspecialchars($product['title']); ?></h1>
            <div style="font-size: 32px; font-weight: 700; color: #2563eb; margin-bottom: 24px;"><?php echo $formattedPrice; ?></div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px; background: #f9fafb; border-radius: 16px; margin-bottom: 32px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <img src="<?php echo $avatarUrl; ?>" alt="Avatar" style="width: 48px; height: 48px; border-radius: 50%;">
                    <div>
                        <div style="font-weight: 700; font-size: 16px;">Người Bán (Khách)</div>
                        <div style="font-size: 13px; color: var(--text-secondary);"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($product['location'] ?? 'Đang cập nhật'); ?></div>
                    </div>
                </div>
                <button style="background: var(--primary-light); color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; cursor: pointer;"><i class="fa-solid fa-phone"></i> Gọi điện</button>
            </div>

            <h3 style="font-size: 18px; margin-bottom: 16px; border-bottom: 2px solid var(--border); padding-bottom: 8px;">Thông số kỹ thuật</h3>
            <table style="width: 100%; font-size: 15px; margin-bottom: 32px; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px 0; color: var(--text-secondary);">Hãng xe</td>
                    <td style="padding: 12px 0; font-weight: 600; text-align: right;"><?php echo htmlspecialchars($product['brand'] ?? 'Khác'); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px 0; color: var(--text-secondary);">Size khung</td>
                    <td style="padding: 12px 0; font-weight: 600; text-align: right;"><?php echo htmlspecialchars($product['size'] ?? 'Đang cập nhật'); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px 0; color: var(--text-secondary);">Độ mới</td>
                    <td style="padding: 12px 0; font-weight: 600; text-align: right; color: var(--danger);"><?php echo htmlspecialchars($product['condition'] ?? 'Đang cập nhật'); ?>%</td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: var(--text-secondary);">Groupset/Phụ tùng</td>
                    <td style="padding: 12px 0; font-weight: 600; text-align: right;"><?php echo htmlspecialchars($product['groupset'] ?? 'Nguyên bản'); ?></td>
                </tr>
            </table>

            <h3 style="font-size: 18px; margin-bottom: 12px;">Mô tả chi tiết</h3>
            <p style="color: #4b5563; line-height: 1.6; font-size: 15px; white-space: pre-wrap;"><?php echo htmlspecialchars($product['description'] ?? 'Người bán không cung cấp mô tả chi tiết.'); ?></p>
        </div>
    </div>
</div>


<style>
@media (max-width: 900px) {
    .container > div:nth-child(2) {
        grid-template-columns: 1fr !important;
    }
    #mainImageDisplay { height: 300px !important; }
}
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>