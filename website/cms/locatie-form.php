<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

$loc = [
    'id' => 0, 'slug' => '', 'video_start_sec' => 0, 'video_end_sec' => 0,
    'map_x_pct' => 50, 'map_y_pct' => 50, 'sort_order' => 0,
];
// short_description = korte kindertekst, archive_text = originele tekst van het archief
$i18n = [
    'nl' => ['name' => '', 'short_description' => '', 'archive_text' => ''],
    'en' => ['name' => '', 'short_description' => '', 'archive_text' => ''],
];

if ($id > 0) {
    $loc = q('SELECT * FROM locations WHERE id = ?', [$id])->fetch();
    if (!$loc) { http_response_code(404); exit('Locatie niet gevonden.'); }
    foreach (q('SELECT lang, name, short_description, archive_text FROM location_i18n WHERE location_id = ?', [$id])->fetchAll() as $row) {
        $i18n[$row['lang']] = [
            'name' => $row['name'],
            'short_description' => $row['short_description'],
            'archive_text' => (string)$row['archive_text'],
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $slug = trim((string)($_POST['slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($slug));
    $slug = trim($slug, '-');

    // Tijden komen binnen als minuten:seconden, bijv. "1:45" (zie includes/time.php)
    $startValue = trim((string)($_POST['video_start'] ?? ''));
    $endValue   = trim((string)($_POST['video_end'] ?? ''));
    $startSec   = parse_time($startValue);
    $endSec     = parse_time($endValue);

    $data = [
        'slug'            => $slug,
        'video_start_sec' => $startSec ?? 0,
        'video_end_sec'   => $endSec ?? 0,
        'map_x_pct'       => max(0, min(100, (float)($_POST['map_x_pct'] ?? 50))),
        'map_y_pct'       => max(0, min(100, (float)($_POST['map_y_pct'] ?? 50))),
        'sort_order'      => (int)($_POST['sort_order'] ?? 0),
    ];
    $nameNl    = trim((string)($_POST['name_nl'] ?? ''));
    $shortNl   = trim((string)($_POST['short_nl'] ?? ''));
    $archiveNl = trim((string)($_POST['archive_nl'] ?? ''));
    $nameEn    = trim((string)($_POST['name_en'] ?? ''));
    $shortEn   = trim((string)($_POST['short_en'] ?? ''));
    $archiveEn = trim((string)($_POST['archive_en'] ?? ''));

    if ($data['slug'] === '') $errors[] = 'Slug is verplicht.';
    if ($nameNl === '')       $errors[] = 'Nederlandse naam is verplicht.';
    if ($nameEn === '')       $errors[] = 'English name is required.';
    if ($startSec === null) $errors[] = 'Starttijd klopt niet: gebruik minuten:seconden, bijv. 1:45.';
    if ($endSec === null)   $errors[] = 'Eindtijd klopt niet: gebruik minuten:seconden, bijv. 2:10.';
    if ($startSec !== null && $endSec !== null && $endSec <= $startSec) $errors[] = 'Eindtijd moet na starttijd liggen.';

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
            foreach ([['nl', $nameNl, $shortNl, $archiveNl], ['en', $nameEn, $shortEn, $archiveEn]] as [$lang, $name, $short, $archive]) {
                q(
                    'INSERT INTO location_i18n (location_id, lang, name, short_description, archive_text)
                     VALUES (?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE name = VALUES(name), short_description = VALUES(short_description),
                                             archive_text = VALUES(archive_text)',
                    [$id, $lang, $name, $short, $archive]
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
    $i18n['nl'] = ['name' => $nameNl, 'short_description' => $shortNl, 'archive_text' => $archiveNl];
    $i18n['en'] = ['name' => $nameEn, 'short_description' => $shortEn, 'archive_text' => $archiveEn];
}

// Tijden in de invoervelden als m:ss tonen. Na een foutmelding laten we
// staan wat de beheerder zelf had getypt.
$startValue = $startValue ?? format_time((float)$loc['video_start_sec']);
$endValue   = $endValue   ?? format_time((float)$loc['video_end_sec']);

// De andere locaties, voor de video-preview (zo zie je meteen of tijden overlappen)
$others = array_map(fn($r) => [
    'name'  => $r['name'],
    'start' => (float)$r['video_start_sec'],
    'end'   => (float)$r['video_end_sec'],
], q("SELECT l.video_start_sec, l.video_end_sec, i.name
      FROM locations l
      JOIN location_i18n i ON i.location_id = l.id AND i.lang = 'nl'
      WHERE l.id <> ?
      ORDER BY l.video_start_sec", [$id])->fetchAll());

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

        <!-- Video-preview: zo komt deze locatie op de site in de video en de
             tijdlijn te staan. Werking: src/js/cms-time-preview.js
             Inklapbaar met <details>, zodat de pagina kort blijft. -->
        <div class="cms-form__row">
            <label>Voorbeeld</label>
            <details class="cms-collapse">
                <summary class="cms-collapse__summary">
                    <span class="cms-collapse__text">Video-preview</span>
                    <span class="cms-collapse__hint">zie hoe deze locatie in de video en de tijdlijn staat</span>
                    <span class="cms-collapse__toggle"><span class="when-closed">Uitklappen</span><span class="when-open">Inklappen</span></span>
                </summary>
                <div class="cms-collapse__body">
                    <div class="time-preview" data-time-preview
                         data-others='<?= e(json_encode($others, JSON_UNESCAPED_UNICODE)) ?>'
                         data-duration="183">
                        <div class="time-preview__screen">
                            <video class="time-preview__video" data-time-preview-video
                                   src="/assets/video/hua-oudegracht-1929-mail.mp4"
                                   controls preload="metadata" playsinline muted></video>
                            <!-- Zo ziet de pulserende stip eruit op de site (geel randje = deze locatie) -->
                            <div class="time-preview__dot" data-time-preview-dot hidden>
                                <span class="time-preview__dot-circle" aria-hidden="true"></span>
                                <span data-time-preview-dot-label></span>
                            </div>
                        </div>
                        <!-- Mini-tijdlijn: lichtrood = geen locatie, rood = andere locaties,
                             donkerrood met geel randje = deze locatie. Klik = naar dat moment. -->
                        <div class="time-preview__timeline" data-time-preview-timeline>
                            <div class="time-preview__playhead" data-time-preview-playhead></div>
                        </div>
                        <div class="time-preview__actions">
                            <span>Video staat op <strong data-time-preview-now>0:00</strong></span>
                            <button type="button" class="cms-button cms-button--ghost" data-time-preview-set="start">Start = hier</button>
                            <button type="button" class="cms-button cms-button--ghost" data-time-preview-set="end">Eind = hier</button>
                        </div>
                        <p class="time-preview__hint">Speel de video af of klik op de tijdlijn. Pauzeer op het goede moment en klik op "Start = hier" of "Eind = hier".</p>
                    </div>
                </div>
            </details>
        </div>

        <div class="cms-form__row">
            <label for="video_start">Start (min:sec)</label>
            <input id="video_start" name="video_start" type="text" inputmode="decimal" required
                   placeholder="bijv. 1:45" title="Minuten:seconden, bijv. 1:45"
                   pattern="\d{1,3}:[0-5]?\d(?:[.,]\d+)?|\d+(?:[.,]\d+)?"
                   value="<?= e($startValue) ?>" data-time-preview-start>
        </div>

        <div class="cms-form__row">
            <label for="video_end">Eind (min:sec)</label>
            <input id="video_end" name="video_end" type="text" inputmode="decimal" required
                   placeholder="bijv. 2:10" title="Minuten:seconden, bijv. 2:10"
                   pattern="\d{1,3}:[0-5]?\d(?:[.,]\d+)?|\d+(?:[.,]\d+)?"
                   value="<?= e($endValue) ?>" data-time-preview-end>
        </div>

        <!-- Kaart om de locatie te plaatsen (src/js/map-picker.js).
             Inklapbaar met <details>; de X/Y-velden hieronder blijven zichtbaar. -->
        <div class="cms-form__row">
            <label>Positie op kaart</label>
            <details class="cms-collapse">
                <summary class="cms-collapse__summary">
                    <span class="cms-collapse__text">Kaart</span>
                    <span class="cms-collapse__hint">klik op de kaart om de locatie te plaatsen</span>
                    <span class="cms-collapse__toggle"><span class="when-closed">Uitklappen</span><span class="when-open">Inklappen</span></span>
                </summary>
                <div class="cms-collapse__body">
                    <div class="map-picker"
                         data-map-picker
                         data-image="/assets/img/plattegrond-utrecht-1929-full.jpg?v=2"
                         data-preview="/assets/img/plattegrond-utrecht-1929-preview.jpg?v=2">
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
                                     src="/assets/img/plattegrond-utrecht-1929-full.jpg?v=2"
                                     alt="Plattegrond van Utrecht 1929" draggable="false">
                                <div class="map-picker__marker"
                                     data-map-picker-marker
                                     style="left: <?= (float)$loc['map_x_pct'] ?>%; top: <?= (float)$loc['map_y_pct'] ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </details>
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

        <!-- De pop-up toont eerst de kindertekst, daaronder de archieftekst.
             Een leeg veld wordt niet getoond (bijv. bij plekken met alleen foto's). -->
        <div class="cms-form__row">
            <label for="short_nl">Kindertekst (NL)</label>
            <textarea id="short_nl" name="short_nl" rows="3" placeholder="Korte, eenvoudige tekst voor kinderen (groep 5/6)"><?= e($i18n['nl']['short_description']) ?></textarea>
        </div>

        <div class="cms-form__row">
            <label for="archive_nl">Archieftekst (NL)</label>
            <textarea id="archive_nl" name="archive_nl" rows="5" placeholder="De originele tekst van Het Utrechts Archief"><?= e($i18n['nl']['archive_text']) ?></textarea>
        </div>

        <div class="cms-form__row">
            <label for="name_en">Name (EN)</label>
            <input id="name_en" name="name_en" type="text" required value="<?= e($i18n['en']['name']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="short_en">Children’s text (EN)</label>
            <textarea id="short_en" name="short_en" rows="3"><?= e($i18n['en']['short_description']) ?></textarea>
        </div>

        <div class="cms-form__row">
            <label for="archive_en">Archive text (EN)</label>
            <textarea id="archive_en" name="archive_en" rows="5"><?= e($i18n['en']['archive_text']) ?></textarea>
        </div>

        <div class="cms-form__actions">
            <a href="/cms/locaties.php" class="cms-button cms-button--ghost">Annuleren</a>
            <button type="submit" class="cms-button">Opslaan</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
