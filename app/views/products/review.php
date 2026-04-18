<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../helpers/AdminAuth.php';

$query = $_SERVER['QUERY_STRING'] ?? '';
$redirectUrl = admin_listings_url($query);

header('Location: ' . $redirectUrl, true, 302);
exit;
?>
