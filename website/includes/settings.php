<?php
declare(strict_types=1);

function settings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;

    $row = q('SELECT minimap_focus_x, minimap_focus_y, minimap_focus_zoom FROM settings WHERE id = 1')->fetch();
    if (!$row) {
        $row = [
            'minimap_focus_x'    => 35.00,
            'minimap_focus_y'    => 52.00,
            'minimap_focus_zoom' => 2.20,
        ];
    }
    $cache = [
        'minimap_focus_x'    => (float)$row['minimap_focus_x'],
        'minimap_focus_y'    => (float)$row['minimap_focus_y'],
        'minimap_focus_zoom' => (float)$row['minimap_focus_zoom'],
    ];
    return $cache;
}

function save_minimap_focus(float $x, float $y, float $zoom): void {
    $x    = max(0, min(100, $x));
    $y    = max(0, min(100, $y));
    $zoom = max(1, min(8, $zoom));
    q('UPDATE settings SET minimap_focus_x = ?, minimap_focus_y = ?, minimap_focus_zoom = ? WHERE id = 1',
      [$x, $y, $zoom]);
}
