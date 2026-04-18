<?php
// File: app/models/Product.php

require_once __DIR__ . '/../helpers/ProjectFlow.php';

class Product {
    private $conn;
    private $table_name = "products";

    // Các thuộc tính
    public $id;
    public $title;
    public $brand;
    public $price;
    public $location;
    public $description;
    public $bike_type;
    public $frame_size;
    public $groupset;
    public $condition_percent;
    public $listing_status;
    public $seller_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ---------------------------------------------------
    // HÀM 0: Lấy tất cả sản phẩm
    // ---------------------------------------------------
public function getAll() {
    $query = "SELECT p.*,
              p.frame_size AS size,
              p.condition_percent AS `condition`,
              (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as main_image,
              (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count
              FROM " . $this->table_name . " p
              WHERE p.listing_status = :listing_status
              ORDER BY p.id DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':listing_status', ProjectFlow::LISTING_APPROVED);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // ---------------------------------------------------
    // HÀM 1: Đăng xe mới
    // ---------------------------------------------------
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, brand, bike_type, price, location, description, frame_size, groupset, condition_percent, listing_status, seller_id) 
                  VALUES 
                  (:title, :brand, :bike_type, :price, :location, :description, :frame_size, :groupset, :condition_percent, :listing_status, :seller_id)";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu (Chống XSS/Hack)
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        $this->bike_type = htmlspecialchars(strip_tags((string) $this->bike_type));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->frame_size = htmlspecialchars(strip_tags((string) $this->frame_size));
        $this->groupset = htmlspecialchars(strip_tags((string) $this->groupset));
        $this->condition_percent = $this->condition_percent !== null && $this->condition_percent !== ''
            ? (int) $this->condition_percent
            : null;
        $this->listing_status = $this->listing_status ?: ProjectFlow::LISTING_PENDING;
        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));

        // Gắn dữ liệu
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindValue(":bike_type", $this->bike_type !== '' ? $this->bike_type : null, $this->bike_type !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindValue(":frame_size", $this->frame_size !== '' ? $this->frame_size : null, $this->frame_size !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(":groupset", $this->groupset !== '' ? $this->groupset : null, $this->groupset !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(":condition_percent", $this->condition_percent, $this->condition_percent === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(":listing_status", $this->listing_status);
        $stmt->bindParam(":seller_id", $this->seller_id);

        return $stmt->execute();
    }

    // ---------------------------------------------------
    // HÀM 2: Lấy ID của chiếc xe vừa đăng
    // ---------------------------------------------------
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

    // ---------------------------------------------------
    // HÀM 3: Lưu ĐƯỜNG LINK ẢNH Cloudinary vào Database
    // ---------------------------------------------------
    public function saveImage($product_id, $image_url) {
        // Đã đổi cột thành image_url
        $query = "INSERT INTO product_images (product_id, image_url) 
                  VALUES (:product_id, :image_url)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":image_url", $image_url);
        
        return $stmt->execute();
    }
    // Hàm lấy chi tiết 1 sản phẩm theo ID
    public function getProductDetail($id) {
        $productId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($productId === false) {
            return null;
        }

        $query = "SELECT p.*,
                         p.frame_size AS size,
                         p.condition_percent AS `condition`,
                         u.name AS seller_name,
                         u.avatar AS seller_avatar
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON u.id = p.seller_id
                  WHERE p.id = ?
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Lấy thêm toàn bộ ảnh của xe đó
        if ($product) {
            $imgQuery = "SELECT image_url FROM product_images WHERE product_id = ?";
            $imgStmt = $this->conn->prepare($imgQuery);
            $imgStmt->execute([$productId]);
            $product['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $product;
    }

    public function markAsSold(int $productId): bool
    {
        $query = "UPDATE " . $this->table_name . "
                  SET listing_status = :listing_status,
                      sold_at = NOW()
                  WHERE id = :id
                    AND listing_status = :current_status";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':listing_status', ProjectFlow::LISTING_SOLD);
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        $stmt->bindValue(':current_status', ProjectFlow::LISTING_APPROVED);

        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }
}
?>
