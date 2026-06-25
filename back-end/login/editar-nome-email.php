<?php
    session_start();
    require_once __DIR__ . "/../banco/conexao.php";

    // Verifica se o usuário está logado
    $login = isset($_SESSION['usuario_id']);
    if(!$login){
        header("Location: /../../front-end/index.php");
        exit;
    }

    $erro = "";

    // Busca os dados quando o usuário vem de editar.php
    if($_SERVER["REQUEST_METHOD"] === "GET"){
        $nome = "";
        $email = "";
        $senha = "";
    
        $stmt = mysqli_prepare($conexao, "SELECT nome, email, senha FROM usuarios WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['usuario_id']);

        if(!mysqli_stmt_execute($stmt)){
            $erro = "Erro ao editar nome ou email: " . mysqli_stmt_error(($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
    
        if($usuario = mysqli_fetch_assoc($result)){
            $nome = $usuario['nome'];
            $email = $usuario['email'];
        }
        
        mysqli_stmt_close($stmt);
    }

    // Executa quando o usuário clica no butão submit do forms
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        $nome  = trim($_POST['nome']  ?? "")  ;
        $email = trim($_POST['email'] ?? "") ;
        $senha = trim($_POST['senha'] ?? "") ;

        // Validação de campos vazios
        if(empty($nome) || empty($email) || empty($senha)){
            $erro = "Preencha todos os campos.";
        // Validação de nome maior que o BD suporta
        } elseif(strlen($nome) > 255){
            $erro = "Nome muito grande! Digite um nome com menos caracteres.";
        // Validação de email válido
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $erro = "Erro ao editar: Por favor, insira um email válido. Por exemplo: seu-email@dominio.com";
        // Validação de email existente em outra conta
        } else if(!empty($email)){
            $stmt_email = mysqli_prepare($conexao, "SELECT EXISTS(SELECT 1 FROM usuarios WHERE email = ? AND id != ?) AS email_em_uso;");

            mysqli_stmt_bind_param($stmt_email, "si", $email, $_SESSION['usuario_id']);

            if(mysqli_stmt_execute($stmt_email)){
                $result = mysqli_stmt_get_result($stmt_email);
                if($email_existente = mysqli_fetch_assoc($result)){
                    if($email_existente['email_em_uso']){
                        $erro = "Erro ao alterar email: Já existe uma conta associada a este endereço de e-mail.";
                    }
                }
            } else{
                $erro = "Erro ao editar: " . mysqli_stmt_error($stmt_email);
            }
            mysqli_stmt_close($stmt_email);
        } 

        // Validação de senha igual a senha do usuário no BD
        if(!empty($senha)){
            $stmt = mysqli_prepare($conexao, "SELECT senha FROM usuarios WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
    
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if($senha_usuario_bd = mysqli_fetch_assoc($result)){
                    if(!password_verify($senha, $senha_usuario_bd['senha'])){
                        $erro = "Erro ao editar: Senha inválida";
                    }
                }
            }else{
                $erro = "Erro ao editar: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        }

        // Se não tiver nenhum erro, atualiza os dados
        if(empty($erro)){
            $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $nome, $email, $_SESSION['usuario_id']);
            
            if(mysqli_stmt_execute($stmt)){
                $_SESSION['usuario_nome'] = $nome;
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
    <title>Editando Nome/Email</title>
    <link rel="stylesheet" href="../../front-end/css/auth.css">
    <link rel="stylesheet" href="../../front-end/css/pages.css">
    <script src="../../front-end/scripts/pages-script.js" defer></script>
</head>
<body class="auth-page">
    <main class="auth-shell">
    <section class="auth-card">
    <h1>Dados pessoais</h1>
    <p class="auth-subtitle">Confirme sua senha atual para salvar as alterações.</p>
    <?php if ($erro): ?>
        <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
    <?php endif; ?>
    <form class="auth-form" method="post" action="editar-nome-email.php">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="senha">Senha atual</label>
        <input type="password" id="senha" name="senha" required>

        <button class="auth-button" type="submit">Salvar alterações</button>
    </form>
    <p class="auth-links"><a href="editar.php">Voltar para minha conta</a></p>
    </section>
    </main>
</body>
</html>
