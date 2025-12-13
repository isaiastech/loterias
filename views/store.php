<?php 
 require_once '../vendor/autoload.php';

  use class\Conexao;
  use class\Auth;
$auth = new Auth();
$auth->requireAuth(); // só usuário logado pode cadastrar
$user = $auth->user();
  $db = new Conexao();

$concurso = $_POST['concurso'];
$data = $_POST['data_sorteio'];

$dezenas = [];
for($i=1;$i<=15;$i++){
    $dezenas[] = $_POST["d".sprintf('%02d',$i)];
}

$sql = "
INSERT INTO lotofacil (
    concurso, data_sorteio,
    d01, d02, d03, d04, d05,
    d06, d07, d08, d09, d10,
    d11, d12, d13, d14, d15
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)
";

$params = array_merge([$concurso, $data], $dezenas);

$db->getResultFromQuery($sql, $params);

header("Location: index.php?ok=1");
exit;
