<?php
// Đã tách thành /orders/buying và /orders/selling
require_once __DIR__ . '/../../../config/config.php';
header('Location: ' . route_url('orders.buying'), true, 301);
exit;
