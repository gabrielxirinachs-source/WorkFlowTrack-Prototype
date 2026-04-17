<?php
require_once __DIR__ . '/includes/app.php';
logoutUser();
header('Location: index.php');
exit;
