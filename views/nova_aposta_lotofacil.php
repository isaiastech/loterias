<?php
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
require_once '../vendor/autoload.php';

use class\Auth;
use class\Conexao;
// ===============================
// CONFIGURAÇÃO DE FUSO HORÁRIO E LOCALE
// ===============================

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
    <style>
      .lotofacil-grid{
          display:grid;
          grid-template-columns: repeat(5,1fr);
          gap:8px;
      }
      .dezena{
          height:55px;
          font-weight:bold;
          font-size:18px;
      }
      .dezena.selecionada{
          background:#198754;
          color:white;
      }
    </style>
</head>
<?php include('../header/header.php'); ?>

<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">    
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Cadastrar Aposta - Lotofácil</h5>
            <?php echo date('d/m/Y H:i:s');?>
        </div>
          <div class="card-body">
            <form method="POST" action="salvar_aposta_lotofacil.php">
                        <!-- CSRF -->
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <!-- Apostador -->
          <div class="mb-3">
            <label class="form-label">Apostador</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['nome']) ?>" readonly>
          </div>
<hr>
<!-- Concurso -->
<div class="row mb-3">
  <div class="col-md-4">
    <label class="form-label">Concurso</label>
<!-- Visual -->
    <input type="text" class="form-control" value="<?= $concursoAposta ?>" readonly>
<!-- Enviado -->
    <input type="hidden" name="concurso" value="<?= $concursoAposta ?>">
  <div class="form-text">
    Próximo concurso disponível para apostas
  </div>
  </div>
    <div class="col-md-4">
      <label class="form-label">Data do Sorteio</label>
        <input type="text" class="form-control" value="A definir" readonly>
    </div>
  </div>
<!-- Dezenas -->
 <hr>
<h6 class="mb-3">Escolha 15 dezenas</h6>
<div class="lotofacil-grid mb-3">
<?php for ($i = 1; $i <= 25; $i++): ?>
  <button type="button" class="btn btn-outline-secondary dezena" data-num="<?= $i ?>">
    <?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>
</button>
<?php endfor; ?>
</div>
<div class="mb-3">
    <small class="text-muted">
        Selecionadas: <span id="contador">0</span> / 15
    </small>
</div>
<!-- CAMPOS OCULTOS PARA ENVIAR -->
<div id="camposDezenas"></div>
<div class="d-flex justify-content-end">
  <button type="submit" class="btn btn-success" id="btnSalvar" disabled>
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

    const botoes = document.querySelectorAll('.dezena');
    const contador = document.getElementById('contador');
    const btnSalvar = document.getElementById('btnSalvar');
    const campos = document.getElementById('camposDezenas');

    let selecionados = [];

    botoes.forEach(btn => {

        btn.addEventListener('click', () => {

            const num = parseInt(btn.dataset.num);

            if (selecionados.includes(num)) {

                selecionados = selecionados.filter(n => n !== num);
                btn.classList.remove('selecionada');

            } else {

                if (selecionados.length >= 15) {
                    alert('Você só pode escolher 15 números.');
                    return;
                }

                selecionados.push(num);
                btn.classList.add('selecionada');
            }

            atualizar();
        });

    });

    function atualizar(){

        contador.innerText = selecionados.length;

        campos.innerHTML = "";

        selecionados.sort((a,b)=>a-b);

        selecionados.forEach((num,index)=>{

            let input = document.createElement("input");
            input.type="hidden";
            input.name="d"+String(index+1).padStart(2,"0");
            input.value=num;

            campos.appendChild(input);

        });

        btnSalvar.disabled = selecionados.length !== 15;

    }

});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
