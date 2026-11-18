<?php
require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['nl', 'en'], true) ? $_GET['lang'] : current_lang();

$rows = q(
    "SELECT l.id, l.slug, l.video_start_sec AS start, l.video_end_sec AS end,
            l.map_x_pct AS x, l.map_y_pct AS y,
            i.name, i.short_description AS short
     FROM locations l
     JOIN location_i18n i ON i.location_id = l.id AND i.lang = ?
     ORDER BY l.sort_order, l.video_start_sec",
    [$lang]
)->fetchAll();

echo json_encode(['locations' => $rows], JSON_UNESCAPED_UNICODE);
