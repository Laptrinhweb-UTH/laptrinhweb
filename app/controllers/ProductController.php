<?php
// Bổ sung gọi file config để lấy hằng số BASE_URL cho việc chuyển hướng
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../helpers/Database.php';
require_once __DIR__ . '/../helpers/CloudinaryHelper.php';
require_once __DIR__ . '/../helpers/ProjectFlow.php';
require_once __DIR__ . '/../models/Product.php';

class ProductController {
    private function redirectWithFeedback(string $url, string $message, string $status = 'error'): never
    {
        $separator = str_contains($url, '?') ? '&' : '?';
        header('Location: ' . $url . $separator . 'status=' . rawurlencode($status) . '&message=' . rawurlencode($message));
        exit;
    }
    
    // Hàm xử lý việc lưu tin đăng
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . route_url('auth'));
            exit;
        }

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
            $product->condition_percent = $_POST['condition_percent'] ?? null;
            $product->listing_status = ProjectFlow::LISTING_PENDING;
            $product->seller_id = (int) $_SESSION['user_id'];

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

                $manageListingsUrl = route_url('my-listings', ['filter' => 'pending']);
                $this->redirectWithFeedback(
                    $manageListingsUrl,
                    'Tin đăng đã được gửi duyệt thành công. Bạn có thể theo dõi trạng thái ở mục Tin đăng của tôi.',
                    'success'
                );
            } else {
                $this->redirectWithFeedback(
                    route_url('sell'),
                    'Có lỗi xảy ra khi lưu thông tin. Vui lòng kiểm tra lại dữ liệu và thử lại.'
                );
            }
        }
    }
}
?>
