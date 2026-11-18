<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

$stats = [
    'locations' => (int)q('SELECT COUNT(*) FROM locations')->fetchColumn(),
    'items'     => (int)q('SELECT COUNT(*) FROM archive_items')->fetchColumn(),
];

$admin = current_admin();
$page_title = 'Dashboard';
require __DIR__ . '/_layout.php';
?>

<div class="cms-card">
    <h1>Welkom, <?= e($admin['email']) ?></h1>
    <p>Hier beheer je de historische locaties en archiefstukken die op de website getoond worden.</p>
</div>

<div class="cms-card">
    <h2>Overzicht</h2>
    <table class="cms-table">
        <tbody>
            <tr>
                <th style="width:50%"><a href="/cms/locaties.php">Locaties</a></th>
                <td><?= $stats['locations'] ?></td>
            </tr>
            <tr>
                <th><a href="/cms/archiefstukken.php">Archiefstukken</a></th>
                <td><?= $stats['items'] ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="cms-card">
    <h2>Snel aan de slag</h2>
    <p>
        <a href="/cms/locatie-form.php" class="cms-button">+ Nieuwe locatie</a>
        <a href="/cms/archiefstuk-form.php" class="cms-button">+ Nieuw archiefstuk</a>
        <a href="/cms/minimap-focus.php" class="cms-button cms-button--ghost">Minimap-gebied aanpassen</a>
    </p>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
