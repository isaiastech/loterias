<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3">
    <a class="navbar-brand fw-bold" href="">
        🎯 Loterias
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="/views/analise_lotofacil.php">Analizar Sorteio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/views/cadastro.php">Cadastrar usuário</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="menuOutros" data-bs-toggle="dropdown">
                    Outras Loterias
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Mega-Sena</a></li>
                    <li><a class="dropdown-item" href="#">Quina</a></li>
                    <li><a class="dropdown-item" href="#">Lotomania</a></li>
                </ul>
            </li>

        </ul>
        <div class="d-flex">
            <a href="/auth/logout.php" class="btn btn-outline-light btn-sm">Sair</a>
        </div>

    </div>
</nav>
