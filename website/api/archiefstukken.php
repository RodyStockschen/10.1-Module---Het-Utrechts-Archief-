<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$locationId = isset($_GET['location_id']) ? (int)$_GET['location_id'] : 0;
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['nl', 'en'], true) ? $_GET['lang'] : current_lang();

if ($locationId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'location_id is required']);
    exit;
}

$items = q(
    "SELECT a.id, a.image_path, a.year, a.credit, a.source_url, i.title, i.description
     FROM archive_items a
     JOIN archive_i18n i ON i.archive_item_id = a.id AND i.lang = ?
     WHERE a.location_id = ?
     ORDER BY a.sort_order, a.id",
    [$lang, $locationId]
)->fetchAll();

echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
