<?php 
require_once '../vendor/autoload.php';

use class\Auth;
use class\Conexao;

$auth = new Auth();
$auth->requireAuth();

$db = new Conexao();
$conn = $db->getConnection();
function pegarDadosAPI($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "User-Agent: Mozilla/5.0"
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        return false;
    }

    return $response;
}

function atualizarLotofacil($conn)
{
    // usa API alternativa sem bloqueio
    $url = "https://loteriascaixa-api.herokuapp.com/api/lotofacil/latest";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$data) {
        return;
    }

    $json = json_decode($data, true);
    if (!$json) {
        return;
    }

    $concurso = (int)($json["concurso"] ?? $json["concurso"] ?? 0);

    $check = $conn->prepare("SELECT concurso FROM lotofacil WHERE concurso=?");
    $check->bind_param("i", $concurso);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        return;
    }

    // algumas APIs retornam campo diferente
    $dataApuracao = $json["data"] ?? $json["dataApuracao"] ?? null;
    $dataObj = $dataApuracao ? DateTime::createFromFormat("d/m/Y", $dataApuracao) : null;
    $dataFormatada = $dataObj ? $dataObj->format("Y-m-d") : null;

    $dezenas = $json["dezenas"] ?? $json["listaDezenas"] ?? [];

    if (count($dezenas) < 15) {
        return;
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
    $stmt->execute();
}
/* EXECUTA AUTOMATICAMENTE */
atualizarLotofacil($conn);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lotofácil - Resultados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<?php include('../header/header.php'); ?>

<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>Resultados da Lotofácil</h3>
        <div>
            <a href="gerador_lotofacil.php" class="btn btn-primary">Gerador Sorteio</a>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="number" id="busca" class="form-control"
                   placeholder="Buscar por concurso">
        </div>

        <div class="col-md-3">
            <select id="ano" class="form-select">
                <option value="">Todos os anos</option>
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- RESULTADO AJAX -->
    <div id="resultado"></div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function () {

    carregarResultados(1);

    function carregarResultados(pagina) {
        $.ajax({
            url: 'resultados_ajax.php',
            type: 'GET',
            data: {
                pagina: pagina,
                busca: $('#busca').val(),
                ano: $('#ano').val()
            },
            success: function (html) {
                $('#resultado').html(html);
            }
        });
    }

    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        carregarResultados($(this).data('pagina'));
    });

    $('#busca, #ano').on('input change', function () {
        carregarResultados(1);
    });

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
