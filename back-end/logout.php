<?php
    session_start();

    $login = $_SESSION['usuario_id'];
    if(!$login){
        header('Location: index.php');
        exit;
    }

    // Limpa variáveis da sessão
    $_SESSION = [];

    // Remove o cookie PHPSESSID do navegador
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 3600,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destrói a sessão no servidor
    session_destroy();

    // Redireciona para o login
    header("Location: login.php");
    exit;
?>
