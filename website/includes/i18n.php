<?php
declare(strict_types=1);

function current_lang(): string {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['nl', 'en'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    $lang = $_SESSION['lang'] ?? APP_DEFAULT_LANG;
    return in_array($lang, ['nl', 'en'], true) ? $lang : 'nl';
}

function lang_strings(): array {
    static $cache = [];
    $lang = current_lang();
    if (!isset($cache[$lang])) {
        $cache[$lang] = require __DIR__ . "/lang/{$lang}.php";
    }
    return $cache[$lang];
}

function t(string $key, string $fallback = ''): string {
    $parts = explode('.', $key);
    $node  = lang_strings();
    foreach ($parts as $p) {
        if (!is_array($node) || !array_key_exists($p, $node)) {
            return $fallback !== '' ? $fallback : $key;
        }
        $node = $node[$p];
    }
    return is_string($node) ? $node : $key;
}

function e(string $raw): string {
    return htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
}

function other_lang(): string {
    return current_lang() === 'nl' ? 'en' : 'nl';
}

function lang_url(string $lang): string {
    $uri   = $_SERVER['REQUEST_URI'] ?? '/';
    $parts = parse_url($uri);
    $path  = $parts['path'] ?? '/';
    parse_str($parts['query'] ?? '', $qs);
    $qs['lang'] = $lang;
    return $path . '?' . http_build_query($qs);
}
