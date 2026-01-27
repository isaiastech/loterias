<?php
date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
$dataHora = date('Y-m-d H:i:s');
require_once '../vendor/autoload.php';

use class\Auth;
use class\Conexao;

// ===============================
// 1️⃣ AUTENTICAÇÃO
// ===============================
session_start();

$auth = new Auth();
$auth->requireAuth();
$user = $auth->user();

// ===============================
// 2️⃣ BLOQUEIA ACESSO DIRETO
// ===============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método não permitido.');
}

// ===============================
// 3️⃣ CSRF
// ===============================
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    die('Falha de segurança (CSRF).');
}

// Token usado → invalida
unset($_SESSION['csrf_token']);

// ===============================
// 4️⃣ CONEXÃO
// ===============================
$db = new Conexao();

// ===============================
// 5️⃣ CONCURSO (REGRA CORRETA)
// ===============================
$concursoPost = (int)($_POST['concurso'] ?? 0);

if ($concursoPost <= 0) {
    die('Concurso inválido.');
}

// Busca o último concurso sorteado
$sqlUltimo = "
    SELECT concurso
    FROM lotofacil
    ORDER BY concurso DESC
    LIMIT 1
";

$resUltimo = $db->getResultFromQuery($sqlUltimo);
$ultimo = $resUltimo->fetch_assoc();

if (!$ultimo) {
    die('Nenhum concurso disponível.');
}

$ultimoConcurso = (int)$ultimo['concurso'];
$concursoPermitido = $ultimoConcurso + 1;

// 🔒 VALIDAÇÃO DEFINITIVA
if ($concursoPost !== $concursoPermitido) {
    die('Concurso não disponível para apostas.');
}

// ===============================
// 6️⃣ DEZENAS
// ===============================
$dezenas = [];

for ($i = 1; $i <= 15; $i++) {
    $campo = 'd' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $valor = (int)($_POST[$campo] ?? 0);

    if ($valor < 1 || $valor > 25) {
        die("Dezena inválida ({$campo}).");
    }

    $dezenas[] = $valor;
}

// ===============================
// 7️⃣ NÃO PERMITIR REPETIÇÃO
// ===============================
if (count(array_unique($dezenas)) !== 15) {
    die('Não é permitido repetir dezenas.');
}

// Ordena padrão Lotofácil
sort($dezenas);

// ===============================
// 8️⃣ LIMITE DE APOSTAS POR USUÁRIO
// ===============================
$sqlLimite = "
    SELECT COUNT(*) AS total
    FROM lotofacil_apostas
    WHERE concurso = ?
      AND apostador = ?
";

$resLimite = $db->getResultFromQuery($sqlLimite, [
    $concursoPermitido,
    $user['nome']
]);

$dadosLimite = $resLimite->fetch_assoc();

if ($dadosLimite['total'] >= 10) {
    die('Você já atingiu o limite de 10 apostas para este concurso.');
}

// ===============================
// 9️⃣ INSERT DA APOSTA
// ===============================
$sqlInsert = "
INSERT INTO lotofacil_apostas (
    concurso,
    apostador,
    d01, d02, d03, d04, d05,
    d06, d07, d08, d09, d10,
    d11, d12, d13, d14, d15,
    created_at
) VALUES (
    ?, ?,
    ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?,
    ?, ?, ?, ?, ?,
    ?
)
";

$params = array_merge(
    [$concursoPermitido, $user['nome']],
    $dezenas,
    [$dataHora]
);

$db->getResultFromQuery($sqlInsert, $params);

// ===============================
// 🔟 REDIRECIONAMENTO
// ===============================
header("Location: nova_aposta_lotofacil.php?sucesso=1");
exit;
