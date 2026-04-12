<?php
// Tự động gọi file config để lấy tên Cloudinary và Preset
require_once __DIR__ . '/../../config/config.php';

class CloudinaryHelper {
    
    // Hàm nhận file ảnh từ Form và đẩy lên mây
    public static function uploadImage($fileTmpPath) {
        $cloudName = CLD_CLOUD_NAME;
        $uploadPreset = CLD_UPLOAD_PRESET;
        
        // Đường dẫn API của Cloudinary
        $apiUrl = "https://api.cloudinary.com/v1_1/" . $cloudName . "/image/upload";

        // Đóng gói file để gửi đi
        $cFile = new CURLFile($fileTmpPath);
        $data = [
            "file" => $cFile,
            "upload_preset" => $uploadPreset
        ];

        // Mở kết nối HTTP để gửi file (Dùng cURL của PHP)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Chờ Cloudinary trả lời
        $response = curl_exec($ch);
        curl_close($ch);

        // Giải mã câu trả lời của Cloudinary (dạng JSON)
        $result = json_decode($response, true);

        // Nếu có đường link 'secure_url' tức là thành công
        if (isset($result['secure_url'])) {
            return $result['secure_url']; // Trả về link ảnh (Ví dụ: https://res.cloudinary...)
        }

        return false; // Nếu thất bại
    }
}
?>