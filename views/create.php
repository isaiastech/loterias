<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Auth;

$auth = new Auth();
$auth->requireAuth(); // só usuário logado pode cadastrar
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Sorteio - Lotofácil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
<div class="container mt-4">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Cadastrar Sorteio</h4>
        </div>

        <div class="card-body">
            <form action="store.php" method="POST">

                <div class="mb-3">
                    <label>Concurso</label>
                    <input type="number" name="concurso" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Data do Sorteio</label>
                    <input type="date" name="data_sorteio" class="form-control" required>
                </div>

                <h5>Dezenas</h5>
                <div class="row">
                    <?php for ($i=1;$i<=15;$i++): ?>
                        <div class="col-2 mb-2">
                            <input type="number" min="1" max="25" class="form-control"
                                   required name="d<?= sprintf('%02d',$i) ?>">
                        </div>
                    <?php endfor ?>
                </div>

                <button class="btn btn-success mt-3">Salvar</button>
                <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
            </form>
        </div>
    </div>

</div>
</body>
</html>
