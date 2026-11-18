<?php
require_once __DIR__ . '/includes/bootstrap.php';
http_response_code(404);
$page_title = '404 – ' . t('site.title');
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero__inner">
        <h1 class="hero__title">404</h1>
        <p class="hero__lead"><?= e(current_lang() === 'nl'
            ? 'Deze pagina is niet gevonden. Ga terug naar de startpagina.'
            : 'This page was not found. Return to the home page.') ?></p>
        <p><a class="button" href="/">← <?= e(t('cms.back')) ?></a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
