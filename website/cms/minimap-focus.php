<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$s = settings();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $x    = (float)($_POST['focus_x']    ?? 50);
    $y    = (float)($_POST['focus_y']    ?? 50);
    $zoom = (float)($_POST['focus_zoom'] ?? 2);
    try {
        save_minimap_focus($x, $y, $zoom);
        $_SESSION['flash_success'] = 'Minimap-focus opgeslagen.';
        header('Location: /cms/minimap-focus.php');
        exit;
    } catch (Throwable $ex) {
        $errors[] = 'Opslaan mislukt: ' . $ex->getMessage();
    }
}

// Haal pins op als context (zodat Rody ziet waar ze op de kaart liggen).
$locations = q(
    "SELECT l.id, l.map_x_pct, l.map_y_pct, i.name
     FROM locations l
     LEFT JOIN location_i18n i ON i.location_id = l.id AND i.lang='nl'
     ORDER BY l.sort_order, l.id"
)->fetchAll();

$page_title = 'Minimap-focus';
require __DIR__ . '/_layout.php';
?>

<div class="cms-card">
    <h1>Kies het minimap-gebied</h1>
    <p>
        Sleep en zoom op de kaart hieronder tot je precies het gedeelte ziet dat in de minimap
        op de website getoond moet worden. Het witte kader laat zien wat zichtbaar wordt.
        Klik daarna op <strong>Opslaan</strong>.
    </p>

    <?php if ($errors): ?>
        <div class="cms-alert"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>

    <form method="post" class="focus-picker"
          data-focus-picker
          data-initial-x="<?= (float)$s['minimap_focus_x'] ?>"
          data-initial-y="<?= (float)$s['minimap_focus_y'] ?>"
          data-initial-zoom="<?= (float)$s['minimap_focus_zoom'] ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="focus_x"    data-focus-input-x>
        <input type="hidden" name="focus_y"    data-focus-input-y>
        <input type="hidden" name="focus_zoom" data-focus-input-zoom>

        <div class="focus-picker__toolbar">
            <button type="button" class="cms-button cms-button--ghost" data-focus-in>+ Inzoomen</button>
            <button type="button" class="cms-button cms-button--ghost" data-focus-out>− Uitzoomen</button>
            <button type="button" class="cms-button cms-button--ghost" data-focus-reset>↺ Terug naar standaard</button>
            <span class="focus-picker__coords" data-focus-coords>…</span>
        </div>

        <div class="focus-picker__viewport" data-focus-viewport>
            <div class="focus-picker__stage" data-focus-stage>
                <img class="focus-picker__image" data-focus-image
                     src="/assets/img/plattegrond-utrecht-1929-full.jpg?v=2"
                     alt="Plattegrond van Utrecht 1929" draggable="false">
                <?php foreach ($locations as $loc): ?>
                    <span class="focus-picker__pin"
                          style="left: <?= (float)$loc['map_x_pct'] ?>%; top: <?= (float)$loc['map_y_pct'] ?>%;"
                          title="<?= e((string)($loc['name'] ?? '')) ?>"></span>
                <?php endforeach; ?>
            </div>
            <div class="focus-picker__frame" aria-hidden="true"></div>
        </div>

        <div class="focus-picker__hint">
            Scroll of gebruik de knoppen om te zoomen. Sleep om te verplaatsen.
            Pins staan op de huidige locaties uit de database.
        </div>

        <div class="cms-form__actions">
            <a href="/cms/" class="cms-button cms-button--ghost">Terug</a>
            <button type="submit" class="cms-button">Opslaan</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
