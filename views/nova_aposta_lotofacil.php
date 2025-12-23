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
// BUSCAR ÚLTIMO CONCURSO SORTEADO
// ===============================
$sql = "
    SELECT concurso
    FROM lotofacil
    ORDER BY concurso DESC
    LIMIT 1
";

$res = $db->getResultFromQuery($sql);
$ultimo = $res->fetch_assoc();

if (!$ultimo) {
    die('Nenhum concurso disponível.');
}

// Próximo concurso disponível para apostas
$concursoAposta = (int)$ultimo['concurso'] + 1;

// ===============================
// CSRF TOKEN
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Aposta - Lotofácil</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php include('../header/header.php'); ?>

<body class="bg-light">

<div class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Cadastrar Aposta - Lotofácil</h5>
                </div>

                <div class="card-body">

                    <form method="POST" action="salvar_aposta_lotofacil.php">

                        <!-- CSRF -->
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <!-- Apostador -->
                        <div class="mb-3">
                            <label class="form-label">Apostador</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['nome']) ?>"
                                   readonly>
                        </div>

                        <hr>

                        <!-- Concurso -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Concurso</label>

                                <!-- Visual -->
                                <input type="text"
                                       class="form-control"
                                       value="<?= $concursoAposta ?>"
                                       readonly>

                                <!-- Enviado -->
                                <input type="hidden"
                                       name="concurso"
                                       value="<?= $concursoAposta ?>">

                                <div class="form-text">
                                    Próximo concurso disponível para apostas
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Data do Sorteio</label>
                                <input type="text"
                                       class="form-control"
                                       value="A definir"
                                       readonly>
                            </div>
                        </div>

                        <hr>

                        <!-- Dezenas -->
                        <h6 class="mb-3">Escolha 15 dezenas (01 a 25)</h6>

                        <div class="row g-2">
                            <?php for ($i = 1; $i <= 15; $i++): ?>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small">
                                        D<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                                    </label>
                                    <input type="number"
                                           name="d<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"
                                           class="form-control"
                                           min="1"
                                           max="25"
                                           required>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end">
                            <button type="submit"
                                    class="btn btn-success"
                                    id="btnSalvar"
                                    disabled>
                                💾 Salvar Aposta
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<!-- VALIDAÇÃO EM TEMPO REAL -->
<script>
document.addEventListener('DOMContentLoaded', () => {

    const inputs = document.querySelectorAll('input[type="number"]');
    const btn = document.getElementById('btnSalvar');

    function validar() {
        let valores = [];
        let valido = true;

        inputs.forEach(i => i.classList.remove('is-valid', 'is-invalid'));

        inputs.forEach(input => {
            const v = parseInt(input.value);

            if (isNaN(v) || v < 1 || v > 25 || valores.includes(v)) {
                input.classList.add('is-invalid');
                valido = false;
            } else {
                input.classList.add('is-valid');
                valores.push(v);
            }
        });

        btn.disabled = !valido;
    }

    inputs.forEach(input => {
        input.addEventListener('input', validar);
        input.addEventListener('change', validar);
    });
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
