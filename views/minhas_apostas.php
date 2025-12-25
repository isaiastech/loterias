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
$concursoFiltro = isset($_GET['concurso']) ? (int)$_GET['concurso'] : null;
$dataInicioFiltro = !empty($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
$dataFimFiltro = !empty($_GET['data_fim']) ? $_GET['data_fim'] : null;

// ===============================
// PAGINAÇÃO
// ===============================
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// ===============================
// BUSCAR TOTAL DE APOSTAS COM FILTRO
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

$sqlTotal = "SELECT COUNT(*) as total FROM lotofacil_apostas WHERE $whereSQL";
$resTotal = $db->getResultFromQuery($sqlTotal, $params);
$totalApostas = (int)$resTotal->fetch_assoc()['total'];
$totalPages = ceil($totalApostas / $limit);

// ===============================
// BUSCAR ÚLTIMO RESULTADO
// ===============================
$sqlResultado = "SELECT * FROM lotofacil ORDER BY concurso DESC LIMIT 1";
$resResultado = $db->getResultFromQuery($sqlResultado);
$resultadoDB = $resResultado->fetch_assoc();
$resultado = [];

if ($resultadoDB) {
    for ($i = 1; $i <= 15; $i++) {
        $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
        $resultado[] = (int)$resultadoDB[$campo];
    }
    sort($resultado);
}

// ===============================
// BUSCAR APOSTAS FILTRADAS E PAGINADAS
// ===============================
$sqlApostas = "SELECT * FROM lotofacil_apostas WHERE $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$resApostas = $db->getResultFromQuery($sqlApostas, $params);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Apostas - Lotofácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dezena { display:inline-flex; width:38px; height:38px; border-radius:50%; align-items:center; justify-content:center; font-weight:bold; margin:2px; background:#e0e0e0; }
        .dezena.ok { background:#28a745; color:#fff; }
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
            <input type="number" name="concurso" class="form-control" value="<?= htmlspecialchars($concursoFiltro) ?>" min="1">
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
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <?php if ($resApostas->num_rows === 0): ?>
        <div class="alert alert-info">Nenhuma aposta encontrada para os filtros selecionados.</div>
    <?php else: ?>
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
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <strong>Concurso:</strong> <?= $apostaDB['concurso'] ?>
                    <span class="badge bg-primary"><?= $total ?> acertos</span>
                </div>
                <small>Cadastrado em: <?= date('d/m/Y H:i', strtotime($apostaDB['created_at'])) ?></small>
                <hr>
                <div>
                    <?php foreach ($aposta as $n): ?>
                        <span class="dezena <?= in_array($n, $acertos) ? 'ok' : '' ?>">
                            <?= str_pad($n, 2, '0', STR_PAD_LEFT) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php if ($resultadoDB): ?>
                <div class="mt-2">
                    Último resultado: 
                    <?php foreach ($resultado as $n): ?>
                        <span class="dezena ok"><?= str_pad($n, 2, '0', STR_PAD_LEFT) ?></span>
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
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&concurso=<?= $concursoFiltro ?>&data_inicio=<?= $dataInicioFiltro ?>&data_fim=<?= $dataFimFiltro ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
