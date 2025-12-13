<?php 
 require_once '../vendor/autoload.php';
  use class\Auth;
  use class\Conexao;
  
  $auth = new Auth();
  $auth->requireAuth();

  $user = $auth->user();
  $db = new Conexao();

$result = $db->getResultFromQuery("SELECT * FROM lotofacil ORDER BY concurso DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lotofácil - Resultados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="login.css">
</head>
<?php 
include('../header/header.php');
?>
<body class="bg-light">
<div class="container mt-4">
<form action="/api/import_lotofacil.php" method="post">
    <button class="btn btn-success mb-3">
        🔄 Importar último concurso
    </button>
</form>
    <div class="d-flex justify-content-between">
        <h3>Resultados da Lotofácil</h3>
        <a href="create.php" class="btn btn-primary">Novo Sorteio</a>
        <a href="gerador_lotofacil.php" class="btn btn-primary">Gerador Sorteio</a>
    </div>

    <table class="table table-bordered table-striped mt-4 shadow">
        <thead class="table-dark">
            <tr>
                <th>Concurso</th>
                <th>Data</th>
                <th>Dezenas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
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
                <td width="180">
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Confirma excluir?')">
                        Excluir
                    </a>
                </td>
            </tr>
            <?php endwhile ?>
        </tbody>
    </table>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>
