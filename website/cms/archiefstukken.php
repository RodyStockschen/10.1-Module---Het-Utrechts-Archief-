<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$rows = q(
    "SELECT a.id, a.image_path, a.year, a.sort_order,
            i.title AS title_nl, e.title AS title_en,
            l.slug AS location_slug, ln.name AS location_name
     FROM archive_items a
     JOIN locations l ON l.id = a.location_id
     LEFT JOIN location_i18n ln ON ln.location_id = l.id AND ln.lang='nl'
     LEFT JOIN archive_i18n i ON i.archive_item_id = a.id AND i.lang='nl'
     LEFT JOIN archive_i18n e ON e.archive_item_id = a.id AND e.lang='en'
     ORDER BY l.sort_order, a.sort_order, a.id"
)->fetchAll();

$page_title = 'Archiefstukken';
require __DIR__ . '/_layout.php';
?>

<div class="cms-card">
    <div class="cms-flex">
        <h1>Archiefstukken</h1>
        <a href="/cms/archiefstuk-form.php" class="cms-button">+ Nieuw archiefstuk</a>
    </div>

    <table class="cms-table">
        <thead>
            <tr>
                <th>Afbeelding</th>
                <th>Locatie</th>
                <th>Titel (NL)</th>
                <th>Title (EN)</th>
                <th>Jaar</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6"><em>Nog geen archiefstukken.</em></td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><img src="/<?= e($r['image_path']) ?>" alt="" style="width:60px;height:45px;object-fit:cover;"></td>
                    <td><?= e($r['location_name'] ?? $r['location_slug']) ?></td>
                    <td><?= e($r['title_nl'] ?? '—') ?></td>
                    <td><?= e($r['title_en'] ?? '—') ?></td>
                    <td><?= $r['year'] !== null ? (int)$r['year'] : '—' ?></td>
                    <td>
                        <a href="/cms/archiefstuk-form.php?id=<?= (int)$r['id'] ?>" class="cms-button cms-button--ghost">Bewerken</a>
                        <form method="post" action="/cms/archiefstuk-delete.php" style="display:inline" data-confirm="Verwijderen?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="cms-button cms-button--danger">Verwijderen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
