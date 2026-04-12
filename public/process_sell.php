<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';

$controller = new ProductController();
$controller->store(); // Gọi hàm xử lý và upload
?>