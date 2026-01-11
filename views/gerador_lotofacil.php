<?php
require_once '../vendor/autoload.php';

use class\Auth;
use class\Conexao;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->user();
$db   = new Conexao();

// Quantidade de jogos (default 5)
$qtdJogos = isset($_POST['qtd']) ? (int)$_POST['qtd'] : 5;
$jogos = [];
$frequencia = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 🔎 Busca TODOS os concursos
    $sql = "SELECT d01,d02,d03,d04,d05,d06,d07,d08,d09,d10,
                   d11,d12,d13,d14,d15
            FROM lotofacil";

    $query = $db->getResultFromQuery($sql);

    $todasDezenas = [];

    while ($row = $query->fetch_assoc()) {
        for ($i = 1; $i <= 15; $i++) {
            $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $todasDezenas[] = (int)$row[$campo];
        }
    }

    // 📊 Frequência geral
    $frequencia = array_count_values($todasDezenas);
    arsort($frequencia);
// 🔥 Dezenas Quentes (mais sorteadas)
$quentes = array_slice(array_keys($frequencia), 0, 10);

// ❄️ Dezenas Frias (menos sorteadas)
$frequenciaAsc = $frequencia;
asort($frequenciaAsc);
$frias = array_slice(array_keys($frequenciaAsc), 0, 10);

    // Dezenas ordenadas por frequência (mais sorteadas primeiro)
    $dezenasOrdenadas = array_keys($frequencia);

    // 🎯 Geração dos jogos
 $jogos = [];

for ($j = 0; $j < $qtdJogos; $j++) {

    // 🔥 8 dezenas quentes
    $baseQuentes = $quentes;
    shuffle($baseQuentes);
    $baseQuentes = array_slice($baseQuentes, 0, 8);

    // ❄️ 4 dezenas frias
    $baseFrias = $frias;
    shuffle($baseFrias);
    $baseFrias = array_slice($baseFrias, 0, 4);

    // 🔀 Complemento intermediário
    $intermediarias = array_diff(range(1, 25), $baseQuentes, $baseFrias);
    shuffle($intermediarias);
    $complemento = array_slice($intermediarias, 0, 3);

    // Junta tudo
    $jogo = array_merge($baseQuentes, $baseFrias, $complemento);
    $jogo = array_unique($jogo);

    // Garantia de 15 dezenas
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

<hr>

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
            <?= implode(
                ' - ',
                array_map(
                    fn($n) => str_pad($n, 2, '0', STR_PAD_LEFT),
                    $jogo
                )
            ); ?>
        </div>
    <?php endforeach; ?>

    <hr>

    <h5>📊 Ranking das Dezenas Mais Sorteadas</h5>
    <div class="row">
        <?php foreach ($frequencia as $dezena => $qt): ?>
            <div class="col-3 col-md-2 mb-2">
                <span class="badge bg-primary w-100">
                    <?= str_pad($dezena, 2, '0', STR_PAD_LEFT) ?> → <?= $qt ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<hr>
<h5>🔥 Dezenas Quentes</h5>
<div class="row mb-3">
    <?php foreach ($quentes as $d): ?>
        <div class="col-2 mb-2">
            <span class="badge bg-danger w-100">
                <?= str_pad($d, 2, '0', STR_PAD_LEFT) ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<h5>❄️ Dezenas Frias</h5>
<div class="row">
    <?php foreach ($frias as $d): ?>
        <div class="col-2 mb-2">
            <span class="badge bg-info w-100">
                <?= str_pad($d, 2, '0', STR_PAD_LEFT) ?>
            </span>
        </div>
    <?php endforeach; ?>
</div>

<a href="index.php" class="btn btn-secondary mt-4">Voltar</a>

</body>
</html>
