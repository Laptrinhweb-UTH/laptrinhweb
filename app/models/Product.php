<?php
// File: app/models/Product.php

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
    public $seller_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ---------------------------------------------------
    // HÀM 0: Lấy tất cả sản phẩm
    // ---------------------------------------------------
public function getAll() {
    // Bổ sung thêm lệnh đếm tổng số lượng ảnh (COUNT)
    $query = "SELECT p.*, 
              (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as main_image,
              (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count
              FROM " . $this->table_name . " p 
              ORDER BY p.id DESC";

    $stmt = $this->conn->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // ---------------------------------------------------
    // HÀM 1: Đăng xe mới
    // ---------------------------------------------------
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, brand, price, location, description, seller_id) 
                  VALUES 
                  (:title, :brand, :price, :location, :description, :seller_id)";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu (Chống XSS/Hack)
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->location = htmlspecialchars(strip_tags($this->location));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));

        // Gắn dữ liệu
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":description", $this->description);
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

        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
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
}
?>
