<?php
require_once __DIR__ . '/../vendor/autoload.php';

use class\Auth;

$auth = new Auth();
$auth->requireAuth(); // só usuário logado pode cadastrar
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Cadastrar Usuário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php 
include('../header/header.php');
?>
<div class="container mt-5" style="max-width: 500px">

    <h3 class="mb-4">Cadastro de Usuário</h3>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger">
            Erro ao cadastrar usuário
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">
            Usuário cadastrado com sucesso
        </div>
    <?php endif; ?>

    <form method="POST" action="../auth/register_action.php">

        <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="senha" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirmar Senha</label>
            <input type="password" name="senha_confirmar" class="form-control" required>
        </div>

        <button class="btn btn-dark w-100">Cadastrar</button>
    </form>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
