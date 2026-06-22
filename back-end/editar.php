<?php
    session_start();

    $login = isset($_SESSION['usuario_id']);
    if(!$login){
        header("Location: /../front-end/index.php");
        exit;
    }

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Dados</title>
</head>
<body>
    <h1>Editando Dados</h1>
    <ul>
        <li><a href="editar-nome-email.php
        ">Editar Nome/Email</a></li>
        <li><a href="editar-senha.php
        ">Editar Senha</a></li>
        <li><a href="../../front-end/index.php">Voltar</a></li>
    </ul>
</body>
</html>