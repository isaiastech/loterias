<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Auth;

$auth = new Auth();
$auth->logout();

header("Location: ../index.php");
exit;
