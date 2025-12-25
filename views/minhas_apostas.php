<?php
require_once '../vendor/autoload.php';

use class\Auth;
use class\Conexao;

// ===============================
// AUTENTICAÇÃO
// ===============================
$auth = new Auth();
$auth->requireAuth();
$user = $auth->user();

// ===============================
// CONEXÃO
// ===============================
$db = new Conexao();

// ===============================
// FILTROS
// ===============================
$concursoFiltro   = isset($_GET['concurso']) ? (int)$_GET['concurso'] : null;
$dataInicioFiltro = !empty($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
$dataFimFiltro    = !empty($_GET['data_fim']) ? $_GET['data_fim'] : null;

// ===============================
// PAGINAÇÃO
// ===============================
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// ===============================
// MONTAR WHERE DINÂMICO
// ===============================
$whereClauses = ["apostador = ?"];
$params = [$user['nome']];

if ($concursoFiltro) {
    $whereClauses[] = "concurso = ?";
    $params[] = $concursoFiltro;
}

if ($dataInicioFiltro) {
    $whereClauses[] = "DATE(created_at) >= ?";
    $params[] = $dataInicioFiltro;
}

if ($dataFimFiltro) {
    $whereClauses[] = "DATE(created_at) <= ?";
    $params[] = $dataFimFiltro;
}

$whereSQL = implode(" AND ", $whereClauses);

// ===============================
// TOTAL DE APOSTAS
// ===============================
$sqlTotal = "SELECT COUNT(*) AS total FROM lotofacil_apostas WHERE $whereSQL";
$resTotal = $db->getResultFromQuery($sqlTotal, $params);
$totalApostas = (int)$resTotal->fetch_assoc()['total'];
$totalPages = ceil($totalApostas / $limit);

// ===============================
// BUSCAR APOSTAS
// ===============================
$sqlApostas = "
    SELECT *
    FROM lotofacil_apostas
    WHERE $whereSQL
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
";

$paramsApostas = array_merge($params, [$limit, $offset]);
$resApostas = $db->getResultFromQuery($sqlApostas, $paramsApostas);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Apostas - Lotofácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .dezena {
            display:inline-flex;
            width:38px;
            height:38px;
            border-radius:50%;
            align-items:center;
            justify-content:center;
            font-weight:bold;
            margin:2px;
            background:#e0e0e0;
        }
        .dezena.ok {
            background:#28a745;
            color:#fff;
        }
    </style>
</head>

<body class="bg-light">

<?php include('../header/header.php'); ?>

<div class="container my-4">

    <h3 class="mb-4">Minhas Apostas - <?= htmlspecialchars($user['nome']) ?></h3>

    <!-- FILTROS -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-3">
            <label class="form-label">Concurso</label>
            <input type="number" name="concurso" class="form-control" value="<?= htmlspecialchars($concursoFiltro) ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Data Início</label>
            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($dataInicioFiltro) ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Data Fim</label>
            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($dataFimFiltro) ?>">
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <?php if ($resApostas->num_rows === 0): ?>
        <div class="alert alert-info">
            Nenhuma aposta encontrada para os filtros selecionados.
        </div>
    <?php else: ?>

        <?php while ($apostaDB = $resApostas->fetch_assoc()): ?>

            <?php
            // ===============================
            // BUSCAR RESULTADO DO CONCURSO DA APOSTA
            // ===============================
            $sqlResultado = "SELECT * FROM lotofacil WHERE concurso = ?";
            $resResultado = $db->getResultFromQuery($sqlResultado, [$apostaDB['concurso']]);
            $resultadoDB = $resResultado->fetch_assoc();

            $resultado = [];
            $apurado = false;

            if ($resultadoDB) {
                $apurado = true;
                for ($i = 1; $i <= 15; $i++) {
                    $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
                    $resultado[] = (int)$resultadoDB[$campo];
                }
                sort($resultado);
            }

            // ===============================
            // APOSTA
            // ===============================
            $aposta = [];
            for ($i = 1; $i <= 15; $i++) {
                $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $aposta[] = (int)$apostaDB[$campo];
            }
            sort($aposta);

            $acertos = $apurado ? array_intersect($aposta, $resultado) : [];
            $totalAcertos = $apurado ? count($acertos) : 0;
            ?>

            <div class="card mb-3 shadow-sm">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Concurso: <?= $apostaDB['concurso'] ?></strong>

                        <?php if ($apurado): ?>
                            <span class="badge bg-success"><?= $totalAcertos ?> acertos</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Não apurado</span>
                        <?php endif; ?>
                    </div>

                    <small class="text-muted">
                        Cadastrado em: <?= date('d/m/Y H:i', strtotime($apostaDB['created_at'])) ?>
                    </small>

                    <hr>

                    <!-- DEZENAS DA APOSTA -->
                    <div>
                        <?php foreach ($aposta as $n): ?>
                            <span class="dezena <?= ($apurado && in_array($n, $acertos)) ? 'ok' : '' ?>">
                                <?= str_pad($n, 2, '0', STR_PAD_LEFT) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <!-- RESULTADO -->
                    <?php if ($apurado): ?>
                        <div class="mt-3">
                            <strong>Resultado do concurso:</strong><br>
                            <?php foreach ($resultado as $n): ?>
                                <span class="dezena ok">
                                    <?= str_pad($n, 2, '0', STR_PAD_LEFT) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        <?php endwhile; ?>

        <!-- PAGINAÇÃO -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $i ?>&concurso=<?= $concursoFiltro ?>&data_inicio=<?= $dataInicioFiltro ?>&data_fim=<?= $dataFimFiltro ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
