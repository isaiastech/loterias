<?php
require_once '../vendor/autoload.php';

use class\Conexao;

$db = new Conexao();

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

$busca = $_GET['busca'] ?? '';
$ano   = $_GET['ano'] ?? '';

$where = [];

if ($busca !== '') {
    $where[] = "concurso = '".intval($busca)."'";
}

if ($ano !== '') {
    $where[] = "YEAR(data_sorteio) = '".intval($ano)."'";
}

$filtro = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Query principal
$sql = "
    SELECT *
    FROM lotofacil
    $filtro
    ORDER BY concurso DESC
    LIMIT $limite OFFSET $offset
";

$result = $db->getResultFromQuery($sql);

// Total
$totalSql = "
    SELECT COUNT(*) AS total
    FROM lotofacil
    $filtro
";

$total = $db->getResultFromQuery($totalSql)
            ->fetch_assoc()['total'];

$totalPaginas = ceil($total / $limite);
?>

<table class="table table-bordered table-striped mt-4 shadow">
    <thead class="table-dark">
        <tr>
            <th>Concurso</th>
            <th>Data</th>
            <th>Dezenas</th>
            <th width="180">Ações</th>
        </tr>
    </thead>
    <tbody>

    <?php if ($total == 0): ?>
        <tr>
            <td colspan="4" class="text-center text-muted">
                Nenhum resultado encontrado
            </td>
        </tr>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['concurso'] ?></td>
            <td>
                <span class="badge bg-dark">
                    <?= !empty($row['data_sorteio'])
                        ? (new DateTime($row['data_sorteio']))->format('d/m/Y')
                        : '-' ?>
                </span>
            </td>
            <td>
                <?php
                for ($i = 1; $i <= 15; $i++) {
                    $c = "d".sprintf('%02d',$i);
                    echo "<span class='badge bg-success me-1'>{$row[$c]}</span>";
                }
                ?>
            </td>
            <td>
                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                <a href="delete.php?id=<?= $row['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Confirma excluir?')">
                   Excluir
                </a>
            </td>
        </tr>
    <?php endwhile; ?>

    </tbody>
</table>

<!-- PAGINAÇÃO -->
<nav>
<ul class="pagination justify-content-center">

    <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
        <a class="page-link" href="#" data-pagina="<?= $pagina - 1 ?>">Anterior</a>
    </li>

    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
            <a class="page-link" href="#" data-pagina="<?= $i ?>"><?= $i ?></a>
        </li>
    <?php endfor; ?>

    <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">
        <a class="page-link" href="#" data-pagina="<?= $pagina + 1 ?>">Próxima</a>
    </li>

</ul>
</nav>
