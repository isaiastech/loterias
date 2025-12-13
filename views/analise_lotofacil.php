<?php
require_once '../vendor/autoload.php';

use class\Conexao;
use class\Auth;
$auth = new Auth();
$auth->requireAuth();
$db = new Conexao();
$user = $auth->user();

// Busca os 10 últimos concursos
$sql = "SELECT d01,d02,d03,d04,d05,d06,d07,d08,d09,d10,d11,d12,d13,d14,d15
        FROM lotofacil
        ORDER BY concurso DESC
        LIMIT 10";

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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Análise Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

<h3>📊 Análise dos 10 últimos concursos da Lotofácil</h3>

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

</body>
</html>
