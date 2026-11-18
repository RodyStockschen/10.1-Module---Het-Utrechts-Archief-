<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

$loc = [
    'id' => 0, 'slug' => '', 'video_start_sec' => 0, 'video_end_sec' => 0,
    'map_x_pct' => 50, 'map_y_pct' => 50, 'sort_order' => 0,
];
$i18n = [
    'nl' => ['name' => '', 'short_description' => ''],
    'en' => ['name' => '', 'short_description' => ''],
];

if ($id > 0) {
    $loc = q('SELECT * FROM locations WHERE id = ?', [$id])->fetch();
    if (!$loc) { http_response_code(404); exit('Locatie niet gevonden.'); }
    foreach (q('SELECT lang, name, short_description FROM location_i18n WHERE location_id = ?', [$id])->fetchAll() as $row) {
        $i18n[$row['lang']] = ['name' => $row['name'], 'short_description' => $row['short_description']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $slug = trim((string)($_POST['slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($slug));
    $slug = trim($slug, '-');

    $data = [
        'slug'            => $slug,
        'video_start_sec' => (float)($_POST['video_start_sec'] ?? 0),
        'video_end_sec'   => (float)($_POST['video_end_sec'] ?? 0),
        'map_x_pct'       => max(0, min(100, (float)($_POST['map_x_pct'] ?? 50))),
        'map_y_pct'       => max(0, min(100, (float)($_POST['map_y_pct'] ?? 50))),
        'sort_order'      => (int)($_POST['sort_order'] ?? 0),
    ];
    $nameNl  = trim((string)($_POST['name_nl'] ?? ''));
    $shortNl = trim((string)($_POST['short_nl'] ?? ''));
    $nameEn  = trim((string)($_POST['name_en'] ?? ''));
    $shortEn = trim((string)($_POST['short_en'] ?? ''));

    if ($data['slug'] === '') $errors[] = 'Slug is verplicht.';
    if ($nameNl === '')       $errors[] = 'Nederlandse naam is verplicht.';
    if ($nameEn === '')       $errors[] = 'English name is required.';
    if ($data['video_end_sec'] <= $data['video_start_sec']) $errors[] = 'Eindtijd moet na starttijd liggen.';

    if (!$errors) {
        try {
            db()->beginTransaction();
            if ($id > 0) {
                q('UPDATE locations SET slug=?, video_start_sec=?, video_end_sec=?, map_x_pct=?, map_y_pct=?, sort_order=? WHERE id=?',
                    [$data['slug'], $data['video_start_sec'], $data['video_end_sec'], $data['map_x_pct'], $data['map_y_pct'], $data['sort_order'], $id]);
            } else {
                q('INSERT INTO locations (slug, video_start_sec, video_end_sec, map_x_pct, map_y_pct, sort_order) VALUES (?,?,?,?,?,?)',
                    [$data['slug'], $data['video_start_sec'], $data['video_end_sec'], $data['map_x_pct'], $data['map_y_pct'], $data['sort_order']]);
                $id = (int)db()->lastInsertId();
            }

            // upsert i18n
            foreach ([['nl', $nameNl, $shortNl], ['en', $nameEn, $shortEn]] as [$lang, $name, $short]) {
                q(
                    'INSERT INTO location_i18n (location_id, lang, name, short_description)
                     VALUES (?,?,?,?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), short_description = VALUES(short_description)',
                    [$id, $lang, $name, $short]
                );
            }

            db()->commit();
            $_SESSION['flash_success'] = 'Locatie opgeslagen.';
            header('Location: /cms/locaties.php');
            exit;
        } catch (Throwable $ex) {
            db()->rollBack();
            $errors[] = 'Opslaan mislukt: ' . $ex->getMessage();
        }
    }

    // keep user input for redisplay
    $loc = array_merge($loc, $data);
    $i18n['nl'] = ['name' => $nameNl, 'short_description' => $shortNl];
    $i18n['en'] = ['name' => $nameEn, 'short_description' => $shortEn];
}

$page_title = $id > 0 ? 'Locatie bewerken' : 'Nieuwe locatie';
require __DIR__ . '/_layout.php';
?>
<div class="cms-card">
    <h1><?= e($page_title) ?></h1>
    <?php if ($errors): ?>
        <div class="cms-alert"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="cms-form">
        <?= csrf_field() ?>

        <div class="cms-form__row">
            <label for="slug">Slug (url-naam)</label>
            <input id="slug" name="slug" type="text" required value="<?= e((string)$loc['slug']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="sort_order">Sorteervolgorde</label>
            <input id="sort_order" name="sort_order" type="number" value="<?= (int)$loc['sort_order'] ?>">
        </div>

        <div class="cms-form__row">
            <label for="video_start_sec">Video start (sec)</label>
            <input id="video_start_sec" name="video_start_sec" type="number" step="0.1" min="0" required value="<?= e((string)$loc['video_start_sec']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="video_end_sec">Video eind (sec)</label>
            <input id="video_end_sec" name="video_end_sec" type="number" step="0.1" min="0" required value="<?= e((string)$loc['video_end_sec']) ?>">
        </div>

        <div class="cms-form__row">
            <label>Positie op kaart</label>
            <div class="map-picker"
                 data-map-picker
                 data-image="/assets/img/plattegrond-utrecht-1929-full.jpg"
                 data-preview="/assets/img/plattegrond-utrecht-1929-preview.jpg">
                <p class="map-picker__hint">Klik op de kaart om deze locatie te plaatsen. Scroll om in te zoomen, sleep om te verplaatsen.</p>
                <div class="map-picker__toolbar">
                    <button type="button" class="cms-button cms-button--ghost" data-map-picker-in>+ Inzoomen</button>
                    <button type="button" class="cms-button cms-button--ghost" data-map-picker-out>− Uitzoomen</button>
                    <button type="button" class="cms-button cms-button--ghost" data-map-picker-reset>↺ Reset</button>
                    <span class="map-picker__coords" data-map-picker-coords>
                        X <?= number_format((float)$loc['map_x_pct'], 1) ?>%, Y <?= number_format((float)$loc['map_y_pct'], 1) ?>%
                    </span>
                </div>
                <div class="map-picker__viewport" data-map-picker-viewport>
                    <div class="map-picker__stage" data-map-picker-stage>
                        <img class="map-picker__image" data-map-picker-image
                             src="/assets/img/plattegrond-utrecht-1929-full.jpg"
                             alt="Plattegrond van Utrecht 1929" draggable="false">
                        <div class="map-picker__marker"
                             data-map-picker-marker
                             style="left: <?= (float)$loc['map_x_pct'] ?>%; top: <?= (float)$loc['map_y_pct'] ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cms-form__row">
            <label for="map_x_pct">Kaart X (%)</label>
            <input id="map_x_pct" name="map_x_pct" type="number" step="0.01" min="0" max="100" required value="<?= e((string)$loc['map_x_pct']) ?>" data-map-picker-x>
        </div>

        <div class="cms-form__row">
            <label for="map_y_pct">Kaart Y (%)</label>
            <input id="map_y_pct" name="map_y_pct" type="number" step="0.01" min="0" max="100" required value="<?= e((string)$loc['map_y_pct']) ?>" data-map-picker-y>
        </div>

        <div class="cms-form__row">
            <label for="name_nl">Naam (NL)</label>
            <input id="name_nl" name="name_nl" type="text" required value="<?= e($i18n['nl']['name']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="short_nl">Korte beschrijving (NL)</label>
            <textarea id="short_nl" name="short_nl" rows="3"><?= e($i18n['nl']['short_description']) ?></textarea>
        </div>

        <div class="cms-form__row">
            <label for="name_en">Name (EN)</label>
            <input id="name_en" name="name_en" type="text" required value="<?= e($i18n['en']['name']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="short_en">Short description (EN)</label>
            <textarea id="short_en" name="short_en" rows="3"><?= e($i18n['en']['short_description']) ?></textarea>
        </div>

        <div class="cms-form__actions">
            <a href="/cms/locaties.php" class="cms-button cms-button--ghost">Annuleren</a>
            <button type="submit" class="cms-button">Opslaan</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
