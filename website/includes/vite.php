<?php
declare(strict_types=1);

// Minimal Vite helper: dev → import directly from vite dev server; prod → read manifest.
function vite_assets(string $entry): string {
    $html = '';

    if (APP_ENV === 'dev') {
        $base = 'http://localhost:5173';
        $html .= '<script type="module" src="' . $base . '/@vite/client"></script>' . "\n";
        $html .= '<script type="module" src="' . $base . '/' . $entry . '"></script>' . "\n";
        return $html;
    }

    $manifestFile = ROOT_PATH . '/dist/.vite/manifest.json';
    if (!is_file($manifestFile)) {
        // Fallback: try to load the un-built source directly so the site still shows
        // something during first setup. Styling will be plain HTML.
        return '<script type="module" src="/' . $entry . '"></script>' . "\n";
    }

    $manifest = json_decode((string)file_get_contents($manifestFile), true);
    if (!isset($manifest[$entry])) return '';

    $meta = $manifest[$entry];

    foreach ($meta['css'] ?? [] as $css) {
        $html .= '<link rel="stylesheet" href="/dist/' . $css . '">' . "\n";
    }
    $html .= '<script type="module" src="/dist/' . $meta['file'] . '"></script>' . "\n";

    return $html;
}
