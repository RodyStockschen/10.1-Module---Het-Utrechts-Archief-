<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (current_admin()) {
    header('Location: /cms/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    if ($email === '' || $pass === '') {
        $error = t('cms.login_error');
    } elseif (login($email, $pass)) {
        header('Location: /cms/');
        exit;
    } else {
        $error = t('cms.login_error');
    }
}
?>
<!doctype html>
<html lang="<?= e(current_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(t('cms.login_title')) ?></title>
<link rel="icon" href="/assets/img/Logo_ingeklapt.png" type="image/png">
<?= vite_assets('src/js/cms.js') ?>
</head>
<body class="cms-body">
<div class="cms-login-shell">
    <div class="cms-login-card">
        <h1><?= e(t('cms.login_title')) ?></h1>
        <?php if ($error): ?>
            <div class="cms-alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" class="cms-form">
            <?= csrf_field() ?>
            <div class="cms-form__row">
                <label for="email"><?= e(t('cms.email')) ?></label>
                <input id="email" name="email" type="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="cms-form__row">
                <label for="password"><?= e(t('cms.password')) ?></label>
                <input id="password" name="password" type="password" required>
            </div>
            <div class="cms-form__actions">
                <button type="submit" class="cms-button"><?= e(t('cms.login_button')) ?></button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
