<?php
    session_start();

    $login = isset($_SESSION['usuario_id']);
    if(!$login){
        header("Location: /../../front-end/index.php");
        exit;
    }

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Dados</title>
    <link rel="stylesheet" href="../../front-end/css/auth.css">
    <link rel="stylesheet" href="../../front-end/css/pages.css">
    <script src="../../front-end/scripts/pages-script.js" defer></script>
</head>
<body class="auth-page">
    <main class="auth-shell">
    <section class="auth-card">
    <h1>Minha conta</h1>
    <p class="auth-subtitle">Escolha quais informações deseja alterar.</p>
    <nav class="auth-menu" aria-label="Opções de edição">
        <a class="auth-link-button" href="editar-nome-email.php">Editar nome e email</a>
        <a class="auth-link-button" href="editar-senha.php">Alterar senha</a>
        <a class="auth-link-button auth-secondary" href="../../front-end/index.php">Voltar ao início</a>
    </nav>
    </section>
    </main>
</body>
</html>
