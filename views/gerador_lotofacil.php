<?php
require_once '../vendor/autoload.php';

use class\Auth;
use class\Conexao;

$auth = new Auth();
$auth->requireAuth();

$user = $auth->user();
$db   = new Conexao();

$qtdJogos = isset($_POST['qtd']) ? (int)$_POST['qtd'] : 5;

$jogos = [];
$frequencia = [];
$quentes = [];
$frias = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$sql = "SELECT * FROM lotofacil";
$query = $db->getResultFromQuery($sql);

$todas = [];
$concursos = [];

while ($row = $query->fetch_assoc()) {

    $linha = [];

    for ($i=1;$i<=15;$i++) {

        $campo = 'd'.str_pad($i,2,'0',STR_PAD_LEFT);
        $n = (int)$row[$campo];

        $linha[] = $n;
        $todas[] = $n;
    }

    $concursos[] = $linha;
}

$ultimo = end($concursos);

# -------------------------
# Frequência
# -------------------------

$frequencia = array_count_values($todas);

arsort($frequencia);
$quentes = array_slice(array_keys($frequencia),0,12);

asort($frequencia);
$frias = array_slice(array_keys($frequencia),0,8);

# -------------------------
# FUNÇÕES
# -------------------------

function validarParImpar($jogo){

    $pares = count(array_filter($jogo,fn($n)=>$n%2==0));

    return ($pares >= 7 && $pares <= 8);
}

function validarLinhas($jogo){

    $linhas=[
        range(1,5),
        range(6,10),
        range(11,15),
        range(16,20),
        range(21,25)
    ];

    foreach($linhas as $l){

        $q=count(array_intersect($jogo,$l));

        if($q<2 || $q>4){
            return false;
        }
    }

    return true;
}

function validarSoma($jogo){

    $soma = array_sum($jogo);

    return ($soma >= 170 && $soma <= 220);
}

function validarSequencias($jogo){

    $seq = 0;

    for($i=0;$i<count($jogo)-1;$i++){

        if($jogo[$i]+1 == $jogo[$i+1]){
            $seq++;
        }
    }

    return ($seq <= 5);
}

# -------------------------
# GERADOR
# -------------------------

$jogos=[];

for($j=0;$j<$qtdJogos;$j++){

    do{

        $jogo=[];

        # repetição do último concurso
        $rep = $ultimo;
        shuffle($rep);

        $jogo = array_slice($rep,0,9);

        # quentes
        $q = $quentes;
        shuffle($q);

        $jogo = array_merge($jogo,array_slice($q,0,3));

        # frias
        $f = $frias;
        shuffle($f);

        $jogo = array_merge($jogo,array_slice($f,0,2));

        # completa até 15
        $pool = range(1,25);
        shuffle($pool);

        foreach($pool as $n){

            if(count($jogo) >= 15) break;

            if(!in_array($n,$jogo)){
                $jogo[] = $n;
            }
        }

        $jogo = array_unique($jogo);

        while(count($jogo) < 15){

            $n = rand(1,25);

            if(!in_array($n,$jogo)){
                $jogo[] = $n;
            }
        }

        sort($jogo);

    }while(
        !validarParImpar($jogo) ||
        !validarLinhas($jogo) ||
        !validarSoma($jogo) ||
        !validarSequencias($jogo)
    );

    $jogos[]=$jogo;
}

}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerador Lotofácil</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css">

</head>
<body class="container mt-4">

<h1>Dashboard</h1>
Olá, <strong><?= htmlspecialchars($user['nome']) ?></strong>

<hr>

<h3>🎯 Gerador Automático – Lotofácil</h3>

<form method="post" class="row g-3 mb-4">

<div class="col-auto">
<select name="qtd" class="form-select">

<?php foreach ([3,5,7,10] as $q): ?>

<option value="<?= $q ?>" <?= $qtdJogos == $q ? 'selected' : '' ?>>
<?= $q ?> jogos
</option>

<?php endforeach; ?>

</select>
</div>

<div class="col-auto">
<button class="btn btn-success">Gerar Jogos</button>
</div>

</form>

<?php if (!empty($jogos)): ?>

<h5>📋 Jogos Gerados</h5>

<?php foreach ($jogos as $i => $jogo): ?>

<div class="alert alert-success">

<strong>Jogo <?= $i + 1 ?>:</strong>

<?= implode(
' - ',
array_map(
fn($n)=>str_pad($n,2,'0',STR_PAD_LEFT),
$jogo
)
); ?>

</div>

<?php endforeach; ?>

<hr>

<h5>📊 Ranking das Dezenas Mais Sorteadas</h5>

<div class="row">

<?php foreach ($frequencia as $dezena => $qt): ?>

<div class="col-3 col-md-2 mb-2">

<span class="badge bg-primary w-100">
<?= str_pad($dezena,2,'0',STR_PAD_LEFT) ?> → <?= $qt ?>
</span>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<hr>

<h5>🔥 Dezenas Quentes</h5>

<div class="row mb-3">

<?php foreach ($quentes as $d): ?>

<div class="col-2 mb-2">
<span class="badge bg-danger w-100">
<?= str_pad($d,2,'0',STR_PAD_LEFT) ?>
</span>
</div>

<?php endforeach; ?>

</div>

<h5>❄️ Dezenas Frias</h5>

<div class="row">

<?php foreach ($frias as $d): ?>

<div class="col-2 mb-2">
<span class="badge bg-info w-100">
<?= str_pad($d,2,'0',STR_PAD_LEFT) ?>
</span>
</div>

<?php endforeach; ?>

</div>

<a href="index.php" class="btn btn-secondary mt-4">Voltar</a>

</body>
</html>