<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Conexao;


function logMsg($msg)
{
    file_put_contents(__DIR__ . "/log_lotofacil_2026.txt",
        date("Y-m-d H:i:s") . " - " . $msg . "\n",
        FILE_APPEND
    );
}

function pegarDadosAPI($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
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

function concursoExiste($conn, $concurso)
{
    $check = $conn->prepare("SELECT id FROM lotofacil WHERE concurso=? LIMIT 1");
    $check->bind_param("i", $concurso);
    $check->execute();
    $check->store_result();

    return $check->num_rows > 0;
}

function inserirConcurso($conn, $json)
{
    $concurso = (int)($json["concurso"] ?? 0);
    $dataApuracao = $json["data"] ?? null;
    $dezenas = $json["dezenas"] ?? [];

    if ($concurso <= 0 || !$dataApuracao || count($dezenas) < 15) {
        return false;
    }

    $dataObj = DateTime::createFromFormat("d/m/Y", $dataApuracao);
    $dataFormatada = $dataObj ? $dataObj->format("Y-m-d") : null;

    if (!$dataFormatada) {
        return false;
    }

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

    return $stmt->execute();
}

function buscarPrimeiroUltimoConcurso2026()
{
    $url = "https://loteriascaixa-api.herokuapp.com/api/lotofacil";
    $retorno = pegarDadosAPI($url);

    if ($retorno["httpCode"] !== 200 || !$retorno["response"]) {
        logMsg("ERRO ao buscar lista geral - HTTP {$retorno["httpCode"]} - CURL {$retorno["erroCurl"]}");
        return null;
    }

    $json = json_decode($retorno["response"], true);

    if (!$json || !is_array($json)) {
        logMsg("ERRO JSON inválido ao buscar lista geral");
        return null;
    }

    $concursos2026 = [];

    foreach ($json as $item) {
        if (!isset($item["concurso"]) || !isset($item["data"])) continue;

        $data = DateTime::createFromFormat("d/m/Y", $item["data"]);
        if (!$data) continue;

        if ($data->format("Y") == "2026") {
            $concursos2026[] = (int)$item["concurso"];
        }
    }

    if (count($concursos2026) == 0) {
        logMsg("Nenhum concurso de 2026 encontrado na API");
        return null;
    }

    sort($concursos2026);

    return [
        "primeiro" => $concursos2026[0],
        "ultimo" => $concursos2026[count($concursos2026) - 1]
    ];
}

function completarConcursos2026($conn)
{
    logMsg("==== INICIANDO COMPLETAR LOTOFACIL 2026 ====");

    $range = buscarPrimeiroUltimoConcurso2026();

    if (!$range) {
        logMsg("Falha ao detectar range de concursos 2026.");
        return;
    }

    $inicio = $range["primeiro"];
    $fim = $range["ultimo"];

    logMsg("Concursos 2026 detectados: {$inicio} até {$fim}");

    $inseridos = 0;
    $jaExistem = 0;
    $falhas = 0;

    for ($concurso = $inicio; $concurso <= $fim; $concurso++) {

        if (concursoExiste($conn, $concurso)) {
            $jaExistem++;
            continue;
        }

        $url = "https://loteriascaixa-api.herokuapp.com/api/lotofacil/" . $concurso;
        $retorno = pegarDadosAPI($url);

        if ($retorno["httpCode"] !== 200 || !$retorno["response"]) {
            logMsg("ERRO concurso {$concurso} - HTTP {$retorno["httpCode"]} - CURL {$retorno["erroCurl"]}");
            $falhas++;
            continue;
        }

        $json = json_decode($retorno["response"], true);

        if (!$json) {
            logMsg("ERRO concurso {$concurso} - JSON inválido");
            $falhas++;
            continue;
        }

        $ok = inserirConcurso($conn, $json);

        if ($ok) {
            logMsg("Concurso {$concurso} inserido com sucesso");
            $inseridos++;
        } else {
            logMsg("ERRO ao inserir concurso {$concurso}");
            $falhas++;
        }

        // pequena pausa para não sobrecarregar API
        usleep(300000); // 0.3 segundos
    }

    logMsg("==== FINALIZADO 2026 ====");
    logMsg("Inseridos: {$inseridos} | Já existiam: {$jaExistem} | Falhas: {$falhas}");
}


// CONECTA
$db = new Conexao();
$conn = $db->getConnection();

// EXECUTA
completarConcursos2026($conn);

echo "OK\n";