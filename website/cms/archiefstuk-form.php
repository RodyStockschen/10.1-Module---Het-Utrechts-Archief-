<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

$item = [
    'id' => 0, 'location_id' => 0, 'image_path' => '', 'year' => '', 'sort_order' => 0,
];
$i18n = [
    'nl' => ['title' => '', 'description' => ''],
    'en' => ['title' => '', 'description' => ''],
];

$locations = q(
    "SELECT l.id, COALESCE(i.name, l.slug) AS label
     FROM locations l
     LEFT JOIN location_i18n i ON i.location_id = l.id AND i.lang='nl'
     ORDER BY l.sort_order, l.id"
)->fetchAll();

if ($id > 0) {
    $item = q('SELECT * FROM archive_items WHERE id = ?', [$id])->fetch();
    if (!$item) { http_response_code(404); exit('Niet gevonden.'); }
    foreach (q('SELECT lang, title, description FROM archive_i18n WHERE archive_item_id = ?', [$id])->fetchAll() as $row) {
        $i18n[$row['lang']] = ['title' => $row['title'], 'description' => $row['description']];
    }
}

function save_upload(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload mislukt (code ' . $file['error'] . ').');
    if ($file['size'] > 5 * 1024 * 1024) throw new RuntimeException('Bestand is groter dan 5 MB.');

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Alleen JPEG, PNG en WebP toegestaan.');

    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOADS_DIR . '/' . $name;
    if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0775, true);
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException('Opslaan mislukt.');
    return 'assets/uploads/' . $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $locationId = (int)($_POST['location_id'] ?? 0);
    $year       = $_POST['year'] !== '' ? (int)$_POST['year'] : null;
    $sortOrder  = (int)($_POST['sort_order'] ?? 0);
    $titleNl    = trim((string)($_POST['title_nl'] ?? ''));
    $descNl     = trim((string)($_POST['desc_nl'] ?? ''));
    $titleEn    = trim((string)($_POST['title_en'] ?? ''));
    $descEn     = trim((string)($_POST['desc_en'] ?? ''));

    $imagePath = $item['image_path'] ?? '';

    try {
        $uploaded = save_upload($_FILES['image'] ?? []);
        if ($uploaded) $imagePath = $uploaded;
    } catch (Throwable $ex) {
        $errors[] = $ex->getMessage();
    }

    if ($locationId <= 0) $errors[] = 'Kies een locatie.';
    if ($titleNl === '')  $errors[] = 'Nederlandse titel is verplicht.';
    if ($titleEn === '')  $errors[] = 'English title is required.';
    if ($imagePath === '' && !$id) $errors[] = 'Afbeelding is verplicht.';

    if (!$errors) {
        try {
            db()->beginTransaction();
            if ($id > 0) {
                q('UPDATE archive_items SET location_id=?, image_path=?, year=?, sort_order=? WHERE id=?',
                    [$locationId, $imagePath, $year, $sortOrder, $id]);
            } else {
                q('INSERT INTO archive_items (location_id, image_path, year, sort_order) VALUES (?,?,?,?)',
                    [$locationId, $imagePath, $year, $sortOrder]);
                $id = (int)db()->lastInsertId();
            }
            foreach ([['nl', $titleNl, $descNl], ['en', $titleEn, $descEn]] as [$lang, $title, $desc]) {
                q('INSERT INTO archive_i18n (archive_item_id, lang, title, description)
                   VALUES (?,?,?,?)
                   ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description)',
                   [$id, $lang, $title, $desc]);
            }
            db()->commit();
            $_SESSION['flash_success'] = 'Archiefstuk opgeslagen.';
            header('Location: /cms/archiefstukken.php');
            exit;
        } catch (Throwable $ex) {
            db()->rollBack();
            $errors[] = 'Opslaan mislukt: ' . $ex->getMessage();
        }
    }

    $item = array_merge($item, [
        'location_id' => $locationId, 'image_path' => $imagePath,
        'year' => $year ?? '', 'sort_order' => $sortOrder,
    ]);
    $i18n['nl'] = ['title' => $titleNl, 'description' => $descNl];
    $i18n['en'] = ['title' => $titleEn, 'description' => $descEn];
}

$page_title = $id > 0 ? 'Archiefstuk bewerken' : 'Nieuw archiefstuk';
require __DIR__ . '/_layout.php';
?>
<div class="cms-card">
    <h1><?= e($page_title) ?></h1>
    <?php if ($errors): ?>
        <div class="cms-alert"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="cms-form" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="cms-form__row">
            <label for="location_id">Locatie</label>
            <select id="location_id" name="location_id" required>
                <option value="">— kies —</option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?= (int)$loc['id'] ?>" <?= (int)$loc['id'] === (int)$item['location_id'] ? 'selected' : '' ?>>
                        <?= e($loc['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cms-form__row">
            <label>Huidige afbeelding</label>
            <div>
                <?php if (!empty($item['image_path'])): ?>
                    <img src="/<?= e((string)$item['image_path']) ?>" alt="" style="max-width:220px;border:1px solid #c9bda4;">
                <?php else: ?>
                    <em>— nog geen —</em>
                <?php endif; ?>
            </div>
        </div>

        <div class="cms-form__row">
            <label for="image">Afbeelding uploaden</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
        </div>

        <div class="cms-form__row">
            <label for="year">Jaar</label>
            <input id="year" name="year" type="number" min="1800" max="2100" value="<?= e((string)$item['year']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="sort_order">Sorteervolgorde</label>
            <input id="sort_order" name="sort_order" type="number" value="<?= (int)$item['sort_order'] ?>">
        </div>

        <div class="cms-form__row">
            <label for="title_nl">Titel (NL)</label>
            <input id="title_nl" name="title_nl" type="text" required value="<?= e($i18n['nl']['title']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="desc_nl">Beschrijving (NL)</label>
            <textarea id="desc_nl" name="desc_nl" rows="4"><?= e($i18n['nl']['description']) ?></textarea>
        </div>

        <div class="cms-form__row">
            <label for="title_en">Title (EN)</label>
            <input id="title_en" name="title_en" type="text" required value="<?= e($i18n['en']['title']) ?>">
        </div>

        <div class="cms-form__row">
            <label for="desc_en">Description (EN)</label>
            <textarea id="desc_en" name="desc_en" rows="4"><?= e($i18n['en']['description']) ?></textarea>
        </div>

        <div class="cms-form__actions">
            <a href="/cms/archiefstukken.php" class="cms-button cms-button--ghost">Annuleren</a>
            <button type="submit" class="cms-button">Opslaan</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
