<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$rows = q(
    "SELECT l.id, l.slug, l.video_start_sec, l.video_end_sec, l.sort_order,
            i.name AS name_nl,
            e.name AS name_en,
            (SELECT COUNT(*) FROM archive_items a WHERE a.location_id = l.id) AS n_items
     FROM locations l
     LEFT JOIN location_i18n i ON i.location_id = l.id AND i.lang='nl'
     LEFT JOIN location_i18n e ON e.location_id = l.id AND e.lang='en'
     ORDER BY l.sort_order, l.video_start_sec"
)->fetchAll();

$page_title = 'Locaties';
require __DIR__ . '/_layout.php';
?>

<div class="cms-card">
    <div class="cms-flex">
        <h1>Locaties</h1>
        <a href="/cms/locatie-form.php" class="cms-button">+ Nieuwe locatie</a>
    </div>

    <table class="cms-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Naam (NL)</th>
                <th>Name (EN)</th>
                <th>Video</th>
                <th>Archief</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6"><em>Nog geen locaties. Voeg er één toe.</em></td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int)$r['sort_order'] ?></td>
                    <td><?= e($r['name_nl'] ?? '—') ?></td>
                    <td><?= e($r['name_en'] ?? '—') ?></td>
                    <td><?= e(format_time((float)$r['video_start_sec'])) ?>–<?= e(format_time((float)$r['video_end_sec'])) ?></td>
                    <td><?= (int)$r['n_items'] ?></td>
                    <td>
                        <a href="/cms/locatie-form.php?id=<?= (int)$r['id'] ?>" class="cms-button cms-button--ghost">Bewerken</a>
                        <form method="post" action="/cms/locatie-delete.php" style="display:inline" data-confirm="Weet je zeker dat je '<?= e($r['name_nl'] ?? $r['slug']) ?>' wilt verwijderen? Alle gekoppelde archiefstukken gaan ook weg.">
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
