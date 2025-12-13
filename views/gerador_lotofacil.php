<?php
require_once '../vendor/autoload.php';

use class\Auth;
use class\Conexao;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->user();
$db = new Conexao();


$db = new Conexao();

// Quantidade de jogos (default 5)
$qtdJogos = isset($_POST['qtd']) ? (int)$_POST['qtd'] : 5;

$jogos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "SELECT concurso, d01,d02,d03,d04,d05,d06,d07,d08,d09,d10,d11,d12,d13,d14,d15
            FROM lotofacil
            ORDER BY concurso DESC
            LIMIT 10";

    $query = $db->getResultFromQuery($sql);

    $resultados = [];
    while ($row = $query->fetch_assoc()) {
        $resultados[] = $row;
    }

    // Último concurso
    $ultimo = array_shift($resultados);

    // Dezenas do último concurso
    $dezenasUltimo = [];
    for ($i = 1; $i <= 15; $i++) {
        $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
        $dezenasUltimo[] = (int)$ultimo[$campo];
    }

    // Frequência dos anteriores
    $todas = [];
    foreach ($resultados as $linha) {
        for ($i = 1; $i <= 15; $i++) {
            $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $todas[] = (int)$linha[$campo];
        }
    }

    $frequencia = array_count_values($todas);
    arsort($frequencia);

    // Score do último concurso
    $scoresUltimo = [];
    foreach ($dezenasUltimo as $num) {
        $scoresUltimo[$num] = $frequencia[$num] ?? 0;
    }
    arsort($scoresUltimo);

    $ordenadosUltimo = array_keys($scoresUltimo);

    // Núcleo fixo (7 mais fortes)
    $core = array_slice($ordenadosUltimo, 0, 7);

    // Números novos
    $novosDisponiveis = [];
    foreach ($frequencia as $num => $freq) {
        if (!in_array($num, $dezenasUltimo)) {
            $novosDisponiveis[] = $num;
        }
    }

    // Geração dos jogos
    for ($j = 0; $j < $qtdJogos; $j++) {

        shuffle($ordenadosUltimo);
        shuffle($novosDisponiveis);

        $repetidos = array_slice(
            array_merge($core, array_diff($ordenadosUltimo, $core)),
            0,
            9
        );

        $novos = array_slice($novosDisponiveis, 0, 6);

        $jogo = array_unique(array_merge($repetidos, $novos));

// Completa até 15 dezenas, se faltar
$pool = range(1, 25);
shuffle($pool);

foreach ($pool as $n) {
    if (count($jogo) >= 15) break;
    if (!in_array($n, $jogo)) {
        $jogo[] = $n;
    }
}

sort($jogo);


        $jogos[] = $jogo;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerador Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
<h1>Dashboard</h1>
Olá, <strong><?= htmlspecialchars($user['nome']) ?></strong>
<h3>🎯 Gerador Automático – Lotofácil</h3>
<form method="post" class="row g-3 mb-4">
    <div class="col-auto">
        <select name="qtd" class="form-select">
            <?php foreach ([3,5,7,10] as $q): ?>
                <option value="<?= $q ?>" <?= $qtdJogos == $q ? 'selected' : '' ?>>
                    <?= $q ?> jogos
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-success">Gerar Jogos</button>
    </div>
</form>
<?php if (!empty($jogos)): ?>
    <h5>📋 Jogos Gerados</h5>
    <?php foreach ($jogos as $i => $jogo): ?>
        <div class="alert alert-success">
            <strong>Jogo <?= $i + 1 ?>:</strong>
            <?= implode(' - ', array_map(fn($n) => str_pad($n, 2, '0', STR_PAD_LEFT), $jogo)); ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<a href="index.php" class="btn btn-secondary mt-3">Voltar</a>

</body>
</html>
