<?php
declare(strict_types=1);

function env(string $key, ?string $default = null): ?string {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

define('APP_ENV', env('APP_ENV', 'prod'));
define('APP_URL', rtrim(env('APP_URL', 'http://localhost:8080') ?? '', '/'));
define('APP_DEFAULT_LANG', env('APP_DEFAULT_LANG', 'nl'));

define('DB_HOST', env('DB_HOST', 'db'));
define('DB_PORT', (int)env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'oudegracht'));
define('DB_USER', env('DB_USER', 'hua'));
define('DB_PASS', env('DB_PASS', 'hua'));

define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_DIR', ROOT_PATH . '/assets/uploads');
define('UPLOADS_URL', '/assets/uploads');
