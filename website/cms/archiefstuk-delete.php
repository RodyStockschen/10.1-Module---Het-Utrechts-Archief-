<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /cms/archiefstukken.php'); exit;
}
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    // Fetch image path to delete file if it's in uploads
    $row = q('SELECT image_path FROM archive_items WHERE id = ?', [$id])->fetch();
    q('DELETE FROM archive_items WHERE id = ?', [$id]);

    if ($row && str_starts_with($row['image_path'], 'assets/uploads/')) {
        $abs = ROOT_PATH . '/' . $row['image_path'];
        if (is_file($abs)) @unlink($abs);
    }
    $_SESSION['flash_success'] = 'Archiefstuk verwijderd.';
}
header('Location: /cms/archiefstukken.php');
exit;
