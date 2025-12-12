<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Conexao;

$db = new Conexao();
$conn = $db->getConnection();

$url = "https://servicebus2.caixa.gov.br/portaldeloterias/api/lotofacil";
$data = file_get_contents($url);

if (!$data) {
    die("Erro ao acessar a API da Caixa.");
}

$json = json_decode($data, true);

if (!isset($json["listaDezenas"]) || !isset($json["numero"])) {
    die("Erro: resposta inesperada da API.");
}

$concurso = (int)$json["numero"];

$dataObj = DateTime::createFromFormat("d/m/Y", $json["dataApuracao"]);
$dataFormatada = $dataObj->format("Y-m-d");

$dezenas = $json["listaDezenas"]; // 15 dezenas certinho

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
    $dezenas[0], $dezenas[1], $dezenas[2], $dezenas[3], $dezenas[4],
    $dezenas[5], $dezenas[6], $dezenas[7], $dezenas[8], $dezenas[9],
    $dezenas[10], $dezenas[11], $dezenas[12], $dezenas[13], $dezenas[14],
    $dataFormatada
];

$types = "i" . str_repeat("i", 15) . "s";

$stmt->bind_param($types, ...$params);
$stmt->execute();

echo "Concurso $concurso importado com sucesso!";
