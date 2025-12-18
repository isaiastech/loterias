<?php
require_once '../vendor/autoload.php';

use class\Conexao;
use class\Auth;
$auth = new Auth();
$auth->requireAuth();
$db = new Conexao();
$user = $auth->user();
// Quantidade padrão
$limite = 10;

// Limites permitidos
$limitesPermitidos = [5, 10, 20];

if (isset($_GET['limite']) && in_array((int)$_GET['limite'], $limitesPermitidos)) {
    $limite = (int)$_GET['limite'];
}

// Busca os 10 últimos concursos
$sql = "SELECT d01,d02,d03,d04,d05,d06,d07,d08,d09,d10,
               d11,d12,d13,d14,d15
        FROM lotofacil
        ORDER BY concurso DESC
        LIMIT {$limite}";

$resultados = $db->getResultFromQuery($sql);

$numeros = [];

// Percorre os resultados
foreach ($resultados as $linha) {
    for ($i = 1; $i <= 15; $i++) {
        $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);

        if (isset($linha[$campo])) {
            $numeros[] = (int)$linha[$campo];
        }
    }
}

// Conta quantas vezes cada número apareceu
$frequencia = array_count_values($numeros);

// Ordena do mais frequente para o menos
arsort($frequencia);
$topQtd = 5;

// Fortes (mais frequentes)
$dezenasFortes = array_slice($frequencia, 0, $topQtd, true);

// Fracas (menos frequentes)
$dezenasFracas = array_slice(array_reverse($frequencia, true), 0, $topQtd, true);

// Totais
$totalOcorrencias = array_sum($frequencia);
$totalFortes = array_sum($dezenasFortes);
$totalFracas = array_sum($dezenasFracas);

// Percentuais
$percFortes = round(($totalFortes / $totalOcorrencias) * 100, 2);
$percFracas = round(($totalFracas / $totalOcorrencias) * 100, 2);

$topQtd = 5;
$topDezenas = array_slice(array_keys($frequencia), 0, $topQtd);
$labels = [];
$data = [];
$backgroundColors = [];

foreach ($frequencia as $numero => $qtd) {
    $labels[] = str_pad($numero, 2, '0', STR_PAD_LEFT);
    $data[] = $qtd;

    // Destaque top dezenas
    if (in_array($numero, $topDezenas)) {
        $backgroundColors[] = 'rgba(220,53,69,0.8)'; // destaque
    } else {
        $backgroundColors[] = 'rgba(13,110,253,0.5)'; // padrão
    }
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Análise Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
  <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
  <form method="get" class="row g-2 align-items-center mb-3">
    <div class="col-auto">
      <label class="form-label mb-0 fw-bold">Últimos concursos:</label>
    </div>
    
    <div class="col-auto">
      <select name="limite" class="form-select" onchange="this.form.submit()">
        <option value="5"  <?= $limite == 5  ? 'selected' : '' ?>>5</option>
        <option value="10" <?= $limite == 10 ? 'selected' : '' ?>>10</option>
        <option value="20" <?= $limite == 20 ? 'selected' : '' ?>>20</option>
      </select>
    </div>
  </form>
  <h3>📊 Análise dos últimos <?= $limite ?> concursos da Lotofácil</h3>
  <div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">
            📈 Frequência das dezenas (últimos <?= $limite ?> concursos)
        </h5>
        <canvas id="graficoLotofacil" height="120"></canvas>
    </div>
</div>
<div class="row mt-4">

    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white fw-bold">
                🔥 Dezenas Fortes (Top <?= $topQtd ?>)
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Participação:</strong> <?= $percFortes ?>%
                </p>

                <ul class="list-group">
                    <?php foreach ($dezenasFortes as $num => $qtd): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= str_pad($num, 2, '0', STR_PAD_LEFT) ?></span>
                            <span><?= $qtd ?>x</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-secondary">
            <div class="card-header bg-secondary text-white fw-bold">
                ❄️ Dezenas Fracas (Top <?= $topQtd ?>)
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Participação:</strong> <?= $percFracas ?>%
                </p>

                <ul class="list-group">
                    <?php foreach ($dezenasFracas as $num => $qtd): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= str_pad($num, 2, '0', STR_PAD_LEFT) ?></span>
                            <span><?= $qtd ?>x</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<table class="table table-bordered table-striped mt-3">
    <thead class="table-dark">
        <tr>
            <th>Número</th>
            <th>Quantidade</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($frequencia as $numero => $qtd): ?>
            <tr>
                <td><?= str_pad($numero, 2, '0', STR_PAD_LEFT) ?></td>
                <td><?= $qtd ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="text-muted">
    Total de dezenas analisadas: <?= count($numeros); ?>
</p>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const canvas = document.getElementById('graficoLotofacil');

    if (!canvas) {
        console.error('Canvas não encontrado');
        return;
    }

    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
    data: <?= json_encode($data) ?>,
    backgroundColor: <?= json_encode($backgroundColors) ?>,
    borderWidth: 1
}]

        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

});
</script>
</body>
</html>
