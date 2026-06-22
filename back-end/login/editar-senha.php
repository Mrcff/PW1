<?php
    session_start();
    require_once __DIR__ . "/../banco/conexao.php";

    $login = isset($_SESSION['usuario_id']);
    if(!$login){
        header("Location: /../../front-end/index.php");
        exit;
    }

    $erro = "";
 
    // Executa quando o usuário clica no botão de submit
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        // Coletando os dados dos campos
        $senha_atual     = trim($_POST["senha_atual"]     ?? "");
        $nova_senha      = trim($_POST["nova_senha"]      ?? "");
        $confirmar_senha = trim($_POST["confirmar_senha"] ?? "");

        // Validação de campos vazios
        if(empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)){
            $erro = "Preencha todos os campos";
        // Validação do tamanho da senha, sendo o mínimo 8 caracteres
        } elseif(strlen($nova_senha) < 8){
            $erro = "Senha muito pequena. Digite pelo menos 8 caracteres";
        // Validação de nova senha igual ao confirmar senha
        } elseif($nova_senha !== $confirmar_senha){
            $erro = "As senhas não conferem.";
        // Validação se a senha atual digitada é a mesma do usuário no BD
        } elseif(!empty($senha_atual)){
            $stmt = mysqli_prepare($conexao, "SELECT senha FROM usuarios WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);

            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);

                if($senha_usuario_bd = mysqli_fetch_assoc($result)){
                    if(!password_verify($senha_atual, $senha_usuario_bd["senha"])){
                        $erro = "Erro ao editar: Senha Atual inválida";
                    }
                }
            }else{
                $erro = "Erro ao editar: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }

        // Se não tiver nenhum erro, altera as senhas no BD
        if(empty($erro)){
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET senha = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $senha_hash, $_SESSION["usuario_id"]);

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_close($stmt);
                mysqli_close($conexao);
                header("Location: editar.php");
                exit;
            }else{
                $erro = "Erro ao editar: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conexao);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editando Senha</title>
</head>
<body>
    <h1>Editando Senha</h1>
    <?php if ($erro): ?>
        <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>
    <form action="editar-senha.php" method="post">
        <label for="senha_atual">Senha Atual:</label><br>
        <input type="password" id="senha_atual" name="senha_atual" required><br><br>

        <label for="nova_senha">Nova senha:</label><br>
        <input type="password" id="nova_senha" name="nova_senha" required><br><br>

        <label for="confirmar_senha">Confirmar senha:</label><br>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required><br><br>

        <button type="submit">Salvar Alterações</button>
    </form>
    <a href="editar.php">Voltar</a>
</body>
</html>