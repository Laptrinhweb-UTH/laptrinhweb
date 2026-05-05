<?php
// Đã chuyển sang /user/settings/profile
require_once __DIR__ . '/../../../config/config.php';
header('Location: ' . route_url('user.settings.profile'), true, 301);
exit;
