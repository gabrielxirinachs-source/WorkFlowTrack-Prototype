<?php
require_once __DIR__ . '/includes/app.php';
logoutUser();
sendNoStoreHeaders();
header('Location: index.php');
exit;
