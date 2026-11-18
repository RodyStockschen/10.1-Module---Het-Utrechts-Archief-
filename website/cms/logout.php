<?php
require_once __DIR__ . '/../includes/bootstrap.php';
logout();
header('Location: /cms/login.php');
exit;
