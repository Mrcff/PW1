<?php
    require_once __DIR__ . "/../banco/conexao.php";

    $erro   = "";
    $nome   = "";
    $email  = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nome            = trim($_POST["nome"]            ?? "");
        $email           = trim($_POST["email"]           ?? "");
        $senha           = trim($_POST["senha"]           ?? "");
        $confirmar_senha = trim($_POST["confirmar_senha"] ?? "");

        // Validação se os campos estão vazios
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
            $erro = "Preencha todos os campos.";
        // Validação do tamanho de caracteres no campo nome
        } elseif (strlen($nome) > 255){
            $erro = "Nome muito grande! Digite um nome com menos caracteres.";
        // Validação do tamanho da senha
        } elseif (strlen($senha) < 8){
            $erro = "Senha muito pequena. Digite pelo menos 8 caracteres";
        // Validação de confirmação de senha
        } elseif ($senha !== $confirmar_senha) {
            $erro = "As senhas não conferem.";
        // Validação do email
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $erro = "Erro ao cadastrar: Por favor, insira um email válido. Por exemplo: seu-email@dominio.com";
        // Verificando se email digitado já existe no BD
        } elseif (!empty($email)){
            $stmt_email = mysqli_prepare($conexao, "SELECT EXISTS(SELECT 1 FROM usuarios WHERE email = ?) AS email_existe;"); // Retorna 1 se email existe, e 0 se email NÃO existe
            mysqli_stmt_bind_param($stmt_email, "s", $email);
            if(mysqli_stmt_execute($stmt_email)){
                $result = mysqli_stmt_get_result($stmt_email);
                if($usuario = mysqli_fetch_assoc($result)){
                    if($usuario["email_existe"]){
                        $erro = "Erro ao cadastrar: Já existe uma conta associada a este endereço de e-mail.";
                    }
                }
            }else{
                $erro = "Erro ao cadastrar: " . mysqli_stmt_error($stmt_email);
            }

            mysqli_stmt_close($stmt_email);
        }
        //Se não tiver nenhum erro, executa o código de cadastro
        if(empty($erro)){
            // NUNCA armazene a senha em texto plano — use password_hash()
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conexao, "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $senha_hash);

            if (mysqli_stmt_execute($stmt)) {
                $usuario_id = mysqli_insert_id($conexao);
                mysqli_stmt_close($stmt);
                require_once __DIR__ . "/../liga/liga-oficial.php";
                $ligaJogoId = garantirLigaOficial($conexao);
                incluirUsuarioNaLigaOficial($conexao, $ligaJogoId, $usuario_id);
                mysqli_close($conexao);
                header("Location: login.php");
                exit;
            } else {
                $erro = "Erro ao cadastrar: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="../../front-end/css/auth.css">
    <link rel="stylesheet" href="../../front-end/css/pages.css">
    <script src="../../front-end/scripts/pages-script.js" defer></script>
</head>
<body class="auth-page">
    <main class="auth-shell">
  
    <section class="auth-card">
    <h1>Criar conta</h1>
    <p class="auth-subtitle">Cadastre-se para salvar sua pontuação e participar das ligas.</p>

    <?php if ($erro): ?>
        <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form class="auth-form" method="post" action="cadastrar.php">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <label for="confirmar_senha">Confirmação da senha</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required>

        <button class="auth-button" type="submit">Criar conta</button>
    </form>

    <p class="auth-links"><a href="../../front-end/index.php">Voltar ao início</a><a href="login.php">Já tenho conta</a></p>
    </section>
    </main>
</body>
</html>
