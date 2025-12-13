<?php
require_once '../vendor/autoload.php';

  use class\Conexao;
  use class\Auth; 
$auth = new Auth();
$auth->requireAuth(); // só usuário logado pode deletar
$user = $auth->user();
  $db = new Conexao();
// Verifica se o ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php?error=no_id");
    exit;
}

$id = intval($_GET['id']); // Segurança

$db = new Conexao();

// Consulta para deletar
$sql = "DELETE FROM lotofacil WHERE id = ?";

$params = [$id];

// Executa usando seu método do Conexao
$result = $db->getResultFromQuery($sql, $params);

// Redireciona após excluir
header("Location: index.php?delete_ok=1");
exit;
