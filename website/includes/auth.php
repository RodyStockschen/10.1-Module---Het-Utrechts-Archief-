<?php
declare(strict_types=1);

function login(string $email, string $password): bool {
    $row = q('SELECT id, email, password_hash FROM admins WHERE email = ? LIMIT 1', [$email])->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin_id']    = (int)$row['id'];
    $_SESSION['admin_email'] = $row['email'];
    return true;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function current_admin(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    return ['id' => $_SESSION['admin_id'], 'email' => $_SESSION['admin_email']];
}

function require_login(): void {
    if (!current_admin()) {
        header('Location: /cms/login.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function verify_csrf(): void {
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('CSRF verification failed.');
    }
}
