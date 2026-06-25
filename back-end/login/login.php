<?php
    session_start();

    // Se já estiver logado, redireciona para o painel
    if (isset($_SESSION["usuario_id"])) {
        header("Location: ../../front-end/index.php");
        exit;
    }

    $erro = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        require_once __DIR__ . "/../banco/conexao.php";

        $email = trim($_POST["email"] ?? "");
        $senha = trim($_POST["senha"] ?? "");

        if (empty($email) || empty($senha)) {
            $erro = "Preencha todos os campos.";
        } else {
            // Prepared Statement para buscar o usuário pelo email
            $stmt = mysqli_prepare($conexao, "SELECT id, nome, senha FROM usuarios WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($usuario = mysqli_fetch_assoc($result)) {
                // Verifica a senha com password_verify()
                if (password_verify($senha, $usuario["senha"])) {
                    // Login bem-sucedido — regenera ID e grava sessão
                    session_regenerate_id(true);
                    $_SESSION["usuario_id"]   = $usuario["id"];
                    $_SESSION["usuario_nome"] = $usuario["nome"];

                    mysqli_stmt_close($stmt);
                    mysqli_close($conexao);

                    header("Location: ../../front-end/index.php");
                    exit;
                }
            }

            // Login falhou (email não encontrado OU senha incorreta)
            $erro = "Email ou senha inválidos.";
            mysqli_stmt_close($stmt);
            
        mysqli_close($conexao);
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../../front-end/css/auth.css">
    <link rel="stylesheet" href="../../front-end/css/auth.css">
    <link rel="stylesheet" href="../../front-end/css/pages.css">
    <script src="../../front-end/scripts/pages-script.js" defer></script>
</head>
<body class="auth-page">
    <?php require_once __DIR__ . "/../../front-end/components/menu.php"; ?>
    <main class="auth-shell">
    <section class="auth-card">
    <h1>Entrar</h1>
    <p class="auth-subtitle">Acesse sua conta para continuar jogando.</p>

    <?php if ($erro): ?>
        <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form class="auth-form" method="post" action="login.php">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <button class="auth-button" type="submit">Entrar</button>
    </form>

    <p class="auth-links"><a href="../../front-end/index.php">Voltar ao início</a><a href="cadastrar.php">Criar conta</a></p>
    </section>
    </main>
</body>
</html>
