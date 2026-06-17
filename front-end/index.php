<?php
    session_start();

    $login = isset($_SESSION["usuario_id"]);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projeto Final Web I</title>
    <link rel="stylesheet" href="css/pages-styles.css">
    <script src="./scripts/pages.js" defer></script>

</head>

<body>
    <div class="page-header">
        <h1>Bem-vindo!</h1>
        <p>Este é um site.</p>
    </div>
        <h1>Bem-vindo<?= $login ? ", " . htmlspecialchars($_SESSION["usuario_nome"]) : "!" ?></h1>

    <?php if ($login): ?>
        <p>Você está autenticado no sistema.</p>
        <ul>
            <li><a href="../back-end/login/logout.php">Sair</a></li>
            <li><a href="./pages/liga.php">Ir para ligas</a></li>
        </ul>
    <?php else: ?>
        <p>Você não está logado.</p>
        <ul>
            <li><a href="../back-end/login/login.php">Fazer login</a></li>
            <li><a href="../back-end/login/cadastrar.php">Cadastrar-se</a></li>
        </ul>
    <?php endif; ?>
</body>