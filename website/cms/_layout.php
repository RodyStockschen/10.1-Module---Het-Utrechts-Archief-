<?php
/** @var string $page_title */
$page_title = $page_title ?? 'CMS';
?>
<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> – Ontmoet de Oudegracht</title>
<link rel="icon" href="/assets/img/Logo_ingeklapt.png" type="image/png">
<?= vite_assets('src/js/cms.js') ?>
</head>
<body class="cms-body">
<div class="cms-shell">
    <header class="cms-header">
        <a class="cms-header__brand" href="/cms/">CMS – <strong>Ontmoet de Oudegracht</strong></a>
        <nav class="cms-header__nav">
            <a href="/cms/">Dashboard</a>
            <a href="/cms/locaties.php">Locaties</a>
            <a href="/cms/archiefstukken.php">Archiefstukken</a>
            <a href="/cms/minimap-focus.php">Minimap</a>
            <a href="/" target="_blank">Site</a>
            <a href="/cms/logout.php">Uitloggen</a>
        </nav>
    </header>
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="cms-alert cms-alert--success"><?= e($_SESSION['flash_success']) ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="cms-alert"><?= e($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
