<?php
    require_once __DIR__ . "/conexao.php";

    $erro   = "";
    $nome   = "";
    $email  = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nome            = trim($_POST["nome"])            ?? "";
        $email           = trim($_POST["email"])           ?? "";
        $senha           = trim($_POST["senha"])           ?? "";
        $confirmar_senha = trim($_POST["confirmar_senha"]) ?? "";

        // Validação se os campos estão vazios
        if (empty(trim($nome)) || empty(trim($email)) || empty($senha) || empty($confirmar_senha)) {
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
                mysqli_stmt_close($stmt);
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
</head>
<body>
    <h1>Cadastro</h1>

    <?php if ($erro): ?>
        <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>

    <form method="post" action="cadastrar.php">
        <label for="nome">Nome:</label><br>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br><br>

        <label for="senha">Senha:</label><br>
        <input type="password" id="senha" name="senha" required><br><br>

        <label for="confirmar_senha">Confirmação da Senha:</label><br>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required><br><br>

        <button type="submit">Criar usuário</button>
    </form>

    <p><a href="index.php">Voltar</a> | <a href="login.php">Fazer login</a></p>
</body>
</html>
