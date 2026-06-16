<?php
    session_start();

    $login = isset($_SESSION["usuario_id"]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Login</title>
</head>
<body>
    <h1>Bem-vindo<?= $login ? ", " . htmlspecialchars($_SESSION["usuario_nome"]) : "!" ?></h1>

    <?php if ($login): ?>
        <p>Você está autenticado no sistema.</p>
        <ul>
            <li><a href="logout.php">Sair</a></li>
        </ul>
    <?php else: ?>
        <p>Você não está logado.</p>
        <ul>
            <li><a href="login.php">Fazer login</a></li>
            <li><a href="cadastrar.php">Cadastrar-se</a></li>
        </ul>
    <?php endif; ?>
</body>
</html>