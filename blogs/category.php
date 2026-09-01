<?php
/**
 * Seychelles International Cargo LLC - Category Filter Forwarder
 * URL: /blogs/category/<name>
 */
$category = $_GET['name'] ?? 'All Articles';
header('Location: index.php?category=' . urlencode($category));
exit;
