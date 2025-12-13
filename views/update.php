<?php
require_once '../vendor/autoload.php';
use class\Conexao;
use class\Auth;
$auth = new Auth();
$auth->requireAuth(); // só usuário logado pode atualizar
$user = $auth->user();
$db = new Conexao();

$id = $_POST['id'];
$concurso = $_POST['concurso'];
$data = $_POST['data_sorteio'];

// Coletar dezenas
$dezenas = [];
for ($i=1; $i<=15; $i++) {
    $dezenas[] = $_POST["d".sprintf('%02d',$i)];
}

$sql = "
UPDATE lotofacil SET
    concurso = ?,
    data_sorteio = ?,
    d01 = ?, d02 = ?, d03 = ?, d04 = ?, d05 = ?,
    d06 = ?, d07 = ?, d08 = ?, d09 = ?, d10 = ?,
    d11 = ?, d12 = ?, d13 = ?, d14 = ?, d15 = ?
WHERE id = ?
";

$params = array_merge([$concurso, $data], $dezenas, [$id]);

$db->getResultFromQuery($sql, $params);

header("Location: index.php?edit_ok=1");
exit;
