<?php 
 require_once '../vendor/autoload.php';

  use class\Conexao;

  $db = new Conexao();
$id = $_GET['id'];

$result = $db->getResultFromQuery("SELECT * FROM lotofacil WHERE id = ?", [$id]);
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Sorteio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
<div class="container mt-4">

    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4>Editar Sorteio</h4>
        </div>

        <div class="card-body">
            <form action="update.php" method="POST">

                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <div class="mb-3">
                    <label>Concurso</label>
                    <input type="number" name="concurso" value="<?= $row['concurso'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Data do Sorteio</label>
                    <input type="date" name="data_sorteio" value="<?= $row['data_sorteio'] ?>" class="form-control" required>
                </div>

                <h5>Dezenas</h5>
                <div class="row">
                    <?php for ($i=1;$i<=15;$i++): 
                        $c = "d".sprintf('%02d',$i);
                    ?>
                        <div class="col-2 mb-2">
                            <input type="number" min="1" max="25" class="form-control"
                                   name="<?= $c ?>" value="<?= $row[$c] ?>" required>
                        </div>
                    <?php endfor ?>
                </div>

                <button class="btn btn-warning mt-3">Salvar Alterações</button>
                <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
            </form>
        </div>
    </div>

</div>
</body>
</html>
