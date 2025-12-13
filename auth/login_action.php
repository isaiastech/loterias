<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Auth;

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = $_POST['senha'] ?? null;

// Se faltar dado → volta para index.php
if (!$email || !$senha) {
    header("Location: ../index.php?erro=1");
    exit;
}

$auth = new Auth();

// Login OK → área interna
if ($auth->login($email, $senha)) {
    header("Location: ../views/index.php");
    exit;
}

// Login inválido → volta para index.php
header("Location: ../index.php?erro=1");
exit;
