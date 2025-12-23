<?php
require_once '../vendor/autoload.php';

use class\Conexao;
use class\Auth;

$auth = new Auth();
$auth->requireAuth();
$user = $auth->user();

$db = new Conexao();

// ===============================
// 1️⃣ BUSCAR ÚLTIMO CONCURSO SORTEADO
// ===============================
$sqlResultado = "
    SELECT *
    FROM lotofacil
    ORDER BY concurso DESC
    LIMIT 1
";

$resResultado = $db->getResultFromQuery($sqlResultado);
$resultadoDB = $resResultado->fetch_assoc();

if (!$resultadoDB) {
    die('Nenhum concurso encontrado.');
}

$concursoResultado = (int)$resultadoDB['concurso'];

// Monta array do resultado
$resultado = [];
for ($i = 1; $i <= 15; $i++) {
    $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $resultado[] = (int)$resultadoDB[$campo];
}
sort($resultado);

// ===============================
// 2️⃣ BUSCAR APOSTAS DO MESMO CONCURSO
// ===============================
$sqlApostas = "
    SELECT *
    FROM lotofacil_apostas
    WHERE concurso = ?
    ORDER BY id DESC
";
$resApostas = $db->getResultFromQuery($sqlApostas, [$concursoResultado]);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Conferir Lotofácil</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .dezena {
            display: inline-flex;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 2px;
            background: #e0e0e0;
        }
        .dezena.ok {
            background: #28a745;
            color: #fff;
        }
    </style>
</head>
<?php include('../header/header.php'); ?>
<body class="bg-light">
<div class="container my-4">

    <!-- RESULTADO DO CONCURSO -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h4 class="mb-1">Concurso <?= $concursoResultado; ?></h4>
            <p class="text-muted mb-2">
                <?= date('d/m/Y', strtotime($resultadoDB['data_sorteio'])); ?>
            </p>

            <div>
                <?php foreach ($resultado as $n): ?>
                    <span class="dezena ok"><?= str_pad($n, 2, '0', STR_PAD_LEFT); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- APOSTAS -->
    <h5 class="mb-3">Apostas Conferidas</h5>

    <?php if ($resApostas->num_rows === 0): ?>
        <div class="alert alert-info">
            Nenhuma aposta cadastrada para este concurso.
        </div>
    <?php endif; ?>

    <?php while ($apostaDB = $resApostas->fetch_assoc()): 

        $aposta = [];
        for ($i = 1; $i <= 15; $i++) {
            $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $aposta[] = (int)$apostaDB[$campo];
        }
        sort($aposta);

        $acertos = array_intersect($aposta, $resultado);
        $total = count($acertos);
    ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">
                <strong><?= htmlspecialchars($apostaDB['apostador'] ?? '—'); ?></strong>
                <span class="badge bg-primary"><?= $total ?> acertos</span>
            </div>

            <hr class="my-2">

            <!-- NÚMEROS DA APOSTA -->
            <div class="mb-2">
                <?php foreach ($aposta as $n): ?>
                    <span class="dezena <?= in_array($n, $acertos) ? 'ok' : '' ?>">
                        <?= str_pad($n, 2, '0', STR_PAD_LEFT); ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- PREMIAÇÃO -->
            <div>
                <?php
                $premios = [
                    15 => ['🏆 15 acertos', 'success'],
                    14 => ['🎯 14 acertos', 'success'],
                    13 => ['👏 13 acertos', 'warning'],
                    12 => ['🙂 12 acertos', 'warning'],
                    11 => ['🙂 11 acertos', 'info'],
                ];

                if (isset($premios[$total])) {
                    echo "<span class='badge bg-{$premios[$total][1]}'>
                            {$premios[$total][0]}
                          </span>";
                } else {
                    echo "<span class='badge bg-secondary'>❌ Não premiado</span>";
                }
                ?>
            </div>

        </div>
    </div>

    <?php endwhile; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
