<!doctype html>
<html lang="pt-br">
<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>

<body class="text-center">

<div class="form-signin bg-light">

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger">
            Email ou senha inválidos
        </div>
    <?php endif; ?>

<form method="POST" action="auth/login_action.php">
        <img class="mb-4" src="image/logo.png" alt="" width="150">
        <h1 class="h3 mb-3 fw-normal">Faça login</h1>

        <div class="form-floating">
            <input type="email"
                   class="form-control"
                   name="email"
                   id="floatingInput"
                   placeholder="name@example.com"
                   required>
            <label for="floatingInput">Email</label>
        </div>

        <div class="form-floating mt-2">
            <input type="password"
                   class="form-control"
                   name="senha"
                   id="floatingPassword"
                   placeholder="Password"
                   required>
            <label for="floatingPassword">Senha</label>
        </div>

        <div class="checkbox mb-3 mt-2">
            <label>
                <input type="checkbox" name="lembrar"> Lembrar-me
            </label>
        </div>
        <button class="w-100 btn btn-lg btn-dark" type="submit">
            Entrar
        </button>
        <p class="mt-5 mb-3 text-muted">
            isaiasTech &copy; <?php echo date('Y') ?>
        </p>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
