<?php 
require_once '../vendor/autoload.php';

use class\Auth;

$auth = new Auth();
$auth->requireAuth();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lotofácil - Resultados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<?php include('../header/header.php'); ?>

<body class="bg-light">
<div class="container mt-4">

    <form action="/api/import_lotofacil.php" method="post">
        <button class="btn btn-success mb-3">🔄 Importar último concurso</button>
    </form>

    <div class="d-flex justify-content-between mb-3">
        <h3>Resultados da Lotofácil</h3>
        <div>
            <a href="gerador_lotofacil.php" class="btn btn-primary">Gerador Sorteio</a>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="number" id="busca" class="form-control"
                   placeholder="Buscar por concurso">
        </div>

        <div class="col-md-3">
            <select id="ano" class="form-select">
                <option value="">Todos os anos</option>
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>"><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- RESULTADO AJAX -->
    <div id="resultado"></div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function () {

    carregarResultados(1);

    function carregarResultados(pagina) {
        $.ajax({
            url: 'resultados_ajax.php',
            type: 'GET',
            data: {
                pagina: pagina,
                busca: $('#busca').val(),
                ano: $('#ano').val()
            },
            success: function (html) {
                $('#resultado').html(html);
            }
        });
    }

    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        carregarResultados($(this).data('pagina'));
    });

    $('#busca, #ano').on('input change', function () {
        carregarResultados(1);
    });

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
