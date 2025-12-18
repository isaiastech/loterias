<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Conexao;
use class\Auth;

session_start();

$auth = new Auth();
$auth->requireAuth(); // só usuário logado pode importar

$db = new Conexao();
$conn = $db->getConnection();

$url = "https://api.guidi.dev.br/loteria/lotofacil/ultimo";
$data = file_get_contents($url);

if (!$data) {
    $_SESSION['error'] = "Erro ao acessar a API da Caixa.";
    header("Location: /views/index.php");
    exit;
}

$json = json_decode($data, true);

if (!isset($json["listaDezenas"], $json["numero"])) {
    $_SESSION['error'] = "Resposta inesperada da API.";
    header("Location: /views/index.php");
    exit;
}

$concurso = (int)$json["numero"];

$dataObj = DateTime::createFromFormat("d/m/Y", $json["dataApuracao"]);
$dataFormatada = $dataObj->format("Y-m-d");

$dezenas = $json["listaDezenas"];

$sql = "INSERT INTO lotofacil (
    concurso,
    d01, d02, d03, d04, d05,
    d06, d07, d08, d09, d10,
    d11, d12, d13, d14, d15,
    data_sorteio
) VALUES (
    ?,?,?,?,?,?,
    ?,?,?,?,?,?,
    ?,?,?,?,?
)
ON DUPLICATE KEY UPDATE concurso = concurso";

$stmt = $conn->prepare($sql);

$params = [
    $concurso,
    ...array_map('intval', $dezenas),
    $dataFormatada
];

$types = "i" . str_repeat("i", 15) . "s";

$stmt->bind_param($types, ...$params);
$stmt->execute();

$_SESSION['success'] = "Concurso $concurso importado com sucesso!";
header("Location: /views/index.php");
exit;
