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
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        
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
}
?>