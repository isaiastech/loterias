<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Conexao;

$nome  = trim($_POST['nome'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$senha = $_POST['senha'] ?? '';
$confirmar = $_POST['senha_confirmar'] ?? '';

if (!$nome || !$email || !$senha || $senha !== $confirmar) {
    header("Location: ../views/cadastro.php?erro=1");
    exit;
}

try {
    $db = new Conexao();

    // Verificar se email já existe
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $res = $db->getResultFromQuery($sql, [$email]);

    if ($res->num_rows > 0) {
        header("Location: ../views/cadastro.php?erro=1");
        exit;
    }

    // Criptografar senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir usuário
    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $db->getResultFromQuery($sql, [$nome, $email, $senhaHash]);

    header("Location: ../views/cadastro.php?sucesso=1");
    exit;

} catch (Exception $e) {
    header("Location: ../views/cadastro.php?erro=1");
    exit;
}
