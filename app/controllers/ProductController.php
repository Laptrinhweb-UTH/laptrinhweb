<?php
// Bổ sung gọi file config để lấy hằng số BASE_URL cho việc chuyển hướng
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/CloudinaryHelper.php';
require_once __DIR__ . '/../helpers/ProjectFlow.php';
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    
    // Hàm xử lý việc lưu tin đăng
    public function store() {
        // Kiểm tra xem người dùng có bấm nút Submit (POST) chưa
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Khởi tạo kết nối DB và Model
            $database = new Database();
            $db = $database->getConnection();
            $product = new Product($db);

            // 2. Nhận dữ liệu từ form
            $product->title = $_POST['title'] ?? '';
            $product->brand = $_POST['brand'] ?? '';
            $product->bike_type = $_POST['bike_type'] ?? '';
            $product->price = $_POST['price'] ?? 0;
            $product->location = $_POST['location'] ?? '';
            $product->description = $_POST['description'] ?? '';
            $product->frame_size = $_POST['frame_size'] ?? '';
            $product->groupset = $_POST['groupset'] ?? '';
            $product->condition_percent = $_POST['condition_percent'] ?? null;
            $product->listing_status = ProjectFlow::LISTING_PENDING;
            
            // Tạm gán ID người bán là 1
            $product->seller_id = 1;

            // 3. Gọi Model để lưu thông tin vào Database trước
            if ($product->create()) {
                // Lấy ID của chiếc xe vừa được tạo
                $product_id = $product->getLastInsertId();

                // 4. Xử lý mảng hình ảnh tải lên Cloudinary
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    $fileCount = count($_FILES['images']['name']);

                    // Chạy vòng lặp qua từng tấm ảnh
                    for ($i = 0; $i < $fileCount; $i++) {
                        $tmpFilePath = $_FILES['images']['tmp_name'][$i];

                        // Đẩy lên mây (Cloudinary)
                        $imageUrl = CloudinaryHelper::uploadImage($tmpFilePath);

                        // Nếu tải lên mây thành công, lưu link ảnh mây vào Database
                        if ($imageUrl) {
                            $product->saveImage($product_id, $imageUrl);
                        }
                    }
                }

                // 5. Báo thành công và quay về trang chủ (Dùng BASE_URL cho chuẩn xác)
                echo "<script>
                        alert('Đăng tin bán xe đạp thành công! Ảnh đã được lên mây.');
                        window.location.href = '" . BASE_URL . "/index.php';
                      </script>";
                exit; // Dừng chạy code tiếp sau khi chuyển trang
            } else {
                echo "<script>alert('Có lỗi xảy ra khi lưu thông tin!');</script>";
            }
        }
    }
}
?>
