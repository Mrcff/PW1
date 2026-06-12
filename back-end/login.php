<?php
    session_start();

    // Se já estiver logado, redireciona para o painel
    if (isset($_SESSION["usuario_id"])) {
        header("Location: index.php");
        exit;
    }

    $erro = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        require_once __DIR__ . "/conexao.php";

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

                    header("Location: index.php");
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
</head>
<body>
    <h1>Login</h1>

    <?php if ($erro): ?>
        <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" required><br><br>

        <button type="submit">Entrar</button>
    </form>

    <p><a href="index.php">Voltar</a> | <a href="cadastrar.php">Cadastrar-se</a></p>
</body>
</html>
