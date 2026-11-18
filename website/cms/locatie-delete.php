<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /cms/locaties.php'); exit;
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    q('DELETE FROM locations WHERE id = ?', [$id]);
    $_SESSION['flash_success'] = 'Locatie verwijderd.';
}
header('Location: /cms/locaties.php');
exit;
