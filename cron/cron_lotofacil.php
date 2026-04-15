<?php
require_once '../vendor/autoload.php';

use class\Conexao;

date_default_timezone_set('America/Sao_Paulo');

function pegarDadosAPI($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erroCurl = curl_error($ch);

    curl_close($ch);

    return [
        "httpCode" => $httpCode,
        "erroCurl" => $erroCurl,
        "response" => $response
    ];
}

function atualizarLotofacil($conn)
{
    $url = "https://loteriascaixa-api.herokuapp.com/api/lotofacil/latest";

    $retorno = pegarDadosAPI($url);

    if ($retorno["httpCode"] !== 200 || !$retorno["response"]) {
        file_put_contents(__DIR__ . "/log_lotofacil.txt",
            date("Y-m-d H:i:s") . " - ERRO HTTP: {$retorno["httpCode"]} CURL: {$retorno["erroCurl"]}\n",
            FILE_APPEND
        );
        return;
    }

    $json = json_decode($retorno["response"], true);

    if (!$json) {
        file_put_contents(__DIR__ . "/log_lotofacil.txt",
            date("Y-m-d H:i:s") . " - JSON INVALIDO\n",
            FILE_APPEND
        );
        return;
    }

    $concurso = (int)($json["concurso"] ?? 0);
    $dataApuracao = $json["data"] ?? null;
    $dezenas = $json["dezenas"] ?? [];

    if ($concurso <= 0 || count($dezenas) < 15) {
        file_put_contents(__DIR__ . "/log_lotofacil.txt",
            date("Y-m-d H:i:s") . " - DADOS INCOMPLETOS\n",
            FILE_APPEND
        );
        return;
    }

    // verifica se já existe
    $check = $conn->prepare("SELECT concurso FROM lotofacil WHERE concurso=?");
    $check->bind_param("i", $concurso);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        file_put_contents(__DIR__ . "/log_lotofacil.txt",
            date("Y-m-d H:i:s") . " - Concurso {$concurso} já existe\n",
            FILE_APPEND
        );
        return;
    }

    $dataObj = DateTime::createFromFormat("d/m/Y", $dataApuracao);
    $dataFormatada = $dataObj ? $dataObj->format("Y-m-d") : null;

    $sql = "INSERT INTO lotofacil (
        concurso,
        d01,d02,d03,d04,d05,
        d06,d07,d08,d09,d10,
        d11,d12,d13,d14,d15,
        data_sorteio
    ) VALUES (
        ?,?,?,?,?,?,
        ?,?,?,?,?,?,
        ?,?,?,?,?
    )";

    $stmt = $conn->prepare($sql);

    $params = [
        $concurso,
        ...array_map('intval', $dezenas),
        $dataFormatada
    ];

    $types = "i" . str_repeat("i", 15) . "s";

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    file_put_contents(__DIR__ . "/log_lotofacil.txt",
        date("Y-m-d H:i:s") . " - Concurso {$concurso} inserido com sucesso\n",
        FILE_APPEND
    );
}

// conecta no banco
$db = new Conexao();
$conn = $db->getConnection();

// executa
atualizarLotofacil($conn);

echo "OK";

