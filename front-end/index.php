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
    <link rel="stylesheet" href="css/pages.css">

</head>

<body class="home-page">
    <?php require_once __DIR__ . "/components/menu.php"; ?>
    <main class="home-shell">
        <section class="home-hero">
            <p class="home-kicker">CAFÉ SEM FRONTEIRAS</p>
            <h1>Bem-vindo<?= $login ? ", " . htmlspecialchars($_SESSION["usuario_nome"]) : "!" ?></h1>
            <p>Organize os pedidos, avance pelos cenários e conquiste sua posição na Liga.</p>

    <?php if ($login): ?>
            <div class="home-actions">
                <a class="home-button home-button-primary" href="./pages/game.php">Jogar agora</a>
                <a class="home-button" href="./pages/liga.php">Ir para Liga</a>
                <a class="home-button" href="../back-end/login/editar.php">Minha conta</a>
                <a class="home-button home-button-danger" href="../back-end/login/logout.php">Sair</a>
            </div>
    <?php else: ?>
            <div class="home-actions">
                <a class="home-button home-button-primary" href="../back-end/login/login.php">Fazer login</a>
                <a class="home-button" href="../back-end/login/cadastrar.php">Criar conta</a>
                <a class="home-button home-button-outline" href="./pages/tutorial.php">Ver tutorial</a>
            </div>
    <?php endif; ?>
        </section>
    </main>
</body>
